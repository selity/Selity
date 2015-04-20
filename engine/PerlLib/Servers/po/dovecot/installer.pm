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

package Servers::po::dovecot::installer;

use strict;
use warnings;
use Selity::Debug;
use Selity::File;
use Selity::Execute;

use vars qw/@ISA/;

@ISA = ('Common::SingletonClass');
use Common::SingletonClass;

sub _init{

	my $self		= shift;
	$self->{cfgDir}	= "$main::selityConfig{'CONF_DIR'}/dovecot";
	$self->{bkpDir}	= "$self->{cfgDir}/backup";
	$self->{wrkDir}	= "$self->{cfgDir}/working";

	my $conf		= "$self->{cfgDir}/dovecot.data";
	my $oldConf		= "$self->{cfgDir}/dovecot.old.data";

	tie %self::dovecotConfig, 'Selity::Config','fileName' => $conf;
	tie %self::dovecotOldConfig, 'Selity::Config','fileName' => $oldConf, noerror => 1 if -f $oldConf;

	0;
}

sub install{

	my $self	= shift;
	my $rs		= 0;

	$self->getVersion() and return 1;

	# Saving all system configuration files if they exists
	for ((
		'dovecot.conf',
		'dovecot-sql.conf'
	)) {
		$rs |= $self->bkpConfFile($_);
	}

	$rs |= $self->setupDB();
	$rs |= $self->buildConf();
	$rs |= $self->saveConf();
	$rs |= $self->migrateMailboxes();

	$rs;
}

sub migrateMailboxes{

	if(
		$main::selityConfigOld{PO_SERVER}
		&&
		$main::selityConfigOld{PO_SERVER} eq 'courier'
		&&
		$main::selityConfig{PO_SERVER}  eq 'dovecot'
	){
		use Selity::Execute;
		use FindBin;
		use Servers::mta;

		my $mta	= Servers::mta->factory();
		my ($rs, $stdout, $stderr);
		my $binPath = "perl $main::selityConfig{'ENGINE_ROOT_DIR'}/PerlVendor/courier-dovecot-migrate.pl";
		my $mailPath = "$mta->{'MTA_VIRTUAL_MAIL_DIR'}";

		$rs = execute("$binPath --to-dovecot --convert --recursive $mailPath", \$stdout, \$stderr);
		debug("$stdout...") if $stdout;
		warning("$stderr") if $stderr && !$rs;
		error("$stderr") if $stderr && $rs;
		error("Error while converting mails") if !$stderr && $rs;
	}

	0;
}

sub getVersion{

	my $self = shift;
	my ($rs, $stdout, $stderr);

	$rs = execute('dovecot --version', \$stdout, \$stderr);
	debug("$stdout") if $stdout;
	error("$stderr") if $stderr;
	error("Can't read dovecot version") if !$stderr and $rs;
	return $rs if $rs;

	chomp($stdout);
	$stdout =~ m/^([0-9\.]+)\s*/;

	if($1){
		$self->{version} = $1;
	} else {
		error("Can't read dovecot version");
		return 1;
	}

	0;
}

sub saveConf{

	use Selity::File;

	my $self		= shift;
	my $file = Selity::File->new(filename => "$self->{cfgDir}/dovecot.data");
	my $cfg = $file->get() or return 1;
	$file->mode(0640) and return 1;
	$file->owner($main::selityConfig{'ROOT_USER'}, $main::selityConfig{'ROOT_GROUP'}) and return 1;

	$file = Selity::File->new(filename => "$self->{cfgDir}/dovecot.old.data");
	$file->set($cfg) and return 1;
	$file->save and return 1;
	$file->mode(0640) and return 1;
	$file->owner($main::selityConfig{'ROOT_USER'}, $main::selityConfig{'ROOT_GROUP'}) and return 1;

	0;
}


sub bkpConfFile{

	my $self		= shift;
	my $cfgFile		= shift;
	my $timestamp	= time;

	if(-f "$self::dovecotConfig{'DOVECOT_CONF_DIR'}/$cfgFile"){
		my $file	= Selity::File->new(
						filename => "$self::dovecotConfig{'DOVECOT_CONF_DIR'}/$cfgFile"
					);
		if(!-f "$self->{bkpDir}/$cfgFile.system") {
			$file->copyFile("$self->{bkpDir}/$cfgFile.system") and return 1;
		} else {
			$file->copyFile("$self->{bkpDir}/$cfgFile.$timestamp") and return 1;
		}
	}

	0;
}

