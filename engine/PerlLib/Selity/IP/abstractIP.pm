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

package Selity::IP::abstractIP;

use strict;
use warnings;

use Selity::Debug;

use vars qw/@ISA/;

@ISA = ('Common::SingletonClass');
use Common::SingletonClass;

sub parseIPs{ fatal('Must be implemmented in class'); }

sub parseNetCards{ fatal('Must be implemmented in class'); }

sub normalize{shift; shift;}


sub getIPs{
	my $self = shift;

	debug("Ip`s: ". join( ' ', keys %{$self->{ips}} ));

	return (wantarray ? keys %{$self->{ips}} : join( ' ', keys %{$self->{ips}} ));
}

sub getNetCards{
	my $self = shift;

	debug("Network cards`s: ". join( ' ', keys %{$self->{cards}} ));

	return (wantarray ? keys %{$self->{cards}} : join( ' ', keys %{$self->{cards}} ));
}

sub getCardByIP{ fatal('Must be implemmented in class'); }

sub existsNetCard{
	my $self	= shift;
	my $card	= shift;

	debug("Network card $card exists? ". (exists $self->{cards}->{$card} ? 'yes' : 'no'));

	return (exists $self->{cards}->{$card});
}

sub isCardUp{
	my $self	= shift;
	my $card	= shift;

	debug("Network card $card is up? ". (exists $self->{cards}->{$card}->{up} ? 'yes' : 'no'));
	return (exists $self->{cards}->{$card}->{up});
}

sub isValidIp{ fatal('Must be implemmented in class'); }

sub attachIpToNetCard{ fatal('Must be implemmented in class'); }

sub detachIpFromNetCard{ fatal('Must be implemmented in class'); }

1;
