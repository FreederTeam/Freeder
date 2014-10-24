<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to handle the entries
 */


require_once(INC_DIR . 'feeds.php');
require_once(INC_DIR . 'tags.php');
require_once(INC_DIR . 'views.php');

/**
 * Clean up 'authors' attribute of entry.
 * @param $authors is an array of author classes (stdClass) to clean.
 */
function clean_authors($authors) {
	// TODO : Unit test
	if ($authors == NULL) return array();

	$new_authors = array();

	foreach($authors as $author) {
		$new_authors[] = (array) $author;
		if (empty($author->name) && !empty($author->email)) {
			$explode = explode(' ', $author->email);
			if (count($explode) == 2 && filter_var(trim($explode[0], ' ()<>'), FILTER_VALIDATE_EMAIL)) {
				$name = trim($explode[1], ' ()');
				$new_authors[count($new_authors) - 1]['name'] = $name;
			}
			elseif (count($explode) == 2 && filter_var(trim($explode[1], ' ()<>'), FILTER_VALIDATE_EMAIL)) {
				$name = trim($explode[0], ' ()');
				$new_authors[count($new_authors) - 1]['name'] = $name;
			}
			else {
				$new_authors[count($new_authors) - 1]['name'] = $author->email;
			}
		}
	}
	return $new_authors;
}


/**
 * Get all the available entries from the database
 * @param $view is the name of the view. By default view rule is empty.
 * @param $page is the page in the view
 * @return Array of associative arrays for each entry.
 */
function get_entries($view='', $page=1) {
	global $dbh, $config;

	$rule = get_view_rule($view);

	$r = rule2sql($rule, 'id, feed_id, authors, title, links, description, content, enclosures, comments, guid, pubDate, lastUpdate', $config->entries_per_page, ($page-1)*$config->entries_per_page);
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
		$tags = array_merge($entry_tags, $feed_tags);
		$entry['system_tags'] = filter_tags($tags, SYSTEM_TAGS);
		$entry['tags'] = filter_tags($tags, USER_TAGS);

		$entries[] = $entry;
	}
	return $entries;
}


/**
 * Get the number of matching entries for the view $view
 * @param $view is the name of the view. By default view rule is empty.
 * @param $page is the page in the view
 * @return Array of associative arrays for each entry.
 */
function get_entries_count($view='', $page=1) {
	global $dbh, $config;

	$rule = get_view_rule($view);

	$r = rule2sql($rule, 'COUNT(*) AS nb', $config->entries_per_page, ($page-1)*$config->entries_per_page);
	$query = $dbh->prepare($r[0]);
	$query->execute($r[1]);
	$fetched = $query->fetch(PDO::FETCH_ASSOC);
	return $fetched['nb'];
}


/**
 * Delete the old entries as specified in the config
 */
function delete_old_entries() {
	global $dbh, $config;

	$query = $dbh->prepare('DELETE FROM entries WHERE id NOT IN (SELECT id FROM (SELECT DISTINCT feed_id FROM entries) t1, entries t2 WHERE t2.id IN (SELECT id FROM entries t3 WHERE t3.feed_id=t1.feed_id ORDER BY pubDate DESC LIMIT ?))');
	$query->execute(array($config->entries_to_keep));
}


/**
 * Returns the link associated with the entry
 */
function get_entry_link($entry) {
	foreach ($entry['links'] as $link) {
		if ($link->rel == 'alternate') {
			return $link->href;
		}
	}
	return '#';
}


/**
 * Check wether an entry has the tag `$tag` (true) or not (false).
 */
function is_tag($tag, $entry) {
	$tags = array_merge($entry['tags'], $entry['system_tags']);
	$res = multiarray_search('name', $tag, $tags, false);
	if (!empty($res)) {
		return true;
	}
	else {
		return false;
	}
}


