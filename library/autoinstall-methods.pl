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

sub preInstall {
	debug('Starting...');

	use Selity::Execute;

	my ($rs, $stdout, $stderr);

	fatal('Not a Debian like system') if(_checkPkgManager());

	my @pkg = ();
	push @pkg, 'lsb-release' if(execute("which lsb_release", \$stdout, \$stderr));
	push @pkg, 'dialog' if(execute("which dialog", \$stdout, \$stderr));


	if(scalar @pkg){
		$rs = execute("apt-get -y install @pkg", \$stdout, \$stderr);
		debug("$stdout") if $stdout;
		error("$stderr") if $stderr;
		error("Unable to install the @pkg package(s)") if $rs && !$stderr;

		return $rs if $rs;
	}

	debug('Ending...');
	0;
}

sub installDependencies {
	debug('Starting...');

	my $autoinstallFile = "$FindBin::Bin/library/" .
		lc(Selity::SO->new()->{Distribution}) .'_autoinstall.pm';

	my $class = 'library::' . lc(Selity::SO->new()->{Distribution}) . '_autoinstall';

	if(-f $autoinstallFile){
		require $autoinstallFile ;
		$main::autoInstallClass = $class->new();
		my $rs = $main::autoInstallClass->preBuild() if $main::autoInstallClass->can('preBuild');
		return $rs if $rs;
	}

	debug('Ending...');
	0;
}

sub testRequirements {
	debug('Starting...');

	Selity::Requirements->new()->test('all');

	debug('Ending...');
	0;
}

sub processConfFile {
	debug('Starting...');

	use Selity::SO;

	my $confFile = shift;

	$confFile = "$FindBin::Bin/library/" . lc(Selity::SO->new()->{Distribution}) .
		'-variable.xml' unless $confFile;

	unless(-f $confFile) {
		error("Error $confFile not found");
		return 1;
	}

	# Creating XML object
	my $xml = XML::Simple->new(ForceArray => 1, ForceContent => 1);

	# Reading XML file
	my $data = eval { $xml->XMLin($confFile, VarAttr => 'export') };

	if ($@) {
		error("$@");
		return 1;
	}

	my $rs;

	# Process xml 'folders' nodes
	foreach(@{$data->{folders}}) {
		$_->{content} = _expandVars($_->{content}) if($_->{content});
		eval("our \$" . $_->{export} . " = \"" . $_->{content} . "\";") if($_->{export});
		fatal("$@") if($@);
		return $rs if $rs;

		$rs = _processFolder($_) if($_->{content});
		return $rs if $rs;
	}

	# Process xml 'copy_config' nodes
	foreach(@{$data->{copy_config}}) {
		$_->{content} = _expandVars($_->{content}) if($_->{content});
		$rs = _copyConfig($_) if($_->{content});
		return $rs if $rs;
	}

	# process xml 'copy' nodes
	foreach(@{$data->{copy}}) {
		$_->{content} = _expandVars($_->{content}) if($_->{content});
		$rs = _copy($_) if($_->{content});
		return $rs if $rs;
	}

	# process xml 'create_file' nodes (Doesn't work for now - See the _createFile subroutine)
	foreach(@{$data->{create_file}}) {
		$_->{content} = _expandVars($_->{content}) if($_->{content});
		$rs = _createFile($_) if($_->{content});
		return $rs if $rs;
	}

	# process xml 'chmod_file' nodes
	foreach(@{$data->{chmod_file}}) {
		$_->{content} = _expandVars($_->{content}) if($_->{content});
		$rs = _chmodFile($_) if($_->{content});
		return $rs if $rs;
	}

	foreach(@{$data->{chown_file}}) {
		$_->{content} = _expandVars($_->{content}) if($_->{content});
		$rs = _chownFile($_) if($_->{content});
		return $rs if $rs;
	}

	debug('Ending...');
	0;
}

