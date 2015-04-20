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

package Selity::STDCapture;

use File::Temp 'tempfile';
use Symbol qw/gensym qualify qualify_to_ref/;
use Selity::Debug;

sub new {
	my $proto			= shift;
	my $class			= ref($proto) || $proto;
	my $self			= {};
	my $STD				= shift;
	$self->{capture}	= shift;

	$STD				= qualify($STD);
	$self->{STDHandler}	= qualify_to_ref($STD);

	debug ("Capturing ${$self->{STDHandler}}");

	open $self->{saved}, ">& $STD" or error("Can't redirect <$STD> - $!");
	(undef, $self->{newSTDFile}) = tempfile;
	open $self->{newSTDHandler}, "+> $self->{newSTDFile}" or error("Can't create temporary file for $STD - $!");
	open $self->{STDHandler}, ">& ".fileno($self->{newSTDHandler}) or error("Can't redirect $STD - $!");
	$self->{pid} = $$;

	bless($self, $class);
}

sub DESTROY {
	my $self	= shift;
	return unless $self->{pid} eq $$;
	debug ("Finishing capture of ${$self->{STDHandler}}");
	select((select ($self->{STDHandler}), $|=1)[0]);
	open $self->{STDHandler}, ">& ". fileno($self->{saved}) or error("Can't restore ${$self->{STDHandler}} - $!");
	seek $self->{newSTDHandler}, 0, 0;
	my $file			= $self->{newSTDHandler};
	my $msg				=do {local $/; <$file>};
	chomp($msg);
	${$self->{capture}}	= $msg;
	unlink $self->{newSTDFile} or error("Couldn't remove temp file '$self->{newSTDFile}' - $!",1);
}

1;
