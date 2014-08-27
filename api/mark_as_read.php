<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief API functions and code to mark entries as read / unread
 */


require_once('../inc/init.php');
require_once('../inc/views.php');


/**
 * Mark an entry as read
 */
function mark_read($entry_id) {
	global $dbh;

	$q = $dbh->query('INSERT INTO tags_entries(tag_id, entry_id) VALUES(1, ?)');
	$q->execute(array($entry_id));
}


/**
 * Mark an entry as unread
 */
function mark_unread($entry_id) {
	global $dbh;

	$q = $dbh->query('DELETE FROM tags_entries WHERE tag_id=1 AND entry_id=?');
	$q->execute(array($entry_id));
}


/**
 * Mark all entries as read
 */
function mark_all_read($view='') {
	global $dbh;

	$dbh->beginTransaction();

	$rule = get_view_rule($view);
	$r = rule2sql($rule, 'id');
	$query = $dbh->prepare($r[0]);
	$query->execute($r[1]);
	$fetched_entries = $query->fetchall(PDO::FETCH_ASSOC);

	$q = $dbh->prepare('INSERT OR IGNORE INTO tags_entries(tag_id, entry_id) VALUES(1, :id)');
	$q->bindParam(':id', $id, PDO::PARAM_INT);

	foreach($fetched_entries as $entry) {
		$id = intval($entry['id']);
		$q->execute();
	}

	$dbh->commit();
}


if (isset($_GET['entry'])) {
	if(!isset($_GET['unread'])) {
		mark_read(intval($_GET['entry']));
	}
	else {
		mark_unread(intval($_GET['entry']));
	}
	exit('OK');
}
elseif (isset($_GET['all'])) {
	$view = (isset($_GET['view'])) ? $_GET['view'] : '';
	mark_all_read($view);
	exit('OK');
}
else {
	exit('Fail');
}

