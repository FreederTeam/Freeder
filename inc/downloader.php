<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Download helper based on curl (with fallback to sequential downloading).
 */

// TODO:
// Test behaviour with non existent URLs and such.

require_once(dirname(__FILE__).'/tools.php');



/**
 * Download helper, trying to download in parallel using `curl`, and fallbacking to `file_get_contents`.
 *
 *  @param	$urls				Array of associative arrays `{'url', 'post'}` for each URL. 'post' is an optionnal JSON array of data to send _via_ POST.
 *  @param	$fetch_header_only	(Optionnal) Whether we want to fetch the full content or only the headers. Defaults to `false`.
 *  @return An array `{'results', 'status_code', `content-types`}`, results being an array of the retrieved contents, indexed by URLs, 'status_codes' being an array of status_code, indexed by URLs and `content-types` being the same for content types.
 */
function downloader($urls, $fetch_header_only=false) {
	if (_has_curl()) {
		return curl_downloader($urls, $fetch_header_only);
	}
	else {
		return sequential_downloader($urls, $fetch_header_only);
	}
}


/**
 * Check whether `curl` PHP module is available or not.
 *
 * @return `true` if the `curl` module is available, `false` otherwise.
 */
function _has_curl(){
	return function_exists('curl_version');
}

/**
 * Downloads all the provided urls, using the `curl` PHP module, and returns the results and the status codes.
 *
 * Mostly inspired by blogotext by timovn : https://github.com/timovn/blogotext/blob/master/inc/fich.php
 *
 *  Note: If open_basedir or safe_mode, Curl will not follow redirections :
 *  https://stackoverflow.com/questions/24687145/curlopt-followlocation-and-curl-multi-and-safe-mode
 *
 *  @param	$urls				Array of associative arrays `{'url', 'post'}` for each URL. 'post' is an optionnal JSON array of data to send _via_ POST.
 *  @param	$fetch_header_only	(Optionnal) Whether we want to fetch the full content or only the headers. Defaults to `false`.
 *  @return An array `{'results', 'status_code', `content-types`}`, results being an array of the retrieved contents, indexed by URLs, 'status_codes' being an array of status_code, indexed by URLs and `content-types` being the same for content types.
 */
function curl_downloader($urls, $fetch_header_only=false) {
	$chunks = array_chunk($urls, 40, true);  // Chunks of 40 urls because curl has problems with too big "multi" requests
	$results = array();
	$status_codes = array();
	$content_types = array();

	$follow_redirect = ini_get('open_basedir') == '' && ini_get('safe_mode') === false;  // Disable followlocation option if one of these is activated, to avoid warnings

	// Download every chunk in parallel
	foreach ($chunks as $chunk) {
		$multihandler = curl_multi_init();
		$handlers = array();
		$total_feed_chunk = count($chunk) + count($results);

		foreach ($chunk as $i=>$url_array) {
			set_time_limit(20); // Reset max execution time
			$url = $url_array['url'];
			$handlers[$i] = curl_init($url);
			curl_setopt_array($handlers[$i], array(
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_TIMEOUT => 15,
				CURLOPT_FOLLOWLOCATION => $follow_redirect,
				CURLOPT_MAXREDIRS => 5,
				CURLOPT_USERAGENT => (!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Freeder RSS reader'),  // Add a user agent to prevent problems with some feeds
				CURLOPT_HEADER => $fetch_header_only,
				CURLOPT_NOBODY => $fetch_header_only
			));
			if (!empty($url_array['post'])) {
				curl_setopt($handlers[$i], CURLOPT_POST, true);
				curl_setopt($handlers[$i], CURLOPT_POSTFIELDS, json_decode($url_array['post'], true));
			}

			curl_multi_add_handle($multihandler, $handlers[$i]);
		}

		do {
			curl_multi_exec($multihandler, $active);
			curl_multi_select($multihandler);
		} while ($active > 0);

		foreach ($chunk as $i=>$url_array) {
			$url = $url_array['url'];
			$results[$url] = curl_multi_getcontent($handlers[$i]);
			$status_codes[$url] = curl_getinfo($handlers[$i], CURLINFO_HTTP_CODE);
			$content_types[$url] = curl_getinfo($handlers[$i], CURLINFO_CONTENT_TYPE);
			curl_multi_remove_handle($multihandler, $handlers[$i]);
			curl_close($handlers[$i]);
		}
		curl_multi_close($multihandler);
	}

	return array('results'=>$results, 'status_codes'=>$status_codes, 'content_types'=>$content_types);
}


/**
 * Downloads all the provided urls, using the `file_get_contents` PHP function, and returns the results and the status codes.
 *
 *  @param	$urls				Array of associative arrays `{'url', 'post'}` for each URL. 'post' is an optionnal JSON array of data to send _via_ POST.
 *  @param	$fetch_header_only	(Optionnal) Whether we want to fetch the full content or only the headers. Defaults to `false`.
 *  @return An array `{'results', 'status_code', `content-types`}`, results being an array of the retrieved contents, indexed by URLs, 'status_codes' being an array of status_code, indexed by URLs and `content-types` being the same for content types.
 */
function sequential_downloader($urls, $fetch_header_only=false) {
	$results = array();
	$status_codes = array();
	$content_types = array();
	foreach ($urls as $url_array) {
		$tmp = file_get_contents($url_array['url']);
		if ($tmp !== false) {
			if (!$fetch_header_only) {
				$results[$url_array['url']] = $tmp;
			}
			else {
				$results[$url_array['url']] = implode("\n", $http_response_header);
			}

			// Parse content-type and status code
			foreach ($http_response_header as $header) {
				if (startswith($header, 'HTTP')) {
					$exploded = explode(' ', $header);
					$status_codes[$url_array['url']] = $exploded[1];
				}
				elseif (startswith($header, 'Content-Type:')) {
					$exploded = explode(' ', $header);
					$content_types[$url_array['url']] = trim($exploded[1], ';');
				}
			}
		}
	}
	return array('results'=>$results, 'status_codes'=>$status_codes, 'content_types'=>$content_types);
}