sub buildConf{

	use Servers::mta;

	my $self		= shift;
	my $mta	= Servers::mta->factory($main::selityConfig{MTA_SERVER});

	my $cfg = {
		DATABASE_TYPE		=> $main::selityConfig{DATABASE_TYPE},
		DATABASE_HOST		=> (
									$main::selityConfig{DATABASE_PORT}
									?
									"$main::selityConfig{DATABASE_HOST} port=$main::selityConfig{DATABASE_PORT}"
									:
									$main::selityConfig{DATABASE_HOST}
								),
		DATABASE_USER		=> $self::dovecotConfig{DATABASE_USER},
		DATABASE_PASSWORD	=> $self::dovecotConfig{DATABASE_PASSWORD},
		DATABASE_NAME		=> $main::selityConfig{DATABASE_NAME},
		GUI_CERT_DIR		=> $main::selityConfig{GUI_CERT_DIR},
		HOST_NAME			=> $main::selityConfig{SERVER_HOSTNAME},
		DOVECOT_SSL			=> ($main::selityConfig{SSL_ENABLED} eq 'yes' ? 'yes' : 'no'),
		COMMENT_SSL			=> ($main::selityConfig{SSL_ENABLED} eq 'yes' ? '' : '#'),
		MAIL_USER			=> $mta->{'MTA_MAILBOX_UID_NAME'},
		MAIL_GROUP			=> $mta->{'MTA_MAILBOX_GID_NAME'},
		vmailUID			=> scalar getpwnam($mta->{'MTA_MAILBOX_UID_NAME'}),
		mailGID				=> scalar getgrnam($mta->{'MTA_MAILBOX_GID_NAME'}),
		DOVECOT_CONF_DIR	=> $self::dovecotConfig{DOVECOT_CONF_DIR}
	};

	use version;
	my $cfgFiles = {
		'dovecot.conf'		=>(
								version->new($self->{version}) < version->new('2.0.0')
								?
								'dovecot.conf.1'
								:
								'dovecot.conf.2'
		),
		'dovecot-sql.conf'	=> 'dovecot-sql.conf',
		'dovecot-dict-sql.conf'	=> 'dovecot-dict-sql.conf'
	};

	for (keys %{$cfgFiles}) {
		my $file	= Selity::File->new(filename => "$self->{cfgDir}/$cfgFiles->{$_}");
		my $cfgTpl	= $file->get();
		return 1 if (!$cfgTpl);
		$cfgTpl = Selity::Templator::process($cfg, $cfgTpl);
		return 1 if (!$cfgTpl);
		$file = Selity::File->new(filename => "$self->{wrkDir}/$_");
		$file->set($cfgTpl) and return 1;
		$file->save() and return 1;
		$file->mode(0640) and return 1;
		$file->owner($main::selityConfig{'ROOT_USER'}, $mta->{'MTA_MAILBOX_GID_NAME'}) and return 1;
		$file->copyFile($self::dovecotConfig{'DOVECOT_CONF_DIR'}) and return 1;
	}

	my $file	= Selity::File->new(filename => "$self::dovecotConfig{'DOVECOT_CONF_DIR'}/dovecot.conf");
	$file->mode(0644) and return 1;

	0;
}

