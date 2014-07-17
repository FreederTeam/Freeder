<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

// TODO: Modularize me!

require_once('inc/init.php');

require_auth();

// Handle posted info for settings
if (!empty($_POST['synchronization_type']) && !empty($_POST['template']) && !empty($_POST['timezone']) && isset($_POST['use_tags_from_feeds']) && isset($_POST['anonymous_access']) && isset($_POST['entries_to_keep'])) {
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
		exit('Erreur - TODO');
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
	refresh_feeds(array(intval($_GET['refresh_feed']) => '')); // TODO
	header('location: settings.php');
	exit();
}

// Handle OPML import
if (isset($_FILES['import'])) {
	if ($_FILES['import']['error'] > 0) {
		exit();  // TODO: Error during upload
	}
	if ($_FILES['import']['size'] > 1048576) {
		exit();  // TODO: Error, file is too large
	}
	require_once('inc/opml.php');
	$feeds_opml = opml_import(file_get_contents($_FILES['import']['tmp_name']));
	if ($feeds_opml === false) {
		exit('ok');  // TODO: Error, OPML file not valid
	}
	$errors_refresh = add_feeds($feeds_opml);
	if(empty($errors_refresh)) {
		header('location: settings.php');
		exit();
	}
	else {
		// Some feeds errorred
		exit();  // TODO
	}
}

$feeds = get_feeds();

$tpl->assign('config', $config);
$tpl->assign('templates', list_templates());
$tpl->assign('feeds', $feeds);
$tpl->draw('settings');


