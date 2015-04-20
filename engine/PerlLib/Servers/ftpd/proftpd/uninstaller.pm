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

package Servers::ftpd::proftpd::uninstaller;

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

	$self->{cfgDir}	= "$main::selityConfig{'CONF_DIR'}/proftpd";
	$self->{bkpDir}	= "$self->{cfgDir}/backup";
	$self->{wrkDir}	= "$self->{cfgDir}/working";

	my $conf		= "$self->{cfgDir}/proftpd.data";

	tie %self::proftpdConfig, 'Selity::Config','fileName' => $conf;

	0;
}

sub uninstall{

	my $self	= shift;
	my $rs		= 0;

	$rs |= $self->restoreConfFile();
	$rs |= $self->removeDB();
	$rs |= $self->removeDirs();

	$rs;
}

sub removeDirs{

	use Selity::Dir;

	my $self	= shift;
	my $rs		= 0;

	for($self::proftpdConfig{FTPD_CONF_DIR}, "$main::selityConfig{TRAFF_LOG_DIR}/proftpd"){

		$rs |= Selity::Dir->new(
			dirname => $_
		)->remove() if -d $_;
	}

	$rs;
}

sub removeDB{

	my $self		= shift;
	my $rs			= 0;
	my $err			= 0;
	my $database	= Selity::Database->new()->factory();

	if($self::proftpdConfig{DATABASE_USER}){

		$err = $database->doQuery(
			'delete',
			"DROP USER ?@?",
			$self::proftpdConfig{'DATABASE_USER'},
			'localhost'
		);

		$err = $database->doQuery(
			'delete',
			"DROP USER ?@?",
			$self::proftpdConfig{'DATABASE_USER'},
			'%'
		);

		$err = $database->doQuery('dummy', 'FLUSH PRIVILEGES');
		if (ref $err ne 'HASH'){
			error($err);
			$rs = 1;
		}
	}

	$rs;
}

sub restoreConfFile{

	use File::Basename;
	use Selity::File;

	my $self	= shift;
	my $rs		= 0;

	for (
		$self::proftpdConfig{FTPD_CONF_FILE}
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
