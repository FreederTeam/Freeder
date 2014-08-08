<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

/**
 * This is a unit test file to check url rewriting.
 * Currently it only checks Rain TPL rewriting but it should also check Freeder's one when it will becom available.
 */

define('INC_DIR', '../inc/');

// Load Rain TPL
require_once(INC_DIR . 'rain.tpl.class.php');
RainTPL::$tpl_dir = 'tpl_dir/';
RainTPL::$base_url = 'base_url/';
$tpl = new RainTPL;
$tpl->draw('rewriting');


