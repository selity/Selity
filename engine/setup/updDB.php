<?php
/**
 * Selity - When virtual hosting becomes scalable
 *
 * Copyright (C) 2006-2010 by isp Control Panel - http://ispcp.net
 * Copyright (C) 2010-2012 by internet Multi Server Control Panel - http://i-mscp.net
 * Copyright (C) 2012-2014 Selity - http://selity.org
 *
 * Author:  Laurent Declercq <l.declercq@nuxwin.com>
 * Version: $Id$
 *
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "ispCP ω (OMEGA) a Virtual Hosting Control Panel".
 *
 * The Initial Developer of the Original Code is ispCP Team.
 * Portions created by Initial Developer are Copyright (C) 2006-2010 by
 * isp Control Panel. All Rights Reserved.
 *
 * Portions created by the i-MSCP Team are Copyright (C) 2010-2012 by
 * internet Multi Server Control Panel. All Rights Reserved.
 *
 * Portions created by the Selity Team are Copyright (C) 2012 by Selity.
 * All Rights Reserved.
 *
 * The Selity Home Page is:
 *
 *    http://selity.org
 *
 */

// GUI root directory absolute path
$guiRootDir = '{GUI_ROOT_DIR}';

if(strpos($guiRootDir, 'GUI_ROOT_DIR') !== false) {
	print 'The gui root directory is not defined in the ' . __FILE__ ." file.\n";
	exit(1);
}

// Sets include path
set_include_path('.' . PATH_SEPARATOR . $guiRootDir . '/library');

// Include core library
require_once 'selity-lib.php';

try {
	// Gets an iMSCP_Update_Database instance
	$databaseUpdate = iMSCP_Update_Database::getInstance();

	if(!$databaseUpdate->applyUpdates()) {
		print "\n[ERROR]: " . $databaseUpdate->getError() . "\n\n";
		exit(1);
	}

} catch(Exception $e) {
	$message = "\n[ERROR]: " . $e->getMessage() . "\n\nStackTrace:\n" .
		$e->getTraceAsString() . "\n\n";

	print "$message\n\n";

	exit(1);
}

print "\n[INFO]: Selity database update succeeded!\n\n";

exit(0);
