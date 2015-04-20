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

use strict;
use warnings;

sub setup_start_up {

	Selity::Boot->new(mode => 'setup')->init({nodatabase => 'yes'});

	#enter silent mode
	silent(1);

	0;
}


sub setup_engine {

	use Selity::Stepper;
	## Starting user dialog
	user_dialog();

	my @steps = (
		[\&load_old_selity_cfg, 'Loading old Selity configuration file: '],
		[\&update_selity_cfg, 'Save old variable if needed: '],
		[\&setup_system_users, 'Creating default users: '],
		[\&setup_selity_database_connection, 'Selity database connection: '],
		[\&setup_selity_database, 'Selity database: '],
		[\&setup_system_dirs, 'Selity directories: '],
		[\&setup_base_server_IP, 'Selity system IP: '],
		[\&setup_hosts, 'Selity system hosts file: '],
		[\&askVHOST, 'Selity virtual hostname'],
		[\&setup_resolver, 'Selity system resolver: '],
		[\&askPHPTimezone, 'PHP timezone: '],
		[\&setup_default_sql_data, 'Selity default SQL data: '],
		[\&setup_ssl, 'Selity certificate setup: '],
		[\&setup_gui_pma, 'Selity PMA configuration file: '],
		[\&preinstallServers, 'Selity server preinstall task: '],
		[\&preinstallAddons, 'Selity addons preinstall task: '],
		[\&installServers, 'Selity server install task: '],
		[\&installAddons, 'Selity addons install task: '],
		[\&postinstallServers, 'Selity server postinstall task: '],
		[\&postinstallAddons, 'Selity addons postinstall task: '],
		[\&setup_crontab, 'Selity crontab file: '],
		[\&setup_selity_daemon_network, 'Selity init scripts: '],
		[\&askBackup, 'Setting backup: '],
		[\&rebuild_customers_cfg, 'Rebuilding all customers configuration files: '],
		[\&set_permissions, 'Permissions setup: '],
		[\&restart_services, 'Starting all services: '],
		[\&save_conf, 'Backup conf file: '],
		[\&additional_tasks, 'Additional tasks: '],
	);
	my $rs = 0;
	my $step = 1;
	for (@steps){
		$rs |= step($_->[0], $_->[1], scalar @steps, $step);
		$step++;
	}
	Selity::Dialog->factory()->endGauge() if Selity::Dialog->factory()->needGauge();

	$rs;
}

sub user_dialog {

	use Selity::Dialog;

	return 0 if $main::noprompt;

	Selity::Dialog->factory()->set('yes-label','CONTINUE');
	Selity::Dialog->factory()->set('no-label','EXIT');
	if (Selity::Dialog->factory()->yesno(
					"\n
						Welcome to \\Z1Selity version $main::selityConfig{'Version'}\\Zn Setup Dialog.

						\\Zu\\Z4[NOTICE]\\Zn
						Make sure you have read and performed all steps from docs/distro/INSTALL document (where distro is your linux distribution).

						\\Zu\\Z4[NOTE]\\Zn
						During the migration process some or all services might require to be shut down or restarted.

						Only services that are not marked with 'NO' in your selity.conf configuration file will be processed by this program.
						You can stop this process by pushing \\Z1EXIT\\Z0 button
						To continue select \\Z1CONTINUE\\Z0 button"

					)
	){
		Selity::Dialog->factory()->msgbox(
					"\n
					\\Z1[NOTICE]\\Zn

					The update process was aborted by user..."
		);
		exit 0;
	}

	0;
}

sub load_old_selity_cfg {

	use Selity::Config;

	$main::selityConfigOld = {};

	$main::selityConfigOld = {};
	my $oldConf = "$main::selityConfig{'CONF_DIR'}/selity.old.conf";

	tie %main::selityConfigOld, 'Selity::Config','fileName' => $oldConf if (-f $oldConf);
	verbose($main::selityConfigOld{'DEBUG'} || $main::selityConfig{'DEBUG'});

	0;
}

sub setup_selity_database_connection {

	use Selity::Crypt;
	use Selity::Dialog;

	my $pass = $main::selityConfig{'DATABASE_PASSWORD'};
	my $crypt = Selity::Crypt->new();

	if(!check_sql_connection(
			$main::selityConfig{'DATABASE_TYPE'},
			'',
			$main::selityConfig{'DATABASE_HOST'} || '',
			$main::selityConfig{'DATABASE_PORT'} || '',
			$main::selityConfig{'DATABASE_USER'} || '',
			$main::selityConfig{'DATABASE_PASSWORD'} ? $crypt->decrypt_db_password($main::selityConfig{'DATABASE_PASSWORD'}) : ''
		)
	){
	}elsif($main::selityConfigOld{'DATABASE_TYPE'} && !check_sql_connection(
			$main::selityConfigOld{'DATABASE_TYPE'},
			'',
			$main::selityConfigOld{'DATABASE_HOST'} || '',
			$main::selityConfigOld{'DATABASE_PORT'} || '',
			$main::selityConfigOld{'DATABASE_USER'} || '',
			$main::selityConfigOld{'DATABASE_PASSWORD'} ? $crypt->decrypt_db_password($main::selityConfigOld{'DATABASE_PASSWORD'}) : ''
		)
	){
		$main::selityConfig{'DATABASE_TYPE'}		= $main::selityConfigOld{'DATABASE_TYPE'};
		$main::selityConfig{'DATABASE_HOST'}		= $main::selityConfigOld{'DATABASE_HOST'};
		$main::selityConfig{'DATABASE_PORT'}		= $main::selityConfigOld{'DATABASE_PORT'};
		$main::selityConfig{'DATABASE_USER'}		= $main::selityConfigOld{'DATABASE_USER'};
		$main::selityConfig{'DATABASE_PASSWORD'}	= $main::selityConfigOld{'DATABASE_PASSWORD'};
	} else {
		my (
			$dbType,
			$dbHost,
			$dbPort,
			$dbUser,
			$dbPass
		) = (
			'mysql',
			$main::selityConfig{'DATABASE_HOST'},
			$main::selityConfig{'DATABASE_PORT'},
			$main::selityConfig{'DATABASE_USER'}
		);

		use Data::Validate::Domain qw/is_domain/;
		my %options = $main::selityConfig{'DEBUG'} ? (domain_private_tld => qr /^(?:bogus|test)$/) : ();

		while (check_sql_connection($dbType, '', $dbHost, $dbPort, $dbUser, $dbPass)){
			my $msg = '';
			do{
				$dbHost = Selity::Dialog->factory()->inputbox( "Please enter database host name (default localhost) $msg", $dbHost);
				$msg = "\n\n$dbHost is not a valid hostname!"
			} while (! (Data::Validate::Domain->new(%options)->is_domain($dbHost)) && $dbHost ne 'localhost');

			$msg = '';
			do{
				$dbPort = Selity::Dialog->factory()->inputbox("Please enter database port name (default null or 3306) $msg", $dbPort);
				$dbPort =~ s/[^\d]//g;
				$msg = "\n\n$dbPort is not a valid port number!";
			} while ($dbPort && $dbPort !~ /^[\d]*$/);

			$dbUser = Selity::Dialog->factory()->inputbox('Please enter database user name (default root)', $dbUser);

			$dbPass = Selity::Dialog->factory()->inputbox('Please enter database password','');

		}

		use Net::LibIDN qw/idn_to_ascii idn_to_unicode/;

		if ($main::selityConfig{'DATABASE_TYPE'} ne $dbType) {$main::selityConfig{'DATABASE_TYPE'} = $dbType};
		if ($main::selityConfig{'DATABASE_HOST'} ne idn_to_ascii($dbHost, 'utf-8')) {$main::selityConfig{'DATABASE_HOST'} = idn_to_ascii($dbHost, 'utf-8');}
		if ($main::selityConfig{'DATABASE_PORT'} ne $dbPort) {$main::selityConfig{'DATABASE_PORT'} = $dbPort;}
		if ($main::selityConfig{'DATABASE_USER'} ne $dbUser) {$main::selityConfig{'DATABASE_USER'} = $dbUser;}
		if ($main::selityConfig{'DATABASE_PASSWORD'} ne $crypt->encrypt_db_password($dbPass)) {$main::selityConfig{'DATABASE_PASSWORD'} = $crypt->encrypt_db_password($dbPass);}

	}
	0;
}

