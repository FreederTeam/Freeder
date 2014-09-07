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
 */

session_start();

// Load current directory's `path.php` to retrieve root path.
// If there is no such file, use the current directory as root.
if (is_file('path.php')) {
	require('path.php');
} else {
	define('ROOT_DIR', dirname(dirname(__FILE__)) . '/');
}

// Load constant config
require_once(ROOT_DIR . 'constants.php');


// Check database installation
if(!is_file(DATA_DIR.DB_FILE)) {
	require_once(INC_DIR . 'install.php');

	install();
}


// Initialize database handler
$dbh = new PDO('sqlite:'.DATA_DIR.DB_FILE);
$dbh->query('PRAGMA foreign_keys = ON');

$query = $dbh->query('SELECT COUNT(*) AS nb_admins FROM users WHERE is_admin=1');
$admins = $query->fetch();
if($admins['nb_admins'] == 0) {
	require_once(INC_DIR . 'install.php');

	install();
}


// Load config from database
require_once(INC_DIR . 'config.class.php');
$config = new Config();
date_default_timezone_set($config->timezone);


// Test wether an update should be done
if($config->version !== Config::$versions[count(Config::$versions) - 1]) {
	require_once(INC_DIR . 'update.php');
	update($config->version, Config::$versions[count(Config::$versions) - 1]);
	header('location: index.php');
	exit();
}


// Load Rain TPL
require_once(INC_DIR . 'rain.tpl.class.php');
require_once(INC_DIR . 'rewriting.class.php');
RainTPL::$tpl_dir = RELATIVE_TPL_DIR.$config->template;
RainTPL::$base_url = $config->base_url;
RewriteEngine::$rewrite_base = RainTPL::$base_url;
RainTPL::$rewriteEngine = new RewriteEngine;
$tpl = new RainTPL;
$tpl->assign('start_generation_time', microtime(true), RainTPL::RAINTPL_IGNORE_SANITIZE);
$tpl->assign('config', $config);

// CSRF protection
require_once(INC_DIR . 'csrf.php');

// Sharing options
require_once(INC_DIR . 'share.php');

// Manage users
require_once(INC_DIR . 'users.php');
log_user_in();
$tpl->assign('user', isset($_SESSION['user']) ? $_SESSION['user'] : false, RainTPL::RAINTPL_HTML_SANITIZE);
check_anonymous_view();


