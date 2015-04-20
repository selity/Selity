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


package Servers::mta::postfix::uninstaller;

use strict;
use warnings;
use Selity::Debug;
use Selity::Execute;
use Selity::File;
use Selity::Templator;

use vars qw/@ISA/;

@ISA = ('Common::SingletonClass');
use Common::SingletonClass;

sub _init{

	my $self		= shift;

	$self->{cfgDir}	= "$main::selityConfig{'CONF_DIR'}/postfix";
	$self->{bkpDir}	= "$self->{cfgDir}/backup";
	$self->{wrkDir}	= "$self->{cfgDir}/working";
	$self->{vrlDir} = "$self->{cfgDir}/selity";

	my $conf		= "$self->{cfgDir}/postfix.data";

	tie %self::postfixConfig, 'Selity::Config','fileName' => $conf;

	0;
}

sub uninstall{

	my $self	= shift;
	my $rs		= 0;

	$rs |= $self->restoreConfFile();
	$rs |= $self->buildAliasses();
	$rs |= $self->removeUsers();
	$rs |= $self->removeDirs();
	$rs;
}

sub removeDirs{
	use Selity::Dir;

	my $self	= shift;
	my $rs		= 0;

	debug('Creating postfix folders');

	for (
		$self::postfixConfig{'MTA_VIRTUAL_CONF_DIR'},
		$self::postfixConfig{'MTA_VIRTUAL_MAIL_DIR'},
	) {
		$rs |= Selity::Dir->new(dirname => $_)->remove();
	}

	$rs;
}

sub removeUsers{

	my $rs = 0;

	use Modules::SystemUser;
	my $user = Modules::SystemUser->new();

	$user->{force} = 'yes';

	$rs |= $user->delSystemUser($self::postfixConfig{'MTA_MAILBOX_UID_NAME'});

	$rs;
}

sub buildAliasses{

	my ($rs, $stdout, $stderr);

	# Rebuilding the database for the mail aliases file - Begin
	$rs = execute("$self::postfixConfig{'CMD_NEWALIASES'}", \$stdout, \$stderr);
	debug("$stdout");
	error("$stderr") if($stderr);
	error("Error while executing $self::postfixConfig{'CMD_NEWALIASES'}") if(!$stderr && $rs);

	$rs;
}

sub restoreConfFile{
	use File::Basename;
	use Selity::File;

	my $self	= shift;
	my $rs		= 0;

	for (
		$self::postfixConfig{'POSTFIX_CONF_FILE'},
		$self::postfixConfig{'POSTFIX_MASTER_CONF_FILE'}
	) {
		my ($filename, $directories, $suffix) = fileparse($_);
		if(-f "$self->{bkpDir}/$filename$suffix.system"){
			$rs	|=	Selity::File->new(
						filename => "$self->{bkpDir}/$filename$suffix.system"
					)->copyFile(
						$_
					);
		}
	}

	$rs;
}

1;
