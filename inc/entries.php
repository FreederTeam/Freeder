<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to handle the entries
 */


require_once('views.php');

/**
 * Get all the available entries from the database
 * @param $view is the name of the view. By default view rule is empty.
 * @return Array of associative arrays for each entry.
 */
function get_entries($view='') {
	global $dbh, $config;

	// Get rule from view name.
	$query = $dbh->query('SELECT rule FROM views WHERE name = ?');
	$query->execute(array($view));
	if (!($rule = $query->fetch(PDO::FETCH_ASSOC)['rule'])) {
		$rule = '';
	}

	$r = rule2sql($rule, 'id, feed_id, authors, title, links, description, content, enclosures, comments, guid, pubDate, lastUpdate');
	$query = $dbh->query($r[0]);
	$query->execute($r[1]);
	$fetched_entries = $query->fetchall(PDO::FETCH_ASSOC);

	$entries = array();
	foreach ($fetched_entries as $entry) {
		switch($config->display_entries) {
			case 'content':
				if (!empty($entry['content'])) {
					$entry['displayed_content'] = $entry['content'];
				}
				else {
					$entry['displayed_content'] = $entry['description'];
				}
				break;

			case 'description':
				$entry['displayed_content'] = $entry['description'];
				break;

			case 'title':
				$entry['displayed_content'] = '';
				break;

			default:
				$entry['displayed_content'] = $entry['description'];
				break;
		}
		$entries[] = $entry;
	}
	return $entries;
}


/**
 * Delete the old entries as specified in the config
 * @todo This function
 */
function delete_old_entries() {
}
