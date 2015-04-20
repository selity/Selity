#!/usr/bin/perl

# Selity - When virtual hosting becomes scalable
#
# Copyright (C) 2001-2006 by moleSoftware GmbH - http://www.molesoftware.com
# Copyright (C) 2006-2010 by isp Control Panel - http://ispcp.net
# Copyright (C) 2010-2012 by internet Multi Server Control Panel - http://i-mscp.net
# Copyright (C) 2012-2014 by Selity - http://selity.org
#
# The contents of this file are subject to the Mozilla Public License
# Version 1.1 (the "License"); you may not use this file except in
# compliance with the License. You may obtain a copy of the License at
# http://www.mozilla.org/MPL/
#
# Software distributed under the License is distributed on an "AS IS"
# basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
# License for the specific language governing rights and limitations
# under the License.
#
# The Original Code is "VHCS - Virtual Hosting Control System".
#
# The Initial Developer of the Original Code is moleSoftware GmbH.
# Portions created by Initial Developer are Copyright (C) 2001-2006
# by moleSoftware GmbH. All Rights Reserved.
#
# Portions created by the ispCP Team are Copyright (C) 2006-2010 by
# isp Control Panel. All Rights Reserved.
#
# Portions created by the i-MSCP Team are Copyright (C) 2010-2012 by
# internet Multi Server Control Panel. All Rights Reserved.
#
# Portions created by the Selity Team are Copyright (C) 2012 by Selity.
# All Rights Reserved.
#
# The Selity Home Page is:
#
#    http://selity.org
#

BEGIN {

	my %needed 	= (
		'strict' => '',
		'warnings' => '',
		'IO::Socket'=> '',
		'DBI'=> '',
		DBD::mysql => '',
		MIME::Entity => '',
		MIME::Parser => '',
		Crypt::CBC => '',
		Crypt::Blowfish => '',
		Crypt::PasswdMD5 => '',
		MIME::Base64 => '',
		Term::ReadPassword => '',
		File::Basename => '',
		File::Path => '',
		HTML::Entities=> '',
		File::Temp => 'qw(tempdir)',
		File::Copy::Recursive => 'qw(rcopy)',
		Net::LibIDN => 'qw/idn_to_ascii idn_to_unicode/'
	);

	my ($mod, $mod_err, $mod_missing) = ('', '_off_', '');

	for $mod (keys %needed) {

		if (eval "require $mod") {

			eval "use $mod $needed{$mod}";

		} else {

			print STDERR "\n[FATAL] Module [$mod] WAS NOT FOUND !\n" ;

			$mod_err = '_on_';

			if ($mod_missing eq '') {
				$mod_missing .= $mod;
			} else {
				$mod_missing .= ", $mod";
			}
		}
	}

	if ($mod_err eq '_on_') {
		print STDERR "\nModules [$mod_missing] WAS NOT FOUND in your system...\n";

		exit 1;

	} else {
		$| = 1;
	}
}

use strict;
use warnings;

# Hide the "used only once: possible typo" warnings
no warnings 'once';

$main::engine_debug = undef;

require 'selity_common_methods.pl';

################################################################################
# Load Selity configuration from the selity.conf file

if(-e '/usr/local/etc/selity/selity.conf'){
	$main::cfg_file = '/usr/local/etc/selity/selity.conf';
} else {
	$main::cfg_file = '/etc/selity/selity.conf';
}

my $rs = get_conf($main::cfg_file);
die("FATAL: Can't load the selity.conf file") if($rs != 0);


################################################################################
# Enable debug mode if needed
if ($main::cfg{'DEBUG'} != 0) {
	$main::engine_debug = '_on_';
}

my $key_file		= "$main::cfg{'CONF_DIR'}/selity-db-keys";
our $db_pass_key	= '{KEY}';
our $db_pass_iv		= '{IV}';
my $file;

require "$key_file" if( -f $key_file);

################################################################################
# Generating Selity Db key and initialization vector if needed
#
if ($db_pass_key eq '{KEY}' || $db_pass_iv eq '{IV}') {

	print STDERR ("Key file not found at $main::cfg{'CONF_DIR'}/selity-db-keys. Run Setup to fix");
	exit 1;

}

