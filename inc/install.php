<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to install the script
 */

require_once('constants.php');
require_once(INC_DIR.'functions.php');

$default_timezone = @date_default_timezone_get();


/**
 * Create a directory, checking writeable and the rights.
 */
function install_dir($dir) {
	if (!file_exists($dir) || !is_writable($dir)) {
		if (!mkdir($dir) || !is_writable($dir)) {
			$current_user = get_current_user();
			$error = array();
			$error['type'] = 'error';
			$error['title'] = 'Permissions error';
			$error['content'] = 'Unable to create or write in data directory. Check the writing rights of Freeder root directory. The user who executes Freeder — '.sanitize($current_user).' — should be able to write in this directory. You may prefere to create the /data directory on your own and allow '.sanitize($current_user).' to write only in /data instead of in the whole Freeder root.';
			return $error;
		}
	}
	return;
}


/**
 * Initialize database.
 */
function install_db() {
	if (!in_array('pdo_sqlite', get_loaded_extensions())) {
		$error = array();
		$error['type'] = 'error';
		$error['title'] = 'Missing dependency';
		$error['content'] = 'Module pdo_sqlite not found.';
		return $error;
	}

	$dbh = new PDO('sqlite:'.DATA_DIR.DB_FILE);
	$dbh->query('PRAGMA foreign_keys = ON');

	$salt = uniqid(mt_rand(), true);
	$password = sha1($salt.$_POST['password']);

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$dbh->beginTransaction();

	// Create the table to handle users
	$dbh->query('CREATE TABLE IF NOT EXISTS users(
		id INTEGER PRIMARY KEY NOT NULL,
		login TEXT UNIQUE,
		password TEXT,
		salt TEXT,
		remember_token TEXT,
		is_admin INT DEFAULT 0
	)');
	$query = $dbh->prepare('INSERT OR IGNORE INTO users(login, password, salt, is_admin) VALUES(:login, :password, :salt, 1)');
	$query->execute(array(
		':login'=>$_POST['login'],
		':password'=>$password,
		':salt'=>$salt)
	);

	// Create the table to store config options
	$dbh->query('CREATE TABLE IF NOT EXISTS config(
		option TEXT UNIQUE COLLATE NOCASE,
		value TEXT
	)');
	// Insert timezone in the config
	$query = $dbh->prepare('INSERT OR IGNORE INTO config(option, value) VALUES("timezone", :value)');
	$query->execute(array(':value'=>$_POST['timezone']));
	$query = $dbh->prepare('INSERT OR IGNORE INTO config(option, value) VALUES("use_rewriting", :value)');
	$query->execute(array(':value'=>get_url_rewriting()));

	// Create the table to store feeds
	$dbh->query('CREATE TABLE IF NOT EXISTS feeds(
		id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
		title TEXT,
		has_user_title INTEGER DEFAULT 0,  -- To specify wether the user edited the title manually or not
		url TEXT UNIQUE COLLATE NOCASE,  -- Feed URL
		links TEXT,  -- JSON array of links associated with the feed
		description TEXT,
		ttl INT DEFAULT 0,  -- This is the ttl of the feed, 0 means that it uses the config value
		has_user_ttl INT DEFAULT 0,  -- To specify wether the user edited the TTL manually or not
		image TEXT,
		post TEXT,
		import_tags_from_feed INTEGER DEFAULT 0 -- To specify wether to use tags from feed or not
	)');

	// Create table to store entries
	$dbh->query('CREATE TABLE IF NOT EXISTS entries(
		id INTEGER PRIMARY KEY NOT NULL,
		feed_id INTEGER NOT NULL,
		authors TEXT,
		title TEXT,
		links TEXT,  -- JSON array of enclosed links
		description TEXT,
		content TEXT,
		enclosures TEXT,  -- JSON array of links to enclosures
		comments TEXT,  -- Link to comments
		guid TEXT UNIQUE,
		pubDate INTEGER,
		lastUpdate INTEGER,
		FOREIGN KEY(feed_id) REFERENCES feeds(id) ON DELETE CASCADE
	)');

	// Create table to store tags
	$dbh->query('CREATE TABLE IF NOT EXISTS tags(
		id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
		name TEXT UNIQUE COLLATE NOCASE
	)');
	$dbh->query('INSERT OR IGNORE INTO tags(name) VALUES("_read")');
	$dbh->query('INSERT OR IGNORE INTO tags(name) VALUES("_sticky")');
	$dbh->query('INSERT OR IGNORE INTO tags(name) VALUES("_private")');
	$dbh->query('INSERT OR IGNORE INTO tags(name) VALUES("_no_home")');
	$dbh->query('INSERT OR IGNORE INTO tags(name) VALUES("_type_audio")');
	$dbh->query('INSERT OR IGNORE INTO tags(name) VALUES("_type_image")');
	$dbh->query('INSERT OR IGNORE INTO tags(name) VALUES("_type_text")');
	$dbh->query('INSERT OR IGNORE INTO tags(name) VALUES("_type_video")');

	// Create table to store association between tags and entries
	$dbh->query('CREATE TABLE IF NOT EXISTS tags_entries(
		tag_id INTEGER,
		entry_id INTEGER,
		auto_added_tag INTEGER DEFAULT 0,
		UNIQUE (tag_id, entry_id),
		FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE,
		FOREIGN KEY(entry_id) REFERENCES entries(id) ON DELETE CASCADE
	)');
	$dbh->query('CREATE TABLE IF NOT EXISTS tags_feeds(
		tag_id INTEGER,
		feed_id INTEGER,
		auto_added_tag INTEGER,
		UNIQUE (tag_id, feed_id),
		FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE,
		FOREIGN KEY(feed_id) REFERENCES feeds(id) ON DELETE CASCADE
	)');

	// Create the table to store views
	$dbh->query('CREATE TABLE IF NOT EXISTS views(
		id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
		name TEXT UNIQUE,
		rule TEXT UNIQUE, -- Specifies what to display. See RFF 4 for more info
		isPublic INT DEFAULT 0 -- Whether the view is publicly available
		-- theme TEXT,
		-- displayStyle INT (Title only, Summary, Full text)
	)');

	$dbh->query('INSERT OR IGNORE INTO views(name, rule) VALUES("_home", "+$all -_read -_no_home BY -$pubDate")');
	$dbh->query('INSERT OR IGNORE INTO views(name, rule) VALUES("_public", "+$all -_private BY -$pubDate")');

	$dbh->commit();

}


