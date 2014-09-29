<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

$force_public = true; // Allow JS loading while not logged in
require_once('inc/init.php');
require_once('inc/js.tpl.class.php');

if (!isset($_GET['script'])) {
	exit('error');
}

$script = $_GET['script'];

RainTPL::$tpl_ext = 'js';
RainTPL::$path_replace = false;
$tpl = new JsTPL();
$tpl->assign('base_url', RainTPL::$base_url);
$tpl->assign('config', $config);
$tpl->assign('token', generate_token('js'));
$tpl->draw($script);