sub setupDB{

	my $self		= shift;
	my $connData;

	if(!$self->check_sql_connection
		(
			$self::dovecotConfig{'DATABASE_USER'} || '',
			$self::dovecotConfig{'DATABASE_PASSWORD'} || ''
		)
	){
		$connData = 'yes';
	}elsif($self::dovecotOldConfig{'DATABASE_USER'} && !$self->check_sql_connection
		(
			$self::dovecotOldConfig{'DATABASE_USER'} || '',
			$self::dovecotOldConfig{'DATABASE_PASSWORD'} || ''
		)
	){
		$self::dovecotConfig{'DATABASE_USER'}		= $self::dovecotOldConfig{'DATABASE_USER'};
		$self::dovecotConfig{'DATABASE_PASSWORD'}	= $self::dovecotOldConfig{'DATABASE_PASSWORD'};
		$connData = 'yes';
	} else {
		my $dbUser = 'dovecot_user';

		do{
			$dbUser = Selity::Dialog->factory()->inputbox("Please enter database user name for the restricted dovecot user (default dovecot_user)", $dbUser);
			#we will not allow root user to be used as database user for dovecot since account will be restricted
			if($dbUser eq $main::selityConfig{DATABASE_USER}){
				Selity::Dialog->factory()->msgbox("You can not use $main::selityConfig{DATABASE_USER} as restricted user");
				$dbUser = undef;
			}
		} while (!$dbUser);

		Selity::Dialog->factory()->set('cancel-label','Autogenerate');
		my $dbPass;
		$dbPass = Selity::Dialog->factory()->inputbox("Please enter database password (leave blank for autogenerate)", $dbPass);
		if(!$dbPass){
			$dbPass = '';
			my @allowedChars = ('A'..'Z', 'a'..'z', '0'..'9', '_');
			$dbPass .= $allowedChars[rand()*($#allowedChars + 1)] for (1..16);
		}
		$dbPass =~ s/('|"|`|#|;|\/|\s|\||<|\?|\\)/_/g;
		Selity::Dialog->factory()->msgbox("Your password is '".$dbPass."' (we have stripped not allowed chars)");
		Selity::Dialog->factory()->set('cancel-label');
		$self::dovecotConfig{'DATABASE_USER'}		= $dbUser;
		$self::dovecotConfig{'DATABASE_PASSWORD'}	= $dbPass;
	}

	#restore db connection
	my $crypt = Selity::Crypt->new();
	my $err = $self->check_sql_connection(
			$main::selityConfig{'DATABASE_USER'},
			$main::selityConfig{'DATABASE_PASSWORD'} ? $crypt->decrypt_db_password($main::selityConfig{'DATABASE_PASSWORD'}) : ''
	);
	if ($err){
		error("$err");
		return 1;
	}

	if(!$connData) {
		my $database = Selity::Database->new(db => $main::selityConfig{DATABASE_TYPE})->factory();

		## We ensure that new data doesn't exist in database
		$err = $database->doQuery(
			'dummy',
			"
				DELETE FROM
					`mysql`.`tables_priv`
				WHERE
					`Host` = ?
				AND
					`Db` = ?
				AND
					`User` = ?;
			", $main::selityConfig{'DATABASE_HOST'}, $main::selityConfig{'DATABASE_NAME'}, $self::dovecotConfig{'DATABASE_USER'}
		);
		return $err if (ref $err ne 'HASH');

		$err = $database->doQuery(
			'dummy',
			"
				DELETE FROM
					`mysql`.`user`
				WHERE
					`Host` = ?
				AND
					`User` = ?;
			", $main::selityConfig{'DATABASE_HOST'}, $self::dovecotConfig{'DATABASE_USER'}
		);
		return $err if (ref $err ne 'HASH');


		$err = $database->doQuery('dummy', 'FLUSH PRIVILEGES');
		return $err if (ref $err ne 'HASH');

		## Inserting new data into the database
		$err = $database->doQuery(
			'dummy',
			"
				GRANT SELECT ON `$main::selityConfig{DATABASE_NAME}`.*
				TO ?@?
				IDENTIFIED BY ?;
			", $self::dovecotConfig{DATABASE_USER}, $main::selityConfig{DATABASE_HOST}, $self::dovecotConfig{DATABASE_PASSWORD}
		);
		return $err if (ref $err ne 'HASH');

		$err = $database->doQuery(
			'dummy',
			"
				GRANT SELECT,INSERT,UPDATE,DELETE ON `$main::selityConfig{DATABASE_NAME}`.`quota_dovecot`
				TO ?@?
			", $self::dovecotConfig{DATABASE_USER}, $main::selityConfig{DATABASE_HOST}
		);
		return $err if (ref $err ne 'HASH');
	}

	0;
}

sub check_sql_connection{

	use Selity::Database;

	my ($self, $dbUser, $dbPass) = (@_);
	my $database = Selity::Database->new(db => $main::selityConfig{DATABASE_TYPE})->factory();
	$database->set('DATABASE_USER',		$dbUser);
	$database->set('DATABASE_PASSWORD',	$dbPass);

	return $database->connect();
}

sub registerHooks{

	my $self = shift;

	use Servers::mta;

	my $mta = Servers::mta->factory();

	$mta->registerPostHook(
		'buildConf', sub { return $self->mtaConf(@_); }
	) if $mta->can('registerPostHook');

	0;
}

sub mtaConf{

	my $self	= shift;
	my $content	= shift || '';

	debug($content);

	use Selity::Templator;
	use Servers::mta;

	my $mta	= Servers::mta->factory($main::selityConfig{MTA_SERVER});

	my $poBloc = getBloc(
		"$mta->{commentChar} dovecot begin",
		"$mta->{commentChar} dovecot end",
		$content
	);

	$content = replaceBloc(
		"$mta->{commentChar} po setup begin",
		"$mta->{commentChar} po setup end",
		$poBloc,
		$content,
		undef
	);

	#register again wait next config file
	$mta->registerPostHook(
		'buildConf', sub { return $self->mtaConf(@_); }
	) if $mta->can('registerPostHook');

	debug($content);

	$content;
}

1;
