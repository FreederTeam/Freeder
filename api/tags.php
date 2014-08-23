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


if (isset($_GET['entry']) && isset($_GET['tag'])) {
	add_tag_to_entry(intval($_GET['entry']), $_GET['tag']);
	exit('OK');
}
else {
	exit('Fail');
}

