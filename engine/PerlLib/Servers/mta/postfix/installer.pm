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

package Servers::mta::postfix::installer;

use strict;
use warnings;
use Selity::Debug;
use Selity::Execute;
use Selity::File;
use Selity::Templator;

use vars qw/@ISA/;

@ISA = ('Common::SingletonClass');
use Common::SingletonClass;

sub _init{

	my $self		= shift;

	$self->{cfgDir}	= "$main::selityConfig{'CONF_DIR'}/postfix";
	$self->{bkpDir}	= "$self->{cfgDir}/backup";
	$self->{wrkDir}	= "$self->{cfgDir}/working";
	$self->{vrlDir} = "$self->{cfgDir}/selity";

	my $conf		= "$self->{cfgDir}/postfix.data";
	my $oldConf		= "$self->{cfgDir}/postfix.old.data";

	tie %self::postfixConfig, 'Selity::Config','fileName' => $conf;
	tie %self::postfixOldConfig, 'Selity::Config','fileName' => $oldConf if -f $oldConf;

	0;
}

sub preinstall{

	my $self = shift;

	$self->addUsers() and return 1;
	$self->makeDirs() and return 1;

	0;
}

sub install{

	my $self	= shift;
	my $rs		= 0;

	# Saving all system configuration files if they exists
	for ((
		$self::postfixConfig{'POSTFIX_CONF_FILE'},
		$self::postfixConfig{'POSTFIX_MASTER_CONF_FILE'},
		$self::postfixConfig{'MTA_VIRTUAL_CONF_DIR'}.'/aliases',
		$self::postfixConfig{'MTA_VIRTUAL_CONF_DIR'}.'/domains',
		$self::postfixConfig{'MTA_VIRTUAL_CONF_DIR'}.'/mailboxes',
		$self::postfixConfig{'MTA_VIRTUAL_CONF_DIR'}.'/transport',
		$self::postfixConfig{'MTA_VIRTUAL_CONF_DIR'}.'/sender-access'
	)) {
		$rs |= $self->bkpConfFile($_);
	}

	$rs |= $self->buildConf();
	$rs |= $self->buildLookup();
	$rs |= $self->buildAliasses();
	$rs |= $self->arplSetup();

	$rs |= $self->saveConf();
	$rs |= $self->setEnginePermissions();

	$rs;
}

sub setEnginePermissions{

	debug('Setting engine permissions');

	use Selity::Rights;

	my $self		= shift;
	my $rs;
	my $rootUName	= $main::selityConfig{'ROOT_USER'};
	my $rootGName	= $main::selityConfig{'ROOT_GROUP'};
	my $mtaUName	= $self::postfixConfig{'MTA_MAILBOX_UID_NAME'};
	my $mtaGName	= $self::postfixConfig{'MTA_MAILBOX_GID_NAME'};
	my $mtaCfg		= $self::postfixConfig{'MTA_VIRTUAL_CONF_DIR'};
	my $mtaFolder	= $self::postfixConfig{'MTA_VIRTUAL_MAIL_DIR'};
	my $ROOT_DIR	= $main::selityConfig{'ROOT_DIR'};
	my $LOG_DIR		= $main::selityConfig{'LOG_DIR'};
	$rs |= setRights($mtaCfg, {user => $rootUName, group => $rootGName, dirmode => '0755', filemode => '0644', recursive => 'yes'});
	$rs |= setRights("$ROOT_DIR/engine/messenger", {user => $mtaUName, group => $mtaGName, dirmode => '0750', filemode => '0550', recursive => 'yes'});
	$rs |= setRights("$LOG_DIR/selity-arpl-msgr", {user => $mtaUName, group => $mtaGName, dirmode => '0750', filemode => '0640', recursive => 'yes'});
	$rs |= setRights($mtaFolder, {user => $mtaUName, group => $mtaGName, dirmode => '0750', filemode => '0640', recursive => 'yes'});

	$rs;
}

sub makeDirs{
	use Selity::Dir;

	my $self	= shift;
	my $rs		= 0;

	debug('Creating postfix folders');

	for (
		[$self::postfixConfig{'MTA_VIRTUAL_CONF_DIR'},	$main::selityConfig{'ROOT_USER'},	$main::selityConfig{'ROOT_GROUP'}],
		[$self::postfixConfig{'MTA_VIRTUAL_MAIL_DIR'},	$self::postfixConfig{'MTA_MAILBOX_UID_NAME'},	$self::postfixConfig{'MTA_MAILBOX_GID_NAME'}],
	) {
		$rs |= Selity::Dir->new(dirname => $_->[0])->make({ user => $_->[1], group => $_->[2], mode => 0755});
	}

	$rs;
}

