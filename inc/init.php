<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

session_start();
define('DATA_DIR', 'data/');
define('TPL_DIR', 'tpl/');
define('DEBUG', true);


// Check config installation
if(!is_file(DATA_DIR.'config.php')) {
	require_once('inc/install.php');

	install();
}


// Load constant config
require_once(DATA_DIR.'config.php');


// Check database installation
if(!is_file(DATA_DIR.DB_FILE)) {
	require_once('inc/install.php');

	install();
}


// Initialize database handler
$dbh = new PDO('sqlite:'.DATA_DIR.DB_FILE);
$dbh->query('PRAGMA foreign_keys = ON');


// Load config from database
require_once('inc/config.class.php');
$config = new Config();
date_default_timezone_set($config->timezone);


// Load Rain TPL
require_once('inc/rain.tpl.class.php');
RainTPL::$tpl_dir = TPL_DIR.$config->template;
$tpl = new RainTPL;
$tpl->assign('start_generation_time', microtime(true));
require_once('inc/functions.php');
require_once('inc/feeds.php');
require_once('inc/entries.php');
require_once('inc/users.php');


// Log user in
if (!empty($_POST['login']) && !empty($_POST['password'])) {
	$user = check_and_get_user($_POST['login'], $_POST['password']);
	if ($user !== false) {
		$_SESSION['user'] = $user;
	}
}
$tpl->assign('user', isset($_SESSION['user']) ? $_SESSION['user'] : false);


// Require connection if no anonymous view has been set
if (empty($_SESSION['user']) && $config->anonymous_access == 0) {
	$tpl->draw('connection');
	exit();
}


