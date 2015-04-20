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

package Selity::Config;

use strict;
use warnings;
use Tie::File;
use Selity::Debug;
use Selity::Args;

use vars qw/@ISA/;
@ISA = ('Common::SimpleClass');
use Common::SimpleClass;

sub _init{
	my $self				= shift;
	$self->{comLineOpts}	= Selity::Args->new();
}

sub TIEHASH {
	my $self = shift;
	$self = $self->new(@_);

	$self->{confFile} = ();

	$self->{configValues} = {};
	$self->{lineMap} = {};

	$self->{confFileName} = $self->{args}->{fileName};

	debug("Tieing $self->{confFileName}");

	$self->_loadConfig();
	$self->_parseConfig();

	return $self;
}

sub _loadConfig{
	my $self	= shift;

	debug('Config file ' . $self->{confFileName});

	tie @{$self->{confFile}}, 'Tie::File', $self->{confFileName} or
		fatal("Can`t read " . $self->{confFileName}, 1);

}

sub _parseConfig{
	my $self = shift;

	my $lineNo = 0;

	for my $line (@{$self->{confFile}}){
		if ($line =~ /^([^#\s=]+)\s{0,}=\s{0,}(.{0,})$/) {
			$self->{configValues}->{$1}	= $2;
			$self->{lineMap}->{$1}		= $lineNo;
		}
		$lineNo++;
	}

}

sub FETCH {
	my $self	= shift;
	my $config	= shift;

	unless (exists($self->{configValues}->{$config})){
		if( defined $self->{comLineOpts}->get($config)){
			$self->STORE($config, $self->{comLineOpts}->get($config));
		} else {
			error("Accessing non existing config value $config") unless($self->{args}->{noerrors});
		}
	}

	return $self->{configValues}->{$config};
}

sub STORE {
	my $self	= shift;
	my $config	= shift;
	my $value	= shift;

	debug("Store ${config} as ".($value ? $value : 'empty')."..." );

	if(!exists($self->{configValues}->{$config})){
		$self->_insertConfig($config, $value);
	} else {
		$self->_replaceConfig($config, $value);
	}

}

sub FIRSTKEY {
	my $self = shift;

	$self->{_list} = [ sort keys %{$self->{configValues}} ];

	return $self->NEXTKEY;
}

sub NEXTKEY {
	my $self = shift;

	return shift @{$self->{_list}};
}

sub _replaceConfig{
	my $self	= shift;
	my $config	= shift;
	my $value	= shift;

	$value = '' unless defined $value;

	debug("Setting $config as $value");

	@{$self->{confFile}}[$self->{lineMap}->{$config}] = "$config = $value";
	$self->{configValues}->{$config} = $value;
}

sub _insertConfig{
	my $self	= shift;
	my $config	= shift;
	my $value	= shift;

	$value = '' unless defined $value;

	debug("Setting $config as $value");

	push (@{$self->{confFile}}, "$config = $value");
	$self->{lineMap}->{$config} = $#{$self->{confFile}};
	$self->{configValues}->{$config} = $value;
}

1;