sub check_sql_connection{

	my ($dbType, $dbName, $dbHost, $dbPort, $dbUser, $dbPass) = (@_);

	use Selity::Database;

	my $database = Selity::Database->new(db => $dbType)->factory();
	$database->set('DATABASE_NAME', $dbName);
	$database->set('DATABASE_HOST', $dbHost);
	$database->set('DATABASE_PORT', $dbPort);
	$database->set('DATABASE_USER', $dbUser);
	$database->set('DATABASE_PASSWORD', $dbPass);

	return $database->connect();
}

sub setup_selity_database {

	use Selity::Crypt;
	use Selity::Dialog;

	my $crypt = Selity::Crypt->new();

	my $dbName = $main::selityConfig{'DATABASE_NAME'} ? $main::selityConfig{'DATABASE_NAME'} : ($main::selityConfigOld{'DATABASE_NAME'} ? $main::selityConfigOld{'DATABASE_NAME'} : undef);

	if(!$dbName || check_sql_connection(
			$main::selityConfig{'DATABASE_TYPE'},
			$dbName,
			$main::selityConfig{'DATABASE_HOST'},
			$main::selityConfig{'DATABASE_PORT'},
			$main::selityConfig{'DATABASE_USER'},
			$main::selityConfig{'DATABASE_PASSWORD'} ? $crypt->decrypt_db_password($main::selityConfig{'DATABASE_PASSWORD'}) : ''
		)
	){

		$dbName = 'selity' unless $dbName;
		my $msg = '';

		do{
			$dbName = Selity::Dialog->factory()->inputbox("Please enter database name (default $dbName)$msg", $dbName);

			if($dbName =~ /[:;]/){
				$dbName = undef ;
				$msg = "\nNot allowed chars : and ;";
			} else {
				$msg = '';
			}
		} while (!$dbName);

		#test if we can connect using user`s suplied database
		if(check_sql_connection(
				$main::selityConfig{'DATABASE_TYPE'},
				$dbName,
				$main::selityConfig{'DATABASE_HOST'},
				$main::selityConfig{'DATABASE_PORT'},
				$main::selityConfig{'DATABASE_USER'},
				$main::selityConfig{'DATABASE_PASSWORD'} ? $crypt->decrypt_db_password($main::selityConfig{'DATABASE_PASSWORD'}) : ''
			)
		){
			#no, then we create tables in database
			if (my $error = createDB($dbName, $main::selityConfig{'DATABASE_TYPE'})){
				error("$error");
				return 1;
			}
		} else {
			#yes we make sure we have last db possible
			if (my $error = updateDb()){
				error("$error");
				return 1;
			}
		}

		#save new database name
		if ($main::selityConfig{'DATABASE_NAME'} ne $dbName) {$main::selityConfig{'DATABASE_NAME'} = $dbName};

	} else {

		$main::selityConfig{'DATABASE_NAME'} = $main::selityConfigOld{'DATABASE_NAME'} if(! $main::selityConfig{'DATABASE_NAME'});

		if (my $error = updateDb()){
			error("$error");
			return 1;
		}
	}

	#secure accounts
	my $rdata = Selity::Database->factory()->doQuery('User', "SELECT `User`, `Host` FROM `mysql`.`user` WHERE `Password` = ''");
	if(ref $rdata ne 'HASH'){
		error("$rdata");
		return 1;
	}

	foreach (keys %$rdata) {
		my $error = Selity::Database->factory()->doQuery('drop', "DROP USER ?@?", $_, $rdata->{$_}->{Host});
		error("$error") if(ref $error ne 'HASH');
	}

	0;
}

sub createDB{
	my $dbName = shift;
	my $dbType = shift;

	use Selity::Database;

	my $database = Selity::Database->new(db => $dbType)->factory();
	$database->set('DATABASE_NAME', '');
	my $error = $database->connect();
	return $error if $error;

	my $qdbName = $database->quoteIdentifier($dbName);
	$error = $database->doQuery('dummy', "CREATE DATABASE $qdbName CHARACTER SET utf8 COLLATE utf8_unicode_ci;");

	$database->set('DATABASE_NAME', $dbName);
	$error = $database->connect();
	return $error if $error;

	$error = importSQLFile($database, "$main::selityConfig{'CONF_DIR'}/database/database.sql");
	return $error if ($error);

	0;
}

sub importSQLFile{
	my $database	= shift;
	my $file		= shift;

	use Selity::File;
	use Selity::Dialog;
	use Selity::Stepper;

	my $content = Selity::File->new(filename => $file)->get();
	$content =~ s/^(--[^\n]{0,})?\n//mg;
	my @queries = (split /;\n/, $content);

	my $title = "Executing ".@queries." queries:";

	startDetail();

	my $step = 1;
	for (@queries){
		my $error = $database->doQuery('dummy', $_);
		return $error if (ref $error ne 'HASH');
		my $msg = $queries[$step] ? "$title\n$queries[$step]" : $title;
		step('', $msg, scalar @queries, $step);
		$step++;
	}

	endDetail();
	0;
}

sub updateDb {

	use Selity::File;
	use Selity::Execute;

	my ($rs, $stdout, $stderr);

	my $file	= Selity::File->new(filename => "$main::selityConfig{'ROOT_DIR'}/engine/setup/updDB.php");
	my $content	= $file->get();
	return 1 if(!$content);

	if($content =~ s/{GUI_ROOT_DIR}/$main::selityConfig{'GUI_ROOT_DIR'}/) {
		$rs = $file->set($content);
		return 1 if($rs != 0);
		$rs = $file->save();
		return 1 if($rs != 0);
	}

	$rs = execute("$main::selityConfig{'CMD_PHP'} $main::selityConfig{'ROOT_DIR'}/engine/setup/updDB.php", \$stdout, \$stderr);
	error("$stdout $stderr") if $rs;
	return ($stdout ? "$stdout " : '' ).$stderr." exitcode: $rs" if $rs;

	0;
}

sub setup_system_dirs {

	use Selity::Dir;
	my $rootUName = $main::selityConfig{'ROOT_USER'};
	my $rootGName = $main::selityConfig{'ROOT_GROUP'};

	for (
		[$main::selityConfig{'USER_HOME_DIR'},	$rootUName,	$rootGName,	0555],
		[$main::selityConfig{'LOG_DIR'},			$rootUName,	$rootGName,	0555],
		[$main::selityConfig{'BACKUP_FILE_DIR'},	$rootUName,	$rootGName,	0750],
	) {
		Selity::Dir->new(dirname => $_->[0])->make({ user => $_->[1], group => $_->[2], mode => $_->[3]}) and return 1;
	}

	0;
}

sub setup_base_server_IP{
	use Selity::Dialog;
	use Selity::IP;
	my $rs;

	my $ips = Selity::IP->new();
	$rs = $ips->loadIPs();
	return $rs if $rs;

	return 0 if(
		$main::selityConfig{'BASE_SERVER_IP'} &&
		$main::selityConfig{'BASE_SERVER_IP'} ne '127.0.0.1' &&
		$main::selityConfig{'BASE_SERVER_IP'} ne $ips->normalize('::1')
	);

	if(
		$main::selityConfigOld{'BASE_SERVER_IP'} &&
		$main::selityConfigOld{'BASE_SERVER_IP'} ne '127.0.0.1' &&
		$main::selityConfig{'BASE_SERVER_IP'} ne $ips->normalize('::1')
	){
		$main::selityConfig{'BASE_SERVER_IP'} = $main::selityConfigOld{'BASE_SERVER_IP'};
		return 0;
	}

	my %allIPs = map { $_ => undef } ($ips->getIPs());

	if(keys %allIPs == 0){
		error('Can not determine servers ips');
		return 1;
	}

	my ($out, $card);

	while (! ($out = Selity::Dialog->factory()->radiolist("Please select your external ip:", keys %allIPs, 'none'))){}
	if(! ($ips->isValidIp($out))){
		do{
			while (! ($out = Selity::Dialog->factory()->inputbox("Please enter your ip:", (keys %allIPs)[0]))){}
		} while(! ($ips->isValidIp($out) && $out ne '127.0.0.1' && $out ne $ips->normalize('::1')) );
		unless(exists $ips->{ips}->{$out}){
			while (! ($card = Selity::Dialog->factory()->radiolist("Please select your network card:", ($ips->getNetCards)))){}
			$ips->attachIpToNetCard($card, $out);
			$rs = $ips->reset();
			return $rs if $rs;
		}
	}

	$main::selityConfig{'BASE_SERVER_IP'} = $out if($main::selityConfig{'BASE_SERVER_IP'} ne $out);

	Selity::Dialog->factory()->set('yes-label','Yes');
	Selity::Dialog->factory()->set('no-label','No');

	my $database = Selity::Database->new(db => $main::selityConfig{'DATABASE_TYPE'})->factory();

	my %otherIPs = %allIPs;
	delete($otherIPs{$out}) if exists $otherIPs{$out};

	my $toSave ='';
	if (scalar(keys %otherIPs) > 0 ){
		my $out = Selity::Dialog->factory()->yesno("\n\n\t\t\tInsert other ips into database?");
		$toSave = Selity::Dialog->factory()->checkbox("Please select ip`s to be entered to database:", keys %otherIPs) if !$out;
		$toSave =~ s/"//g;
	}

	for (split(/ /, $toSave), $out){
		my $error = $database->doQuery(
			'dummy',
			"INSERT IGNORE INTO `server_ips` (`ip_number`, `ip_card`, `ip_status`, `ip_id`)
			VALUES(?, ?, 'toadd', (SELECT `ip_id` FROM `server_ips` as t1 WHERE t1.`ip_number` = ?));",
			$_, $ips->getCardByIP($_), $_
		);
		return $error if (ref $error ne 'HASH');
	}
	0;
}

