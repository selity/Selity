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

package Selity::Crypt;

use strict;
use warnings;
use Selity::Debug;
use Crypt::CBC;
use MIME::Base64;

use vars qw/@ISA/;
@ISA = ('Common::SingletonClass');
use Common::SingletonClass;

sub _init{
	my $self = shift;
	$self->{cipher}->{key}				= '';
	$self->{cipher}->{keysize}			= 32;
	$self->{cipher}->{cipher}			= 'Blowfish';
	$self->{cipher}->{iv}				= '';
	$self->{cipher}->{regenerate_key}	= 0;
	$self->{cipher}->{padding}			= 'space';
	$self->{cipher}->{prepend_iv}		= 0;
}

sub set{
	my $self		= shift;
	my $prop		= shift;
	my $value		= shift;
	debug("Setting $prop as $value") if(exists $self->{cipher}->{$prop});
	$self->{cipher}->{$prop} = $value if(exists $self->{cipher}->{$prop});
}

sub randomString{

	my $self = shift || Selity::Crypt->new();
	my $length = shift;

	if(!ref $self || !$self->isa("Selity::Crypt")){
		$length = $self;
		$self = Selity::Crypt->new();
	}
	my $string = '';

	while(length $string < $length) {
		my $pool = Crypt::CBC->random_bytes(100);
		foreach(unpack "C*", $pool) {
			next if $_ < 32 || $_ > 126;
			length $string < $length ? $string .= chr $_ : last;
		}
	}
	debug("Returning $string");
	$string;
}

sub encrypt_db_password {

	my $self	= shift;
	my $pass	= shift;

	error('Undefined input data...') if (!defined $pass || $pass eq '');
	error('KEY or IV has invalid length') if (length($self->{cipher}->{key}) != $self->{cipher}->{keysize} || length($self->{cipher}->{iv}) != 8);

	my $cipher	= Crypt::CBC -> new($self->{cipher});
	my $encoded	= encode_base64($cipher->encrypt($pass));
	chop($encoded);

	debug("Returning $encoded");
	return $encoded;
}

sub decrypt_db_password {

	my $self	= shift;
	my $pass	= shift;

	if (!defined $pass || $pass eq ''){
		error('Undefined input data...') ;
		return undef;
	}
	if (length($self->{cipher}->{key}) != $self->{cipher}->{keysize} || length($self->{cipher}->{iv}) != 8) {
		error('KEY or IV has invalid length');
		return undef;
	}

	my $cipher		= Crypt::CBC -> new($self->{cipher});
	my $plaintext	= $cipher->decrypt(decode_base64("$pass\n"));

	debug("Returning $plaintext");
	return $plaintext;
}

sub crypt_md5_data {

	my $self = shift || Selity::Crypt->new();
	my $data = shift;

	if(!ref $self || !$self->isa("Selity::Crypt")){
		$data = $self;
		$self = Selity::Crypt->new();
	}

	if (!$data) {
		debug("Undefined input data, data: |$data| !");
		return undef;
	}

	debug("Crypting |$data|!");

	use Crypt::PasswdMD5;

	$data = unix_md5_crypt($data, $self->randomString(8));

	debug("Returning $data");
	$data;
}

1;
