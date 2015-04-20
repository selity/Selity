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

package Addons::awstats::installer;

use strict;
use warnings;
use Selity::Debug;

use vars qw/@ISA/;

@ISA = ('Common::SingletonClass');
use Common::SingletonClass;


sub askAwstats{

	use Selity::Dialog;

	my ($rs, $force);

	if(!$main::selityConfig{'AWSTATS_ACTIVE'}){
		if($main::selityConfigOld{'AWSTATS_ACTIVE'} && $main::selityConfigOld{'AWSTATS_ACTIVE'} =~ /yes|no/){
			$main::selityConfig{'AWSTATS_ACTIVE'}	= $main::selityConfigOld{'AWSTATS_ACTIVE'};
		} else {
			while (! ($rs = Selity::Dialog->factory()->radiolist("Do you want to enable Awstats?", 'yes', 'no'))){}
			if($rs ne $main::selityConfig{'AWSTATS_ACTIVE'}){
				$main::selityConfig{'AWSTATS_ACTIVE'} = $rs;
				$force = 'yes';
			}
		}
	}

	if($main::selityConfig{'AWSTATS_ACTIVE'} eq 'yes'){
		if($force){
			while (! ($rs = Selity::Dialog->factory()->radiolist("Select Awstats mode?", 'dynamic', 'static'))){}
			$rs = $rs eq 'dynamic' ? 0 : 1;
			$main::selityConfig{'AWSTATS_MODE'} = $rs;
		}
		if(!defined $main::selityConfig{'AWSTATS_MODE'} || $main::selityConfig{'AWSTATS_MODE'} !~ /0|1/){
			if(defined $main::selityConfigOld{'AWSTATS_MODE'} && $main::selityConfigOld{'AWSTATS_MODE'} =~ /0|1/){
				$main::selityConfig{'AWSTATS_MODE'}	= $main::selityConfigOld{'AWSTATS_MODE'};
			} else {
				while (! ($rs = Selity::Dialog->factory()->radiolist("Select Awstats mode?", 'dynamic', 'static'))){}
				$rs = $rs eq 'dynamic' ? 0 : 1;
				$main::selityConfig{'AWSTATS_MODE'} = $rs;
			}
		}
	} else {
		$main::selityConfig{'AWSTATS_MODE'} = '' if $main::selityConfig{'AWSTATS_MODE'} ne '';
	}

	0;
}

sub registerHooks{
	my $self = shift;

	use Servers::httpd;

	my $httpd = Servers::httpd->factory();

	$httpd->registerPreHook(
		'buildConf', sub { return $self->installLogrotate(@_); }
	);

	0;
}

sub install{

	my $self	= shift;
	my $rs		= 0;
	$self->{httpd} = Servers::httpd->factory() unless $self->{httpd} ;

	$self->{user} = $self->{httpd}->can('getRunningUser') ? $self->{httpd}->getRunningUser() : $main::selityConfig{ROOT_USER};
	$self->{group} = $self->{httpd}->can('getRunningUser') ? $self->{httpd}->getRunningGroup() : $main::selityConfig{ROOT_GROUP};

	$self->askAwstats() and return 1;
	if ($main::selityConfig{'AWSTATS_ACTIVE'} eq 'yes') {
		$self->makeDirs() and return 1;
		$self->vhost() and return 1;
	}
	$self->disableConf() and return 1;
	$self->disableCron() and return 1;

	$rs;
}

sub makeDirs{

	use Selity::Dir;

	my $self		= shift;

	Selity::Dir->new(
		dirname => $main::selityConfig{'AWSTATS_CACHE_DIR'}
	)->make({
		user => $self->{user},
		group => $self->{group},
		mode => 0755
	}) and return 1;

	0;
}

sub vhost {

	use Servers::httpd;

	my $rs		= 0;
	my $httpd	= Servers::httpd->factory();

	$httpd->setData({
		AWSTATS_ENGINE_DIR	=> $main::selityConfig{'AWSTATS_ENGINE_DIR'},
		AWSTATS_WEB_DIR		=> $main::selityConfig{'AWSTATS_WEB_DIR'}
	});

	if($httpd->can('buildConfFile')){
		$rs = $httpd->buildConfFile('01_awstats.conf');
		return $rs if $rs;
	}

	if($httpd->can('installConfFile')){
		$rs = $httpd->installConfFile('01_awstats.conf');
		return $rs if $rs;
	}

	if($httpd->can('enableSite')){
		$rs = $httpd->enableSite('01_awstats.conf');
		return $rs if $rs;
	}

	0;
}
sub disableConf{

	use Selity::File;

	my $self	= shift;

	if(-f "$main::selityConfig{'AWSTATS_CONFIG_DIR'}/awstats.conf") {
		Selity::File->new(
			filename => "$main::selityConfig{'AWSTATS_CONFIG_DIR'}/awstats.conf"
		)->moveFile(
			"$main::selityConfig{'AWSTATS_CONFIG_DIR'}/awstats.conf.disabled"
		) and return 1;
	}

	0;
}

sub disableCron{

	use Selity::File;

	my $self	= shift;

	# Removing default Debian Package cron task for awstats
	if(-f "$main::selityConfig{'CRON_D_DIR'}/awstats") {
		Selity::File->new(
			filename => "$main::selityConfig{'CRON_D_DIR'}/awstats"
		)->moveFile(
			"$main::selityConfig{'CONF_DIR'}/cron.d/backup/awstats.system"
		) and return 1;
	}

	0;
}

sub installLogrotate{

	use Selity::Templator;

	my $self	= shift;
	my $content	= shift || '';
	my $file	= shift || '';

	if ($file eq 'logrotate.conf') {
		$content = replaceBloc(
			'# AWSTATS SECTION BEGIN',
			'# AWSTATS SECTION END',
			(
				$main::selityConfig{'AWSTATS_ACTIVE'} eq 'yes'
				?
				"\tprerotate\n".
				"\t\t$main::selityConfig{'AWSTATS_ROOT_DIR'}\/awstats_updateall.pl ".
				"now -awstatsprog=$main::selityConfig{'AWSTATS_ENGINE_DIR'}\/awstats.pl &> \/dev\/null\n".
				"\tendscript"
				:
				''
			),
			$content,
			undef
		);
	} else {
		# Not file we expect, register again
		my $httpd = Servers::httpd->factory();

		$httpd->registerPreHook(
			'buildConf', sub { return $self->installLogrotate(@_); }
		);
	}

	$content;
}

1;
