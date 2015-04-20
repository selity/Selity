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

package Servers::ftpd::proftpd;

use strict;
use warnings;
use Selity::Debug;
use Data::Dumper;

use vars qw/@ISA/;

@ISA = ('Common::SingletonClass');
use Common::SingletonClass;

sub _init{
	my $self	= shift;

	$self->{cfgDir} = "$main::selityConfig{'CONF_DIR'}/proftpd";
	$self->{bkpDir} = "$self->{cfgDir}/backup";
	$self->{wrkDir} = "$self->{cfgDir}/working";
	$self->{tplDir} = "$self->{cfgDir}/parts";

	$self->{commentChar} = '#';

	tie %self::proftpdConfig, 'Selity::Config','fileName' => "$self->{cfgDir}/proftpd.data";
	$self->{$_} = $self::proftpdConfig{$_} foreach(keys %self::proftpdConfig);
}

sub install{

	use Servers::ftpd::proftpd::installer;

	my $self	= shift;
	my $rs		= 0;
	$rs |= Servers::ftpd::proftpd::installer->new()->install();

	$rs;
}

sub uninstall{

	use Servers::ftpd::proftpd::uninstaller;

	my $self	= shift;
	my $rs		= 0;
	$rs |= Servers::ftpd::proftpd::uninstaller->new()->uninstall();
	$rs |= $self->restart();

	$rs;
}

sub postinstall{

	my $self	= shift;
	$self->{restart} = 'yes';

	0;
}

sub registerPreHook{

	my $self		= shift;
	my $fname		= shift;
	my $callback	= shift;

	my $installer	= Servers::ftpd::proftpd::installer->new();

	debug("Register pre hook to $fname on installer")
		if (ref $callback eq 'CODE' && $installer->can($fname));
	push (@{$installer->{preCalls}->{fname}}, $callback)
		if (ref $callback eq 'CODE' && $installer->can($fname));

	debug("Register pre hook to $fname")
		if (ref $callback eq 'CODE' && $self->can($fname));
	push (@{$self->{preCalls}->{fname}}, $callback)
		if (ref $callback eq 'CODE' && $self->can($fname));

	0;
}

sub registerPostHook{

	my $self		= shift;
	my $fname		= shift;
	my $callback	= shift;

	my $installer	= Servers::ftpd::proftpd::installer->new();

	debug("Register post hook to $fname on installer")
		if (ref $callback eq 'CODE' && $installer->can($fname));
	push (@{$installer->{postCalls}->{$fname}}, $callback)
		if (ref $callback eq 'CODE' && $installer->can($fname));

	debug("Register post hook to $fname")
		if (ref $callback eq 'CODE' && $self->can($fname));
	push (@{$self->{postCalls}->{$fname}}, $callback)
		if (ref $callback eq 'CODE' && $self->can($fname));

	0;
}

sub restart{

	my $self = shift;
	my ($rs, $stdout, $stderr);

	use Selity::Execute;

	# Reload config
	$rs = execute("$self->{CMD_FTPD} restart", \$stdout, \$stderr);
	debug("$stdout") if $stdout;
	debug("$stderr") if $stderr && !$rs;
	error("$stderr") if $stderr && $rs;
	return $rs if $rs;

	0;
}

sub addDmn{

	use Selity::File;
	use Selity::Templator;

	my $self	= shift;
	my $data	= shift;
	my $rs		= 0;

	local $Data::Dumper::Terse = 1;
	debug("Data: ". (Dumper $data));

	my $errmsg = {
		'FILE_NAME'	=> 'You must supply a file name!',
		'PATH'		=> 'you must supply mount point!'
	};

	foreach(keys %{$errmsg}){
		error("$errmsg->{$_}") unless $data->{$_};
		return 1 unless $data->{$_};
	}

	Selity::File->new(
		filename => "$self::proftpdConfig{FTPD_CONF_DIR}/$data->{FILE_NAME}"
	)->copyFile( "$self->{bkpDir}/$data->{FILE_NAME}.".time ) and $rs = 1
	if -f "$self::proftpdConfig{FTPD_CONF_DIR}/$data->{FILE_NAME}";

	my $file	= Selity::File->new( filename => "$self->{tplDir}/proftpd.conf.tpl");
	my $content	= $file->get();

	if(!$content){
		error("Can not read $self->{tplDir}/proftpd.conf.tpl");
		return 1;
	}

	$content	= process({PATH => $data->{PATH}}, $content);
	$file	= Selity::File->new( filename => "$self->{wrkDir}/$data->{FILE_NAME}");

	$file->set($content);

	$rs |=	$file->save();
	$rs |=	$file->mode(0644);
	$rs |=	$file->owner(
				$main::selityConfig{'ROOT_USER'},
				$main::selityConfig{'ROOT_GROUP'}
			);
	$rs |= $file->copyFile("$self::proftpdConfig{FTPD_CONF_DIR}/$data->{FILE_NAME}");

	$rs;
}

sub delDmn{

	use Selity::File;
	use Selity::Templator;

	my $self	= shift;
	my $data	= shift;
	my $rs		=0 ;

	local $Data::Dumper::Terse = 1;
	debug("Data: ". (Dumper $data));

	my $errmsg = {
		'FILE_NAME'	=> 'You must supply a file name!'
	};

	foreach(keys %{$errmsg}){
		error("$errmsg->{$_}") unless $data->{$_};
		return 1 unless $data->{$_};
	}

	Selity::File->new(
		filename => "$self::proftpdConfig{FTPD_CONF_DIR}/$data->{FILE_NAME}"
	)->delFile() and $rs = 1;

	$rs;
}

sub addSub{
	my $self = shift;
	return $self->addDmn(@_);
}

sub delSub{
	my $self = shift;
	return $self->delDmn(@_);
}

sub getTraffic{

	use Selity::File;

	my $self	= shift;
	my $who		= shift;
	my $trfFile	= "$main::selityConfig{TRAFF_LOG_DIR}/$self::proftpdConfig{FTP_TRAFF_LOG}";

	unless(exists $self->{logDb}){

		$self->{logDb} = {};
		my $rs = Selity::File->new(filename => $trfFile)->moveFile("$trfFile.old") if -f $trfFile;
		if($rs){
			delete $self->{logDb};
			return 0;
		}
		if(-f "$trfFile.old"){
			my $content = Selity::File->new(filename => "$trfFile.old")->get();
			while($content =~ /^(\d+)\s[^\@]+\@(.*)$/mg){
				$self->{logDb}->{$2} += $1 if (defined $2 && defined $1);
			}
		}
	}

	$self->{logDb}->{$who} ? $self->{logDb}->{$who} : 0;
}

END{

	use Selity::File;

	my $endCode	= $?;
	my $self	= Servers::ftpd::proftpd->new();
	my $rs		= 0;
	my $trfFile	= "$main::selityConfig{TRAFF_LOG_DIR}/$self::proftpdConfig{FTP_TRAFF_LOG}";

	$rs			= $self->restart() if $self->{restart} && $self->{restart} eq 'yes';

	$rs |= Selity::File->new(filename => "$trfFile.old")->delFile() if -f "$trfFile.old";

	$? = $endCode || $rs;
}

1;
