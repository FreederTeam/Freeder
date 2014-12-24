<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Various functions, not specific and widely used.
 */


/**
 * Check that `$haystack` starts with `$needle`.
 */
function startswith($haystack, $needle) {
	 $length = strlen($needle);
	 return (substr($haystack, 0, $length) === $needle);
}


/**
 * Check that `$haystack` ends with `$needle`.
 */
function endswith($haystack, $needle) {
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}

	return (substr($haystack, -$length) === $needle);
}


/**
 * Replace only the first occurrence of `$search` by `$replace` in `$subject`.
 * @param	$search		The value being searched for.
 * @param	$replace	The replacement value that replaces first found `$search` value.
 * @param	$subject	The string being searched and replaced on.
 * @return The input strings with replaced values.
 */
function str_replace_first($search, $replace, $subject) {
	$pos = strpos($subject, $search);
	if ($pos !== false) {
		return substr_replace($subject, $replace, $pos, strlen($search));
	}
	return $subject;
}


/**
 * `array_search` function for bidimensionnal arrays.
 * Search for an item containing `$needle` in `$haystack`.
 *
 * @param	$needle		2D array `{key=>value}` to search for.
 * @param	$haystack	The array to search in.
 * @param	$strict		(optionnal) Whether or not to use strict equality check.
 *
 * @return The matching sub-array if any, `false` otherwise.
 */
function multiarray_search($needle, $haystack, $strict = false) {
	$needle_key = key($needle);
	foreach ($haystack as $k=>$v) {
		if (!$strict && $v[$needle_key] == $needle[$needle_key]) {
			return $v;
		}
		elseif ($strict && $v[$needle_key] === $needle[$needle_key]) {
			return $v;
		}
	}
	return false;
}


/**
 * `array_keys` function for bidimensionnal arrays.
 * Returns all the keys or a subset of keys for a bidimensionnal array.
 *
 * @param	$array			The input 2D array.
 * @param	$search_value	(optionnal) Filter for keys. Array `{key=>value}` that should be contained in the values associated to the returned keys.
 * @param	$strict			(optionnal) Whether or not to use strict equality check.
 *
 * @return Array of keys.
 */
function multiarray_keys($array, $search_value=NULL, $strict = false) {
	if ($search_value === NULL) {
		return array_keys($array);
	}
	$keys = array();
	$search_key = key($search_value);
	foreach ($array as $k=>$v) {
		if ($search_value !== NULL && !$strict && $v[$search_key] == $search_value[$search_key]) {
			$keys[] = $k;
		}
		elseif ($search_value !== NULL && $strict && $v[$search_key] === $search_value[$search_key]) {
			$keys[] = $k;
		}
	}
	return $keys;
}


/**
 * Truncate a content at a certain length, keeping html tags intact.
 *
 * @copyright From cakePHP textHelper framework. Original license: MIT.
 * @param	$text			Text to truncate.
 * @param	$length			(optionnal) Truncating length. Defaults to 500.
 * @param	$ending			(optionnal) Character to add at the end of the truncated text. Defaults to '…'.
 * @param	$considerHTML	(optionnal) Whether to consider or not HTML tags, to prevent from breaking them. Defaults to `true`.
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
				}
				elseif (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
					// delete tag from $open_tags list
					$pos = array_search($tag_matchings[1], $open_tags);
					if ($pos !== false) {
					unset($open_tags[$pos]);
					}
				// if tag is an opening tag
				}
				elseif (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
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
						}
						else {
							// no more characters left
							break;
						}
					}
				}
				$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
				// maximum lenght is reached, so get off the loop
				break;
			}
			else {
				$truncate .= $line_matchings[2];
				$total_length += $content_length;
			}
			// if the maximum length is reached, get off the loop
			if ($total_length>= $length) {
				break;
			}
		}
	}
	else {
		if (strlen($text) <= $length) {
			return $text;
		}
		else {
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
	if ($considerHtml) {
		// close all unclosed html-tags
		foreach ($open_tags as $tag) {
			$truncate .= '</' . $tag . '>';
		}
	}
	return $truncate;
}


/**
* Get the time difference between `$start_time` and now, in a human readable way.
* @param	$start_time		A milliseconds timestamp
* @return The time difference as a string, with units (seconds or milliseconds).
*/
function time_diff($start_time) {
	$round = round(microtime(true) - (float)$start_time, 2).'s';
	if($round == '0s') {
		$round = round((microtime(true) - $start_time)*1000, 3).'ms';
	}
	return $round;
}


/**
* Format date for pretty printing
* @param	$timestamp		Date in timestamp format.
* @return Pretty-formatted date as a string.
*/
function format_date($timestamp) {
	$now = time();
	$diff = $now - $timestamp;
	if ($diff < 60) {
		return $diff.'s ago';
	} else if ($diff < 300) {
		return round($diff / 60).'min ago';
	} else if ($diff < 3600) {
		return (round($diff / 300) * 5).'min ago';
	} else if (floor($now/86400) == floor($timestamp/86400)) {
		return 'Today, '.date('H:i', $timestamp);
	} else if (floor($now/86400) == floor($timestamp/86400) + 1) {
		return 'Yesterday, '.date('H:i', $timestamp);
	} else if (date('Y:W', $now) == date('Y:W', $timestamp)) {
		return date('l, H:i', $timestamp);
	} else if (date('Y',$now) == date('Y',$timestamp)) {
		return date('F d, H:i', $timestamp);
	} else {
		return date('F d, Y, H:i', $timestamp);
	}
}


/**
 * Return the global category of a given MIME-TYPE.
 * @param	$mime_type		The MIME-TYPE whose category should be determined.
 * @return The category if a matching category is found, `false` otherwise.
 */
function get_category_mime_type($mime_type) {
	$end = strpos($mime_type, '/');
	if ($end === false) {
		return false;
	}
	$category = substr($mime_type, 0, $end);
	$available_categories = array(
		'application',
		'audio',
		'example',
		'image',
		'message',
		'model',
		'multipart',
		'text',
		'video'
	);
	$end = in_array($category, $available_categories);
	if ($end !== false) {
		return $category;
	}
	else {
		return false;
	}
}
