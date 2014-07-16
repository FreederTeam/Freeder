<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

session_start();
define('DATA_DIR', 'data/');
define('TPL_DIR', 'tpl/');
define('DEBUG', true);

if(!is_file(DATA_DIR.'config.php')) {
	require('inc/install.php');

	install();
}
else {
	require(DATA_DIR.'config.php');
}

if(!is_file(DATA_DIR.DB_FILE)) {
	unlink(DATA_DIR.'/config.php');
	header('location: index.php');
}
require('inc/config.class.php');
$dbh = new PDO('sqlite:'.DATA_DIR.DB_FILE);
$dbh->query('PRAGMA foreign_keys = ON');

$config = new Config();
date_default_timezone_set($config->timezone);
require('inc/rain.tpl.class.php');
RainTPL::$tpl_dir = TPL_DIR.$config->template;
$tpl = new RainTPL;
$tpl->assign('start_generation_time', microtime(true));
require('inc/functions.php');
require('inc/feeds.php');
require('inc/entries.php');
require('inc/users.php');


if (!empty($_POST['login']) && !empty($_POST['password'])) {
	$user = check_and_get_user($_POST['login'], $_POST['password']);
	if ($user !== false) {
		$_SESSION['user'] = $user;
	}
}

if (empty($_SESSION['user']) && $config->anonymous_access == 0) {
	$tpl->draw('connection');
	exit();
}

$do = isset($_GET['do']) ? $_GET['do'] : '';

$feeds = get_feeds();

$tpl->assign('user', isset($_SESSION['user']) ? $_SESSION['user'] : false);

switch($do) {
	case 'settings':
		if(!isset($_SESSION['user'])) {
			// Prevent access to settings from unauthentified users
			header('location: index.php');
			exit();
		}
		if (!empty($_POST['synchronization_type']) && !empty($_POST['template']) && !empty($_POST['timezone']) && isset($_POST['use_tags_from_feeds']) && isset($_POST['anonymous_access'])) {
			$config->synchronization_type = $_POST['synchronization_type'];
			if (is_dir(TPL_DIR.$_POST['template'])) {
				$config->template = $_POST['template'];
				if(!endswith($config->template, '/')) {
					$config->template = $config->template.'/';
				}
			}
			if (in_array($_POST['timezone'], timezone_identifiers_list())) {
				$config->timezone = $_POST['timezone'];
			}
			$config->use_tags_from_feeds = (int) $_POST['use_tags_from_feeds'];
			$config->anonymous_access = (int) $_POST['anonymous_access'];
			$config->save();
			header('location: index.php?do=settings');
			exit();
		}
		if (!empty($_POST['feed_url'])) {
			if(empty(add_feeds(array($_POST['feed_url'])))) {
				header('location: index.php?do=settings');
				exit();
			}
			else {
				exit('Erreur - TODO');
			}
		}
		if (!empty($_GET['delete_feed'])) {
			delete_feed_id(intval($_GET['delete_feed']));
			header('location: index.php?do=settings');
			exit();
		}
		if (!empty($_GET['refresh_feed'])) {
			refresh_feeds(array(intval($_GET['refresh_feed']) => '')); // TODO
			header('location: index.php?do=settings');
			exit();
		}
		if (isset($_FILES['import'])) {
			if ($_FILES['import']['error'] > 0) {
				exit();  // TODO: Error during upload
			}
			if ($_FILES['import']['size'] > 1048576) {
				exit();  // TODO: Error, file is too large
			}
			require('inc/opml.php');
			$feeds_opml = opml_import(file_get_contents($_FILES['import']['tmp_name']));
			if ($feeds_opml === false) {
				exit('ok');  // TODO: Error, OPML file not valid
			}
			$urls = array();
			foreach($feeds_opml as $feed) {
				$urls[] = $feed['url'];
			}

			$errors_refresh = add_feeds($urls);
			if(empty($errors_refresh)) {
				// TODO: Feed tags + restore feed title
				header('location: index.php?do=settings');
				exit();
			}
			else {
				// Some feeds errorred
				// TODO
				exit();
			}
		}
		$tpl->assign('config', $config);
		$tpl->assign('templates', list_templates());
		$tpl->assign('feeds', $feeds);
		$tpl->draw('settings');
		exit();

	case 'refresh':
		if(!isset($_SESSION['user'])) {
			// Prevent refresh from unauthentified users
			header('location: index.php');
			exit();
		}
		$feeds_to_refresh = array();
		foreach($feeds as $feed) {
			$feeds_to_refresh[$feed['id']] = $feed['url'];
		}
		refresh_feeds($feeds_to_refresh);
		header('location: index.php');
		exit();

	case 'login':
		if(isset($_SESSION['user'])) {
			header('location: index.php');
		}
		else {
			$tpl->draw('connection');
		}
		exit();

	case 'logout':
		session_destroy();
		header('location: index.php');
		exit();

	default:
		$tpl->assign('entries', get_entries());
		$tpl->draw('index');
		exit();
}