sub addUsers{
	debug('Adding system users...');

	use Modules::SystemGroup;

	my $rs = 0;

	my $group = Modules::SystemGroup->new();

	$group->{system}	= 'yes';
	$rs |= $group->addSystemGroup($self::postfixConfig{'MTA_MAILBOX_GID_NAME'});

	use Modules::SystemUser;
	my $user = Modules::SystemUser->new();

	$user->{comment}	= 'vmail-user';
	$user->{home}		= $self::postfixConfig{'MTA_VIRTUAL_MAIL_DIR'};
	$user->{group}		= $self::postfixConfig{'MTA_MAILBOX_GID_NAME'};
	$user->{system}		= 'yes';

	$rs |= $user->addSystemUser($self::postfixConfig{'MTA_MAILBOX_UID_NAME'});
	$rs |= $user->addToGroup($main::selityConfig{'MASTER_GROUP'});

	$user = Modules::SystemUser->new();
	$rs |= $user->addToGroup($self::postfixConfig{'SASLDB_GROUP'}, $self::postfixConfig{'POSTFIX_USER'});

	$rs;
}

sub buildAliasses{

	my ($rs, $stdout, $stderr);

	# Rebuilding the database for the mail aliases file - Begin
	$rs = execute("$self::postfixConfig{'CMD_NEWALIASES'}", \$stdout, \$stderr);
	debug("$stdout");
	error("$stderr") if($stderr);
	error("Error while executing $self::postfixConfig{'CMD_NEWALIASES'}") if(!$stderr && $rs);

	$rs;
}

sub arplSetup{
	debug('Autoresponder install...');

	my $file;
	my $rs = 0;

	$file = Selity::File->new(filename => "$main::selityConfig{'ROOT_DIR'}/engine/messenger/selity-arpl-msgr");
	$rs |= $file->mode(0755);
	$rs |= $file->owner($self::postfixConfig{'MTA_MAILBOX_UID_NAME'}, $self::postfixConfig{'MTA_MAILBOX_GID_NAME'});

	$rs;
}

sub buildLookup{

	my $self	= shift;
	my $rs		= 0;
	my ($stdout, $stderr, $file);

	use Selity::File;
	use Selity::Execute;

	for (qw/aliases domains mailboxes transport sender-access/) {
		# Storing the new files in the working directory
		$file = Selity::File->new(filename => "$self->{vrlDir}/$_");
		$rs |= $file->copyFile("$self->{wrkDir}");

		# Install the files in the production directory
		$rs |= $file->copyFile("$self::postfixConfig{'MTA_VIRTUAL_CONF_DIR'}");

		# Creating/updating databases for all lookup tables
		my $rv = execute("$self::postfixConfig{'CMD_POSTMAP'} $self::postfixConfig{'MTA_VIRTUAL_CONF_DIR'}/$_", \$stdout, \$stderr);
		debug("$stdout");
		error("$stderr") if($rv);
		$rs |= $rv;
	}

	$rs;
}

sub bkpConfFile{

	use File::Basename;

	my $self		= shift;
	my $rs			= 0;
	my $cfgFile		= shift;
	my $timestamp	= time;

	if(-f $cfgFile){
		my $file	= Selity::File->new( filename => $cfgFile );
		my ($filename, $directories, $suffix) = fileparse($cfgFile);
		if(!-f "$self->{bkpDir}/$filename$suffix.system") {
			$rs |= $file->copyFile("$self->{bkpDir}/$filename$suffix.system");
		} else {
			$rs |= $file->copyFile("$self->{bkpDir}/$filename$suffix.$timestamp");
		}
	}

	$rs;
}

sub saveConf{

	my $self	= shift;
	my $rs		= 0;

	use Selity::File;

	my$file = Selity::File->new(filename => "$self->{cfgDir}/postfix.data");
	my $cfg = $file->get() or return 1;

	$file = Selity::File->new(filename => "$self->{cfgDir}/postfix.old.data");
	$rs |= $file->set($cfg);
	$rs |= $file->save;
	$rs |= $file->mode(0640);
	$rs |= $file->owner($main::selityConfig{'ROOT_USER'}, $main::selityConfig{'ROOT_GROUP'});

	$rs;
}

sub buildConf{

	my $self	= shift;
	my $rs		= 0;

	$rs |= $self->buildMain();
	$rs |= $self->buildMaster();

	$rs;
}

