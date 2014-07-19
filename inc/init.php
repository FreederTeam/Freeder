<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

/**
 * This include file defines the following variables that you can reuse in
 * your code after including it:
 *  $config Configuration object
 *  $tpl Rain TPL handler
 *  $dbh Database handler
 *
 * This file automatically includes `functions.php` which is required by
 * template generation of almost every page.
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

// `functions.php` must be included in each page for templates.
require_once('inc/functions.php');

// Manage users
require_once('inc/users.php');
log_user_in();
$tpl->assign('user', isset($_SESSION['user']) ? $_SESSION['user'] : false);
check_anonymous_view();


