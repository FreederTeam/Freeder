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

// Global variables defined in this file
$dbh = NULL;
$admins = NULL;
$config = NULL;
$tpl = NULL;


/**
 * Load current directory's `path.php` to retrieve root path.
 * If there is no such file, use the current directory as root.
 */
function init_path() {
	if (is_file('path.php')) {
		require('path.php');
	} else {
		define('ROOT_DIR', dirname(dirname(__FILE__)) . '/');
	}
}


/**
 * Load constant config
 */
function init_constants() {
	require_once(ROOT_DIR . 'constants.php');
}


/**
 * Initialize database handler
 */
function init_dbh() {
	global $dbh, $admins;

	if (is_file(DATA_DIR.DB_FILE)) {
		$dbh = new PDO('sqlite:'.DATA_DIR.DB_FILE);
		$dbh->query('PRAGMA foreign_keys = ON');

		$query = $dbh->query('SELECT COUNT(*) AS nb_admins FROM users WHERE is_admin=1');
		$admins = $query->fetch();
		if($admins['nb_admins'] == 0) {
			$admins = NULL;
		}
	}
}


/**
 * Load config from database or set it to default values
 */
function init_load_config() {
	global $config;

	require_once(INC_DIR . 'config.class.php');
	$config = new Config();
	date_default_timezone_set($config->timezone);
}

/**
 * Test wether an update should be done
 */
function init_check_update() {
	global $config;

	if($config->version !== Config::$versions[count(Config::$versions) - 1]) {
		require_once(INC_DIR . 'update.php');
		update($config->version, Config::$versions[count(Config::$versions) - 1]);
		header('location: index.php');
		exit();
	}
}


/**
 * Load Rain TPL
 */
function init_tpl() {
	global $config, $tpl;

	if (file_exists(TMP_DIR) && is_writable(TMP_DIR)) {
		require_once(INC_DIR . 'rain.tpl.class.php');
		require_once(INC_DIR . 'rewriting.class.php');
		RainTPL::$tpl_dir = RELATIVE_TPL_DIR.$config->template;
		RainTPL::$cache_dir = TMP_DIR.$config->template;
		RainTPL::$base_url = $config->base_url;
		RewriteEngine::$rewrite_base = RainTPL::$base_url;
		RainTPL::$rewriteEngine = new RewriteEngine;
		$tpl = new RainTPL;
		$tpl->assign('start_generation_time', microtime(true), RainTPL::RAINTPL_IGNORE_SANITIZE);
		$tpl->assign('config', $config);
	}
}


/**
 * CSRF protection
 */
function init_csrf() {
	require_once(INC_DIR . 'csrf.php');
}


/**
 * Sharing options
 */
function init_sharing() {
	require_once(INC_DIR . 'share.php');
}


/**
 * Run installation if needed
 */
function init_install() {
	global $dbh, $admins, $tpl;

	if(!defined('PUBLIC') && (!$dbh || !$admins || !$tpl)) {
		require_once(INC_DIR . 'install.php');

		install();
	}
}


/**
 * Manage users
 */
function init_users() {
	global $tpl;

	require_once(INC_DIR . 'users.php');
	if ($tpl && log_user_in() === false) {
		$error = array();
		$error['type'] = 'error';
		$error['title'] = 'Login error';
		$error['content'] = '<p>The provided username or password is incorrect.</p>';
		$tpl->assign('error', $error, RainTPL::RAINTPL_IGNORE_SANITIZE);
	}
	if ($tpl) $tpl->assign('user', isset($_SESSION['user']) ? $_SESSION['user'] : false, RainTPL::RAINTPL_HTML_SANITIZE);
}


/**
 * Check whether the file is public or the user logged in
 */
function init_check_visibility() {
	if (!defined('PUBLIC')) {
		check_anonymous_view();
	}
}


/**
 * Run all init functions
 */
function init_all() {
	init_path();
	init_constants();
	init_dbh();
	init_load_config();
	init_check_update();
	init_tpl();
	init_csrf();
	init_sharing();
	init_install();
	init_users();
	init_check_visibility();
}


init_all();
