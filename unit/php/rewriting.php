<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

/**
 * This is a unit test file to check url rewriting.
 * Currently it only checks Rain TPL rewriting but it should also check Freeder's one when it will becom available.
 */

define('TESTING', 'raintpl'); // Defines what unit to test

define('INC_DIR', '../inc/');

if (TESTING == 'raintpl') {

	// Load Rain TPL
	require_once(INC_DIR . 'rain.tpl.class.php');
	RainTPL::$tpl_dir = 'tpl/';
	RainTPL::$base_url = 'base_url/';
	$tpl = new RainTPL;
	$tpl->draw('rewriting');

}
else if (TESTING == 'local') {

	require_once('rewriting.inc.php');
	$html = file_get_contents('tpl/rewriting.html');
	echo(path_replace($html));

}

