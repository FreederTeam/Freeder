<?php
/** Favicons Lib
 *  ------------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions. Written by Phyks, complete code available at https://github.com/Phyks/FaviconsLib.
 *  @brief Simple lib to try to get favicons from URLs.
 */

require_once(INC_DIR . 'functions.php');


/**
 * Try to get the favicon associated with some URLs, by parsing the header and
 * trying to get the file favicon.ico at the root of the server
 *
 * @param an array $urls of URLs
 * @return an array {'favicons', 'errors'}. `errors` is an array of URLs for which there could not be any fetched favicon. `favicons` is an array with URLs as keys and an array of favicon urls and sizes ({favicon_url, size}, associative array).
 */
function get_favicon($urls) {
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

	$contents = curl_downloader($urls_to_fetch);
	foreach($contents['status_codes'] as $url=>$status) {
		if($status != 200) {
			$errors[] = $url;
		}
	}

	foreach($contents['results'] as $url=>$content) {
		$content = substr($content, 0, strpos($content, '</head>')).'</head></html>'; // We don't need the full page, just the <head>

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
		foreach($xml->head->children() as $head_tag) {
			if($head_tag->getName() != 'link') {
				continue;
			}
			$go_next_tag = false;
			foreach($head_tag->attributes() as $key=>$attribute) {
				if($go_next_tag || $key != 'rel') {
					continue;
				}
				if(strstr((string) $attribute, 'icon')) {
					if(isset($head_tag->attributes()['sizes'])) {
						$sizes = (string)$head_tag->attributes()['sizes'];
					}
					else {
						$sizes = '';
					}
					$favicons[$url][] = array(
						'favicon_url'=>(string) $head_tag->attributes()['href'],
						'sizes'=>$sizes
					);
					$go_next_tag = true;
				}
			}
		}
	}

	// Add to errors the URLs without any favicons associated
	$favicons_keys = array_keys($favicons);
	foreach($contents['results'] as $url=>$content) {
		if(!in_array($url, $favicons_keys)) {
			$errors[] = $url;
		}
	}

	// Check for errorred feeds wether the favicon.ico file at the root exists
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


