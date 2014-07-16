<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to handle the entries
 */


/**
 * Get all the available entries from the database
 * @return Array of associative arrays for each entry.
 */
function get_entries() {
    global $dbh;

	$query = $dbh->query('SELECT id, feed_id, authors, title, links, description, content, enclosures, comments, guid, pubDate, lastUpdate, is_sticky, is_read FROM entries ORDER BY pubDate DESC');
	$entries = $query->fetchall(PDO::FETCH_ASSOC);
	return $entries;
}