sub setup_hosts {

	use Selity::File;
	use Selity::IP;

	my $rs = 0;
	my $err = askHostname();
	return 1 if($err);

	my @labels = split /\./, $main::selityConfig{'SERVER_HOSTNAME'};

	use Net::LibIDN qw/idn_to_ascii/;

	my $host = idn_to_ascii(shift(@labels), 'utf-8');
	my $hostname_local = "$main::selityConfig{'SERVER_HOSTNAME'}.local";

	my $file = Selity::File->new(filename => "/etc/hosts");
	$rs |= $file->copyFile("/etc/hosts.bkp") if(!-f '/etc/hosts.bkp');

	my $content = "# 'hosts' file configuration.\n\n";

	$content .= "127.0.0.1\t$hostname_local\tlocalhost\n";
	$content .= "$main::selityConfig{'BASE_SERVER_IP'}\t$main::selityConfig{'SERVER_HOSTNAME'}\t$host\n";
	$content .= "::ffff:$main::selityConfig{'BASE_SERVER_IP'}\t$main::selityConfig{'SERVER_HOSTNAME'}\t$host\n" if Selity::IP->new()->getIpType($main::selityConfig{BASE_SERVER_IP}) eq 'ipv4';
	$content .= "::1\tip6-localhost\tip6-loopback\n" if Selity::IP->new()->getIpType($main::selityConfig{BASE_SERVER_IP}) eq 'ipv4';
	$content .= "::1\tip6-localhost\tip6-loopback\t$host\n"  if Selity::IP->new()->getIpType($main::selityConfig{BASE_SERVER_IP}) ne 'ipv4';
	$content .= "fe00::0\tip6-localnet\n";
	$content .= "ff00::0\tip6-mcastprefix\n";
	$content .= "ff02::1\tip6-allnodes\n";
	$content .= "ff02::2\tip6-allrouters\n";
	$content .= "ff02::3\tip6-allhosts\n";

	$rs |= $file->set($content);
	$rs |= $file->save();
	$rs |= $file->mode(0644);
	$rs |= $file->owner($main::selityConfig{'ROOT_USER'}, $main::selityConfig{'ROOT_GROUP'});

	$file = Selity::File->new(filename => "/etc/hostname");
	$rs |= $file->copyFile("/etc/hostname.bkp") if(!-f '/etc/hostname.bkp');
	$content = $host;
	$rs |= $file->set($content);
	$rs |= $file->save();
	$rs |= $file->mode(0644);
	$rs |= $file->owner($main::selityConfig{'ROOT_USER'}, $main::selityConfig{'ROOT_GROUP'});

	my ($stdout, $stderr);
	$rs |= execute("$main::selityConfig{'CMD_HOSTNAME'} $host", \$stdout, \$stderr);
	debug("$stdout") if $stdout;
	warning("$stderr") if !$rs && $stderr;
	error("$stderr") if $rs && $stderr;
	error("Can not set hostname") if $rs && !$stderr;

	$rs;
}

sub askHostname{

	my ($out, $err, $hostname);

	use Selity::Dialog;
	use Socket;

	#$hostname = gethostbyaddr($main::selityConfig{'BASE_SERVER_IP'}, &AF_INET);
	if( !$hostname || $hostname !~ /^([\w][\w-]{0,253}[\w])\.([\w][\w-]{0,253}[\w])\.([a-zA-Z]{2,6})$/) {
		if (execute("$main::selityConfig{'CMD_HOSTNAME'} -f", \$hostname, \$err)){
			error("Can not find hostname (misconfigured?): $err");
			$hostname = '';
		}
	}

	chomp($hostname);

	if($hostname && $main::selityConfig{'SERVER_HOSTNAME'} eq $hostname){
		return 0;
	}
	if($hostname && $main::selityConfigOld{'SERVER_HOSTNAME'} && $main::selityConfigOld{'SERVER_HOSTNAME'} eq $hostname){
		$main::selityConfig{'SERVER_HOSTNAME'} = $main::selityConfigOld{'SERVER_HOSTNAME'};
		return 0;
	}

	use Data::Validate::Domain qw/is_domain/;

	my %options = $main::selityConfig{'DEBUG'} ? (domain_private_tld => qr /^(?:bogus|test)$/) : ();

	my ($msg, @labels) = ('', ());
	do{
		while (! ($out = Selity::Dialog->factory()->inputbox( "Please enter a fully qualified hostname (fqdn): $msg", $hostname))){}
		$msg = "\n\n$out is not a valid fqdn!";
		@labels = split(/\./, $out);
	} while (! (Data::Validate::Domain->new(%options)->is_domain($out) && ( @labels >= 3)));

	use Net::LibIDN qw/idn_to_ascii idn_to_unicode/;

	$main::selityConfig{'SERVER_HOSTNAME'} = idn_to_ascii($out, 'utf-8');

	0;
}

sub setup_resolver {

	use Selity::File;
	use Selity::Dialog;

	my ($err, $file, $content, $out);

	if(-f $main::selityConfig{'RESOLVER_CONF_FILE'}) {
		$file = Selity::File->new(filename => $main::selityConfig{'RESOLVER_CONF_FILE'});
		$content = $file->get();

		if (! $content){
			$err = "Can't read $main::selityConfig{'RESOLVER_CONF_FILE'}";
			error("$err");
			return 1;
		}

		if($main::selityConfig{'LOCAL_DNS_RESOLVER'} !~ /yes|no/i) {
			if($main::selityConfigOld{'LOCAL_DNS_RESOLVER'} && $main::selityConfigOld{'LOCAL_DNS_RESOLVER'} =~ /yes|no/i){
				$main::selityConfig{'LOCAL_DNS_RESOLVER'} = $main::selityConfigOld{'LOCAL_DNS_RESOLVER'};
			} else {
				while (! ($out = Selity::Dialog->factory()->radiolist("Do you want allow the system resolver to use the local nameserver?:", ('yes', 'no')))){}
				$main::selityConfig{'LOCAL_DNS_RESOLVER'} = $out;
			}
		}

		if($main::selityConfig{'LOCAL_DNS_RESOLVER'} =~ /yes/i) {
			if($content !~ /nameserver 127.0.0.1/i) {
				$content =~ s/(nameserver.*)/nameserver 127.0.0.1\n$1/i;
			}
		} else {
			$content =~ s/nameserver 127.0.0.1//i;
		}

		# Saving the old file if needed
		if(!-f "$main::selityConfig{'RESOLVER_CONF_FILE'}.bkp") {
			$file->copyFile("$main::selityConfig{'RESOLVER_CONF_FILE'}.bkp") and return 1;
		}

		# Storing the new file
		$file->set($content) and return 1;
		$file->save() and return 1;
		$file->owner($main::selityConfig{'ROOT_USER'}, $main::selityConfig{'ROOT_GROUP'}) and return 1;
		$file->mode(0644) and return 1;

	} else {
		error("Unable to found your resolv.conf file!");
		return 1;
	}

	0;
}

