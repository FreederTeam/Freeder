<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to handle the tags
 */

require_once(INC_DIR . 'functions.php');


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


/**
 * Get list of tags
 */
define('ALL_TAGS', 0);
define('SYSTEM_TAGS', 1);
define('USER_TAGS', 2);
function get_tags($filter=ALL_TAGS) {
	global $dbh;

	switch($filter) {
		case SYSTEM_TAGS:
		$query = $dbh->query('SELECT id, name FROM tags WHERE name LIKE "\_%" ESCAPE "\"');
		break;

		case USER_TAGS:
		$query = $dbh->query('SELECT id, name FROM tags WHERE name NOT LIKE "\_%" ESCAPE "\"');
		break;

		case ALL_TAGS:
		default:
		$query = $dbh->query('SELECT id, name FROM tags');
		break;
	}

	return $query->fetchAll(PDO::FETCH_ASSOC);
}


/**
 * Get tags for entry
 */
function get_entry_tags($entry_id) {
	global $dbh;

	$query = $dbh->prepare('SELECT T.name AS name, TE.tag_id AS id, TE.auto_added_tag AS auto_added_tag, 1 AS entry_tag FROM tags_entries AS TE JOIN tags AS T ON T.id=TE.tag_id WHERE entry_id=:entry_id');
	$query->bindValue('entry_id', (int) $entry_id);
	$query->execute();

	return $query->fetchAll(PDO::FETCH_ASSOC);
}


/**
 * Get tags for feed
 */
function get_feed_tags($feed_id) {
	global $dbh;

	$query = $dbh->prepare('SELECT T.name AS name, TF.tag_id AS id, TF.auto_added_tag AS auto_added_tag, 0 AS entry_tag FROM tags_feeds AS TF JOIN tags AS T ON T.id=TF.tag_id WHERE feed_id=:feed_id');
	$query->bindValue('feed_id', (int) $feed_id);
	$query->execute();

	return $query->fetchAll(PDO::FETCH_ASSOC);
}


/**
 * Filter a list of tags
 */
function filter_tags($tags, $keep=ALL_TAGS) {
	$output = $tags;
	foreach($tags as $key=>$tag) {
		if ($keep == USER_TAGS && startswith($tag, '_')) {
			unset($output[$key]);
		}
		elseif ($keep == SYSTEM_TAGS && !startswith($tag, '_')) {
			unset($output[$key]);
		}
	}
	return $output;
}
