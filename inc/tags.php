<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to handle the tags
 */

require_once('functions.php');


/**
 * Delete the auto added tags
 */
function delete_auto_added_tags() {
	global $dbh;

	$dbh->query('DELETE FROM tags_entries WHERE auto_added_tag=1');
	$dbh->query('DELETE FROM tags_feeds WHERE auto_added_tag=1');

	delete_useless_tags();
}


/**
 * Delete the tags with no entries nor feeds associated
 * @todo This function
 */
function delete_useless_tags() {
}


/**
 * Add a tag to a feed based on its id.
 */
function add_tag_to_feed($id, $tag) {
	global $dbh;

	$dbh->beginTransaction();

	$query = $dbh->query('INSERT OR IGNORE INTO tags(name) VALUES(:tag_name)');
	$query_execute(array(':tag_name'=>$tag));

	$query = $dbh->query('INSERT INTO tags_feeds(tag_id, feed_id, auto_added_tag) VALUES((SELECT id FROM tags WHERE name=:tag_name), :feed_id, 0)');
	$query->bindValue(':tag_name', $tag);
	$query->bindValue(':feed_id', (int)$id, PDO::PARAM_INT);
	$query->execute();

	$dbh->commit();
}


/**
 * Remove a tag from a feed based on its id.
 */
function remove_tag_from_feed($id, $tag) {
	global $dbh;

	$query = $dbh->query('DELETE FROM tags_feeds WHERE tag_id=(SELECT id FROM tags WHERE name=:tag_name) AND feed_id=:feed_id');
	$query->bindValue(':tag_name', $tag);
	$query->bindValue(':feed_id', (int)$id, PDO::PARAM_INT);
	$query->execute();

	delete_useless_tags();
}


/**
 * Add a tag to an entry based on its id.
 */
function add_tag_to_entry($id, $tag) {
	global $dbh;

	$dbh->beginTransaction();

	$query = $dbh->query('INSERT OR IGNORE INTO tags(name) VALUES(:tag_name)');
	$query_execute(array(':tag_name'=>$tag));

	$query = $dbh->query('INSERT INTO tags_entries(tag_id, entry_id, auto_added_tag) VALUES((SELECT id FROM tags WHERE name=:tag_name), :entry_id, 0)');
	$query->bindValue(':tag_name', $tag);
	$query->bindValue(':entry_id', (int)$id, PDO::PARAM_INT);
	$query->execute();

	$dbh->commit();
}


/**
 * Remove a tag from an entry based on its id.
 */
function remove_tag_from_entry($id, $tag) {
	global $dbh;

	$query = $dbh->query('DELETE FROM tags_entries WHERE tag_id=(SELECT id FROM tags WHERE name=:tag_name) AND entry_id=:entry_id');
	$query->bindValue(':tag_name', $tag);
	$query->bindValue(':entry_id', (int)$id, PDO::PARAM_INT);
	$query->execute();

	delete_useless_tags();
}


