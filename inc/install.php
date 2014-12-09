<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to install the script
 */

require_once(INC_DIR.'functions.php');

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
	if (!is_pdo_sqlite_available()) {
		$error = array();
		$error['type'] = 'error';
		$error['title'] = 'Missing dependency';
		$error['content'] = 'Module pdo_sqlite not found.';
		return $error;
	}
	$curl_disabled_functions = check_curl_availability ();
	if ($curl_disabled_functions !== null) {
		$error = array();
		$error['type'] = 'error';
		$error['title'] = 'Missing dependency';
		$error['content'] = 'Curl functions missing : ';
		$error['content'] .= implode (', ', $curl_disabled_functions) . '.';
		return $error;
	}

	$dbh = new PDO('sqlite:'.DATA_DIR.DB_FILE);
	$dbh->query('PRAGMA foreign_keys = ON');

	$salt = uniqid(mt_rand(), true);
	$password = sha1($salt.$_POST['password']);

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$dbh->beginTransaction();
	$install_sql = file_get_contents('inc/db.sql');
	$dbh->exec($install_sql);

	// Init users
	$query = $dbh->prepare('INSERT OR IGNORE INTO users(login, password, salt, is_admin) VALUES(:login, :password, :salt, 1)');
	$query->execute(array(
		':login'=>$_POST['login'],
		':password'=>$password,
		':salt'=>$salt)
	);

	// Insert timezone in the config
	$query = $dbh->prepare('INSERT OR IGNORE INTO config(option, value) VALUES("timezone", :value)');
	$query->execute(array(':value'=>$_POST['timezone']));
	$query = $dbh->prepare('INSERT OR IGNORE INTO config(option, value) VALUES("use_rewriting", :value)');
	$query->execute(array(':value'=>get_url_rewriting()));

	// Add default system tags
	$dbh->query('INSERT OR IGNORE INTO tags(name) VALUES("_read")');
	$dbh->query('INSERT OR IGNORE INTO tags(name) VALUES("_sticky")');
	$dbh->query('INSERT OR IGNORE INTO tags(name) VALUES("_private")');
	$dbh->query('INSERT OR IGNORE INTO tags(name) VALUES("_no_home")');
	$dbh->query('INSERT OR IGNORE INTO tags(name) VALUES("_type_audio")');
	$dbh->query('INSERT OR IGNORE INTO tags(name) VALUES("_type_image")');
	$dbh->query('INSERT OR IGNORE INTO tags(name) VALUES("_type_text")');
	$dbh->query('INSERT OR IGNORE INTO tags(name) VALUES("_type_video")');

	// Add default views
	$dbh->query('INSERT OR IGNORE INTO views(name, rule) VALUES("_home", "+$all -_read -_no_home BY -$pubDate")');
	$dbh->query('INSERT OR IGNORE INTO views(name, rule) VALUES("_public", "+$all -_private BY -$pubDate")');

	$dbh->commit();

}


/**
 * Proceed to Freeder installation.
 */
function install() {
	global $config, $tpl;

	$current_user = get_current_user();
	$tmp = install_dir(TMP_DIR);
	if (!empty($tmp)) {
		exit('Unable to create or write to '.TMP_DIR.' folder. Please check write permissions on this folder.');
	}
	init_tpl(); // Tpl has not been initialized before TMP_DIR was made.²

	$login = isset($_POST['login']) ? $_POST['login'] : '';
	$timezone = isset($_POST['timezone']) ? $_POST['timezone'] : $config->get('timezone');

	$tpl->assign('login', $login, RainTPL::RAINTPL_HTML_SANITIZE);
	$tpl->assign('timezone', $timezone, RainTPL::RAINTPL_HTML_SANITIZE);

	$error = array();
	$error['type'] = 'error';
	$error['title'] = '';
	$error['content'] = '';

	if ($err = RainTPL::$rewriteEngine->write_htaccess()) {
		$error['title'] = 'Permissions error';
		$error['content'] = 'Unable to create or write .htaccess file. Check the writing rights of Freeder root directory. The user who executes Freeder — '.sanitize($current_user).' — should be able to write in this directory. You may prefer to create the .htaccess file on your own and allow '.sanitize($current_user).' to write only in .htaccess instead of in the whole Freeder root.';
	}

	if (!empty($_POST['login']) && !empty($_POST['password']) && !empty($_POST['confirm_password']) && !empty($_POST['timezone'])) {
		if ($_POST['confirm_password'] != $_POST['password']) {
			$error['title'] = 'Password mismatch';
			$error['content'] = 'Passwords do not match!';
		}
		else {
			$error = install_dir(DATA_DIR);
			if(empty($error)) {
				$error = install_db();
				if(empty($error)) {
					// Add the crontask
					register_crontask('0 * * * * cd '.dirname(__FILE__).'../ && php refresh.php > logs/cron.log 2>&1', 'FREEDER AUTOADDED CRONTASK ('.$config->base_url.')');

					$_SESSION['user'] = new stdClass;
					$_SESSION['user']->login = $_POST['login'];
					$_SESSION['is_admin'] = 1;
					header('location: index.php');
					exit();
				}
			}
		}
	} else {
		if(isset($_POST['login'])) {
			$error['title'] = 'Incomplete installation form';
			$error['content'] = 'You must fill every field.';
		}
	}

	if ($error['title'] == '') {
		$error = NULL;
	}
	$tpl->assign('error', $error, RainTPL::RAINTPL_IGNORE_SANITIZE);
	$tpl->draw('install');
	exit();
}

