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
if (!empty($_POST['synchronization_type']) && !empty($_POST['template']) && !empty($_POST['timezone']) && isset($_POST['use_tags_from_feeds']) && isset($_POST['anonymous_access']) && isset($_POST['entries_to_keep']) && !empty($_POST['display_entries'])) {
	$config->synchronization_type = $_POST['synchronization_type'];

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

	// Timezone
	if (in_array($_POST['timezone'], timezone_identifiers_list())) {
		$config->timezone = $_POST['timezone'];
	}
	else {
		die('Error: Invalid timezone.');
	}

	$config->use_tags_from_feeds = (int) $_POST['use_tags_from_feeds'];
	$config->anonymous_access = (int) $_POST['anonymous_access'];
	$config->entries_to_keep = (int) $_POST['entries_to_keep'];
	if ($_POST['display_entries'] == 'content' || $_POST['display_entries'] == 'description' || $_POST['display_entries'] == 'title') {
		$config->display_entries = $_POST['display_entries'];
	}
	else {
		die('Error: Invalid `display_entries` configuration option.');
	}
	$config->save();
	header('location: settings.php');
	exit();
}

// Handle posted info for new feed
if (!empty($_POST['feed_url']) && isset($_POST['feed_post'])) {
	// If provided, get POST data to send to the feed server (for authentification essentially).
	if (is_array(json_decode($_POST['feed_post'], true))) {
		$post = $_POST['feed_post'];
	}
	else {
		$post = '';
	}

	// Try to add feed
	$add_errors = add_feeds(array(array('url'=>$_POST['feed_url'], 'post'=>$post)));

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
if (!empty($_GET['delete_feed'])) {
	delete_feed_id(intval($_GET['delete_feed']));
	header('location: settings.php');
	exit();
}

// Handle feed refresh
if (!empty($_GET['refresh_feed'])) {
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
if (isset($_POST['export'])) {
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
if (isset($_FILES['import'])) {
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
	$errors_refresh = add_feeds($feeds_opml);
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