sub processSpecificConfFile {
	debug('Starting...');

	use Selity::Dir;
	use Selity::SO;

	my $SO = Selity::SO->new();
	my $specificPath = "$FindBin::Bin/configs/" . lc($SO->{Distribution});
	my $commonPath = "$FindBin::Bin/configs/debian";
	my $path = -d $specificPath ? $specificPath : $commonPath;

	unless(chdir($path)){
		error("Unable to change path to $path: $!");
		return 1;
	}

	my $file = -f "$specificPath/install.xml"
		? "$specificPath/install.xml" : "$commonPath/install.xml";

	my $rs = processConfFile($file);
	return $rs if $rs;

	my $dir = Selity::Dir->new();

	# /configs/debian
	$dir->{dirname} = $commonPath;


	$rs = $dir->get();
	return $rs if $rs;

	my @configs = $dir->getDirs();

	foreach(@configs){
		next if($_ eq '.svn');

		$path = -d "$specificPath/$_" ? "$specificPath/$_" : "$commonPath/$_";

		unless(chdir($path)){
			error("Can not change path to $path: $!");
			return 1;
		}


		$file = -f "$specificPath/$_/install.xml"
			? "$specificPath/$_/install.xml" : "$commonPath/$_/install.xml";

		$rs = processConfFile($file);

		return $rs if $rs;
	}

	debug('Ending...');
	0;
}

sub buildSelityDaemon {
	debug('Starting...');

	unless(chdir "$FindBin::Bin/daemon"){
		error("Unable to change path to $FindBin::Bin/daemon");
		return 1;
	}

	my ($rs, $stdout, $stderr);
	my $return = 0;

	$rs = execute("make clean selity_daemon", \$stdout, \$stderr);
	debug("$stdout") if $stdout;
	error("$stderr") if $stderr;
	error("Can not build daemon") if $rs;
	$return |= $rs;

	unless($rs) {
		my $dir = Selity::Dir->new();
		$dir->{dirname} = "$main::SYSTEM_ROOT/daemon";
		$dir->make() and return 1;

		my $file = Selity::File->new();
		$file->{filename} = 'selity_daemon';
		$file->copyFile("$main::SYSTEM_ROOT/daemon");
	} else {
		error("Fail build daemon");
		return 1;
	}

	$rs = execute('make clean', \$stdout, \$stderr);
	debug("$stdout") if $stdout;
	error("$stderr") if $stderr;
	error('Can not clean daemon artifacts') if $rs;
	$return |= $rs;

	debug('Ending...');
	$return;
}

sub installEngine {
	debug('Starting...');

	unless(chdir "$FindBin::Bin/engine"){
		error("Cannot change path to $FindBin::Bin/engine");
		return 1;
	}

	my $rs = processConfFile("$FindBin::Bin/engine/install.xml");
	return $rs if $rs;

	my $dir = Selity::Dir->new();

	$dir->{dirname} = "$FindBin::Bin/engine";

	$rs = $dir->get();
	return $rs if $rs;

	my @configs = $dir->getDirs();

	foreach(@configs){

		next if($_ eq '.svn');

		if (-f "$FindBin::Bin/engine/$_/install.xml"){

			unless(chdir "$FindBin::Bin/engine/$_"){
				error("Can not change path to $FindBin::Bin/engine/$_");
				return 1;
			}

			$rs = processConfFile("$FindBin::Bin/engine/$_/install.xml") ;
			return $rs if $rs;
		}
	}

	debug('Ending...');
	0;
}

sub installGui {
	debug('Starting...');

	my ($rs, $stdout, $stderr);

	$rs = execute("cp -R $FindBin::Bin/gui $main::SYSTEM_ROOT", \$stdout, \$stderr);
	debug("$stdout") if $stdout;
	error("$stderr") if $stderr;

	debug('Ending...');
	$rs;
}

sub finishBuild {
	debug('Starting...');

	my $rs = $main::autoInstallClass->postBuild()
		if(
			defined $main::autoInstallClass
			&&
			$main::autoInstallClass->can('postBuild')
		);
	return $rs if $rs;

	debug('Ending...');
	0;
}

sub cleanUpTmp {
	debug('Starting...');

	my $tmp = qualify_to_ref('INST_PREF', 'main');
	my ($rs, $stdout, $stderr);

	$rs = execute(
		"find $$$tmp -type d -name '.svn' -print0 |xargs -0 -r rm -fr",
		\$stdout, \$stderr
	);

	debug("$stdout") if $stdout;
	error("$stderr") if $stderr;

	return $rs if $rs;

	debug('Ending...');
	0;
}

