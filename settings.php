<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

// TODO: Modularize me!

require_once('inc/init.php');
require_once('inc/feeds.php');
require_once('inc/entries.php');

require_auth();

$feeds = get_feeds();

$tpl->assign('config', $config, RainTPL::RAINTPL_HTML_SANITIZE);
$tpl->assign('templates', list_templates(), RainTPL::RAINTPL_HTML_SANITIZE);
$tpl->assign('feeds', $feeds, RainTPL::RAINTPL_XSS_SANITIZE);

// Handle posted info for settings
if (!empty($_POST['synchronization_type']) && !empty($_POST['template']) && !empty($_POST['timezone']) && isset($_POST['import_tags_from_feeds']) && isset($_POST['anonymous_access']) && isset($_POST['entries_to_keep']) && !empty($_POST['display_entries']) && isset($_POST['entries_per_page']) && isset($_POST['share_input_shaarli']) && isset($_POST['share_input_diaspora']) && !empty($_POST['token']) && check_token(600, 'settings_form')) {
	if ($config->synchronization_type != $_POST['synchronization_type']) {
		$config->synchronization_type = $_POST['synchronization_type'];
		require_once(INC_DIR.'cron.php');
		if ($config->synchronization_type == 'cron') {
			register_crontask('0 * * * * cd '.dirname(__FILE__).'../ && php refresh.php > logs/cron.log 2>&1  # FREEDER AUTOADDED CRONTASK');
		}
		else {
			unregister_crontask('# FREEDER AUTOADDED CRONTASK');
		}
	}

	// Template
	if (is_dir(TPL_DIR.$_POST['template'])) {
		$config->template = $_POST['template'];
		if(!endswith($config->template, '/')) {
			$config->template = $config->template.'/';
		}
	}
	else {
		die('Error: Invalid template name.');
	}

	// If update password
	if (!empty($_POST['password']) && !empty($_POST['password_check'])) {
		if ($_POST['password'] == $_POST['password_check']) {
			$password = sha1($_SESSION['user']->salt.$_POST['password']);
			$query = $dbh->prepare('UPDATE users SET password=:password WHERE login=:login');
			$query->execute(array(
				':login'=>$_SESSION['user']->login,
				':password'=>$password
			));
		}
		else {
			$error = array();
			$error['type'] = 'error';
			$error['title'] = 'Password mismatch';
			$error['content'] = 'Passwords do not match!';
		}
	}

	// Timezone
	$timezone = trim($_POST['timezone']);
	if (in_array($timezone, timezone_identifiers_list())) {
		$config->timezone = $timezone;
	}
	else {
		die('Error: Invalid timezone.');
	}

	$config->import_tags_from_feeds = (int) $_POST['import_tags_from_feeds'];
	$config->anonymous_access = (int) $_POST['anonymous_access'];
	$config->entries_to_keep = (int) $_POST['entries_to_keep'];
	$config->entries_per_page = (int) $_POST['entries_per_page'];
	if ($_POST['display_entries'] == 'content' || $_POST['display_entries'] == 'description' || $_POST['display_entries'] == 'title') {
		$config->display_entries = $_POST['display_entries'];
	}
	else {
		die('Error: Invalid `display_entries` configuration option.');
	}
	if ($config->use_rewriting != $_POST['use_rewriting']) {
		$config->use_rewriting = (int) $_POST['use_rewriting'];
		if ($config->use_rewriting == 1) {
			if ($err = RainTPL::$rewriteEngine->write_htaccess()) {
				$current_user = get_current_user();
				die('Error: Unable to create or write .htaccess file. Check the writing rights of Freeder root directory. The user who executes Freeder — '.sanitize($current_user).' — should be able to write in this directory. You may prefer to create the .htaccess file on your own and allow '.sanitize($current_user).' to write only in .htaccess instead of in the whole Freeder root.');
			}
		}
	}

	if (!empty($_POST['share_input_facebook'])) {
		$config->facebook_share = (int) $_POST['share_input_facebook'];
	}
	else {
		$config->facebook_share = 0;
	}
	if (!empty($_POST['share_input_twitter'])) {
		$config->twitter_share = (int) $_POST['share_input_twitter'];
	}
	else {
		$config->twitter_share = 0;
	}
	if (!empty($_POST['share_input_shaarli'])) {
		if (filter_var($_POST['share_input_shaarli'], FILTER_VALIDATE_URL) !== false) {
			if (endswith($_POST['share_input_shaarli'], '/')) {
				$config->shaarli_share = $_POST['share_input_shaarli'];
			}
			else {
				$config->shaarli_share = $_POST['share_input_shaarli'].'/';
			}
		}
		else {
			die('Error: Incorrect shaarli URL');
		}
	}
	if (!empty($_POST['share_input_diaspora'])) {
		if (filter_var($_POST['share_input_diaspora'], FILTER_VALIDATE_URL) !== false) {
			if (endswith($_POST['share_input_diaspora'], '/')) {
				$config->diaspora_share = $_POST['share_input_diaspora'];
			}
			else {
				$config->diaspora_share = $_POST['share_input_diaspora'].'/';
			}
		}
		else {
			die('Error: Incorrect diaspora URL');
		}
	}

	$config->save();
	if (empty($error)) {
		header('location: settings.php');
		exit();
	}
	else {
		$tpl->assign('error', $error);
		$tpl->draw('settings');
		exit();
	}
}

