<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to handle the entries
 */


require_once(INC_DIR . 'tags.php');
require_once(INC_DIR . 'views.php');

/**
 * Clean up 'authors' attribute of entry.
 * @param authors list to clean.
 */
function clean_authors($authors) {
	if ($authors == NULL) return array();
	$new_authors = array();
	foreach($authors as $author) {
		$new_authors[] = (array) $author;
		if (empty($author->name) && !empty($author->email)) {
			$explode = explode(' ', $author->email);
			if (!empty($explode[1])) {
				$name = trim($explode[1], ' ()');
				$new_authors[count($new_authors) - 1]['name'] = $name;
			}
		}
		if (empty($new_authors[count($new_authors) - 1]['name'])) {
			$new_authors[count($new_authors) - 1]['name'] = $new_authors[count($new_authors) - 1]['email'];
		}
	}
	return $new_authors;
}

/**
 * Get all the available entries from the database
 * @param $view is the name of the view. By default view rule is empty.
 * @return Array of associative arrays for each entry.
 */
function get_entries($view='') {
	global $dbh, $config;

	$rule = get_view_rule($view);

	$r = rule2sql($rule, 'id, feed_id, authors, title, links, description, content, enclosures, comments, guid, pubDate, lastUpdate', 10);
	$query = $dbh->prepare($r[0]);
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

		$entry['authors'] = clean_authors(json_decode($entry['authors']));
		$entry['links'] = json_decode($entry['links']);
		$entry['enclosures'] = json_decode($entry['enclosures']);

		$entry_tags = get_entry_tags($entry['id']);
		$feed_tags = get_feed_tags($entry['feed_id']);
		$entry['tags'] = array_merge($entry_tags, $feed_tags);

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


function get_entry_link($entry) {
	foreach ($entry['links'] as $link) {
		if ($link->rel == 'alternate') {
			return $link->href;
		}
	}
	return '#';
}


