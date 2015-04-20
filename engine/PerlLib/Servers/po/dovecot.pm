#!/usr/bin/perl

# Selity - When virtual hosting becomes scalable
# Copyright 2012-2014 by Selity
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# @category		Selity
# @copyright	2012 by Selity | http://selity.org
# @author		Daniel Andreca <sci2tech@gmail.com>
# @link			http://selity.org Selity Home Site
# @license		http://www.gnu.org/licenses/gpl-2.0.html GPL v2

package Servers::po::dovecot;

use strict;
use warnings;
use Selity::Debug;
use Data::Dumper;

use vars qw/@ISA/;

@ISA = ('Common::SingletonClass');
use Common::SingletonClass;

sub _init{

	my $self		= shift;
	$self->{cfgDir}	= "$main::selityConfig{'CONF_DIR'}/dovecot";
	$self->{bkpDir}	= "$self->{cfgDir}/backup";
	$self->{wrkDir}	= "$self->{cfgDir}/working";

	my $conf		= "$self->{cfgDir}/dovecot.data";

	tie %self::dovecotConfig, 'Selity::Config','fileName' => $conf;

	0;
}

sub preinstall{

	use Servers::po::dovecot::installer;

	my $self	= shift;
	my $rs		= Servers::po::dovecot::installer->new()->registerHooks();

	$rs;
}

sub install{

	use Servers::po::dovecot::installer;

	my $self	= shift;
	my $rs		= Servers::po::dovecot::installer->new()->install();

	$rs;
}

sub uninstall{

	use Servers::po::dovecot::uninstaller;

	my $self	= shift;
	my $rs		= Servers::po::dovecot::uninstaller->new()->uninstall();
	$rs |= $self->restart();

	$rs;
}

sub postinstall{

	my $self	= shift;
	$self->{restart} = 'yes';

	0;
}

sub restart{

	my $self = shift;
	my ($rs, $stdout, $stderr);

	use Selity::Execute;

	# Reload config
	$rs = execute("$self::dovecotConfig{'CMD_DOVECOT'} restart", \$stdout, \$stderr);
	debug("$stdout") if $stdout;
	debug("$stderr") if $stderr && !$rs;
	error("$stderr") if $stderr && $rs;
	return $rs if $rs;

	0;
}

sub getTraffic{

	use Selity::Execute;
	use Selity::File;
	use Selity::Config;
	use Tie::File;

	my $self		= shift;
	my $who			= shift;
	my $dbName		= "$self->{wrkDir}/log.db";
	my $logFile		= "$main::selityConfig{TRAFF_LOG_DIR}/mail.log";
	my $wrkLogFile	= "$main::selityConfig{LOG_DIR}/mail.po.log";
	my ($rv, $rs, $stdout, $stderr);

	##only if files was not aleady parsed this session
	unless($self->{logDb}){
		#use a small conf file to memorize last line readed and his content
		tie %{$self->{logDb}}, 'Selity::Config','fileName' => $dbName, noerrors => 1;
		##first use? we zero line and content
		$self->{logDb}->{line}		= 0 unless $self->{logDb}->{line};
		$self->{logDb}->{content}	= '' unless $self->{logDb}->{content};
		my $lastLineNo	= $self->{logDb}->{line};
		my $lastLine	= $self->{logDb}->{content};
		##copy log file
		$rs = Selity::File->new(filename => $logFile)->copyFile($wrkLogFile) if -f $logFile;
		#retunt 0 traffic if we fail
		return 0 if $rs;
		#link log file to array
		tie my @content, 'Tie::File', $wrkLogFile or return 0;
		#save last line
		$self->{logDb}->{line}		= $#content;
		$self->{logDb}->{content}	= @content[$#content];
		#test for logratation
		if(@content[$lastLineNo] && @content[$lastLineNo] eq $lastLine){
			## No logratation ocure. We zero already readed files
			(tied @content)->defer;
			@content = @content[$lastLineNo + 1 .. $#content];
			(tied @content)->flush;
		}

		# Read log file
		my $content = Selity::File->new(filename => $wrkLogFile)->get() || '';

		# IMAP
		#Oct 15 13:50:31 daniel dovecot: imap(ndmn@test1.eu.bogus): Disconnected: Logged out bytes=235/1032
		# 1   2     3      4      5                6                      7          8    9      10
		while($content =~ m/^.*imap\([^\@]+\@([^\)]+).*Logged out bytes=(\d+)\/(\d+)$/mg){
					# date time   mailfrom @ domain                    size / size
					#                           1                       2       3
			$self->{traff}->{$1} += $2 + $3 if $1 && defined $2 && defined $3 && ($2+$3);
			debug("Traffic for $1 is $self->{traff}->{$1} (added IMAP traffic: ". ($2 + $3).")") if $1 && defined $2 && defined $3 && ($2+$3);
		}

		# POP
		#Oct 15 14:23:39 daniel dovecot: pop3(ndmn@test1.eu.bogus): Disconnected: Logged out top=0/0, retr=1/533, del=0/1, size=517
		# 1   2     3      4      5                6                      7          8    9      10       11        12       13
		while($content =~ m/^.*pop3\([^\@]+\@([^\)]+).*Logged out .* retr=(\d+)\/(\d+).*$/mg){
					# date time   mailfrom @ domain                    size / size
					#                           1                       2       3
			$self->{traff}->{$1} += $2 + $3 if $1 && defined $2 && defined $3 && ($2+$3);
			debug("Traffic for $1 is $self->{traff}->{$1} (added POP traffic: ". ($2 + $3).")") if $1 && defined $2 && defined $3 && ($2+$3);
		}
	}
	$self->{traff}->{$who} ? $self->{traff}->{$who} : 0;
}

END{

	my $endCode	= $?;
	my $self	= Servers::po::dovecot->new();
	my $wrkLogFile	= "$main::selityConfig{LOG_DIR}/mail.po.log";
	my $rs		= 0;

	$rs |= $self->restart() if $self->{restart} && $self->{restart} eq 'yes';
	$rs |= Selity::File->new(filename => $wrkLogFile)->delFile() if -f $wrkLogFile;

	$? = $endCode || $rs;
}

1;
