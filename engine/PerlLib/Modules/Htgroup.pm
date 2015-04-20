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

package Modules::Htgroup;

use strict;
use warnings;
use Selity::Debug;
use Data::Dumper;

use vars qw/@ISA/;

@ISA = ('Common::SimpleClass', 'Modules::Abstract');
use Common::SimpleClass;
use Modules::Abstract;

sub _init{
	my $self		= shift;
	$self->{type}	= 'Htgroup';
}

sub loadData{

	my $self = shift;

	my $sql = "
		SELECT
			`t2`.`id`, `t2`.`ugroup`, `t2`.`status`, `t2`.`users`, `t3`.`admin_name`
		FROM
			(
				SELECT * from `htaccess_groups`,
				(
					SELECT IFNULL(
					(
						SELECT group_concat(`uname` SEPARATOR ' ')
						FROM `htaccess_users`
						WHERE `id` regexp (
							CONCAT(
								'^(',
								(
									SELECT REPLACE(
										(SELECT `members` FROM `htaccess_groups` WHERE `id` = ?),
										',',
										'|'
									)
								),
								')\$'
							)
						) GROUP BY `admin_id`
					), '') as `users`
				) as t1
			) as t2
		LEFT JOIN
			`admin` AS `t3`
		ON
			`t2`.`admin_id` = `t3`.`admin_id`
		WHERE `id` = ?
	";

	my $rdata = Selity::Database->factory()->doQuery('id', $sql, $self->{htgroupId}, $self->{htgroupId});

	error("$rdata") and return 1 if(ref $rdata ne 'HASH');
	error("No group in table htaccess_groups has id = $self->{htgroupId}") and return 1 unless(exists $rdata->{$self->{htgroupId}});

	unless($rdata->{$self->{htgroupId}}->{admin_name}){
		local $Data::Dumper::Terse = 1;
		error("Orphan entry: ".Dumper($rdata->{$self->{htgroupId}}));
		my @sql = (
			"UPDATE `htaccess_groups` SET `status` = ? WHERE `id` = ?",
			"Orphan entry: ".Dumper($rdata->{$self->{htgroupId}}),
			$self->{htgroupId}
		);
		my $rdata = Selity::Database->factory()->doQuery('update', @sql);
		return 1;
	}

	$self->{$_} = $rdata->{$self->{htgroupId}}->{$_} for keys %{$rdata->{$self->{htgroupId}}};

	0;
}

sub process{

	my $self		= shift;
	$self->{htgroupId}	= shift;

	my $rs = $self->loadData();
	return $rs if $rs;

	my @sql;

	if($self->{status} =~ /^toadd|change$/){
		$rs = $self->add();
		@sql = (
			"UPDATE `htaccess_groups` SET `status` = ? WHERE `id` = ?",
			($rs ? scalar getMessageByType('ERROR') : 'ok'),
			$self->{id}
		);
	}elsif($self->{status} =~ /^delete$/){
		$rs = $self->delete();
		if($rs){
			@sql = (
				"UPDATE `htaccess_groups` SET `status` = ? WHERE `id` = ?",
				scalar getMessageByType('ERROR'),
				$self->{id}
			);
		}else {
			@sql = ("DELETE FROM `htaccess_groups` WHERE `id` = ?", $self->{id});
		}
	}

	my $rdata = Selity::Database->factory()->doQuery('delete', @sql);
	error("$rdata") and return 1 if(ref $rdata ne 'HASH');

	$rs;
}

sub buildHTTPDData{

	my $self	= shift;

	$self->{httpd} = {
		HTGROUP_NAME	=> $self->{ugroup},
		HTGROUP_USERS	=> $self->{users},
		HTGROUP_DMN		=> $self->{admin_name},
	};

	0;
}

1;
