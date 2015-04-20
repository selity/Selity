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

package Modules::Certificates;

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
	$self->{type}	= 'Certificates';
}

sub loadData{

	my $self = shift;

	my $sql = " SELECT * FROM `ssl_certs` WHERE `cert_id` = ?";

	my $certData = Selity::Database->factory()->doQuery('cert_id', $sql, $self->{cert_id});

	error("$certData") and return 1 if(ref $certData ne 'HASH');
	error("No record in table cert_ssl has id = $self->{cert_id}") and return 1 unless(exists $certData->{$self->{cert_id}});

	$self->{$_} = $certData->{$self->{cert_id}}->{$_} for keys %{$certData->{$self->{cert_id}}};

	if($self->{type} eq 'dmn') {
		$sql = 'SELECT `domain_name` AS `name`, `domain_id` AS `id` FROM `domain` WHERE `domain_id` = ?';
	} elsif($self->{type} eq 'als') {
		$sql = 'SELECT `alias_name` AS `name`, `alias_id` AS `id` FROM `domain_aliasses` WHERE `alias_id` = ?';
	} elsif($self->{type} eq 'sub') {
		$sql = 'SELECT CONCAT(`subdomain_name`, \'.\', `domain_name`) AS `name`, `subdomain_id` AS `id` FROM `subdomain` LEFT JOIN `domain` USING(`domain_id`) WHERE `subdomain_id` = ?';
	} else { #'alssub':
		$sql = 'SELECT CONCAT(`subdomain_alias_name`, \'.\', `alias_name`) AS `name`, `subdomain_alias_id` AS `id` FROM `subdomain_alias` LEFT JOIN `domain_aliasses` USING(`alias_id`) WHERE `subdomain_alias_id` = ?';
	}

	my $rdata = Selity::Database->factory()->doQuery('id', $sql, $self->{id});
	error("$rdata") and return 1 if(ref $rdata ne 'HASH');
	error("No record in table $self->{type} has id = $self->{id}") and return 1 unless(exists $rdata->{$self->{id}});

	unless($rdata->{$self->{id}}->{name}){
		local $Data::Dumper::Terse = 1;
		error("Orphan entry: ".Dumper($certData->{$self->{cert_id}}));
		my @sql = (
			"UPDATE `ssl_certs` SET `status` = ? WHERE `cert_id` = ?",
			"Orphan entry: ".Dumper($rdata->{$self->{cert_id}}),
			$self->{cert_id}
		);
		my $rdata = Selity::Database->factory()->doQuery('update', @sql);
		return 1;
	}

	$self->{name} = $rdata->{$self->{id}}->{name};

	0;
}

sub process{

	my $self		= shift;
	$self->{cert_id}	= shift;

	my $rs = $self->loadData();
	return $rs if $rs;

	my @sql;

	if($self->{status} =~ /^toadd|change$/){
		$rs = $self->add();
		@sql = (
			"UPDATE `ssl_certs` SET `status` = ? WHERE `cert_id` = ?",
			($rs ? scalar getMessageByType('ERROR') : 'ok'),
			$self->{cert_id}
		);
	}elsif($self->{status} =~ /^delete$/){
		$rs = $self->delete();
		if($rs){
			@sql = (
				"UPDATE `ssl_certs` SET `status` = ? WHERE `cert_id` = ?",
				scalar getMessageByType('ERROR'),
				$self->{cert_id}
			);
		}else {
			@sql = ("DELETE FROM `ssl_certs` WHERE `cert_id` = ?", $self->{cert_id});
		}
	}

	my $rdata = Selity::Database->factory()->doQuery('something', @sql);
	error("$rdata") and return 1 if(ref $rdata ne 'HASH');

	$rs;
}

sub add{

	use File::Temp;
	use Selity::File;
	use Selity::Dir;
	use Modules::openssl;

	my $self		= shift;
	my $rs			= 0;
	my $rootUser	= $main::selityConfig{ROOT_USER};
	my $rootGroup	= $main::selityConfig{ROOT_GROUP};
	my $certPath	= "$main::selityConfig{GUI_ROOT_DIR}/data/certs";

	Selity::Dir->new(dirname => $certPath)->make({
			mode => 0750,
			owner => $rootUser,
			group => $rootGroup
	});

	my $certFH = File::Temp->new(
		DIR => '/tmp',
		UNLINK => 1,
		OPEN => 0
	);
	my $file = Selity::File->new(filename => $certFH->filename);
	$file->set($self->{cert});
	$rs |= $file->save();
	Modules::openssl->new()->{cert_path} = $certFH->filename;

	my $keyFH = File::Temp->new(
		DIR => '/tmp',
		UNLINK => 1,
		OPEN => 0
	);
	$file = Selity::File->new(filename => $keyFH->filename);
	$file->set($self->{key});
	$rs |= $file->save();
	Modules::openssl->new()->{key_path} = $keyFH->filename;

	my $caFH;
	if($self->{ca_cert}){
		$caFH = File::Temp->new(
			DIR => '/tmp',
			UNLINK => 1,
			OPEN => 0
		);

		$file = Selity::File->new(filename => $caFH->filename);
		$file->set($self->{ca_cert});
		$rs |= $file->save();
		Modules::openssl->new()->{intermediate_cert_path} = $caFH->filename;
	} else {
		Modules::openssl->new()->{intermediate_cert_path} = '';
	}

	Modules::openssl->new()->{openssl_path} = $main::selityConfig{'CMD_OPENSSL'};

	Modules::openssl->new()->{key_pass} = $self->{password};
	$rs |= Modules::openssl->new()->ssl_check_all();
	unless($rs){
		Modules::openssl->new()->{new_cert_path} = $certPath,
		Modules::openssl->new()->{new_cert_name} = $self->{name};

		Modules::openssl->new()->{cert_selfsigned} = 1;
		Modules::openssl->new()->ssl_export_all();
	}

	$rs;
}

sub delete{

	use Selity::File;
	use Selity::Dir;

	my $self		= shift;
	my $rs			= 0;
	my $rootUser	= $main::selityConfig{ROOT_USER};
	my $rootGroup	= $main::selityConfig{ROOT_GROUP};
	my $certPath	= "$main::selityConfig{GUI_ROOT_DIR}/data/certs";
	my $cert		= "$certPath/$self->{name}.pem";

	Selity::Dir->new(dirname => $certPath)->make({
			mode => 0750,
			owner => $rootUser,
			group => $rootGroup
	});

	$rs |= Selity::File->new(filename => $cert)->delFile($cert) if -f $cert;

	$rs;
}

1;