$main::db_pass_key	= $db_pass_key;
$main::db_pass_iv	= $db_pass_iv;

die("FATAL: Can't load database parameters")  if (setup_main_vars() != 0);

################################################################################
# Lock file system variables
#
$main::lock_file = $main::cfg{'MR_LOCK_FILE'};
$main::fh_lock_file = undef;

$main::log_dir = $main::cfg{'LOG_DIR'};
$main::root_dir = $main::cfg{'ROOT_DIR'};

$main::selity = "$main::log_dir/selity-rqst-mngr.el";

################################################################################
# selity_rqst_mngr variables
#
$main::selity_rqst_mngr = "$main::root_dir/engine/selity-rqst-mngr";
$main::selity_rqst_mngr_el = "$main::log_dir/selity-rqst-mngr.el";
$main::selity_rqst_mngr_stdout = "$main::log_dir/selity-rqst-mngr.stdout";
$main::selity_rqst_mngr_stderr = "$main::log_dir/selity-rqst-mngr.stderr";

################################################################################
# selity_dmn_mngr variables
#
$main::selity_dmn_mngr = "$main::root_dir/engine/selity-dmn-mngr";
$main::selity_dmn_mngr_el = "$main::log_dir/selity-dmn-mngr.el";
$main::selity_dmn_mngr_stdout = "$main::log_dir/selity-dmn-mngr.stdout";
$main::selity_dmn_mngr_stderr = "$main::log_dir/selity-dmn-mngr.stderr";

################################################################################
# selity_sub_mngr variables
#
$main::selity_sub_mngr = "$main::root_dir/engine/selity-sub-mngr";
$main::selity_sub_mngr_el = "$main::log_dir/selity-sub-mngr.el";
$main::selity_sub_mngr_stdout = "$main::log_dir/selity-sub-mngr.stdout";
$main::selity_sub_mngr_stderr = "$main::log_dir/selity-sub-mngr.stderr";

################################################################################
# selity_alssub_mngr variables
#
$main::selity_alssub_mngr = "$main::root_dir/engine/selity-alssub-mngr";
$main::selity_alssub_mngr_el = "$main::log_dir/selity-alssub-mngr.el";
$main::selity_alssub_mngr_stdout = "$main::log_dir/selity-alssub-mngr.stdout";
$main::selity_alssub_mngr_stderr = "$main::log_dir/selity-alssub-mngr.stderr";

################################################################################
# selity_als_mngr variables
#
$main::selity_als_mngr = "$main::root_dir/engine/selity-als-mngr";
$main::selity_als_mngr_el = "$main::log_dir/selity-als-mngr.el";
$main::selity_als_mngr_stdout = "$main::log_dir/selity-als-mngr.stdout";
$main::selity_als_mngr_stderr = "$main::log_dir/selity-als-mngr.stderr";

################################################################################
# selity_mbox_mngr variables
#
$main::selity_mbox_mngr = "$main::root_dir/engine/selity-mbox-mngr";
$main::selity_mbox_mngr_el = "$main::log_dir/selity-mbox-mngr.el";
$main::selity_mbox_mngr_stdout = "$main::log_dir/selity-mbox-mngr.stdout";
$main::selity_mbox_mngr_stderr = "$main::log_dir/selity-mbox-mngr.stderr";

################################################################################
# selity_serv_mngr variables
#
$main::selity_serv_mngr = "$main::root_dir/engine/selity-serv-mngr";
$main::selity_serv_mngr_el = "$main::log_dir/selity-serv-mngr.el";
$main::selity_serv_mngr_stdout = "$main::log_dir/selity-serv-mngr.stdout";
$main::selity_serv_mngr_stderr = "$main::log_dir/selity-serv-mngr.stderr";

################################################################################
# selity_net_interfaces_mngr variables
#
$main::selity_net_interfaces_mngr = "$main::root_dir/engine/tools/selity-net-interfaces-mngr";
$main::imccp_net_interfaces_mngr_el = "$main::log_dir/selity-net-interfaces-mngr.el";
$main::selity_net_interfaces_mngr_stdout = "$main::log_dir/selity-net-interfaces-mngr.log";

