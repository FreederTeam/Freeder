<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to handle the OPML files
 */

require_once(dirname(__FILE__).'/tools.php');
require_once(dirname(__FILE__).'/tags.php');


/**
 * Generate an OPML file to export the feeds.
 *
 * @param	$pretty_print	(optionnal) Wether the output should be pretty-printed or not. Defaults to `true`.
 * @copyright Heavily based on a function from FreshRSS.
 * @return The export as OPML text.
 */
function opml_export($feeds, $pretty_print=true) {
	$eol = $pretty_print ? PHP_EOL : '';

	$tags = tags_from_feeds_array($feeds);

	$now = new Datetime();
	$txt = '<?xml version="1.0" encoding="UTF-8"?>'.$eol;
	$txt .= '<opml version="2.0">'.$eol;
	$txt .= "\t".'<head>'.$eol;
	$txt .= "\t\t".'<title>Export of Freeder feeds</title>'.$eol;
	$txt .= "\t\t".'<dateCreated>'.$now->format(DateTime::RFC822).'</dateCreated>'.$eol;
	$txt .= "\t".'</head>'.$eol;
	$txt .= "\t".'<body>'.$eol;

	foreach ($tags as $tag=>$id_feeds) {
		$txt .= "\t\t".'<outline text="'.$tag.'">'.$eol;

		foreach ($id_feeds as $id_feed) {
			$website = multiarray_search(array('rel'=>'alternate'), $feeds[$id_feed]['links']);
			if ($website !== false) {
				$website = 'htmlUrl="'.htmlspecialchars_utf8($website['href']).'"';
			}
			$txt .= "\t\t\t".'<outline text="'.htmlspecialchars_utf8($feeds[$id_feed]['title']).'" type="rss" xmlUrl="'.htmlspecialchars_utf8($feeds[$id_feed]['url']).'" '.$website.' description="'.htmlspecialchars_utf8($feeds[$id_feed]['description']).'" />'.$eol;
		}

		$txt .= "\t\t".'</outline>'.$eol;
	}

	$txt .= "\t".'</body>'.$eol;
	$txt .= '</opml>';

	return $txt;
}


/**
 * Parse an OPML file for import.
 *
 * @param	$xml	An OPML file.
 * @return An array of associative arrays for each feed, containing the feed URL, title and associated list of tags.
 * @copyright Heavily based on a function from FreshRSS.
 */
function opml_import($xml) {
	$opml = simplexml_load_string($xml);

	if (!$opml) {
		return false;
	}

	$feeds = array ();

	foreach ($opml->body->outline as $outline) {
		if (!isset ($outline['xmlUrl'])) {  // Folder
			$tag = '';

			if (isset ($outline['text'])) {
				$tag = (string) $outline['text'];
			}
			elseif (isset ($outline['title'])) {
				$tag = (string) $outline['title'];
			}

			if ($tag) {
				foreach ($outline->outline as $feed) {
					if (!isset($feed['xmlUrl'])) {
						continue;
					}

					$search = multiarray_keys(array('url'=>(string) $feed['xmlUrl']), $feeds);
					if (empty($search)) {
						// Feed was not yet encountered, so add it first
						if (isset($feed['title'])) {
							$feed_title = (string) $feed['title'];
						}
						elseif (isset($feed['text'])) {
							$feed_title = (string) $feed['text'];
						}
						else {
							$feed_title = '';
						}

						$feeds[] = array(
							'url'=>(string) $feed['xmlUrl'],
							'title'=>$feed_title,
							'tags'=>array()
						);
						$search = key($feeds);
					}
					else {
						$search = $search[0];
					}
					// Update tags for this feed
					$feeds[$search]['tags'][] = $tag;
				}
			}
		}
		else {  // This is directly an RSS feed
			if (isset($outline['title'])) {
				$title = (string) $outline['title'];
			}
			elseif (isset($outline['text'])) {
				$title = (string) $outline['text'];
			}
			else {
				$title = '';
			}

			$search = multiarray_keys(array('url'=>(string) $outline['xmlUrl'], $feeds));
			if (empty($search)) {
				// Feed was not yet added, so add it
				$feeds[] = array(
					'url'=>(string) $outline['xmlUrl'],
					'title'=>$title,
					'tags'=>array()
				);
			}
		}
	}

	return $feeds;
}
