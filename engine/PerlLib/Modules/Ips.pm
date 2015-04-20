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

package Modules::Ips;

use strict;
use warnings;
use Selity::Debug;
use Selity::Execute;
use Data::Dumper;

use vars qw/@ISA/;

@ISA = ('Common::SimpleClass', 'Modules::Abstract');
use Common::SimpleClass;
use Modules::Abstract;

sub _init{
	my $self		= shift;
	$self->{type}	= 'Ips';
}

sub process{

	my $self	= shift;
	my $rs		= 0;

	my $sql = "
		SELECT `alias_ip_id` AS `ip_id`, `ip_number` FROM `domain_aliasses`
		LEFT JOIN `server_ips` ON `domain_aliasses`.`alias_ip_id` = `server_ips`.`ip_id`
		WHERE `alias_status` NOT IN ('delete', 'ordered')
	";

	my $rdata = Selity::Database->factory()->doQuery('ip_number', $sql);

	error("$rdata") and return 1 if(ref $rdata ne 'HASH');

	@{$self->{ips}} = keys %{$rdata};

	my $sql = "
		SELECT `ip_number` FROM `ssl_certs`
		LEFT JOIN `domain_aliasses` on `ssl_certs`.`id` = `domain_aliasses`.`alias_id`
		LEFT JOIN `server_ips` ON `domain_aliasses`.`alias_ip_id` = `server_ips`.`ip_id`
		WHERE `type` = 'als'
		UNION
		SELECT `ip_number` FROM `ssl_certs`
		LEFT JOIN `subdomain_alias` on `ssl_certs`.`id` = `subdomain_alias`.`subdomain_alias_id`
		LEFT JOIN `domain_aliasses` on `subdomain_alias`.`alias_id` = `domain_aliasses`.`alias_id`
		LEFT JOIN `server_ips` ON `domain_aliasses`.`alias_ip_id` = `server_ips`.`ip_id`
		WHERE `type` = 'alssub'
	";

	my $sslIPData = Selity::Database->factory()->doQuery('ip_number', $sql);

	error("$sslIPData") and return 1 if(ref $sslIPData ne 'HASH');

	@{$self->{sslIPs}} = keys %{$sslIPData};

	$rs = $self->add();

	$rs;
}

sub buildHTTPDData{

	my $self	= shift;

	$self->{httpd} = {
		IPS					=> $self->{ips},
		SSLIPS				=> $self->{sslIPs}
	};

	0;
}

1;