sub doSelityBackup {
	debug('Starting...');

	my ($rs, $stdout, $stderr);

	if(-x "$main::defaultConf{'ROOT_DIR'}/engine/backup/selity-backup-selity noreport") {
		$rs = execute(
			"$main::defaultConf{'ROOT_DIR'}/engine/backup/selity-backup-selity",
			\$stdout, \$stderr
		);

		debug("$stdout") if $stdout;
		warning("$stderr") if $stderr;
		error('Could not create backups') if $rs;

		$rs = Selity::Dialog->factory()->yesno(
			"\n\n\\Z1Unable to create backups\\Zn\n\n".
			'This is not a fatal error, setup may continue, but '.
			"you will not have a backup (unless you have previously builded one)\n\n".
			'Do you want to continue?'
		) if $rs;
	}

	debug('Ending...');
	$rs;
}

sub saveGuiWorkingData {
	debug('Starting...');

	my ($rs, $stdout, $stderr);
	my $tmp = qualify_to_ref('INST_PREF', 'main');

	if(-d "$main::defaultConf{'ROOT_DIR'}/gui/data") {
		$rs = execute(
			"cp -vTRf $main::defaultConf{'ROOT_DIR'}/gui/data $$$tmp$main::defaultConf{'ROOT_DIR'}/gui/data",
			\$stdout, \$stderr
		);

		debug("$stdout") if $stdout;
		error("$stderr") if $stderr;
		return $rs if $rs;
	}

	if(-d "$main::defaultConf{'ROOT_DIR'}/gui/public/tools/filemanager/data") {
		$rs = execute(
			"cp -vRTf $main::defaultConf{'ROOT_DIR'}/gui/public/tools/filemanager/data $$$tmp$main::defaultConf{'ROOT_DIR'}/gui/public/tools/filemanager/data",
			\$stdout, \$stderr
		);

		debug("$stdout") if $stdout;
		error("$stderr") if $stderr;
		return $rs if $rs;
	}

	if(-d "$main::defaultConf{'ROOT_DIR'}/gui/plugins") {
		$rs = execute(
			"cp -vRTf $main::defaultConf{'ROOT_DIR'}/gui/plugins $$$tmp$main::defaultConf{'ROOT_DIR'}/gui/plugins",
			\$stdout, \$stderr
		);

		debug("$stdout") if $stdout;
		error("$stderr") if $stderr;
		return $rs if $rs;
	}

	debug('Ending...');
	0;
}

sub installTmp {
	debug('Starting...');

	use Selity::Execute;

	my ($rs, $stdout, $stderr);
	my $tmp = qualify_to_ref('INST_PREF', 'main');

	if(-f "/etc/init.d/selity_daemon" && -f "$main::defaultConf{'ROOT_DIR'}/daemon/selity_daemon") {
		$rs = execute("/etc/init.d/selity_daemon stop", \$stdout, \$stderr);
		debug("$stdout") if $stdout;
		error("$stderr") if $stderr;
		return $rs if $rs;
	}

	$rs = execute(
		"rm -fr $$$tmp$main::defaultConf{'ROOT_DIR'}/gui/data/sessions/*",
		\$stdout, \$stderr
	);

	debug("$stdout") if $stdout;
	error("$stderr") if $stderr;
	return $rs if $rs;

	$rs = execute(
		"rm -fr $$$tmp$main::defaultConf{'ROOT_DIR'}/gui/data/cache/*",
		\$stdout, \$stderr
	);

	debug("$stdout") if $stdout;
	error("$stderr") if $stderr;
	return $rs if $rs;

	$rs = execute(
		"rm -vfr ".
		"$main::defaultConf{'ROOT_DIR'}/daemon ".
		"$main::defaultConf{'ROOT_DIR'}/engine ".
		"$main::defaultConf{'ROOT_DIR'}/gui ",
		\$stdout, \$stderr
	);

	debug("$stdout") if $stdout;
	error("$stderr") if $stderr;
	return $rs if $rs;

	$rs = execute("cp -Rf $$$tmp/* /", \$stdout, \$stderr);
	debug("$stdout") if $stdout;
	error("$stderr") if $stderr;
	return $rs if $rs;

	debug('Ending...');
	0;
}

sub removeTmp {
	debug('Starting...');

	my ($rs, $stdout, $stderr);
	my $tmp = qualify_to_ref('INST_PREF', 'main');

	if($$$tmp && -d $$$tmp){
		$rs = execute("rm -fr $$$tmp", \$stdout, \$stderr);
		debug("$stdout") if $stdout;
		error("$stderr") if $stderr;
		return $rs if $rs;
	}

	debug('Ending...');
	0;
}