sub buildMaster{

	my $self	= shift;
	my $rs		= 0;

	use Selity::File;
	use Selity::Templator;

	# Storing the new file in the working directory
	my $file = Selity::File->new(filename => "$self->{cfgDir}/master.cf");
	my $cfgTpl	= $file->get();
	return 1 if (!$cfgTpl);

	my @calls = exists $self->{preCalls}->{buildConf}
				?
				(@{$self->{preCalls}->{buildConf}})
				:
				()
	; # is a reason for this!!! Simplify code and you have infinite loop

	# avoid running same hook again if is not self register again
	delete $self->{preCalls}->{buildConf};

	foreach(@calls){
		eval {$cfgTpl = &$_($cfgTpl);};
		error("$@") if ($@);
		$rs |= 1 if $@;
	}

	$cfgTpl = Selity::Templator::process(
		{
			ARPL_USER					=> $self::postfixConfig{'MTA_MAILBOX_UID_NAME'},
			ARPL_GROUP					=> $main::selityConfig{'MASTER_GROUP'},
			ARPL_PATH					=> $main::selityConfig{'ROOT_DIR'}."/engine/messenger/selity-arpl-msgr"
		},
		$cfgTpl
	);
	return 1 if (!$cfgTpl);

	@calls = exists $self->{postCalls}->{buildConf}
				?
				(@{$self->{postCalls}->{buildConf}})
				:
				()
	; # is a reason for this!!! Simplify code and you have infinite loop

	# avoid running same hook again if is not self register again
	delete $self->{postCalls}->{buildConf};

	foreach(@calls){
		eval {$cfgTpl = &$_($cfgTpl);};
		error("$@") if ($@);
		$rs |= 1 if $@;
	}

	$file = Selity::File->new(filename => "$self->{wrkDir}/master.cf");
	$rs |= $file->set($cfgTpl);
	$rs |= $file->save();
	$rs |= $file->mode(0644);
	$rs |= $file->owner($main::selityConfig{'ROOT_USER'}, $main::selityConfig{'ROOT_GROUP'});

	# Installing the new file in the production dir
	$rs |= $file->copyFile($self::postfixConfig{'POSTFIX_MASTER_CONF_FILE'});

	$rs;
}

sub buildMain{

	my $self	= shift;
	my $rs		= 0;

	use Selity::File;
	use Selity::Templator;

	# Loading the template from /etc/selity/postfix/
	my $file	= Selity::File->new(filename => "$self->{cfgDir}/main.cf");
	my $cfgTpl	= $file->get();
	return 1 if (!$cfgTpl);

	# Building the file
	my $hostname = $main::selityConfig{'SERVER_HOSTNAME'};
	my $gid	= getgrnam($self::postfixConfig{'MTA_MAILBOX_GID_NAME'});
	my $uid	= getpwnam($self::postfixConfig{'MTA_MAILBOX_UID_NAME'});

	my @calls = exists $self->{preCalls}->{buildConf}
				?
				(@{$self->{preCalls}->{buildConf}})
				:
				()
	; # is a reason for this!!! Simplify code and you have infinite loop

	# avoid running same hook again if is not self register again
	delete $self->{preCalls}->{buildConf};

	foreach(@calls){
		eval {$cfgTpl = &$_($cfgTpl);};
		error("$@") if ($@);
		$rs |= 1 if $@;
	}

	$cfgTpl = Selity::Templator::process(
		{
			MTA_HOSTNAME				=> $hostname,
			MTA_LOCAL_DOMAIN			=> "$hostname.local",
			MTA_VERSION					=> $main::selityConfig{'Version'},
			MTA_TRANSPORT_HASH			=> $self::postfixConfig{'MTA_TRANSPORT_HASH'},
			MTA_LOCAL_MAIL_DIR			=> $self::postfixConfig{'MTA_LOCAL_MAIL_DIR'},
			MTA_LOCAL_ALIAS_HASH		=> $self::postfixConfig{'MTA_LOCAL_ALIAS_HASH'},
			MTA_VIRTUAL_MAIL_DIR		=> $self::postfixConfig{'MTA_VIRTUAL_MAIL_DIR'},
			MTA_VIRTUAL_DMN_HASH		=> $self::postfixConfig{'MTA_VIRTUAL_DMN_HASH'},
			MTA_VIRTUAL_MAILBOX_HASH	=> $self::postfixConfig{'MTA_VIRTUAL_MAILBOX_HASH'},
			MTA_VIRTUAL_ALIAS_HASH		=> $self::postfixConfig{'MTA_VIRTUAL_ALIAS_HASH'},
			MTA_MAILBOX_MIN_UID			=> $uid,
			MTA_MAILBOX_UID				=> $uid,
			MTA_MAILBOX_GID				=> $gid,
			PORT_POSTGREY				=> $main::selityConfig{'PORT_POSTGREY'},
			GUI_CERT_DIR				=> $main::selityConfig{'GUI_CERT_DIR'},
			SSL							=> ($main::selityConfig{'SSL_ENABLED'} eq 'yes' ? '' : '#')
		},
		$cfgTpl
	);
	return 1 if (!$cfgTpl);

	@calls = exists $self->{postCalls}->{buildConf}
				?
				(@{$self->{postCalls}->{buildConf}})
				:
				()
	; # is a reason for this!!! Simplify code and you have infinite loop

	# avoid running same hook again if is not self register again
	delete $self->{postCalls}->{buildConf};

	foreach(@calls){
		eval {$cfgTpl = &$_($cfgTpl);};
		error("$@") if ($@);
		$rs |= 1 if $@;
	}

	# Storing the new file in working directory
	$file = Selity::File->new(filename => "$self->{wrkDir}/main.cf");
	$rs |= $file->set($cfgTpl);
	$rs |= $file->save();
	$rs |= $file->mode(0644);
	$rs |= $file->owner($main::selityConfig{'ROOT_USER'}, $main::selityConfig{'ROOT_GROUP'});

	# Installing the new file in production directory
	$rs |= $file->copyFile($self::postfixConfig{'POSTFIX_CONF_FILE'});

	$rs;
}

1;
