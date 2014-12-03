<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief feed2array lib by Bronco to converts feeds to arrays
 */


/**
 * Converts a feed (RSS or ATOM) to an array
 *
 * Modified version, closer to the specifications and handling more elements
 *
 * @author bronco@warriordudimanche.net, modified for Freeder
 * @version 0.2.1
 * @copyright Original version by bronco : https://github.com/broncowdd/feed2array (under "free and opensource license")
 *
 * @param $feed is either a feed content ($load=false) or a feed URI ($load=true)
 * @param $debug should be set to true to display error messages.
 * @return returns an (array)array, or (boolean)false if an exception occurs.
 */
function feed2array($feed, $load=false, $debug=false) {
	$flux = array('infos' => array(), 'items' => array());
	try {
		// If we have libxml, disable the incredibly dangerous entity loader. Cf. http://mikeknoop.com/lxml-xxe-exploit/.
		if (function_exists('libxml_disable_entity_loader')) {
			libxml_disable_entity_loader(true);
		}
		if($feed_obj = new SimpleXMLElement($feed, LIBXML_NOCDATA, $data_is_url=$load)) {
			switch($feed_obj->getName()) {
				case 'rss':
					$type = 'RSS';
					if(empty($feed_obj->channel->item)) {
						return false;
					}
					$items = $feed_obj->channel->item;
					break;

				case 'feed':
					$type = 'ATOM';
					if(empty($feed_obj->entry)) {
						return false;
					}
					$items = $feed_obj->entry;
					break;

				case 'RDF':
					$type = 'RDF';
					if(empty($feed_obj->item)) {
						return false;
					}
					$items = array();
					foreach($feed_obj->item as $i) {
						$items[] = $i;
					}
					break;

				default:
					return false;
			}

			$flux['infos']['type'] = $type;

			if($type == "RSS") {  // RSS feed
				if($feed_obj->attributes()->version){
					$flux['infos']['version'] = (string)$feed_obj->attributes()->version;
				}
				if($feed_obj->channel->language) {
					$flux['infos']['language'] = (string)$feed_obj->channel->language;
				}
				if($feed_obj->channel->copyright) {
					$flux['infos']['copyright'] = (string)$feed_obj->channel->copyright;
				}
				if($feed_obj->channel->pubDate) {
					try {
						$tmp_date = new DateTime((string)$feed_obj->channel->pubDate);
						$flux['infos']['pubDate'] = $tmp_date->format('U');
					}
					catch (Exception $e) {
					}
				}
				if($feed_obj->channel->lastBuildDate) {
					$flux['infos']['lastBuildDate'] = (string)$feed_obj->channel->lastBuildDaye;
				}
				if($feed_obj->channel->category) {
					foreach($feed_obj->channel->category as $category) {
						$flux['infos']['categories'][] = (string)$category;
					}
				}
				if($feed_obj->channel->ttl) {
					$flux['infos']['ttl'] = (string)$feed_obj->channel->ttl;
				}
				if($feed_obj->channel->image) {
					$flux['infos']['image'] = array(
						'url'=>(string)$feed_obj->channel->image->url,
						'title'=>(string)$feed_obj->channel->image->title,
						'link'=>(string)$feed_obj->channel->image->link
					);
				}
				if($feed_obj->channel->skipHours) {
					foreach($feed_obj->channel->skipHours->children() as $hour) {
						$flux['infos']['skipHours'] = (string)$hour;
					}
				}
				if($feed_obj->channel->skipDays) {
					foreach($feed_obj->channel->skipDays->children() as $day) {
						$flux['infos']['skipDays'] = (string)$day;
					}
				}
			}

			if($type == "RSS" || $type == "RDF") {  // RSS or RDF feed
				if($feed_obj->channel->title) {
					$flux['infos']['title'] = (string)$feed_obj->channel->title;
				}
				if($feed_obj->channel->link) {
					$flux['infos']['links'][] = array(
						'href'=>(string)$feed_obj->channel->link,
						'rel'=>'self',
						'title'=>''
					);
				}
				if($feed_obj->channel->description) {
					$flux['infos']['description'] = (string)$feed_obj->channel->description;
				}
			}

			if ($type == 'RDF') {  // RDF feed
				if($feed_obj->channel->children('dc', true)->date) {
					try {
						$tmp_date = new DateTime((string)$feed_obj->channel->children('dc', true)->date);
						$flux['infos']['pubDate'] = $tmp_date->format('U');
					}
					catch (Exception $e) {
					}
				}
			}

			if($type == "ATOM") {  // ATOM feed
				if($feed_obj->id) {
					$flux['infos']['id'] = (string)$feed_obj->id;
				}
				if($feed_obj->title) {
					$flux['infos']['title'] = (string)$feed_obj->title;
				}
				if($feed_obj->updated) {
					try {
						$tmp_date = new DateTime((string)$feed_obj->updated);
						$flux['infos']['updated'] = $tmp_date->format('U');
					}
					catch (Exception $e) {
					}
				}
				if($feed_obj->author) {
					foreach($feed_obj->author as $author) {
						$author = (array) $author;
						if(!empty($author['email'])) {
							$email = (string)$author['email'];
						}
						else {
							$email = '';
						}
						if(!empty($author['uri'])) {
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
				if($feed_obj->link) {
					foreach($feed_obj->link as $link) {
						if(!empty($link['title'])) {
							$title = (string)$link['title'];
						}
						else {
							$title = '';
						}
						if(!empty($link['rel'])) {
							$rel = (string)$link['rel'];
						}
						else {
							$rel = 'alternate';
						}

						if($rel != 'enclosure') {  // Discard enclosures in the feed element
							$flux['info']['links'][] = array(
								'href'=>(string)$link['href'],
								'title'=>$title,
								'rel'=>$rel
							);
						}
					}
				}
				if($feed_obj->category) {
					foreach($feed->obj->category as $tag) {
						if(!empty($tag['label'])) {
							$flux['infos']['categories'][] = (string)$tag['label'];
						}
						else {
							$flux['infos']['categories'][] = (string)$tag['term'];
						}
					}
				}
				if($feed_obj->icon) {
					$flux['infos']['image'] = array(
						'url'=>(string)$feed_obj->icon,
						'title'=>'',
						'link'=>''
					);
				}
				if($feed_obj->rights) {
					$flux['infos']['copyright'] = (string)$feed_obj->rights;
				}
				if($feed_obj->subtitle) {
					$flux['infos']['description'] = (string)$feed_obj->subtitle;
				}
			}


			foreach($items as $item) {
				$c = count($flux['items']);
				if($type == "RSS" || $type == 'RDF') {
					if($item->title) {
						$flux['items'][$c]['title'] = (string)$item->title;
					}
					if($item->link) {
						$flux['items'][$c]['links'][] = array(
							'href'=>(string)$item->link,
							'rel'=>'alternate',
							'title'=>''
						);
					}
					if($item->description) {
						$flux['items'][$c]['description'] = (string)$item->description;
					}
				}
				if ($type == 'RDF') {
					if($item->children('dc', true)->date) {
						try {
							$tmp_date = new DateTime((string)$item->children('dc', true)->date);
							$flux['items'][$c]['pubDate'] = $tmp_date->format('U');
						}
						catch (Exception $e) {
						}
					}
					if($item->attributes('rdf', true)->about) {
						$flux['items'][$c]['guid'] = (string) $item->attributes('rdf', true)->about;
						$flux['items'][$c]['guid_is_permalink'] = false;
					}
				}
				if($type == 'RSS') {
					if($item->author) {
						$flux['items'][$c]['authors'][] = array(
							'name'=>'',
							'email'=>(string)$item->author,
							'uri'=>''
						);
					}
					if($item->category) {
						foreach($item->category as $category) {
							$flux['items'][$c]['categories'][] = (string)$category;
						}
					}
					if($item->comments) {
						$flux['items'][$c]['comments'] = (string)$item->comments;
					}
					if($item->enclosure) {
						foreach($item->enclosure as $enclosure) {
							$flux['items'][$c]['enclosures'][] = array(
								'url'=>(string)$enclosure['url'],
								'type'=>(string)$enclosure['type'],
								'size'=>(string)$enclosure['length']
							);
						}
					}
					if($item->guid) {
						$flux['items'][$c]['guid'] = (string)$item->guid;
						if(!empty($item->guid['isPermaLink'])) {
							$flux['items'][$c]['guid_is_permalink'] = (bool)$item->guid['isPermaLink'];
						}
						else {
							$flux['items'][$c]['guid_is_permalink'] = true;
						}
					}
					if($item->pubDate) {
						try {
							$tmp_date = new DateTime((string)$item->pubDate);
							$flux['items'][$c]['pubDate'] = $tmp_date->format('U');
						}
						catch (Exception $e) {
						}
					}
				}
				if($type == "ATOM") {
					if($item->id) {
						$flux['items'][$c]['guid'] = (string)$item->id;
					}
					if($item->title) {
						$flux['items'][$c]['title'] = (string)$item->title;
					}
					if($item->updated) {
						try {
							$tmp_date = new DateTime((string)$item->updated);
							$flux['items'][$c]['updated'] = $tmp_date->format('U');
						}
						catch (Exception $e) {
						}
					}
					if($item->author) {
						foreach($item->author as $author) {
							$author = (array) $author;
							if(!empty($author['email'])) {
								$email = (string)$author['email'];
							}
							else {
								$email = '';
							}
							if(!empty($author['uri'])) {
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
					if($item->link) {
						foreach($item->link as $link) {
							if(!empty($link['title'])) {
								$title = (string)$link['title'];
							}
							else {
								$title = '';
							}
							if(!empty($link['rel'])) {
								$rel = (string)$link['rel'];
							}
							else {
								$rel = 'alternate';
							}

							if($rel != 'enclosure') {
								$flux['items'][$c]['links'][] = array(
									'href'=>(string)$link['href'],
									'title'=>$title,
									'rel'=>$rel
								);
							}
							else {
								if(!empty($link['type'])) {
									$type = (string)$link['type'];
								}
								else {
									$type = '';
								}
								if(!empty($link['length'])) {
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
					if($item->summary) {
						$flux['items'][$c]['description'] = (string)$item->summary;
					}
					if($item->category) {
						foreach($item->category as $tag) {
							if(!empty($tag['label'])) {
								$flux['items'][$c]['categories'][] = (string)$tag['label'];
							}
							else {
								$flux['items'][$c]['categories'][] = (string)$tag['term'];
							}
						}
					}
					if($item->published) {
						try {
							$tmp_date = new DateTime((string)$item->published);
							$flux['items'][$c]['pubDate'] = $tmp_date->format('U');
						}
						catch (Exception $e) {
						}
					}
					if($item->rights) {
						$flux['items'][$c]['copyright'] = (string)$item->rights;
					}

					// Only updated is mandatory in ATOM spec
					if (empty($flux['items'][$c]['pubDate'])) {
						$flux['items'][$c]['pubDate'] = $flux['items'][$c]['updated'];
					}
				}

				if($item->content) {
					$flux['items'][$c]['content'] = (string)$item->content;
				}
				// for the tricky <content:encoded> tag
				if($item->children('content', true)->encoded) {
					$flux['items'][$c]['content'] = (string)$item->children('content', true)->encoded;
				}

				// For the feedburner origLink tag
				if($item->children('feedburner', true)->origLink) {
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

				// Add authors to items
				if(empty($flux['items'][$c]['authors']) && !empty($flux['infos']['authors'])) {
					$flux['items'][$c]['authors'] = $flux['infos']['authors'];
				}
			}
			return $flux;
		}
		else {
			return false;
		}
	} catch (Exception $e) {
		if($debug) {
			echo 'Parse error XML: '.$feed.' : ' .$e->getMessage();
		}
		return false;
	}
}


/**
 * Truncate at a certain length, keeping html tags intact.
 *
 * @copyright From cakePHP textHelper framework. Original license: MIT
 */
function truncate($text, $length = 500, $ending = '…', $exact=false, $considerHtml=true) {
	if ($considerHtml) {
		// if the plain text is shorter than the maximum length, return the whole text
		if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
			return $text;
		}
		// splits all html-tags to scanable lines
		preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
		$total_length = strlen($ending);
		$open_tags = array();
		$truncate = '';
		foreach ($lines as $line_matchings) {
			// if there is any html-tag in this line, handle it and add it (uncounted) to the output
			if (!empty($line_matchings[1])) {
				// if it's an "empty element" with or without xhtml-conform closing slash
				if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
					// do nothing
				// if tag is a closing tag
				} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
					// delete tag from $open_tags list
					$pos = array_search($tag_matchings[1], $open_tags);
					if ($pos !== false) {
					unset($open_tags[$pos]);
					}
				// if tag is an opening tag
				} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
					// add tag to the beginning of $open_tags list
					array_unshift($open_tags, strtolower($tag_matchings[1]));
				}
				// add html-tag to $truncate'd text
				$truncate .= $line_matchings[1];
			}
			// calculate the length of the plain text part of the line; handle entities as one character
			$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
			if ($total_length+$content_length> $length) {
				// the number of characters which are left
				$left = $length - $total_length;
				$entities_length = 0;
				// search for html entities
				if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
					// calculate the real length of all entities in the legal range
					foreach ($entities[0] as $entity) {
						if ($entity[1]+1-$entities_length <= $left) {
							$left--;
							$entities_length += strlen($entity[0]);
						} else {
							// no more characters left
							break;
						}
					}
				}
				$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
				// maximum lenght is reached, so get off the loop
				break;
			} else {
				$truncate .= $line_matchings[2];
				$total_length += $content_length;
			}
			// if the maximum length is reached, get off the loop
			if($total_length>= $length) {
				break;
			}
		}
	} else {
		if (strlen($text) <= $length) {
			return $text;
		} else {
			$truncate = substr($text, 0, $length - strlen($ending));
		}
	}
	// if the words shouldn't be cut in the middle...
	if (!$exact) {
		// ...search the last occurance of a space...
		$spacepos = strrpos($truncate, ' ');
		if (isset($spacepos)) {
			// ...and cut the text in this position
			$truncate = substr($truncate, 0, $spacepos);
		}
	}
	// add the defined ending to the text
	$truncate .= $ending;
	if($considerHtml) {
		// close all unclosed html-tags
		foreach ($open_tags as $tag) {
			$truncate .= '</' . $tag . '>';
		}
	}
	return $truncate;
}


