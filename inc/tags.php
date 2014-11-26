<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to handle tags.
 */


/**
 * Returns an array of tags for a feeds array.
 *
 * @param	$feeds	An array of feeds, with a `tags` entrie for each feed.
 * @return An associative array with tags as keys and list of keys of matching feeds as values.
 */
function tags_from_feeds_array($feeds) {
	$tags = array();
	foreach ($feeds as $key=>$feed) {
		if (empty($feed['tags'])) {
			$tags['untagged'][] = $key;
			continue;
		}
		foreach ($feed['tags'] as $tag) {
			$tags[$tag][] = $key;
		}
	}
	return $tags;
}
