<?php
/**
 * Selity - A server control panel
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2008 by ispCP | http://isp-control.net
 * @copyright	2012-2015 by Selity
 * @link 		http://selity.org
 * @author 		ispCP Team
 *
 * @license
 *   This program is free software; you can redistribute it and/or modify it under
 *   the terms of the MPL General Public License as published by the Free Software
 *   Foundation; either version 1.1 of the License, or (at your option) any later
 *   version.
 *   You should have received a copy of the MPL Mozilla Public License along with
 *   this program; if not, write to the Open Source Initiative (OSI)
 *   http://opensource.org | osi@opensource.org
 */

/**
 * false: don't set (not even auto),
 * null: set if missing,
 * true: force update from session/default, anything else: set it as a language
 */
function curlang($newlang = null, $force = false) {
	static $language = null;

	// we store old value so if $language is changed old value is returned
	$_language = $language;

	// forcibly set $language to $newlang (use with CARE!)
	if ($force) {
		$language = $newlang;
		return $_language;
	}

	if ($language === null || ($newlang !== null && $newlang !== false)) {

		if ($newlang === true || (($newlang === null || $newlang === false) && $language === null)) {
			$newlang = (isset($_SESSION['user_def_lang'])) ? $_SESSION['user_def_lang'] : Config::get('USER_INITIAL_LANG');
		}

		if ($newlang !== false) {
			$language = $newlang;
		}
	}

	return ($_language !== null)? $_language : $language;

}

/**
 * 	Function:		replace_html
 * 	Description:	replaces special encoded strings back to their original signs
 *
 * 	@access			public
 * 	@version		1.0
 *  @author			ispCP Team, Benedikt Heintel (2007)
 *
 * 	@param		$string		string to replace chars
 * 	@return					string with replaced chars
 **/
function replace_html($string) {
	$pattern = array (
						'#&lt;[ ]*b[ ]*&gt;#i',
						'#&lt;[ ]*/[ ]*b[ ]*&gt;#i',
						'#&lt;[ ]*em[ ]*&gt;#i',
						'#&lt;[ ]*/[ ]*em[ ]*&gt;#i',
						'#&lt;[ ]*i[ ]*&gt;#i',
						'#&lt;[ ]*/[ ]*i[ ]*&gt;#i',
						'#&lt;[ ]*small[ ]*&gt;#i',
						'#&lt;[ ]*/[ ]*small[ ]*&gt;#i',
						'#&lt;[ ]*br[ ]*(/|)[ ]*&gt;#i'
					 );

	$replacement = array (
							'<b>',
							'</b>',
							'<em>',
							'</em>',
							'<i>',
							'</i>',
							'<small>',
							'</small>',
							'<br />'
						 );

	$string = preg_replace($pattern, $replacement, $string);

	return $string;
}

// Dirty hack to make gettext add this entry to the .pot file
if (false) {
	tr('_: Localised language');
}