sub _expandVars {
	debug('Starting...');

	my $var = shift;

	use Symbol;

	debug("Input... $var");

	if($var =~ m/\$\{([^\}]{1,})\}/g) {
		my $x = qualify_to_ref("$1");
		$var =~ s/\$\{$1\}/$$$x/g;
	}

	debug("Expanded... $var");

	debug('Ending...');
	$var;
}

sub _processFolder {
	debug('Starting...');

	my $data = shift;

	use Selity::Dir;

	my $dir  = Selity::Dir->new();
	$dir->{dirname} = $data->{content};
	debug("Create $dir->{dirname}");

	my $options = {};

	$options->{mode} = oct($data->{mode}) if($data->{mode});
	$options->{user} = _expandVars($data->{owner}) if($data->{owner});
	$options->{group} = _expandVars($data->{group}) if($data->{group});
	debug $options->{group} if $options->{group};

	my $rs = $dir->make($options);
	return $rs if $rs;

	debug('Ending...');
	0;
}

sub _copyConfig {
	debug('Starting...');

	use Cwd;
	use Selity::SO;
	use Selity::Execute;
	use Selity::File;

	my $SO = Selity::SO->new();

	my $data = shift;

	my @parts = split '/', $data->{content};
	my $name = pop(@parts);
	my $path = join '/', @parts;

	my $distro = lc($SO->{Distribution});

	my $alternativeFolder = my $currentFolder = getcwd(); #upstream
	$alternativeFolder =~ s!\/$distro!\/debian!;

	my $source = -e $name ? $name : "$alternativeFolder/$name";

	debug("Copy recursive $source in $path");

	my ($rs, $stdout, $stderr);
	$rs = execute("cp -R $source $path", \$stdout, \$stderr);
	debug("$stdout") if $stdout;
	error("$stderr") if $stderr;

	return $rs if $rs;

	if($data->{user} || $data->{group} || $data->{mode}) {
		my $filename = -e "$path/$name" ? "$path/$name" : $path;

		my $file = Selity::File->new(filename => $filename);
		$file->mode(oct($data->{mode})) and return 1 if $data->{mode};

		$file->owner(
			$data->{user} ? $data->{user} : -1,
			$data->{group} ? $data->{group} : -1
		)  and return 1 if($data->{user} || $data->{group});
	}

	debug('Ending...');
	0;
}

sub _copy {
	debug('Starting...');

	use Selity::Execute;
	use Selity::File;

	my $data = shift;
	my @parts = split '/', $data->{content};
	my $name = pop(@parts);
	my $path = join '/', @parts;

	debug("Copy recursive $name in $path");

	my ($rs, $stdout, $stderr);
	$rs = execute("cp -R $name $path", \$stdout, \$stderr);
	debug("$stdout") if $stdout;
	error("$stderr") if $stderr;
	return $rs if $rs;

	if($data->{user} || $data->{group} || $data->{mode}){

		my $filename = -e "$path/$name" ? "$path/$name" : $path;

		my $file = Selity::File->new(filename => $filename);
		$file->mode(oct($data->{mode})) and return 1 if $data->{mode};
		$file->owner(
			$data->{user} ? $data->{user} : -1,
			$data->{group} ? $data->{group} : -1
		)  and return 1 if($data->{user} || $data->{group});

	}

	debug('Ending...');
	0;
}

sub _createFile {
	debug('Starting...');

	use Selity::File;

	my $data = shift;

	my $rs = Selity::File->new(filename => $data->{content})->save();
	return $rs if $rs;

	debug('Ending...');
	0;
}

sub _chownFile {
	debug('Starting...');

	my $data = shift;

	if($data->{owner} && $data->{group}){
		my ($rs, $stdout, $stderr);
		$rs = execute("chown -R $data->{owner}:$data->{group} $data->{content}", \$stdout, \$stderr);
		debug("$stdout") if $stdout;
		error("$stderr") if $stderr;
		return $rs if $rs;
	}

	debug('Ending...');
	0;
}

sub _chmodFile {
	debug('Starting...');

	my $data = shift;

	if($data->{mode}) {
		my ($rs, $stdout, $stderr);
		$rs = execute("chmod -R $data->{mode} $data->{content}", \$stdout, \$stderr);
		debug("$stdout") if $stdout;
		error("$stderr") if $stderr;
		return $rs if $rs;
	}

	debug('Ending...');
	0;
}

sub _checkPkgManager {
	debug('Starting...');

	use Selity::Execute;

	my ($rs, $stdout, $stderr);

	debug('Ending...');
	return execute('which apt-get', \$stdout, \$stderr);
}

1;