################################################################################
# selity_htaccess_mngr variables
#
$main::selity_htaccess_mngr = "$main::root_dir/engine/selity-htaccess-mngr";
$main::selity_htaccess_mngr_el = "$main::log_dir/selity-htaccess-mngr.el";
$main::selity_htaccess_mngr_stdout = "$main::log_dir/selity-htaccess-mngr.stdout";
$main::selity_htaccess_mngr_stderr = "$main::log_dir/selity-htaccess-mngr.stderr";

################################################################################
# selity_htusers_mngr variables
#
$main::selity_htusers_mngr = "$main::root_dir/engine/selity-htusers-mngr";
$main::selity_htusers_mngr_el = "$main::log_dir/selity-htusers-mngr.el";
$main::selity_htusers_mngr_stdout = "$main::log_dir/selity-htusers-mngr.stdout";
$main::selity_htusers_mngr_stderr = "$main::log_dir/selity-htusers-mngr.stderr";

################################################################################
# selity_htgroups_mngr variables
#
$main::selity_htgroups_mngr = "$main::root_dir/engine/selity-htgroups-mngr";
$main::selity_htgroups_mngr_el = "$main::log_dir/selity-htgroups-mngr.el";
$main::selity_htgroups_mngr_stdout = "$main::log_dir/selity-htgroups-mngr.stdout";
$main::selity_htgroups_mngr_stderr = "$main::log_dir/selity-htgroups-mngr.stderr";


################################################################################
# selity_vrl_traff variables
#
$main::selity_vrl_traff = "$main::root_dir/engine/messenger/selity-vrl-traff";
$main::selity_vrl_traff_el = "$main::log_dir/selity-vrl-traff.el";
$main::selity_vrl_traff_stdout = "$main::log_dir/selity-vrl-traff.stdout";
$main::selity_vrl_traff_stderr = "$main::log_dir/selity-vrl-traff.stderr";

################################################################################
# selity_httpd_logs variables
#
$main::selity_httpd_logs_mngr_el = "$main::log_dir/selity-httpd-logs-mngr.el";
$main::selity_httpd_logs_mngr_stdout = "$main::log_dir/selity-httpd-logs-mngr.stdout";
$main::selity_httpd_logs_mngr_stderr = "$main::log_dir/selity-httpd-logs-mngr.stderr";

################################################################################
# selity_ftp_acc_mngr variables
#
$main::selity_ftp_acc_mngr_el = "$main::log_dir/selity-ftp-acc-mngr.el";
$main::selity_ftp_acc_mngr_stdout = "$main::log_dir/selity-ftp-acc-mngr.stdout";
$main::selity_ftp_acc_mngr_stderr = "$main::log_dir/selity-ftp-acc-mngr.stderr";

$main::selity_bk_task_el = "$main::log_dir/selity-bk-task.el";
$main::selity_srv_traff_el = "$main::log_dir/selity-srv-traff.el";
$main::selity_dsk_quota_el = "$main::log_dir/selity-dsk-quota.el";

################################################################################
# selity_apps-installer_logs variables
#
$main::selity_sw_mngr = "$main::root_dir/engine/selity-sw-mngr";
$main::selity_sw_mngr_el = "$main::log_dir/selity-sw-mngr.el";
$main::selity_sw_mngr_stdout = "$main::log_dir/selity-sw-mngr.stdout";
$main::selity_sw_mngr_stderr = "$main::log_dir/selity-sw-mngr.stderr";

$main::selity_pkt_mngr = "$main::root_dir/engine/selity-pkt-mngr";
$main::selity_pkt_mngr_el = "$main::log_dir/selity-pkt-mngr.el";
$main::selity_pkt_mngr_stdout = "$main::log_dir/selity-pkt-mngr.stdout";
$main::selity_pkt_mngr_stderr = "$main::log_dir/selity-pkt-mngr.stderr";

1;
