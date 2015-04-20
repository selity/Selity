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

package Selity::Dialog;

use strict;
use warnings;

use Selity::Debug;
use Selity::Execute qw/execute/;
use Common::SingletonClass;

use vars qw/@ISA/;
@ISA = ('Common::SingletonClass');

sub factory{

	my $self	= Selity::Dialog->new();

	unless($self->{instance}){
		my ($dialog, $whiptail, $rs, $stdout, $stderr, $file, $class);
		if(!execute('which dialog', \$stdout, \$stderr)){
			$file	= "Selity/Dialog/Dialog.pm";
			$class	= "Selity::Dialog::Dialog";
			require $file;
			$self->{instance} = $class->new();
		}elsif(!execute('which whiptail', \$stdout, \$stderr)){
			$file	= "Selity/Dialog/Whiptail.pm";
			$class	= "Selity::Dialog::Whiptail";
			require $file;
			$self->{instance} = $class->new();
		} else {
			fatal('Can not find whiptail or dialog. Please reinstall...');
		}
		$self->{instance}->set('title', 'Selity Setup');
		$self->{instance}->set('backtitle',	'Selity internet Multi Server Control Panel');
	}
	$self->{instance};
}

sub reset{
	my $self	= Selity::Dialog->new();
	$self->{instance} = undef;
	0;
}
1;