sub setup_crontab {

	use Selity::File;
	use Selity::Templator;

	my ($rs, $cfgTpl, $err);

	my $awstats = '';
	my ($rkhunter, $chkrootkit);

	# Directories paths
	my $cfgDir = $main::selityConfig{'CONF_DIR'} . '/cron.d';
	my $bkpDir = $cfgDir . '/backup';
	my $wrkDir = $cfgDir . '/working';

	# Retrieving production directory path
	my $prodDir = ($^O =~ /bsd$/ ? '/usr/local/etc/cron.daily/selity' : '/etc/cron.d');

	# Saving the current production file if it exists
	if(-f "$prodDir/selity") {
		Selity::File->new(filename => "$prodDir/selity")->copyFile("$bkpDir/selity." . time) and return 1;
	}

	## Building new configuration file

	# Loading the template from /etc/selity/cron.d/selity
	$cfgTpl = Selity::File->new(filename => "$cfgDir/selity")->get();
	return 1 if (!$cfgTpl);

	# Awstats cron task preparation (On|Off) according status in selity.conf
	if ($main::selityConfig{'AWSTATS_ACTIVE'} ne 'yes' || $main::selityConfig{'AWSTATS_MODE'} eq 1) {
		$awstats = '#';
	}

	# Search and cleaning path for rkhunter and chkrootkit programs
	# @todo review this s...
	($rkhunter = `which rkhunter`) =~ s/\s$//g;
	($chkrootkit = `which chkrootkit`) =~ s/\s$//g;

	# Building the new file
	$cfgTpl = Selity::Templator::process(
		{
			LOG_DIR				=> $main::selityConfig{'LOG_DIR'},
			CONF_DIR			=> $main::selityConfig{'CONF_DIR'},
			QUOTA_ROOT_DIR		=> $main::selityConfig{'QUOTA_ROOT_DIR'},
			TRAFF_ROOT_DIR		=> $main::selityConfig{'TRAFF_ROOT_DIR'},
			TOOLS_ROOT_DIR		=> $main::selityConfig{'TOOLS_ROOT_DIR'},
			BACKUP_ROOT_DIR		=> $main::selityConfig{'BACKUP_ROOT_DIR'},
			RKHUNTER_LOG		=> $main::selityConfig{'RKHUNTER_LOG'},
			CHKROOTKIT_LOG		=> $main::selityConfig{'CHKROOTKIT_LOG'},
			AWSTATS_ROOT_DIR	=> $main::selityConfig{'AWSTATS_ROOT_DIR'},
			AWSTATS_ENGINE_DIR	=> $main::selityConfig{'AWSTATS_ENGINE_DIR'},
			'AW-ENABLED'		=> $awstats,
			'RK-ENABLED'		=> !length($rkhunter) ? '#' : '',
			RKHUNTER			=> $rkhunter,
			'CR-ENABLED'		=> !length($chkrootkit) ? '#' : '',
			CHKROOTKIT			=> $chkrootkit
		},
		$cfgTpl
	);
	return 1 if (!$cfgTpl);

	## Storage and installation of new file

	# Storing new file in the working directory
	my $file = Selity::File->new(filename => "$wrkDir/selity");
	$file->set($cfgTpl);
	$file->save() and return 1;
	$file->owner($main::selityConfig{'ROOT_USER'}, $main::selityConfig{'ROOT_GROUP'}) and return 1;
	$file->mode(0644) and return 1;

	# Install the new file in production directory
	$file->copyFile("$prodDir/") and return 1;

	0;
}

sub setup_selity_daemon_network {

	my ($rs, $rdata, $fileName, $stdout, $stderr);

	# Odering is important here.
	# Service selity_network has to be enabled to start service selity_daemon. It's a
	# dependency added to be sure that if an admin adds an new IP through the GUI,
	# the traffic will always be correctly computed. When we'll switch to mutli-server,
	# the traffic logger will be review to avoid this dependency
	for ($main::selityConfig{'CMD_SELITYN'}, $main::selityConfig{'CMD_SELITYD'}) {
		# Do not process if the service is disabled
		next if(/^no$/i);

		($fileName) = /.*\/([^\/]*)$/;

		my $file = Selity::File->new(filename => $_);
		$file->owner($main::selityConfig{'ROOT_USER'}, $main::selityConfig{'ROOT_GROUP'}) and return 1;
		$file->mode(0755) and return 1;

		# Services installation / update (Debian, Ubuntu)
		$rs = execute("/usr/sbin/update-rc.d -f $fileName remove", \$stdout, \$stderr);
		debug("$stdout") if $stdout;
		error("$stderr") if $rs;

		# Fix for #119: Defect - Error when adding IP's
		# We are now using dependency based boot sequencing (insserv)
		# See http://wiki.debian.org/LSBInitScripts ; Must be read carrefully
		$rs = execute("/usr/sbin/update-rc.d $fileName defaults", \$stdout, \$stderr);
		debug("$stdout") if $stdout;
		error("$stderr") if $rs;
	}

	0;
}

sub set_permissions {

	use Selity::Rights;

	my $rs;
	my $rootUName	= $main::selityConfig{'ROOT_USER'};
	my $rootGName	= $main::selityConfig{'ROOT_GROUP'};
	my $masterUName	= $main::selityConfig{'MASTER_GROUP'};
	my $CONF_DIR	= $main::selityConfig{'CONF_DIR'};
	my $ROOT_DIR	= $main::selityConfig{'ROOT_DIR'};
	my $LOG_DIR		= $main::selityConfig{'LOG_DIR'};

	$rs |= setRights("$CONF_DIR", {user => $rootUName, group => $masterUName, mode => '0770'});
	$rs |= setRights("$CONF_DIR/selity.conf", {user => $rootUName, group => $masterUName, mode => '0660'});
	$rs |= setRights("$CONF_DIR/selity-db-keys", {user => $rootUName, group => $masterUName, mode => '0640'});
	$rs |= setRights("$ROOT_DIR/engine", {user => $rootUName, group => $masterUName, mode => '0755', recursive => 'yes'});
	$rs |= setRights($LOG_DIR, {user => $rootUName, group => $masterUName, mode => '0750'});

	0;
}

sub restart_services {

	use Selity::Dialog;
	use Selity::Stepper;

	startDetail();

	my @services = (
		#['Variable holding command', 'command to execute', 'ignore error if 0 exit on error if 1']
		['CMD_SELITYN',			'restart',	1],
		['CMD_SELITYD',			'restart',	1],
		['CMD_CLAMD',			'reload',	1],
		['CMD_POSTGREY',		'restart',	1],
		['CMD_POLICYD_WEIGHT',	'reload',	0],
		['CMD_AMAVIS',			'reload',	1]
	);

	my ($rs, $stdout, $stderr);
	my $count = 1;

	for (@services) {
		if($main::selityConfig{$_->[0]} && ($main::selityConfig{$_->[0]} !~ /^no$/i) && -f $main::selityConfig{$_->[0]}) {
			$rs = step(
				sub { execute("$main::selityConfig{$_->[0]} $_->[1]", \$stdout, \$stderr)},
				"Restarting $main::selityConfig{$_->[0]}",
				scalar @services,
				$count
			);
			debug("$main::selityConfig{$_->[0]} $stdout") if $stdout;
			error("$main::selityConfig{$_->[0]} $stderr $rs") if ($rs && $_->[2]);
			return $rs if ($rs && $_->[2]);
		}
		$count++;
	}

	endDetail();

	0;
}

