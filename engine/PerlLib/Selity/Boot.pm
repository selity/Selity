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

package Selity::Boot;

use strict;
use warnings;
use Selity::Debug;
use Selity::Crypt;
use Selity::Config;
use Selity::Requirements;

use vars qw/@ISA/;

@ISA = ('Common::SingletonClass');
use Common::SingletonClass;

sub init{
	my $self = shift;
	my $option = shift;

	$option = {} if ref $option ne 'HASH';

	unless($self->{'loaded'}) {
		debug('Booting...');

		tie %main::selityConfig, 'Selity::Config','fileName' => (($^O =~ /bsd$/ ? '/usr/local/etc/' : '/etc/').'selity/selity.conf'), noerrors => 1;

		verbose($main::selityConfig{'DEBUG'}) unless($self->{args}->{mode} && $self->{args}->{mode} eq 'setup'); #on setup DEBUG is allways 0.

		Selity::Requirements->new()->test($self->{args}->{mode} && $self->{args}->{mode} eq 'setup' ? 'all' : 'user') unless($option->{norequirements} && $option->{norequirements} eq 'yes');

		$self->lock($main::selityConfig{MR_LOCK_FILE}) unless($option->{nolock} && $option->{nolock} eq 'yes');

		$self->genKey();

		unless ($option->{nodatabase} && $option->{nodatabase} eq 'yes'){
				use Selity::Database;
				use Selity::Crypt;

				my $crypt = Selity::Crypt->new();
				my $database = Selity::Database->new(db => $main::selityConfig{'DATABASE_TYPE'})->factory();

				$database->set('DATABASE_HOST', $main::selityConfig{'DATABASE_HOST'});
				$database->set('DATABASE_PORT', $main::selityConfig{'DATABASE_PORT'});
				$database->set('DATABASE_NAME', $main::selityConfig{'DATABASE_NAME'});
				$database->set('DATABASE_USER', $main::selityConfig{'DATABASE_USER'});
				$database->set('DATABASE_PASSWORD', $crypt->decrypt_db_password($main::selityConfig{'DATABASE_PASSWORD'}));
				my $rs = $database->connect();
				fatal("$rs") if $rs;
		}

		$self->{'loaded'} = 1;
	}

	0;
}

sub lock{
	my $self	= shift;
	my $lock	= shift || $main::selityConfig{MR_LOCK_FILE};

	fatal('Unable to open lock file!') if(!open($self->{lock}, '>', $lock));

	use Fcntl ":flock";
	fatal('Unable to acquire global lock!') if(!flock($self->{lock}, LOCK_EX));

	0;
}

sub unlock{
	my $self	= shift;
	my $lock	= shift;

	use Fcntl ":flock";
	fatal('Unable to release global lock!') if(!flock($self->{lock}, LOCK_UN));

	0;
}

sub genKey{

	use Selity::File;

	my $key_file		= "$main::selityConfig{'CONF_DIR'}/selity-db-keys";
	our $db_pass_key	= '{KEY}';
	our $db_pass_iv		= '{IV}';

	require "$key_file" if( -f $key_file);

	if ($db_pass_key eq '{KEY}' || $db_pass_iv eq '{IV}') {

		print STDOUT "\tGenerating database keys, it may take some time, please  wait...\n";
		print STDOUT "\tIf it takes to long, please check:  http://selity.org/dokuwiki/doku.php?id=keyrpl\n";

		if(-d $main::selityConfig{'CONF_DIR'}) {
			open(F, '>:utf8', "$main::selityConfig{'CONF_DIR'}/selity-db-keys") or fatal("Error: Can't open file '$main::selityConfig{'CONF_DIR'}/selity-db-keys' for writing: $!");
			print F Data::Dumper->Dump([Selity::Crypt::randomString(32), Selity::Crypt::randomString(8)], [qw(db_pass_key db_pass_iv)]);
			close F;
		} else {
			fatal("Error: Destination path $main::selityConfig{'CONF_DIR'} don't exists or is not a directory!");
		}
		require "$key_file";
	}

	$main::selityDBKey	= $db_pass_key;
	$main::selityDBiv	= $db_pass_iv;

	Selity::Crypt->new()->set('key', $main::selityDBKey);
	Selity::Crypt->new()->set('iv', $main::selityDBiv);

	debug("Key: |$main::selityDBKey|, iv:|$main::selityDBiv|");

}

1;

__END__
