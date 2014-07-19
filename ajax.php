<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

require_once('inc/init.php');
require_once('inc/tags.php');

if (empty($_GET['do'])) {
	http_response_code(400);
	exit('No action specified.');
}

switch ($_GET['do']) {
case 'mark_read':
case 'mark_sticky':
case 'mark_private':
case 'mark_no_home':
	$tag_do = str_replace_first('mark', '', $_GET['do']);
	if (!empty($_GET['entry_id'])) {
		add_tag_to_entry(intval($_GET['entry_id']), $tag_do);
		exit();
	}
	elseif (!empty($_GET['feed_id'])) {
		add_tag_from_feed(intval($_GET['feed_id']), $tag_do);
		exit();
	}
	else {
		http_response_code(400);
		exit();
	}
	break;

case 'unmark_read':
case 'unmark_sticky':
case 'unmark_private':
case 'unmark_no_home':
	$tag_do = str_replace_first('mark', '', $_GET['do']);
	if (!empty($_GET['entry_id'])) {
		remove_tag_to_entry(intval($_GET['entry_id']), $tag_do);
		exit();
	}
	elseif (!empty($_GET['feed_id'])) {
		remove_tag_from_feed(intval($_GET['feed_id']), $tag_do);
		exit();
	}
	else {
		http_response_code(400);
		exit();
	}
	break;

case 'add_tag':
	if (!empty($_GET['tag'])) {
		if (!empty($_GET['entry_id'])) {
			add_tag_to_entry(intval($_GET['entry_id']), $_GET['tag']);
			exit();
		}
		elseif (!empty($_GET['feed_id'])) {
			add_tag_to_feed(intval($_GET['entry_id']), $_GET['feed']);
			exit();
		}
		else {
			http_response_code(400);
			exit();
		}
	}
	else {
		http_response_code(400);
		exit();
	}
	break;

case 'remove_tag':
	if (!empty($_GET['tag'])) {
		if (!empty($_GET['entry_id'])) {
			remove_tag_to_entry(intval($_GET['entry_id']), $_GET['tag']);
			exit();
		}
		elseif (!empty($_GET['feed_id'])) {
			remove_tag_to_feed(intval($_GET['entry_id']), $_GET['feed']);
			exit();
		}
		else {
			http_response_code(400);
			exit();
		}
	}
	else {
		http_response_code(400);
		exit();
	}
	break;

default:
	exit('Unknown action: '.htmlspecialchars($_GET['do']).'.');
}