sub setup_default_sql_data {

	use Selity::Crypt;
	use Selity::Database;

	my ($error);

	my $database = Selity::Database->new(db => $main::selityConfig{'DATABASE_TYPE'})->factory();
	my $admins = $database->doQuery(
						'admin_id',
						'SELECT
							*
						FROM
							`admin`
						WHERE
							`admin_type` = \'admin\'
						'
	);
	return 1 if (ref $admins ne 'HASH');

	my $msg = '';
	if( ! scalar keys %{$admins} ){
		my ($admin, $pass, $rpass, $msg, $admin_email) = ('admin');
		while(!($admin	= Selity::Dialog->factory()->inputbox('Please enter administrator login name', $admin))){};
		do{
			while(!($pass	= Selity::Dialog->factory()->passwordbox("Please enter administrator password ". ($msg ? $msg : ''),''))){};
			while(!($rpass	= Selity::Dialog->factory()->passwordbox('Please repeat administrator password',''))){};
			$msg = "\n\n\\Z1Password do not match\\Zn.\n\nPlease try again";
		}while($pass ne $rpass);
		$pass = Selity::Crypt->new()->crypt_md5_data($pass);
		$admin_email = askAdminEmail();
		my $error = $database->doQuery(
			'dummy',
			"INSERT INTO `admin` (`admin_name`, `admin_pass`, `admin_type`, `email`)
			VALUES (?, ?, 'admin', ?);", $admin, $pass, $admin_email
		);
		return $error if (ref $error ne 'HASH');

		$error = $database->doQuery(
			'dummy',
			"INSERT INTO `user_gui_props` (`user_id`) values (LAST_INSERT_ID());"
		);
		return $error if (ref $error ne 'HASH');
	} else {
		askAdminEmail();
	}

	## First Ip data - Begin

	debug('Inserting primary Ip data...');

	$error = $database->doQuery(
		'dummy',
		"
		UPDATE
			`server_ips`
		SET
			`ip_domain` = ?
		WHERE
			`ip_number` = ?
		", $main::selityConfig{'SERVER_HOSTNAME'}, $main::selityConfig{'BASE_SERVER_IP'}
	);
	return $error if (ref $error ne 'HASH');

	$error = $database->doQuery(
		'dummy',
		"
		UPDATE
			`server_ips`
		SET
			`ip_domain` = NULL
		WHERE
			`ip_number` != ?
		AND
			`ip_domain` = ?
		", $main::selityConfig{'BASE_SERVER_IP'}, $main::selityConfig{'SERVER_HOSTNAME'}
	);
	return $error if (ref $error ne 'HASH');

	askMYSQLPrefix();

	0;
}

sub askMYSQLPrefix{

	my $useprefix	= $main::selityConfig{'MYSQL_PREFIX'} ? $main::selityConfig{'MYSQL_PREFIX'} : ($main::selityConfigOld{'MYSQL_PREFIX'} ? $main::selityConfigOld{'MYSQL_PREFIX'} : '');
	my $prefix		= $main::selityConfig{'MYSQL_PREFIX_TYPE'} ? $main::selityConfig{'MYSQL_PREFIX_TYPE'} : ($main::selityConfigOld{'MYSQL_PREFIX_TYPE'} ? $main::selityConfigOld{'MYSQL_PREFIX_TYPE'} : '');

	while(!$useprefix || !$prefix){
		my $prefix = $prefix = Selity::Dialog->factory()->radiolist("Use MySQL Prefix? Possible values:", 'do not use', 'infront', 'after');
		if($prefix eq 'do not use'){
			$useprefix	= 'no';
			$prefix		= 'none';
		} elsif($prefix =~ /^(infront|after)$/){
			$useprefix	= 'yes';
		}
	}

	$main::selityConfig{'MYSQL_PREFIX'} = $useprefix if($main::selityConfig{'MYSQL_PREFIX'} ne $useprefix);
	$main::selityConfig{'MYSQL_PREFIX_TYPE'} = $prefix if($main::selityConfig{'MYSQL_PREFIX_TYPE'} ne $prefix);

	0;
}

sub askAdminEmail{

	my $admin_email = $main::selityConfig{'DEFAULT_ADMIN_ADDRESS'} ? $main::selityConfig{'DEFAULT_ADMIN_ADDRESS'} : ($main::selityConfigOld{'DEFAULT_ADMIN_ADDRESS'} ? $main::selityConfigOld{'DEFAULT_ADMIN_ADDRESS'} : '');
	use Email::Valid;
	my $msg = '';
	while(!$admin_email){
		$admin_email = Selity::Dialog->factory()->inputbox("Please enter administrator e-mail address .$msg");
		$admin_email = '' if(!Email::Valid->address($admin_email));
		$msg = "\n\n\\Z1Email is not valid\\Zn.\n\nPlease try again";
	}
	$main::selityConfig{'DEFAULT_ADMIN_ADDRESS'} = $admin_email if($main::selityConfig{'DEFAULT_ADMIN_ADDRESS'} ne $admin_email);

	$admin_email;
}

