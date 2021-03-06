<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief API functions and code to handle tags for entries
 */


require_once('../inc/init.php');
require_once('../inc/views.php');
require_once('../inc/tags.php');


if (isset($_GET['entry']) && !empty($_GET['tag'])) {
	if (!isset($_GET['remove'])) {
		add_tag_to_entry(intval($_GET['entry']), $_GET['tag']);
	}
	else {
		remove_tag_from_entry(intval($_GET['entry']), $_GET['tag']);
	}
	exit(json_encode(array('status'=>'OK')));
}
elseif (isset($_GET['all']) && !empty($_GET['tag'])) {
	$view = (isset($_GET['view'])) ? $_GET['view'] : '';
	if (!isset($_GET['remove'])) {
		add_tag_to_all($view, $_GET['tag']);
	}
	else {
		remove_tag_to_all($view, $_GET['tag']);
	}
	exit(json_encode(array('status'=>'OK')));
}
else {
	http_response_code(400);
	exit(json_encode(array('status'=>'error', 'error'=>'Unknown method.')));
}