// Handle posted info for new feed
if (!empty($_POST['feed_url']) && isset($_POST['feed_post']) && isset($_POST['import_tags_add']) && !empty($_POST['token']) && check_token(600, 'add_feed')) {
	// If provided, get POST data to send to the feed server (for authentification essentially).
	$feed_post = trim($_POST['feed_post']);
	if (is_array(json_decode($feed_post, true))) {
		$post = $feed_post;
	}
	else {
		$post = '';
	}

	// Try to add feed
	$add_errors = add_feeds(array(array('url'=>trim($_POST['feed_url']), 'post'=>$post)), (bool) $_POST['import_tags_add']);

	if(empty($add_errors)) {
		header('location: settings.php');
		exit();
	}
	else {
		$error = array();
		$error['type'] = 'error';
		$error['title'] = 'Error encountered when adding feeds.';
		$error['content'] = '<p>There were errors while trying to add the following feeds:</p><ul>';
		foreach($add_errors as $add_error) {
			$error['content'] .= '<li><a href="'.htmlspecialchars($add_error['url']).'">'.htmlspecialchars($add_error['url']).'</a> ('.htmlspecialchars($add_error['msg']).')</li>';
		}
		$error['content'] .= '</ul>';
		$tpl->assign('error', $error, RainTPL::RAINTPL_IGNORE_SANITIZE);
		$tpl->draw('settings');
		exit();
	}
}

// Handle feed deletion
if (!empty($_GET['delete_feed']) && !empty($_GET['token']) && check_token(600, 'delete_feed')) {
	delete_feed_id(intval($_GET['delete_feed']));
	header('location: settings.php');
	exit();
}

// Handle feed refresh
if (!empty($_GET['refresh_feed']) && !empty($_GET['token']) && check_token(600, 'refresh_feed')) {
	$query = $dbh->prepare('SELECT url FROM feeds WHERE id=:id');
	$query->execute(array('id'=>intval($_GET['refresh_feed'])));
	$url = $query->fetch();
	if (!empty($url['url'])) {
		refresh_feeds(array(intval($_GET['refresh_feed']) => array('url'=>$url['url'], 'post'=>''))); // TODO
	}
	header('location: settings.php');
	exit();
}

// Handle OPML export
if (isset($_POST['export']) && !empty($_POST['token']) && check_token(600, 'export_form')) {
	$feeds = array();
	foreach($_POST['export'] as $feed_id) {
		$feeds[] = get_feed($feed_id);
	}
	require_once('inc/opml.php');
	$now = new DateTime();
	header('Content-disposition: attachment; filename="freeder_export_'.$now->format('d-m-Y_H-i').'.xml"');
	header('Content-type: "text/xml"; charset="utf8"');
	exit(opml_export($feeds));
}

// Handle OPML import
if (isset($_FILES['import']) && isset($_POST['import_tags_opml']) && !empty($_POST['token']) && check_token(600, 'import_form')) {
	if ($_FILES['import']['error'] > 0) {
		$error = array();
		$error['type'] = 'error';
		$error['title'] = 'OPML import error';
		$error['content'] = '<p>The OPML file could not be imported.</p>';
		$tpl->assign('error', $error, RainTPL::RAINTPL_IGNORE_SANITIZE);
		$tpl->draw('settings');
		exit();
	}
	if ($_FILES['import']['size'] > 1048576) {
		$error = array();
		$error['type'] = 'error';
		$error['title'] = 'OPML import error';
		$error['content'] = '<p>The OPML file is too large.</p>';
		$tpl->assign('error', $error, RainTPL::RAINTPL_IGNORE_SANITIZE);
		$tpl->draw('settings');
		exit();
	}
	require_once('inc/opml.php');
	$feeds_opml = opml_import(file_get_contents($_FILES['import']['tmp_name']));
	if ($feeds_opml === false) {
		$error = array();
		$error['type'] = 'error';
		$error['title'] = 'OPML import error';
		$error['content'] = '<p>An error occurred during the OPML import. Maybe you did not upload a valid OPML file ?</p>';
		$tpl->assign('error', $error, RainTPL::RAINTPL_IGNORE_SANITIZE);
		$tpl->draw('settings');
		exit();
	}
	$errors_refresh = add_feeds($feeds_opml, (bool) $_POST['import_tags_opml']);
	if(empty($errors_refresh)) {
		header('location: settings.php');
		exit();
	}
	else {
		// Some feeds errorred
		$error = array();
		$error['type'] = 'warning';
		$error['title'] = 'OPML import error';
		$error['content'] = '<p>Some of the imported feeds encountered errors during refresh. The following feeds were <strong>NOT</strong> imported:</p><ul>';
		foreach($errors_refresh as $error_refresh) {
			$error['content'] .= '<li><a href="'.sanitize($error_refresh['url']).'">'.sanitize($error_refresh['url']).'</a> ('.sanitize($error_refresh['msg']).')</li>';
		}
		$error['content'] .= '</ul>';
		$tpl->assign('error', $error, RainTPL::RAINTPL_IGNORE_SANITIZE);
		$tpl->draw('settings');
		exit();
	}
}

$tpl->draw('settings');


