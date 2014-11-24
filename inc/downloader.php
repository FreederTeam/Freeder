<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Handler for the download using Curl.
 */


/**
 * Downloads all the urls in the array $urls and returns an array with the results and the http status_codes.
 *
 * Mostly inspired by blogotext by timovn : https://github.com/timovn/blogotext/blob/master/inc/fich.php
 *
 *  Note: If open_basedir or safe_mode, Curl will not follow redirections :
 *  https://stackoverflow.com/questions/24687145/curlopt-followlocation-and-curl-multi-and-safe-mode
 *
 *  @param an array $urls of associative arrays {'url', 'post'} for each URL. 'post' is a JSON array of data to send _via_ POST.
 *  @return an array {'results', 'status_code'}, results being an array of the retrieved contents, indexed by URLs, and 'status_codes' being an array of status_code, indexed by URL.
 *  @todo Fix this function
 */
function curl_downloader($urls, $fetch_content=true, $verbose=true) {
	$chunks = array_chunk($urls, 40, true);  // Chunks of 40 urls because curl has problems with too big "multi" requests
	$results = array();
	$status_codes = array();
	$content_types = array();
	$command_line = is_command_line();

	if (ini_get('open_basedir') == '' && ini_get('safe_mode') === false) { // Disable followlocation option if this is activated, to avoid warnings
		$follow_redirect = true;
	}
	else {
		$follow_redirect = false;
	}

	if ($verbose) {
		if (function_exists('apache_setenv')) {
			/* Selon l'hébergeur la fonction peut être désactivée. Alors Php
			   arrête le programme avec l'erreur :
			   "PHP Fatal error:  Call to undefined function apache_setenv()".
			*/
			@apache_setenv('no-gzip', 1);
		}
		@ini_set('zlib.output_compression', 0);
		@ini_set('implicit_flush', 1);
		for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
		ob_implicit_flush(1);

		if ($command_line) {
			$backspace = PHP_EOL;
			$span_start = '';
			$span_end = '';
		}
		else {
			$backspace = '<br/>';
			$span_start = '<span CLASS>';
			$span_end = '</span>';
		}
	}

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
				CURLOPT_USERAGENT => (!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''),  // Add a user agent to prevent problems with some feeds
				CURLOPT_HEADER => $fetch_content ? FALSE : TRUE,
				CURLOPT_NOBODY => $fetch_content ? FALSE : TRUE,
			));
			if (!empty($url_array['post'])) {
				curl_setopt($handlers[$i], CURLOPT_POST, true);
				curl_setopt($handlers[$i], CURLOPT_POSTFIELDS, json_decode($url_array['post'], true));
			}

			if ($verbose) {
				echo str_replace('CLASS', 'class="refreshed-feed-tmp"', $span_start).'Starting to download '.$url.'…'.$backspace.$span_end;
			}

			curl_multi_add_handle($multihandler, $handlers[$i]);
		}

		do {
			curl_multi_exec($multihandler, $active);
			curl_multi_select($multihandler);
		} while ($active > 0);

		if ($verbose && !$command_line) {
			echo '<script type="text/javascript">var elts = document.getElementsByClassName("refreshed-feed-tmp"); while(elts[0]) { elts[0].parentNode.removeChild(elts[0]); };</script>';
		}
		foreach ($chunk as $i=>$url_array) {
			$url = $url_array['url'];
			$results[$url] = curl_multi_getcontent($handlers[$i]);
			$status_codes[$url] = curl_getinfo($handlers[$i], CURLINFO_HTTP_CODE);
			$content_types[$url] = curl_getinfo($handlers[$i], CURLINFO_CONTENT_TYPE);
			curl_multi_remove_handle($multihandler, $handlers[$i]);
			curl_close($handlers[$i]);

			if ($verbose && !$command_line) {
				echo str_replace('CLASS', '', $span_start).'Starting to download '.$url.'… Done.'.$backspace.$span_end;
			}
		}
		curl_multi_close($multihandler);
	}

	return array('results'=>$results, 'status_codes'=>$status_codes, 'content_types'=>$content_types);
}
