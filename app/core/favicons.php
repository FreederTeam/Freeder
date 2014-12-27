<?php
/** Freeder
 *  ------------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions. Written by Phyks, complete code available at https://github.com/Phyks/FaviconsLib.
 *  @brief Simple lib to try to get favicons from URLs.
 */

require_once(dirname(__FILE__).'/Downloader.php');
require_once(dirname(__FILE__).'/tools.php');


function handle_favicons_from_link_tags($result) {
	if ($result->info['http_code'] != 200 || false === strstr($result->info['content-type'], 'text/html')) {
		return false;
	}

	$content = $result->body;
	$content = substr($content, 0, strpos($content, '</head>')).'</head></html>'; // We don't need the full page, just the <head>

	// Create an XML object from the content of the <head>
	$html = new DOMDocument();
	$html->strictErrorChecking = false;
	$fail = @$html->loadHTML($content);
	if (false === $fail) {
		throw new Exception("Invalid HTML content.");
	}
	$xpath = new DOMXPath($html);
	if (false === $xpath) {
		throw new Exception("Invalid HTML content.");
	}

	$favicons = array();
	$favicons_links = $xpath->query('/html/head/link[@rel="icon" and @href]');
	foreach ($favicons_links as $link) {
		$favicons[] = array(
			'favicon_url'=>$link->getAttribute('href'),
			'sizes'=>$link->getAttribute('sizes')
		);
	}

	return $favicons;
}


function handle_favicons_from_root_favicon($result) {
	if ($result->info['http_code'] != 200) {
		return false;
	}
	else {
		return true;
	}
}



/**
 * Try to get the favicon associated with some URLs, by parsing the header or
 * trying to get the file favicon.ico at the root of the server.
 *
 * @param	$urls	Array of URLs to handle.
 * @return An array `{favicons, errors}`. `errors` is an array of URLs for which there could not be any fetched favicon. `favicons` is an array with URLs as keys and arrays of `{url, favicon_url, size}` as values.
 */
function get_favicons($urls) {
	$favicons = array();

	// Don't do first check for non HTML content
	$urls_to_fetch = array_filter($urls, function ($u) { return !endswith($u, '.xml'); });

	// Download the pages
	$downloader = new Downloader();
	$downloader->get($urls_to_fetch, function ($result) use (&$favicons) {
		$url = $result->info['url'];
		try {
			$r = handle_favicon_from_link_tags($result);
			if (false !== $r) {
				$r['url'] = $url;
				$favicons[$url] = $r;
			}
		}
		catch (Exception $e) {
		}
	});

	// Determine the remaining feeds for the second try
	$urls_to_fetch = array_diff($urls, array_keys($favicons));

	// For them, check whether the favicon.ico file at the root exists
	$second_try = array();
	foreach ($urls_to_fetch as $url) {
		$parsed_url = parse_url(trim($url));
		$second_try_url = "";
		if(isset($parsed_url['scheme'])) {
			$second_try_url .= $parsed_url['scheme'].'://';
		}
		else {
			$second_try_url .= "http://";
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
		$second_try_url .= '/favicon.ico'
		$second_try[$second_try_url] = array(
			'input_url'=>$url,
			'url'=>$second_try_url
		);
	}

	$urls_to_fetch = array_map($second_try, function ($u) { return $u['url']; });
	$downloader->header($urls_to_fetch, function ($result) use ($second_try, &$favicons) {
		$favicon_url = $result->info['url'];
		$url = $second_try[$favicon_url]['input_url'];
		if (handle_favicon_from_root_favicon($result)) {
			$favicons[$url] = array("url"=>$url, "favicon_url"=>$favicon_url, "sizes"=>"");
		}
	});

	$errors = array_diff($urls, array_keys($favicons));

	return array('favicons'=>$favicons, 'errors'=>$errors);
}


