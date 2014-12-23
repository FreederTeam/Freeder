<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Modifier Bronco'sfeed2array lib to convert feeds to arrays.
 */

require_once(dirname(__FILE__).'/tools.php');


/**
 * Converts a feed (RSS or ATOM) to an array.
 *
 * This is a modified version, closer to the specifications and handling more elements.
 *
 * @author bronco@warriordudimanche.net, modified for Freeder
 * @version (Original) 0.2.1
 * @copyright [Original version by bronco](https://github.com/broncowdd/feed2array) (released under "free and opensource license")
 *
 * @param	$feed	Either a feed content or a feed URI.
 * @return Returns an array representing the feed, or `false` if an error occurred.
 */
function feed2array($feed) {
	global $config;
	$flux = array('infos' => array(), 'items' => array());
	try {
		// If we have libxml, disable the incredibly dangerous entity loader. Cf. http://mikeknoop.com/lxml-xxe-exploit/.
		if (function_exists('libxml_disable_entity_loader')) {
			libxml_disable_entity_loader(true);
		}
		$load = filter_var($feed, FILTER_VALIDATE_URL) === false ? false : true;
		if ($load) {
			$feed = file_get_contents($feed);
		}
		if (false !== $feed_obj = new SimpleXMLElement($feed, LIBXML_NOCDATA, false)) {
			switch($feed_obj->getName()) {
				case 'rss':
					$type = 'RSS';
					if(empty($feed_obj->channel->item)) {
						throw new Exception("RSS feed seems to be empty.");
					}
					$items = $feed_obj->channel->item;
					break;

				case 'feed':
					$type = 'ATOM';
					if(empty($feed_obj->entry)) {
						throw new Exception("ATOM feed seems to be empty.");
					}
					$items = $feed_obj->entry;
					break;

				case 'RDF':
					$type = 'RDF';
					if(empty($feed_obj->item)) {
						throw new Exception("RDF feed seems to be empty.");
					}
					$items = array();
					foreach($feed_obj->item as $i) {
						$items[] = $i;
					}
					break;

				default:
					throw new Exception("Provided feed does not appear to be a valid RSS/ATOM/RDF feed.");
			}

			$flux['infos']['type'] = $type;

			// First, parse the feed head item
			if ($type == "RSS" || $type == "RDF") {
				if ($feed_obj->channel->title) {
					$flux['infos']['title'] = (string)$feed_obj->channel->title;
				}
				if ($feed_obj->channel->link) {
					$flux['infos']['links'][] = array(
						'href'=>(string)$feed_obj->channel->link,
						'rel'=>'self',
						'title'=>''
					);
				}
				if ($feed_obj->channel->description) {
					$flux['infos']['description'] = (string)$feed_obj->channel->description;
				}
			}
			if ($type == "RSS") {  // RSS feed
				if ($feed_obj->attributes()->version) {
					$flux['infos']['version'] = (string)$feed_obj->attributes()->version;
				}
				if ($feed_obj->channel->language) {
					$flux['infos']['language'] = (string)$feed_obj->channel->language;
				}
				if ($feed_obj->channel->copyright) {
					$flux['infos']['copyright'] = (string)$feed_obj->channel->copyright;
				}
				if ($feed_obj->channel->pubDate) {
					try {
						$tmp_date = new DateTime((string)$feed_obj->channel->pubDate);
						$flux['infos']['pubDate'] = $tmp_date->format('U');
					}
					catch (Exception $e) {
						if ($config->debug) {
							echo "Error while parsing feed pubDate: ".$e->getMessage().".<br/>";
						}
					}
				}
				if ($feed_obj->channel->lastBuildDate) {
					try {
						$tmp_date = new DateTime((string)$feed_obj->channel->lastBuildDate);
						$flux['infos']['lastBuildDate'] = $tmp_date->format('U');
					}
					catch (Exception $e) {
						if ($config->debug) {
							echo "Error while parsing feed lastBuildDate: ".$e->getMessage().".<br/>";
						}
					}
				}
				if ($feed_obj->channel->category) {
					foreach($feed_obj->channel->category as $category) {
						$flux['infos']['categories'][] = (string)$category;
					}
				}
				if ($feed_obj->channel->ttl) {
					$flux['infos']['ttl'] = (string)$feed_obj->channel->ttl;
				}
				if ($feed_obj->channel->image) {
					$flux['infos']['image'] = array(
						'url'=>(string)$feed_obj->channel->image->url,
						'title'=>(string)$feed_obj->channel->image->title,
						'link'=>(string)$feed_obj->channel->image->link
					);
				}
				if ($feed_obj->channel->skipHours) {
					foreach($feed_obj->channel->skipHours->children() as $hour) {
						$flux['infos']['skipHours'] = (string)$hour;
					}
				}
				if ($feed_obj->channel->skipDays) {
					foreach($feed_obj->channel->skipDays->children() as $day) {
						$flux['infos']['skipDays'] = (string)$day;
					}
				}
			}
			elseif ($type == 'RDF') {  // RDF feed
				if ($feed_obj->channel->children('dc', true)->date) {
					try {
						$tmp_date = new DateTime((string)$feed_obj->channel->children('dc', true)->date);
						$flux['infos']['pubDate'] = $tmp_date->format('U');
					}
					catch (Exception $e) {
						if ($config->debug) {
							echo "Error while parsing feed pubDate: ".$e->getMessage().".<br/>";
						}
					}
				}
			}
			elseif ($type == "ATOM") {  // ATOM feed
				if ($feed_obj->id) {
					$flux['infos']['id'] = (string)$feed_obj->id;
				}
				if ($feed_obj->title) {
					$flux['infos']['title'] = (string)$feed_obj->title;
				}
				if ($feed_obj->updated) {
					try {
						$tmp_date = new DateTime((string)$feed_obj->updated);
						$flux['infos']['updated'] = $tmp_date->format('U');
					}
					catch (Exception $e) {
						if ($config->debug) {
							echo "Error while parsing feed updated date: ".$e->getMessage().".<br/>";
						}
					}
				}
				if ($feed_obj->author) {
					foreach ($feed_obj->author as $author) {
						$author = (array) $author;
						if (!empty($author['email'])) {
							$email = (string)$author['email'];
						}
						else {
							$email = '';
						}
						if (!empty($author['uri'])) {
							$uri = (string)$author['uri'];
						}
						else {
							$uri = '';
						}
						$flux['infos']['authors'][] = array(
							'name'=>(string)$author['name'],
							'email'=>$email,
							'uri'=>$uri
						);
					}
				}
				if ($feed_obj->link) {
					foreach ($feed_obj->link as $link) {
						if (!empty($link['title'])) {
							$title = (string)$link['title'];
						}
						else {
							$title = '';
						}
						if (!empty($link['rel'])) {
							$rel = (string)$link['rel'];
						}
						else {
							$rel = 'alternate';
						}

						if ($rel != 'enclosure') {  // Discard enclosures in the feed element
							$flux['info']['links'][] = array(
								'href'=>(string)$link['href'],
								'title'=>$title,
								'rel'=>$rel
							);
						}
					}
				}
				if ($feed_obj->category) {
					foreach ($feed_obj->category as $tag) {
						if (!empty($tag['label'])) {
							$flux['infos']['categories'][] = (string)$tag['label'];
						}
						else {
							$flux['infos']['categories'][] = (string)$tag['term'];
						}
					}
				}
				if ($feed_obj->icon) {
					$flux['infos']['image'] = array(
						'url'=>(string)$feed_obj->icon,
						'title'=>'',
						'link'=>''
					);
				}
				if ($feed_obj->rights) {
					$flux['infos']['copyright'] = (string)$feed_obj->rights;
				}
				if ($feed_obj->subtitle) {
					$flux['infos']['description'] = (string)$feed_obj->subtitle;
				}
			}


			// Parse each items
			$c = 0;
			foreach ($items as $item) {
				if ($type == "RSS" || $type == 'RDF') {
					if ($item->title) {
						$flux['items'][$c]['title'] = (string)$item->title;
					}
					if ($item->link) {
						$flux['items'][$c]['links'][] = array(
							'href'=>(string)$item->link,
							'rel'=>'alternate',
							'title'=>''
						);
					}
					if ($item->description) {
						$flux['items'][$c]['description'] = (string)$item->description;
					}
				}
				if ($type == 'RDF') {
					if ($item->children('dc', true)->date) {
						try {
							$tmp_date = new DateTime((string)$item->children('dc', true)->date);
							$flux['items'][$c]['pubDate'] = $tmp_date->format('U');
						}
						catch (Exception $e) {
							if ($config->debug) {
								echo "Error while parsing feed entry pubDate: ".$e->getMessage().".<br/>";
							}
						}
					}
					if ($item->attributes('rdf', true)->about) {
						$flux['items'][$c]['guid'] = (string) $item->attributes('rdf', true)->about;
						$flux['items'][$c]['guid_is_permalink'] = false;
					}
				}
				elseif ($type == 'RSS') {
					if ($item->author) {
						$flux['items'][$c]['authors'][] = array(
							'name'=>'',
							'email'=>(string)$item->author,
							'uri'=>''
						);
					}
					if ($item->category) {
						foreach($item->category as $category) {
							$flux['items'][$c]['categories'][] = (string)$category;
						}
					}
					if ($item->comments) {
						$flux['items'][$c]['comments'] = (string)$item->comments;
					}
					if ($item->enclosure) {
						foreach($item->enclosure as $enclosure) {
							$flux['items'][$c]['enclosures'][] = array(
								'url'=>(string)$enclosure['url'],
								'type'=>(string)$enclosure['type'],
								'size'=>(string)$enclosure['length']
							);
						}
					}
					if ($item->guid) {
						$flux['items'][$c]['guid'] = (string)$item->guid;
						if(!empty($item->guid['isPermaLink'])) {
							$flux['items'][$c]['guid_is_permalink'] = (bool)$item->guid['isPermaLink'];
						}
						else {
							$flux['items'][$c]['guid_is_permalink'] = true;
						}
					}
					if ($item->pubDate) {
						try {
							$tmp_date = new DateTime((string)$item->pubDate);
							$flux['items'][$c]['pubDate'] = $tmp_date->format('U');
						}
						catch (Exception $e) {
							if ($config->debug) {
								echo "Error while parsing feed entry pubDate: ".$e->getMessage().".<br/>";
							}
						}
					}
				}
				elseif ($type == "ATOM") {
					if ($item->id) {
						$flux['items'][$c]['guid'] = (string)$item->id;
					}
					if ($item->title) {
						$flux['items'][$c]['title'] = (string)$item->title;
					}
					if ($item->updated) {
						try {
							$tmp_date = new DateTime((string)$item->updated);
							$flux['items'][$c]['updated'] = $tmp_date->format('U');
						}
						catch (Exception $e) {
							if ($config->debug) {
								echo "Error while parsing feed entry updated date: ".$e->getMessage().".<br/>";
							}
						}
					}
					if ($item->author) {
						foreach ($item->author as $author) {
							$author = (array) $author;
							if (!empty($author['email'])) {
								$email = (string)$author['email'];
							}
							else {
								$email = '';
							}
							if (!empty($author['uri'])) {
								$uri = (string)$author['uri'];
							}
							else {
								$uri = '';
							}
							$flux['items'][$c]['authors'][] = array(
								'name'=>(string)$author['name'],
								'email'=>$email,
								'uri'=>$uri
							);
						}
					}
					if ($item->link) {
						foreach ($item->link as $link) {
							if (!empty($link['title'])) {
								$title = (string)$link['title'];
							}
							else {
								$title = '';
							}
							if (!empty($link['rel'])) {
								$rel = (string)$link['rel'];
							}
							else {
								$rel = 'alternate';
							}

							if ($rel != 'enclosure') {
								$flux['items'][$c]['links'][] = array(
									'href'=>(string)$link['href'],
									'title'=>$title,
									'rel'=>$rel
								);
							}
							else {
								if (!empty($link['type'])) {
									$type = (string)$link['type'];
								}
								else {
									$type = '';
								}
								if (!empty($link['length'])) {
									$length = (string)$link['length'];
								}
								else {
									$length = '';
								}
								$flux['items'][$c]['enclosures'][] = array(
									'url'=>(string)$link['href'],
									'type'=>$type,
									'size'=>$length
								);
							}
						}
					}
					if ($item->summary) {
						$flux['items'][$c]['description'] = (string)$item->summary;
					}
					if ($item->category) {
						foreach ($item->category as $tag) {
							if (!empty($tag['label'])) {
								$flux['items'][$c]['categories'][] = (string)$tag['label'];
							}
							else {
								$flux['items'][$c]['categories'][] = (string)$tag['term'];
							}
						}
					}
					if ($item->published) {
						try {
							$tmp_date = new DateTime((string)$item->published);
							$flux['items'][$c]['pubDate'] = $tmp_date->format('U');
						}
						catch (Exception $e) {
							if ($config->debug) {
								echo "Error while parsing feed entry pubDate: ".$e->getMessage().".<br/>";
							}
						}
					}
					if ($item->rights) {
						$flux['items'][$c]['copyright'] = (string)$item->rights;
					}

					// Only updated is mandatory in ATOM spec
					if (empty($flux['items'][$c]['pubDate'])) {
						$flux['items'][$c]['pubDate'] = $flux['items'][$c]['updated'];
					}
				}

				// Handle special stuff
				// Content (with <content:encoded> tag)
				if ($item->content) {
					$flux['items'][$c]['content'] = (string)$item->content;
				}
				if ($item->children('content', true)->encoded) {
					$flux['items'][$c]['content'] = (string)$item->children('content', true)->encoded;
				}

				// For the feedburner origLink tag
				if ($item->children('feedburner', true)->origLink) {
					$flux['items'][$c]['links'][] = array(
						'url'=>(string)$item->children('feedburner', true)->origLink,
						'title'=>'',
						'rel'=>'origLink'
					);
				}

				// Fill description with a summary if it does not exist
				if(!empty($flux['items'][$c]['content']) && empty($flux['items'][$c]['description'])) {
					$flux['items'][$c]['description'] = truncate($flux['items'][$c]['content']);
				}

				// Add authors to items if not filled
				if(empty($flux['items'][$c]['authors']) && !empty($flux['infos']['authors'])) {
					$flux['items'][$c]['authors'] = $flux['infos']['authors'];
				}
				$c++;
			}
			return $flux;
		}
		else {
			throw new Exception("Unable to parse feed.");
		}
	} catch (Exception $e) {
		if($config->debug) {
			echo "[Feed2Array] Parse error: ".$e->getMessage().".<br/>Provided feed was:<br/>$feed<br/>";
		}
		return false;
	}
}
