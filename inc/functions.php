<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Various functions, not specific and widely used.
 */


/**
 * Search for the first item with value $value for field $field in a 2D array.
 * @return The sub-array or $default_value.
 */
function multiarray_search($field, $value, $array, $default_value=false) {
	foreach($array as $key=>$val) {
		if($val[$field] == $value) {
			return $val;
		}
	}
	return $default_value;
}


/**
 * Search for the key of the first item with value $value for field $field in a 2D array.
 * @return The matching key or -1
 */
function multiarray_search_key($field, $value, $array) {
	foreach($array as $key=>$val) {
		if($val[$field] == $value) {
			return $key;
		}
	}
	return -1;
}


/**
 * Filters a 2D array returning all the entries where $field is not equal to $value.
 * @return The filtered array.
 */
function multiarray_filter($field, $value, $array) {
	$return = array();
	foreach($array as $key=>$val) {
		if($val[$field] != $value) {
			$return[] = $val;
		}
	}
	return $return;
}


/**
 * Check that $haystack starts with $needle.
 */
function startswith($haystack, $needle) {
	 $length = strlen($needle);
	 return (substr($haystack, 0, $length) === $needle);
}


/**
 * Check that $haystack ends with $needle.
 */
function endswith($haystack, $needle) {
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}

	return (substr($haystack, -$length) === $needle);
}


/**
 * Replace only the first occurrence of $needle in $haystack by $replace.
 */
function str_replace_first($needle, $replace, $haystack) {
	$pos = strpos($haystack, $needle);
	if ($pos !== false) {
		$newstring = substr_replace($haystack, $replace, $pos, strlen($needle));
	}
}


/**
 * List all available templates.
 * @return An array {path, name, current} where path is the template path, name is the template name and current is true if this is the current template, false otherwise.
 */
function list_templates() {
	global $config;

	$paths = array_filter(scandir(TPL_DIR), function($item) { return is_dir(TPL_DIR.$item) && !startswith($item, '.'); });
	$names = array_map('ucfirst', $paths);
	$templates = array();
	foreach($paths as $key=>$path) {
		$path .= '/';
		$templates[] = array(
			'path'=>$path,
			'name'=>$names[$key],
			'current'=>$path == $config->template
		);
	}
	return $templates;
}


/**
 * Get the total generation time.
 * @param $start_generation_time is a milliseconds timestamp
 * @return Generation time as a string, with units (seconds or milliseconds)
 */
function get_generation_time($start_generation_time) {
	$round = round(microtime(true) - (float)$start_generation_time, 2).'s';
	if($round == '0s') {
		$round = round((microtime(true) - $start_generation_time)*1000, 3).'ms';
	}
	return $round;
}


/**
 * Returns the global category of a MIME-TYPE
 * @param $mime_type, a MIME-TYPE
 */
function get_category_mime_type($mime_type) {
	$end = strpos($mime_type, '/');
	if ($end === false) {
		return false;
	}
	$category = substr($mime_type, 0, $end);
	$available_categories = array('application', 'audio', 'example', 'image', 'message', 'model', 'multipart', 'text', 'video');
	$end = in_array($category, $available_categories);
	if ($end !== false) {
		return $category;
	}
	else {
		return false;
	}
}


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


/**
 * Clean the rainTPL cache
 * @param (optional) $folder, folder with the rainTPL cache, default to tmp
 */
function clean_cache($folder='tmp/') {
	$folder_handler = opendir(ROOT_DIR.$folder);
	while ($file = readdir($folder_handler)) {
		if ($file == '.' || $file == '..') {
			continue;
		}
		unlink(ROOT_DIR.$folder.'/'.$file);
	}
	closedir($folder_handler);
}


/**
 * Check if the script is run from the command line or not
 * @return true if script is run from command line, false otherwise
 */
function is_command_line() {
	return 'cli' == php_sapi_name();
}


/**
 * Check if url_rewriting is available
 * @return 1 if available, 0 otherwise
 */
function get_url_rewriting() {
	if (function_exists('apache_get_modules')) {
		return (int) in_array('mod_rewrite', apache_get_modules());
	}
	else {
		return getenv('HTTP_MOD_REWRITE')=='On' ? 1 : 0;
	}
}


/**
 * Check if curl is available
 * @return true if available, false otherwise
 */
function is_curl_available() {
  $curl_functions = array( 'curl_multi_init', 'curl_init', 'curl_setopt_array',
			   'curl_setopt', 'curl_multi_add_handle',
			   'curl_multi_exec', 'curl_multi_select',
			   'curl_multi_get_content', 'curl_getinfo',
			   'curl_multi_remove_handle', 'curl_close',
			   'curl_multi_close' );
  $curl_ok = true;
  foreach($curl_functions as $curl_function)
    $curl_ok = $curl_ok && function_exists($curl_function);
  return $curl_ok;
}   


/**
 * Check if pdo_sqlite is available
 * @return true if available, false otherwise
 */
function is_pdo_sqlite_available() {
  return in_array('pdo_sqlite', get_loaded_extensions());
}


/**
 * Format date for pretty printing
 * @param $timestamp: date in timestamp format.
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

