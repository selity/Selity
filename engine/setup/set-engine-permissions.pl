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

use strict;
use warnings;

use FindBin;
use lib "$FindBin::Bin/..";
use lib "$FindBin::Bin/../PerlLib";
use lib "$FindBin::Bin/../PerlVendor";

use Selity::Debug;
use Selity::Boot;


newDebug('selity-set-engine-permissions.log');

sub start_up {


	umask(027);
	Selity::Boot->new()->init({nolock => 'yes', nodatabase => 'yes'});

	0;
}

sub shut_down {

	use Selity::Mail;

	my @warnings	= getMessageByType('WARNING');
	my @errors		= getMessageByType('ERROR');

	my $msg	 = "\nWARNINGS:\n"		. join("\n", @warnings)	. "\n" if @warnings > 0;
	$msg	.= "\nERRORS:\n"		. join("\n", @errors)	. "\n" if @errors > 0;
	Selity::Mail->new()->errmsg($msg) if ($msg);

	0;
}

sub set_permissions {

	use Selity::Rights;

	my ($rs, $server, $file, $class);
	my $rootUName	= $main::selityConfig{'ROOT_USER'};
	my $rootGName	= $main::selityConfig{'ROOT_GROUP'};
	my $masterUName	= $main::selityConfig{'MASTER_GROUP'};
	my $CONF_DIR	= $main::selityConfig{'CONF_DIR'};
	my $ROOT_DIR	= $main::selityConfig{'ROOT_DIR'};
	my $LOG_DIR		= $main::selityConfig{'LOG_DIR'};

	$rs |= setRights("$CONF_DIR/selity.conf", {user => $rootUName, group => $masterUName, mode => '0660'});
	$rs |= setRights("$CONF_DIR/selity-db-keys", {user => $rootUName, group => $masterUName, mode => '0640'});
	$rs |= setRights("$ROOT_DIR/engine", {user => $rootUName, group => $masterUName, mode => '0755', recursive => 'yes'});
	$rs |= setRights($LOG_DIR, {user => $rootUName, group => $masterUName, mode => '0750'});

	for(qw/named ftpd mta po httpd/){
		$file	= "Servers/$_.pm";
		$class	= "Servers::$_";
		require $file;
		$server	= $class->factory();
		$rs |= $server->setEnginePermissions() if($server->can('setEnginePermissions'));
	}

	$rs;
}

exit 1 if start_up();

exit 1 if set_permissions();

exit 1 if shut_down();

exit 0;
