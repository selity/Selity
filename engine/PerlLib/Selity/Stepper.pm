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

package Selity::Stepper;

use strict;
use warnings;
use Selity::Dialog;
use Selity::Debug;

use vars qw/@ISA @EXPORT_OK @EXPORT %EXPORT_TAGS/;
use Exporter;
use Common::SingletonClass;

@ISA = ('Common::SingletonClass', 'Exporter');
@EXPORT = qw/step startDetail endDetail/;

sub _init{

	my $self = Selity::Stepper->new();

	$self->{title}	= "Performing step %s from total of %s \n\n%s";
	$self->{all}	= [];
	$self->{last}	= '';

	0;
}

sub startDetail{

	my $self = Selity::Stepper->new();

	push (@{$self->{all}}, $self->{last});
	0;
}

sub endDetail{

	my $self = Selity::Stepper->new();

	$self->{last} = pop (@{$self->{all}});
	0;
}

sub step($ $ $ $){

	my $self = Selity::Stepper->new();

	my ($code, $text, $steps, $index, $exit) = (@_);

	$self->{last} = sprintf ($self->{title}, $index, $steps, $text);

	my $msg = join ("\n", @{$self->{all}}) . "\n\n" . $self->{last};

	Selity::Dialog->factory()->startGauge($msg, int($index*100/$steps)) if Selity::Dialog->factory()->needGauge();
	Selity::Dialog->factory()->setGauge(int($index*100/$steps), $msg);

	my $rs = &{$code}() if (ref $code eq 'CODE');

	if($rs){
		Selity::Dialog->factory()->endGauge()  if Selity::Dialog->factory()->needGauge();
		Selity::Dialog->factory()->msgbox(
					"\n
					\\Z1[ERROR]\\Zn

					Error while performing step:

					$text

					Error was:

					\\Z1".($rs =~ /^-?\d+$/ ? getLastError() : $rs)."\\Zn\n

					To obtain help please use http://selity.org/forum/

					");
		return $rs;
	}

	0;
}

1;