/**
 * Proceed to Freeder installation.
 */
function install() {
	global $default_timezone;

	$current_user = get_current_user();
	$tmp = install_dir(TMP_DIR);
	if (!empty($tmp)) {
		exit('Unable to create or write to '.TMP_DIR.' folder. Please check write permissions on this folder.');
	}

	$login = isset($_POST['login']) ? $_POST['login'] : '';
	$timezone = isset($_POST['timezone']) ? $_POST['timezone'] : $default_timezone;

	require_once(INC_DIR . 'rain.tpl.class.php');
	require_once(INC_DIR . 'rewriting.class.php');
	RainTPL::$tpl_dir = RELATIVE_TPL_DIR . DEFAULT_THEME . '/';
	RainTPL::$base_url = dirname($_SERVER['SCRIPT_NAME']) . '/';
	RewriteEngine::$rewrite_base = RainTPL::$base_url;
	RainTPL::$rewriteEngine = new RewriteEngine;
	$tpl = new RainTPL;
	$tpl->assign('start_generation_time', microtime(true), RainTPL::RAINTPL_IGNORE_SANITIZE);
	$tpl->assign('login', $login, RainTPL::RAINTPL_HTML_SANITIZE);
	$tpl->assign('timezone', $timezone, RainTPL::RAINTPL_HTML_SANITIZE);

	if ($err = RainTPL::$rewriteEngine->write_htaccess()) {
		$error = array();
		$error['type'] = 'error';
		$error['title'] = 'Permissions error';
		$error['content'] = 'Unable to create or write .htaccess file. Check the writing rights of Freeder root directory. The user who executes Freeder — '.sanitize($current_user).' — should be able to write in this directory. You may prefer to create the .htaccess file on your own and allow '.sanitize($current_user).' to write only in .htaccess instead of in the whole Freeder root.';
		$tpl->assign('error', $error, RainTPL::RAINTPL_IGNORE_SANITIZE);
	}

	if (!empty($_POST['login']) && !empty($_POST['password']) && !empty($_POST['confirm_password']) && !empty($_POST['timezone'])) {
		if ($_POST['confirm_password'] != $_POST['password']) {
			$error = array();
			$error['type'] = 'error';
			$error['title'] = 'Password mismatch';
			$error['content'] = 'Passwords do not match!';
		}
		else {
			$error = install_dir(DATA_DIR);
			if(empty($error)) {
				$error = install_db();
				if(empty($error)) {
					$_SESSION['user'] = new stdClass;
					$_SESSION['user']->login = $_POST['login'];
					$_SESSION['is_admin'] = 1;
					header('location: index.php');
					exit();
				}
				else {
					$tpl->assign('error', $error, RainTPL::RAINTPL_IGNORE_SANITIZE);
				}
			}
			else {
				$tpl->assign('error', $error, RainTPL::RAINTPL_IGNORE_SANITIZE);
			}
		}
	} else {
		if(isset($_POST['login'])) {
			$error = array();
			$error['type'] = 'error';
			$error['title'] = 'Incomplete installation form';
			$error['content'] = 'You must fill every field.';
			$tpl->assign('error', $error, RainTPL::RAINTPL_IGNORE_SANITIZE);
		}
	}

	$tpl->draw('install');
	exit();
}

