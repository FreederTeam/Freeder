<?php
/** Favicons Lib
 *  ------------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions. Written by Phyks, complete code available at https://github.com/Phyks/FaviconsLib.
 *  @brief Simple lib to try to get favicons from URLs.
 */

require_once(dirname(__FILE__).'/downloader.php');
require_once(dirname(__FILE__).'/tools.php');


/**
 * Try to get the favicon associated with some URLs, by parsing the header or
 * trying to get the file favicon.ico at the root of the server.
 *
 * @param	$urls	Array of URLs to handle.
 * @return An array `{favicons, errors}`. `errors` is an array of URLs for which there could not be any fetched favicon. `favicons` is an array with URLs as keys and arrays of `{favicon_url, size}` as values.
 */
function get_favicons($urls) {
	$favicons = array();
	$errors = array();

	$urls_to_fetch = array();
	// Don't do first check for non HTML content
	foreach ($urls as $url_array) {
		if (endswith($url_array['url'], '.xml')) {
			$errors[] = $url_array['url'];
		}
		else {
			$urls_to_fetch[] = $url_array;
		}
	}

	// Download the pages
	$contents = curl_downloader($urls_to_fetch);
	foreach ($contents['status_codes'] as $url=>$status) {
		if ($status != 200) {
			$errors[] = $url;
		}
	}

	foreach ($contents['results'] as $url=>$content) {
		$content = substr($content, 0, strpos($content, '</head>')).'</head></html>'; // We don't need the full page, just the <head>
		if ($content === '</head></html>') {
			continue;
		}

		// Create an XML object from the content of the <head>
		$html = new DOMDocument();
		$html->strictErrorChecking = false;
		$fail = @$html->loadHTML($content);
		if ($fail === false) {
			continue;
		}
		$xml = simplexml_import_dom($html);
		if ($xml === false) {
			continue;
		}

		// Try to fetch the favicon URL from the <head> tag
		foreach ($xml->head->children() as $head_tag) {
			// Favicon is necessarily in a <link> tag
			if ($head_tag->getName() != 'link') {
				continue;
			}

			// Check that the current tag is a favicon tag
			$attrs = $head_tag->attributes();
			$is_favicon_tag = current(array_filter($attrs, function ($k, $a) { return $key == 'rel' && strstr((string) $attribute, 'icon'); }));

			if ($is_favicon_tag !== false && isset($attrs['href'])) {
				if (isset($attrs['sizes'])) {
					$sizes = (string) $attrs['sizes'];
				}
				else {
					$sizes = '';
				}
				$favicons[$url][] = array(
					'favicon_url'=>(string) $attrs['href'],
					'sizes'=>$sizes
				);
			}
		}
	}

	// Add to errors the URLs without any favicons associated
	foreach (array_diff(array_keys($contents['results']), array_keys($favicons)) as $url) {
		$errors[] = $url;
	}

	// For feeds with errors, check whether the favicon.ico file at the root exists
	$second_try = array();
	foreach ($errors as $url) {
		$parsed_url = parse_url(trim($url));
		$second_try_url = "";
		if(isset($parsed_url['scheme'])) {
			$second_try_url .= $parsed_url['scheme'].'://';
		}
		if(isset($parsed_url['host'])) {
			$second_try_url .= $parsed_url['host'];
		}
		if(isset($parsed_url['port'])) {
			$second_try_url .= $parsed_url['port'];
		}
		if(isset($parsed_url['user'])) {
			$second_try_url .= $parsed_url['user'];
		}
		if(isset($parsed_url['pass'])) {
			$second_try_url .= $parsed_url['pass'];
		}
		$second_try[] = array(
			'input_url'=>$url,
			'url'=>$second_try_url . '/favicon.ico'
		);
	}
	$second_try_curl = curl_downloader($second_try, false);
	$errors = array();

	foreach($second_try as $tested_url) {
		$status_code = (int) $second_try_curl['status_codes'][$tested_url['url']];
		if ($status_code >= 200 && $status_code < 400) {
			$favicons[$tested_url['input_url']][] = array(
				'favicon_url'=>$tested_url['url'],
				'sizes'=>''
			);
		}
		else {
			$errors[] = $tested_url['input_url'];
		}
	}

	return array('favicons'=>$favicons, 'errors'=>$errors);
}


