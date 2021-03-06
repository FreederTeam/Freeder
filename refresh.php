<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

require_once('inc/init.php');
require_once('inc/feeds.php');

$command_line = is_command_line();
if (!$command_line && (!require_auth(false) || empty($_GET['token']) || !check_token(600, 'refresh'))) {
	header('location: index.php');
	exit();
}

$refresh_start = microtime(true);
$feeds = get_feeds('title');

$feeds_to_refresh = array();
foreach($feeds as $feed) {
	$feeds_to_refresh[$feed['id']] = array('id'=>$feed['id'], 'url'=>$feed['url'], 'post'=>$feed['post'], 'import_tags_from_feed'=>$feed['import_tags_from_feed']);
}

$tpl->assign('feeds_to_refresh', $feeds_to_refresh);
$tpl->assign('feeds', $feeds);
$tpl->assign('command_line', $command_line);
$tpl->draw('refresh');
