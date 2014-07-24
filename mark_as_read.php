<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

require_once('inc/init.php');


if (isset($_GET['entry'])) {
	$q = $dbh->query('INSERT INTO tags_entries(tag_id, entry_id) VALUES(1, ?)');
	$q->execute(array($_GET['entry']));

	echo('ok');
} else {
	echo('fail');
}
