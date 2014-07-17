<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to handle the OPML files
 */

require_once('functions.php');


/**
 * Generate an OPML file to export the feeds.
 *
 * @copyright Heavily based on a function from FreshRSS.
 * @todo Adapt to our code
 */
function opml_export($cats) {
	$now = new Datetime();
	$txt = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	$txt .= '<opml version="2.0">'."\n";
	$txt .= "\t".'<head>'."\n";
	$txt .= "\t\t".'<title>Freeder</title>'."\n";
	$txt .= "\t\t".'<dateCreated>'.$now->format(DateTime::RFC822).'</dateCreated>'."\n";
	$txt .= "\t".'</head>'."\n";
	$txt .= "\t".'<body>'."\n";

	foreach ($cats as $cat) {
		$txt .= "\t\t".'<outline text="'.$cat['name'].'">'."\n";

		foreach ($cat['feeds'] as $feed) {
			$txt .= "\t\t\t".'<outline text="'.$feed->name.'" type="rss" xmlUrl="'.$feed->url.'" htmlUrl="'.$feed->website.'" description="'.htmlspecialchars($feed->description, ENT_COMPAT, 'UTF-8').'" />'."\n";
		}

		$txt .= "\t\t".'</outline>'."\n";
	}
	$txt .= "\t".'</body>'."\n";
	$txt .= '</opml>';

	return $txt;
}


/**
 * Parse an OPML file.
 *
 * @return An array of associative array for each feed with URL, title and associated tags.
 * @copyright Heavily based on a function from FreshRSS.
 */
function opml_import($xml) {
	$opml = simplexml_load_string($xml);

	if (!$opml) {
		return false;
	}

	$categories = array ();
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
				foreach($outline->outline as $feed) {
					if(!isset($feed['xmlUrl'])) {
						continue;
					}

					$search = multiarray_search('url', (string) $feed['xmlUrl'], $feeds, false);
					if($search === FALSE) {
						// Feed was not yet encountered, so add it first
						if(isset($feed['title'])) {
							$feed_title = (string) $feed['title'];
						}
						elseif(isset($feed['text'])) {
							$feed_title = (string) $feed['text'];
						}
						else {
							$feed_title = '';
						}

						$feeds[] = array(
							'url'=>(string) $feed['xmlUrl'],
							'title'=>$feed_title,
							'tags'=>array(),
							'post'=>''
						);
						$key = count($feeds) - 1;
					}
					else {
						// Else, append categories to the existing feed
						$key = array_search($search, $feeds);
					}
					$feeds[$key]['tags'][] = $tag;
				}
			}
		}
		else {  // This is a RSS feed without any folder
			if(isset($outline['title'])) {
				$title = (string) $outline['title'];
			}
			elseif(isset($outline['text'])) {
				$title = (string) $outline['text'];
			}
			else {
				$title = '';
			}

			if(multiarray_search('url', (string) $outline['xmlUrl'], $feeds, false) !== false) {
				$feeds[] = array(
					'url'=>(string) $outline['xmlUrl'],
					'title'=>$title,
					'tags'=>array(),
					'post'=>''
				);
			}
		}
	}

	return $feeds;
}
