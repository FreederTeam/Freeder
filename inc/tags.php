<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to handle the tags
 */


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
