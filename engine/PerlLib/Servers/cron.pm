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

package Servers::cron;

use strict;
use warnings;
use Selity::Debug;
use Data::Dumper;
use vars qw/@ISA/;

@ISA = ('Common::SingletonClass');
use Common::SingletonClass;

sub _init{
	my $self	= shift;

	$self->{cfgDir} = "$main::selityConfig{'CONF_DIR'}/cron.d";
	$self->{bkpDir} = "$self->{cfgDir}/backup";
	$self->{wrkDir} = "$self->{cfgDir}/working";
	$self->{tplDir}	= "$self->{cfgDir}/parts";
}

sub factory{ return Servers::cron->new(); }

sub addTask{

	use Selity::File;
	use Selity::Templator;

	my $self	= shift;
	my $data	= shift;
	my $rs		= 0;

	$data = {} if (ref $data ne 'HASH');

	local $Data::Dumper::Terse = 1;
	debug("Task data: ". (Dumper $data));

	my $errmsg = {
		USER	=> 'You must provide running user!',
		C0MMAND	=> 'You must provide cron command!',
		TASKID	=> 'You must provide a unique task id!',
	};

	foreach(keys %{$errmsg}){
		error("$errmsg->{$_}") unless $data->{$_};
		return 1 unless $data->{$_};
	}
	$data->{MINUTE}		= 1 unless exists $data->{MINUTE};
	$data->{HOUR}		= 1 unless exists $data->{HOUR};
	$data->{DAY}		= 1 unless exists $data->{DAY};
	$data->{MONTH}		= 1 unless exists $data->{MONTH};
	$data->{DWEEK}		= 1 unless exists $data->{DWEEK};
	$data->{LOG_DIR}	= $main::selityConfig{LOG_DIR};

	##BACKUP PRODUCTION FILE
	$rs |=	Selity::File->new(
				filename => "$main::selityConfig{CRON_D_DIR}/selity"
			)->copyFile(
				"$self->{bkpDir}/selity." . time
			) if(-f "$main::selityConfig{CRON_D_DIR}/selity");

	my $file	= Selity::File->new(filename => "$self->{wrkDir}/selity");
	my $wrkFileContent	= $file->get();

	unless($wrkFileContent){
		error("Can not read $self->{wrkDir}/selity");
		$rs = 1;
	} else {
		my $cleanBTag	= Selity::File->new(filename => "$self->{tplDir}/task_b.tpl")->get();
		my $cleanTag	= Selity::File->new(filename => "$self->{tplDir}/task_entry.tpl")->get();
		my $cleanETag	= Selity::File->new(filename => "$self->{tplDir}/task_e.tpl")->get();
		my $bTag 		= process({TASKID => $data->{TASKID}}, $cleanBTag);
		my $eTag 		= process({TASKID => $data->{TASKID}}, $cleanETag);
		my $tag			= process($data, $cleanTag);

		$wrkFileContent = replaceBloc($bTag, $eTag, '', $wrkFileContent, undef);
		$wrkFileContent = replaceBloc($cleanBTag, $cleanETag, "$bTag$tag$eTag", $wrkFileContent, 'keep');

		# Store the file in the working directory
		my $file = Selity::File->new(filename =>"$self->{wrkDir}/selity");
		$rs |= $file->set($wrkFileContent);
		$rs |= $file->save();
		$rs |= $file->mode(0644);
		$rs |= $file->owner($main::selityConfig{ROOT_USER}, $main::selityConfig{ROOT_GROUP});

		# Install the file in the production directory
		$rs |= $file->copyFile("$main::selityConfig{CRON_D_DIR}/selity");
	}

	$rs;
}

sub delTask{

	use Selity::File;
	use Selity::Templator;

	my $self	= shift;
	my $data	= shift;
	my $rs;

	$data = {} if (ref $data ne 'HASH');

	local $Data::Dumper::Terse = 1;
	debug("Data: ". (Dumper $data));

	my $errmsg = {
		TASKID	=> 'You must provide a unique task id!'
	};

	foreach(keys %{$errmsg}){
		error("$errmsg->{$_}") unless $data->{$_};
		return 1 unless $data->{$_};
	}

	##BACKUP PRODUCTION FILE
	$rs |=	Selity::File->new(
				filename => "$main::selityConfig{CRON_D_DIR}/selity"
			)->copyFile(
				"$self->{bkpDir}/selity." . time
			) if(-f "$main::selityConfig{CRON_D_DIR}/selity");

	my $file	= Selity::File->new(filename => "$self->{wrkDir}/selity");
	my $wrkFileContent	= $file->get();

	unless($wrkFileContent){
		error("Can not read $self->{wrkDir}/selity");
		$rs = 1;
	} else {
		my $cleanBTag	= Selity::File->new(filename => "$self->{tplDir}/task_b.tpl")->get();
		my $cleanETag	= Selity::File->new(filename => "$self->{tplDir}/task_e.tpl")->get();
		my $bTag 		= process({TASKID => $data->{TASKID}}, $cleanBTag);
		my $eTag 		= process({TASKID => $data->{TASKID}}, $cleanETag);

		$wrkFileContent = replaceBloc($bTag, $eTag, '', $wrkFileContent, undef);

		# Store the file in the working directory
		my $file = Selity::File->new(filename =>"$self->{wrkDir}/selity");
		$rs |= $file->set($wrkFileContent);
		$rs |= $file->save();
		$rs |= $file->mode(0644);
		$rs |= $file->owner($main::selityConfig{ROOT_USER}, $main::selityConfig{ROOT_GROUP});

		# Install the file in the production directory
		$rs |= $file->copyFile("$main::selityConfig{CRON_D_DIR}/selity");
	}

	$rs;
}

1;
