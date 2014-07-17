<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

require_once('inc/init.php');

require_auth();

$feeds = get_feeds();

$feeds_to_refresh = array();
foreach($feeds as $feed) {
	$feeds_to_refresh[$feed['id']] = array('url'=>$feed['url'], 'post'=>$feed['post']);
}

refresh_feeds($feeds_to_refresh);

header('location: index.php');
