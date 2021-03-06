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

package Selity::Database::mysql::Result;

use strict;
use warnings;
use Selity::Debug;

use vars qw/@ISA/;
@ISA = ('Common::SimpleClass');
use Common::SimpleClass;

sub TIEHASH {

	my $self = shift;

	$self = $self->new(@_);

	debug('Tieing ...');

	return $self;
};

sub FIRSTKEY {
	my $self	= shift;

	my $a = scalar keys %{$self->{args}->{result}};

	each %{$self->{args}->{result}};
}

sub NEXTKEY {
	my $self	= shift;

	each %{$self->{args}->{result}};
}

sub FETCH {
	my $self = shift;
	my $key = shift;

	debug("Fetching $key");

	$self->{args}->{result}->{$key} ? $self->{args}->{result}->{$key} : undef;
};

sub EXISTS {
	my $self = shift;
	my $key = shift;

	debug("Cheching key $key ...".(exists $self->{args}->{result}->{$key} ? 'exists' : 'not exists'));

	$self->{args}->{result}->{$key} ? 1 : 0;
};

sub STORE {};

1;
