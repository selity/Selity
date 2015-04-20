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

package Servers::po::courier::uninstaller;

use strict;
use warnings;
use Selity::Debug;
use Selity::File;
use Selity::Execute;

use vars qw/@ISA/;

@ISA = ('Common::SingletonClass');
use Common::SingletonClass;

sub _init{

	my $self		= shift;
	$self->{cfgDir}	= "$main::selityConfig{'CONF_DIR'}/courier";
	$self->{bkpDir}	= "$self->{cfgDir}/backup";
	$self->{wrkDir}	= "$self->{cfgDir}/working";

	my $conf		= "$self->{cfgDir}/courier.data";

	tie %self::courierConfig, 'Selity::Config','fileName' => $conf;

	0;
}

sub uninstall{

	my $self	= shift;
	my $rs		= 0;

	$rs |= $self->restoreConfFile();
	$rs |= $self->authDaemon();
	$rs |= $self->userDB();

	$rs;
}

sub restoreConfFile{

	my $self	= shift;
	my $rs		= 0;
	my $file;

	for (
		'authdaemonrc',
		'userdb',
		$self::courierConfig{COURIER_IMAP_SSL},
		$self::courierConfig{COURIER_POP_SSL}
	) {
		$rs	|=	Selity::File->new(
					filename => "$self->{bkpDir}/$_.system"
				)->copyFile(
					"$self::courierConfig{'AUTHLIB_CONF_DIR'}/$_"
				)
				if -f "$self->{bkpDir}/$_.system"
		;
	}

	$rs;
}

sub authDaemon{

	my $self	= shift;
	my $rs		= 0;
	my $file;

	$file = Selity::File->new(filename => "$self::courierConfig{'AUTHLIB_CONF_DIR'}/authdaemonrc");
	$rs |= $file->mode(0660);
	$rs |= $file->owner($main::selityConfig{'ROOT_USER'}, $main::selityConfig{'ROOT_GROUP'});

	$rs;
}

sub userDB{

	my $self = shift;
	my $rs		= 0;
	my $file;

	$file = Selity::File->new(filename => "$self::courierConfig{'AUTHLIB_CONF_DIR'}/userdb");
	$rs |= $file->mode(0600);
	$rs |= $file->owner($main::selityConfig{'ROOT_USER'}, $main::selityConfig{'ROOT_GROUP'});

	my ($rs, $stdout, $stderr);
	$rs |= execute($self::courierConfig{'CMD_MAKEUSERDB'}, \$stdout, \$stderr);
	debug("$stdout") if ($stdout);
	if($rs){
		error("$stderr") if $stderr;
		error("Error while executing $self::courierConfig{CMD_MAKEUSERDB} returned status $rs") unless $stderr;
	}

	$rs;
}

1;