sub setup_gui_pma {

	my $cfgDir	= "$main::selityConfig{'CONF_DIR'}/pma";
	my $bkpDir	= "$cfgDir/backup";
	my $wrkDir	= "$cfgDir/working";
	my $prodDir	= "$main::selityConfig{'GUI_PUBLIC_DIR'}/tools/pma";
	my $dbType	= $main::selityConfig{'DATABASE_TYPE'};
	my $dbHost	= $main::selityConfig{'DATABASE_HOST'};
	my $dbPort	= $main::selityConfig{'DATABASE_PORT'};
	my $dbName	= $main::selityConfig{'DATABASE_NAME'};

	my ($error, $blowfishSecret, $ctrlUser, $ctrlUserPwd, $cfgFile, $file, $rebuild);

	# Saving the current production file if it exists
	if(-f "$prodDir/config.inc.php") {
		$file = Selity::File->new(filename => "$prodDir/config.inc.php")->copyFile("$bkpDir/config.inc.php." . time) and return 1;
	}

	if(-f "$wrkDir/config.inc.php") {
		# Gets the pma configuration file
		$file = Selity::File->new(filename => "$cfgDir/working/config.inc.php");
		$cfgFile = $file->get();
		return 1 if (!$cfgFile);

		# Retrieving the needed values from the working file
		($blowfishSecret, $ctrlUser, $ctrlUserPwd) = map {
			$cfgFile =~ /\['$_'\]\s*=\s*'(.+)'/
		} qw /blowfish_secret controluser controlpass/;
		$rebuild = check_sql_connection($dbType, '', $dbHost, $dbPort, $ctrlUser || '', $ctrlUserPwd || '');

		my $crypt = Selity::Crypt->new();

		my $err = check_sql_connection(
			$main::selityConfig{'DATABASE_TYPE'},
			$main::selityConfig{'DATABASE_NAME'},
			$main::selityConfig{'DATABASE_HOST'},
			$main::selityConfig{'DATABASE_PORT'},
			$main::selityConfig{'DATABASE_USER'},
			$main::selityConfig{'DATABASE_PASSWORD'} ? $crypt->decrypt_db_password($main::selityConfig{'DATABASE_PASSWORD'}) : ''
		);
		if ($err){
			error("$err");
			return 1;
		}

	} else {
		$rebuild = 'yes';
	}

	# Getting blowfish secret
	if(!defined $blowfishSecret) {
		$blowfishSecret		= '';
		my @allowedChars	= ('A'..'Z', 'a'..'z', '0'..'9', '_');
		$blowfishSecret		.= $allowedChars[rand()*($#allowedChars + 1)] for (1..31);
	}

	if($rebuild){
		Selity::Dialog->factory()->msgbox("
							\n\\Z1[WARNING]\\Zn

							Unable to found your working PMA configuration file !

							A new one will be created.
						"
		);

		$ctrlUser = $ctrlUser ? $ctrlUser : ($main::selityConfig{'PMA_USER'} ? $main::selityConfig{'PMA_USER'} : ($main::selityConfigOld{'PMA_USER'} ? $main::selityConfigOld{'PMA_USER'} : 'pma'));

		do{
			$ctrlUser = Selity::Dialog->factory()->inputbox("Please enter database user name for the restricted phpmyadmin user (default pma)", $ctrlUser);
			#we will not allow root user to be used as database user for proftpd since account will be restricted
			if($ctrlUser eq $main::selityConfig{DATABASE_USER}){
				Selity::Dialog->factory()->msgbox("You can not use $main::selityConfig{DATABASE_USER} as restricted user");
				$ctrlUser = undef;
			}
		} while (!$ctrlUser);

		Selity::Dialog->factory()->set('cancel-label','Autogenerate');

		# Ask for proftpd SQL user password
		$ctrlUserPwd = Selity::Dialog->factory()->inputbox("Please enter database password (leave blank for autogenerate)", '');
		if(!$ctrlUserPwd){
			$ctrlUserPwd = '';
			my @allowedChars = ('A'..'Z', 'a'..'z', '0'..'9', '_');
			$ctrlUserPwd .= $allowedChars[rand()*($#allowedChars + 1)]for (1..16);
		}
		$ctrlUserPwd =~ s/('|"|`|#|;|\/|\s|\||<|\?|\\)/_/g;
		Selity::Dialog->factory()->msgbox("Your password is '".$ctrlUserPwd."'");
		Selity::Dialog->factory()->set('cancel-label');

		my $database = Selity::Database->new(db => $main::selityConfig{'DATABASE_TYPE'})->factory();

		## We ensure that new data doesn't exist in database
		$error = $database->doQuery(
			'dummy',"
				DELETE FROM `mysql`.`tables_priv`
				WHERE `Host` = ?
				AND `Db` = 'mysql' AND `User` = ?;
			", $dbHost, $ctrlUser
		);
		return $error if (ref $error ne 'HASH');

		$error = $database->doQuery(
			'dummy',"
				DELETE FROM `mysql`.`user`
				WHERE `Host` = ?
				AND `User` = ?;
			", $dbHost, $ctrlUser
		);
		return $error if (ref $error ne 'HASH');

		$error = $database->doQuery(
			'dummy',"
				DELETE FROM `mysql`.`columns_priv`
				WHERE `Host` = ?
				AND `User` = ?;
			", $dbHost, $ctrlUser
		);
		return $error if (ref $error ne 'HASH');

		# Flushing privileges
		$error = $database->doQuery('dummy','FLUSH PRIVILEGES');
		return $error if (ref $error ne 'HASH');

		# Adding the new pma control user
		$error = $database->doQuery(
			'dummy',"
				GRANT USAGE ON
					`mysql`.*
				TO
					?@?
				IDENTIFIED BY
					?
			", $ctrlUser, $dbHost, $ctrlUserPwd
		);
		return $error if (ref $error ne 'HASH');

		## Sets the rights for the pma control user

		$error = $database->doQuery(
			'dummy',"
				GRANT SELECT ON `mysql`.`db` TO ?@?;
			", $ctrlUser, $dbHost
		);
		return $error if (ref $error ne 'HASH');

		$error = $database->doQuery(
			'dummy',"
				GRANT SELECT (
					Host, User, Select_priv, Insert_priv, Update_priv, Delete_priv,
					Create_priv, Drop_priv, Reload_priv, Shutdown_priv, Process_priv,
					File_priv, Grant_priv, References_priv, Index_priv, Alter_priv,
					Show_db_priv, Super_priv, Create_tmp_table_priv,
					Lock_tables_priv, Execute_priv, Repl_slave_priv,
					Repl_client_priv
				)
				ON `mysql`.`user`
				TO ?@?;
			", $ctrlUser, $dbHost
		);
		return $error if (ref $error ne 'HASH');

		$error = $database->doQuery(
			'dummy',"GRANT SELECT ON `mysql`.`host` TO ?@?;", $ctrlUser, $dbHost
		);
		return $error if (ref $error ne 'HASH');

		$error = $database->doQuery(
			'dummy',"
				GRANT SELECT
					(`Host`, `Db`, `User`, `Table_name`, `Table_priv`, `Column_priv`)
				ON
					`mysql`.`tables_priv`
				TO
					?@?;
			", $ctrlUser, $dbHost
		);
		return $error if (ref $error ne 'HASH');

		$main::selityConfig{'PMA_USER'} = $ctrlUser if($main::selityConfig{'PMA_USER'} ne $ctrlUser);
	}

	## Building the new file

	# Getting the template file
	$file = Selity::File->new(filename => "$cfgDir/config.inc.tpl");
	$cfgFile = $file->get();
	return 1 if (!$cfgFile);

	$cfgFile = process(
		{
			PMA_USER	=> $ctrlUser,
			PMA_PASS	=> $ctrlUserPwd,
			HOSTNAME	=> $dbHost,
			UPLOADS_DIR	=> "$main::selityConfig{'GUI_ROOT_DIR'}/data/uploads",
			TMP_DIR		=> "$main::selityConfig{'GUI_ROOT_DIR'}/data/tmp",
			BLOWFISH	=> $blowfishSecret
		},
		$cfgFile
	);
	return 1 if (!$cfgFile);

	# Storing the file in the working directory
	$file = Selity::File->new(filename => "$cfgDir/working/config.inc.php");
	$file->set($cfgFile) and return 1;
	$file->save() and return 1;
	$file->mode(0640) and return 1;
	$file->owner($main::selityConfig{'ROOT_USER'}, $main::selityConfig{'ROOT_GROUP'}) and return 1;

	# Installing the file in the production directory
	# Note: permission are set by the set-gui-permissions.sh script
	$file->copyFile("$prodDir/") and return 1;

	#restore defaul connection
	my $crypt = Selity::Crypt->new();

	$error = check_sql_connection(
		$main::selityConfig{'DATABASE_TYPE'},
		$main::selityConfig{'DATABASE_NAME'},
		$main::selityConfig{'DATABASE_HOST'},
		$main::selityConfig{'DATABASE_PORT'},
		$main::selityConfig{'DATABASE_USER'},
		$main::selityConfig{'DATABASE_PASSWORD'} ? $crypt->decrypt_db_password($main::selityConfig{'DATABASE_PASSWORD'}) : ''
	);
	return $error if ($error);

	0;
}

sub askPHPTimezone{

	use Selity::Dialog;
	use DateTime;
	use DateTime::TimeZone;

	my $dt;

	if($main::selityConfig{'PHP_TIMEZONE'}){
		return 0;
	}

	if($main::selityConfigOld{'PHP_TIMEZONE'}){
		$main::selityConfig{'PHP_TIMEZONE'} = $main::selityConfigOld{'PHP_TIMEZONE'};
		return 0;
	}

	$dt = DateTime->new(year => 0, time_zone => 'local')->time_zone->name;

	my $msg = '';
	do{
		while (! ($dt = Selity::Dialog->factory()->inputbox( "Please enter Server`s Timezone $msg", $dt))){}
		$msg = "$dt is not a valid timezone! The continent and the city, both must start with a capital letter, e.g. Europe/London'";
	} while (! DateTime::TimeZone->is_valid_name($dt));

	$main::selityConfig{'PHP_TIMEZONE'} = $dt;

	0;
}

sub askVHOST{

	use Selity::Dialog;

	if($main::selityConfig{'BASE_SERVER_VHOST'}){
		return 0;
	}

	if($main::selityConfigOld{'BASE_SERVER_VHOST'}){
		$main::selityConfig{'BASE_SERVER_VHOST'} = $main::selityConfigOld{'BASE_SERVER_VHOST'};
		return 0;
	}

	use Data::Validate::Domain qw/is_domain/;

	my $hostname = "admin.$main::selityConfig{'SERVER_HOSTNAME'}";

	my %options = $main::selityConfig{'DEBUG'} ? (domain_private_tld => qr /^(?:bogus|test)$/) : ();

	my ($msg, @labels) = ('', ());
	do{
		while (! ($hostname = Selity::Dialog->factory()->inputbox( "Please enter the domain name where Selity will be reachable on: $msg", $hostname))){}
		$msg = "\n\n$hostname is not a valid fqdn!";
		@labels = split(/\./, $hostname);
	} while (! (Data::Validate::Domain->new(%options)->is_domain($hostname) && ( @labels >= 3)));

	use Net::LibIDN qw/idn_to_ascii/;

	$main::selityConfig{'BASE_SERVER_VHOST'} = idn_to_ascii($hostname, 'utf-8');

	0;
}

sub save_conf{


	use Selity::File;

	my$file = Selity::File->new(filename => "$main::selityConfig{'CONF_DIR'}/selity.conf");
	my $cfg = $file->get() or return 1;

	$file = Selity::File->new(filename => "$main::selityConfig{'CONF_DIR'}/selity.old.conf");
	$file->set($cfg) and return 1;
	$file->save and return 1;
	$file->mode(0644) and return 1;
	$file->owner($main::selityConfig{'ROOT_USER'}, $main::selityConfig{'ROOT_GROUP'}) and return 1;

	0;
}

sub update_selity_cfg {

	for(qw/
		ZIP
		BACKUP_HOUR
		BACKUP_MINUTE
		USER_INITIAL_THEME
		FTP_USERNAME_SEPARATOR
		DATE_FORMAT
		GUI_EXCEPTION_WRITERS
		DEBUG
	/){
		if($main::selityConfigOld{$_} && $main::selityConfigOld{$_} ne $main::selityConfig{$_}){
			$main::selityConfig{$_} = $main::selityConfigOld{$_};
		}
	}

	0;
}

sub setup_system_users{

	use Modules::SystemGroup;
	use Modules::SystemUser;

	my $group = Modules::SystemGroup->new();
	$group->{system}	= 'yes';
	$group->addSystemGroup($main::selityConfig{'MASTER_GROUP'}) and return 1;

	0;
}

sub askBackup{

	use Selity::Dialog;

	my $BACKUP_SELITY	= $main::selityConfig{'BACKUP_SELITY'} ? $main::selityConfig{'BACKUP_SELITY'} : ($main::selityConfigOld{'BACKUP_SELITY'} ? $main::selityConfigOld{'BACKUP_SELITY'} : '');
	my $BACKUP_DOMAINS	= $main::selityConfig{'BACKUP_DOMAINS'} ? $main::selityConfig{'BACKUP_DOMAINS'} : ($main::selityConfigOld{'BACKUP_DOMAINS'} ? $main::selityConfigOld{'BACKUP_DOMAINS'} : '');

	if (!$BACKUP_SELITY){
		while (! ($BACKUP_SELITY = Selity::Dialog->factory()->radiolist("Do you want to enable backup for Selity configuration?", 'yes', 'no'))){}
	}
	if($BACKUP_SELITY ne $main::selityConfig{'BACKUP_SELITY'}){ $main::selityConfig{'BACKUP_SELITY'} = $BACKUP_SELITY; }

	if (!$BACKUP_DOMAINS){
		while (! ($BACKUP_DOMAINS = Selity::Dialog->factory()->radiolist("Do you want to enable backup for domains?", 'yes', 'no'))){}
	}
	if($BACKUP_DOMAINS ne $main::selityConfig{'BACKUP_DOMAINS'}){ $main::selityConfig{'BACKUP_DOMAINS'} = $BACKUP_DOMAINS; }

	0;
}

sub additional_tasks{

	use Selity::Stepper;

	startDetail();

	my @steps = (
		[\&setup_rkhunter, 'Selity Rkhunter configuration:']
	);
	my $step = 1;
	for (@steps){
		step($_->[0], $_->[1], scalar @steps, $step);
		$step++;
	}

	endDetail();

	0;
}

sub setup_rkhunter {

	my ($rs, $rdata);

	# Deleting any existent log files
	my $file = Selity::File->new (filename => $main::selityConfig{'RKHUNTER_LOG'});
	$file->set();
	$file->save() and return 1;
	$file->owner('root', 'adm');
	$file->mode(0644);

	# Updates the rkhunter configuration provided by Debian like distributions
	# to disable the default cron task (Selity provides its own cron job for rkhunter)
	if(-e '/etc/default/rkhunter') {
		# Get the file as a string
		$file = Selity::File->new (filename => '/etc/default/rkhunter');
		$rdata = $file->get();
		return 1 if(!$rdata);

		# Disable cron task default
		$rdata =~ s/CRON_DAILY_RUN="(yes)?"/CRON_DAILY_RUN="no"/gmi;

		# Saving the modified file
		$file->set($rdata) and return 1;
		$file->save() and return 1;
	}

	# Updates the logrotate configuration provided by Debian like distributions
	# to modify rigts
	if(-e '/etc/logrotate.d/rkhunter') {
		# Get the file as a string
		$file = Selity::File->new (filename => '/etc/logrotate.d/rkhunter');
		$rdata = $file->get();
		return 1 if(!$rdata);

		# Disable cron task default
		$rdata =~ s/create 640 root adm/create 644 root adm/gmi;

		# Saving the modified file
		$file->set($rdata) and return 1;
		$file->save() and return 1;
	}

	# Update weekly cron task provided by Debian like distributions to avoid
	# creation of unreadable log file
	if(-e '/etc/cron.weekly/rkhunter') {
		# Get the rkhunter file content
		$file = Selity::File->new (filename => '/etc/cron.weekly/rkhunter');
		$rdata = $file->get();
		return 1 if(!$rdata);

		# Adds `--nolog`option to avoid unreadable log file
		$rdata =~ s/(--versioncheck\s+|--update\s+)(?!--nolog)/$1--nolog /g;

		# Saving the modified file
		$file->set($rdata) and return 1;
		$file->save() and return 1;
	}

	0;
}

sub rebuild_customers_cfg {

	use Selity::Boot;

	my $tables = {
		ssl_certs => 'status',
		user_system_props => 'user_status',
		domain_aliasses => 'alias_status',
		subdomain_alias => 'subdomain_alias_status',
		mail_users => 'status',
		htaccess => 'status',
		htaccess_groups => 'status',
		htaccess_users => 'status'
	};

	# Set status as 'change'
	my $error;
	my $database = Selity::Database->new(db => $main::selityConfig{'DATABASE_TYPE'})->factory();
	while (my ($table, $field) = each %$tables) {
		$error = $database->doQuery('dummy',
			"
				UPDATE
					$table
				SET
					$field = 'change'
				WHERE
					$field = 'ok'
				;
			"
		);
		return $error if (ref $error ne 'HASH');
	}


	Selity::Boot->new()->unlock();

	my ($stdout, $stderr, $rs);
	$rs = execute("perl $main::selityConfig{'ENGINE_ROOT_DIR'}/selity-rqst-mngr", \$stdout, \$stderr);
	debug("$stdout") if $stdout;
	error("$stderr") if $stderr;
	error("Error while rebuilding customers configuration files") if(!$stderr && $rs);
	Selity::Boot->new()->lock();
	return $rs if $rs;

	0;
}

sub setup_ssl{

	use Selity::Dialog;

	my $rs;

	$main::selityConfig{'SSL_ENABLED'} = $main::selityConfigOld{'SSL_ENABLED'}
		if(!$main::selityConfig{'SSL_ENABLED'} && $main::selityConfigOld{'SSL_ENABLED'});
	$main::selityConfig{'BASE_SERVER_VHOST_PREFIX'} = $main::selityConfigOld{'BASE_SERVER_VHOST_PREFIX'}
		if $main::selityConfigOld{'BASE_SERVER_VHOST_PREFIX'} && ($main::selityConfig{'BASE_SERVER_VHOST_PREFIX'} ne $main::selityConfigOld{'BASE_SERVER_VHOST_PREFIX'});

	if(!$main::selityConfig{'SSL_ENABLED'}){
		Modules::openssl->new()->{openssl_path} = $main::selityConfig{'CMD_OPENSSL'};
		$rs = sslDialog();
		return $rs if $rs;
	} elsif($main::selityConfig{'SSL_ENABLED'} eq 'yes') {
		Modules::openssl->new()->{openssl_path}				= $main::selityConfig{'CMD_OPENSSL'};
		Modules::openssl->new()->{cert_path}				= "$main::selityConfig{'GUI_CERT_DIR'}/$main::selityConfig{'SERVER_HOSTNAME'}.pem";
		Modules::openssl->new()->{intermediate_cert_path}	= "$main::selityConfig{'GUI_CERT_DIR'}/$main::selityConfig{'SERVER_HOSTNAME'}.pem";
		Modules::openssl->new()->{key_path}					= "$main::selityConfig{'GUI_CERT_DIR'}/$main::selityConfig{'SERVER_HOSTNAME'}.pem";
		if(Modules::openssl->new()->ssl_check_all()){
			Selity::Dialog->factory()->msgbox("Certificate is missing or corrupt. Starting recover");
			$rs = sslDialog();
			return $rs if $rs;
		}
	}

	if($main::selityConfig{'SSL_ENABLED'} ne 'yes'){
		$main::selityConfig{'BASE_SERVER_VHOST_PREFIX'} = "http://";
	};

	0;
}

sub ask_certificate_key_path{

	use Selity::Dialog;
	use Modules::openssl;

	my $rs;
	my $key = "/root/$main::selityConfig{'SERVER_HOSTNAME'}.key";
	my $pass = '';

	do{
		$rs = Selity::Dialog->factory()->passwordbox("Please enter password for key if needed:", $pass);
		$rs =~s/(["\$`\\])/\\$1/g;
		Modules::openssl->new()->{key_pass} = $rs;
		do{
			while (! ($rs = Selity::Dialog->factory()->fselect($key))){}
		}while (! -f $rs);
		Modules::openssl->new()->{key_path} = $rs;
		$key = $rs;
		$rs = Modules::openssl->new()->ssl_check_key();
	}while($rs);

	0;
}

sub ask_intermediate_certificate_path{

	use Selity::Dialog;
	use Modules::openssl;

	my $rs;
	my $cert = '/root/';

	Selity::Dialog->factory()->set('yes-label');
	Selity::Dialog->factory()->set('no-label');
	return 0 if(Selity::Dialog->factory()->yesno('Do you have an intermediate certificate?'));
	do{
		while (! ($rs = Selity::Dialog->factory()->fselect($cert))){}
	}while ($rs && !-f $rs);
	Modules::openssl->new()->{intermediate_cert_path} = $rs;

	0;
}

sub ask_certificate_path{

	use Selity::Dialog;
	use Modules::openssl;

	my $rs;
	my $cert = "/root/$main::selityConfig{'SERVER_HOSTNAME'}.crt";

	Selity::Dialog->factory()->msgbox('Please select certificate');
	do{
		do{
			while (! ($rs = Selity::Dialog->factory()->fselect($cert))){}
		}while (! -f $rs);
		Modules::openssl->new()->{cert_path} = $rs;
		$cert = $rs;
		$rs = Modules::openssl->new()->ssl_check_cert();
	}while($rs);

	0;
}

sub sslDialog{

	use Selity::Dialog;
	use Modules::openssl;

	my $rs;

	while (! ($rs = Selity::Dialog->factory()->radiolist("Do you want to activate SSL?", 'no', 'yes'))){}
	if($rs ne $main::selityConfig{'SSL_ENABLED'}){ $main::selityConfig{'SSL_ENABLED'} = $rs; }
	if($rs eq 'yes'){
		Modules::openssl->new()->{new_cert_path} = $main::selityConfig{'GUI_CERT_DIR'};
		Modules::openssl->new()->{new_cert_name} = $main::selityConfig{'SERVER_HOSTNAME'};
		while (! ($rs = Selity::Dialog->factory()->radiolist('Select method', 'Create a self signed certificate', 'I already have a signed certificate'))){}
		$rs = $rs eq 'Create a self signed certificate' ? 0 : 1;
		Modules::openssl->new()->{cert_selfsigned} = $rs;
		Modules::openssl->new()->{vhost_cert_name} = $main::selityConfig{'SERVER_HOSTNAME'} if ( !$rs );

		if( Modules::openssl->new()->{cert_selfsigned}){
			Modules::openssl->new()->{intermediate_cert_path} = '';
			ask_certificate_key_path();
			ask_intermediate_certificate_path();
			ask_certificate_path();
		}
		$rs = Modules::openssl->new()->ssl_export_all();
		return $rs if $rs;
	}
	if($main::selityConfig{'SSL_ENABLED'} eq 'yes'){
		while (! ($rs = Selity::Dialog->factory()->radiolist("Select default access mode for master domain?", 'https', 'http'))){}
		$main::selityConfig{'BASE_SERVER_VHOST_PREFIX'} = "$rs://";
	}

	0;
}

sub preinstallServers{

	use Selity::Dir;
	use FindBin;
	use Selity::Stepper;

	my ($rs, $file, $class, $server, $msg);

	my $dir	= Selity::Dir->new(dirname => "$main::selityConfig{'ENGINE_ROOT_DIR'}/PerlLib/Servers");
	$rs		= $dir->get();
	return $rs if $rs;

	my @servers = $dir->getFiles();

	my $step = 1;
	startDetail();

	for(@servers){
		s/\.pm//;
		$file	= "Servers/$_.pm";
		$class	= "Servers::$_";
		require $file;
		$server	= $class->factory();
		$msg = "Performing preinstall tasks for ".uc($_)." server". ($main::selityConfig{uc($_)."_SERVER"} ? ": ".$main::selityConfig{uc($_)."_SERVER"} : '');
		$rs |= step(sub{ $server->preinstall() }, $msg, scalar @servers, $step) if($server->can('preinstall'));
		$step++;
	}

	endDetail();

	$rs;

}

sub preinstallAddons{

	use Selity::Dir;
	use FindBin;
	use Selity::Stepper;

	my ($rs, $file, $class, $addons, $msg);

	my $dir	= Selity::Dir->new(dirname => "$main::selityConfig{'ENGINE_ROOT_DIR'}/PerlLib/Addons");
	$rs		= $dir->get();
	return $rs if $rs;

	my @addons = $dir->getFiles();

	my $step = 1;
	startDetail();

	for(@addons){
		s/\.pm//;
		$file	= "Addons/$_.pm";
		$class	= "Addons::$_";
		require $file;
		$addons	= $class->new();
		$msg = "Performing preinstall tasks for ".uc($_);
		$rs |= step(sub{ $addons->preinstall() }, $msg, scalar @addons, $step) if($addons->can('preinstall'));
		$step++;
	}

	endDetail();

	$rs;
}

sub installServers{

	use Selity::Dir;
	use FindBin;
	use Selity::Stepper;

	my ($rs, $file, $class, $server, $msg);

	my $dir	= Selity::Dir->new(dirname => "$main::selityConfig{'ENGINE_ROOT_DIR'}/PerlLib/Servers");
	$rs		= $dir->get();
	return $rs if $rs;

	my @servers = $dir->getFiles();

	my $step = 1;
	startDetail();

	for(@servers){
		s/\.pm//;
		$file	= "Servers/$_.pm";
		$class	= "Servers::$_";
		require $file;
		$server	= $class->factory();
		$msg = "Performing install tasks for ".uc($_)." server". ($main::selityConfig{uc($_)."_SERVER"} ? ": ".$main::selityConfig{uc($_)."_SERVER"} : '');
		$rs |= step(sub{ $server->install() }, $msg, scalar @servers, $step) if($server->can('install'));
		$step++;
	}

	endDetail();

	$rs;
}

sub installAddons{

	use Selity::Dir;
	use FindBin;
	use Selity::Stepper;

	my ($rs, $file, $class, $addons, $msg);

	my $dir	= Selity::Dir->new(dirname => "$main::selityConfig{'ENGINE_ROOT_DIR'}/PerlLib/Addons");
	$rs		= $dir->get();
	return $rs if $rs;

	my @addons = $dir->getFiles();

	my $step = 1;
	startDetail();

	for(@addons){
		s/\.pm//;
		$file	= "Addons/$_.pm";
		$class	= "Addons::$_";
		require $file;
		$addons	= $class->new();
		$msg = "Performing install tasks for ".uc($_);
		$rs |= step(sub{ $addons->install() }, $msg, scalar @addons, $step) if($addons->can('install'));
		$step++;
	}

	endDetail();

	$rs;
}

sub postinstallServers{

	use Selity::Dir;
	use FindBin;
	use Selity::Stepper;

	my ($rs, $file, $class, $server, $msg);

	my $dir	= Selity::Dir->new(dirname => "$main::selityConfig{'ENGINE_ROOT_DIR'}/PerlLib/Servers");
	$rs		= $dir->get();
	return $rs if $rs;

	my @servers = $dir->getFiles();

	my $step = 1;
	startDetail();

	for(@servers){
		s/\.pm//;
		$file	= "Servers/$_.pm";
		$class	= "Servers::$_";
		require $file;
		$server	= $class->factory();
		$msg = "Performing postinstall tasks for ".uc($_)." server". ($main::selityConfig{uc($_)."_SERVER"} ? ": ".$main::selityConfig{uc($_)."_SERVER"} : '');
		$rs |= step(sub{ $server->postinstall() }, $msg, scalar @servers, $step) if($server->can('postinstall'));
		$step++;
	}

	endDetail();

	$rs;
}

sub postinstallAddons{

	use Selity::Dir;
	use FindBin;
	use Selity::Stepper;

	my ($rs, $file, $class, $addons, $msg);

	my $dir	= Selity::Dir->new(dirname => "$main::selityConfig{'ENGINE_ROOT_DIR'}/PerlLib/Addons");
	$rs		= $dir->get();
	return $rs if $rs;

	my @addons = $dir->getFiles();

	my $step = 1;
	startDetail();

	for(@addons){
		s/\.pm//;
		$file	= "Addons/$_.pm";
		$class	= "Addons::$_";
		require $file;
		$addons	= $class->new();
		$msg = "Performing postinstall tasks for ".uc($_);
		$rs |= step(sub{ $addons->postinstall() }, $msg, scalar @addons, $step) if($addons->can('postinstall'));
		$step++;
	}

	endDetail();

	$rs;
}

1;
