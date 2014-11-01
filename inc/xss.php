<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to counter XSS problems.
 */


/**
 * This include file aims at providing xss sanitizing functions.
 * It is based on the script available [here](https://gist.github.com/mbijon/1098477) and slightly modified.
 *
 *
 * Original header:
 * ================
 * XSS filter
 *
 * This was built from numerous sources.
 * (thanks all, sorry I didn't track to credit you)
 *
 * It was tested against *most* exploits here: http://ha.ckers.org/xss.html
 * WARNING: Some weren't tested!!!
 * Those include the Actionscript and SSI samples, or any newer than Jan 2011
*/


/**
 * Sanitize content to prevent XSS
 * @param $data, data to sanitize
 */
function xss_clean($data) {
	if (is_object($data)) {
		if (get_class($data) === 'stdClass') {
			$output = (object)xss_clean((array) $data);
		}
		else {
			$output = $data->xss_clean();
		}
		$data = $output;
	}
	elseif (is_array($data)) {
		$output = array();
		foreach($data as $key=>$datum) {
			$output[$key] = xss_clean($datum);
		}
		$data = $output;
	}
	else {
		// Fix &entity\n;
		$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

		// Remove any attribute starting with "on" or xmlns + autoplay and seamless
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on[^\s]=|xmlns|autoplay|seamless)[^>]*+>#iu', '$1>', $data);

		// Remove javascript: and vbscript: protocols
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

		// Remove namespaced elements (we do not need them)
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

		do
		{
			// Remove really unwanted tags
			$old_data = $data;
			$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link|ody)|doctype|frame(?:set)?|font|form|html|ilayer|input|l(?:ayer|ink)|marquee|meta|noscript|object|param|plaintext|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
		}
		while ($old_data !== $data);

		// we are done...
	}
	return $data;
}


/**
 * Sanitize data for displaying in HTML
 * @param $data, data to sanitize
 */
function sanitize($data) {
	if (is_object($data)) {
		if (get_class($data) === 'stdClass') {
			$output = (object)sanitize((array) $data);
		}
		else {
			$output = $data->sanitize();
		}
	}
	elseif (is_array($data)) {
		$output = array();
		foreach($data as $key=>$datum) {
			$output[$key] = sanitize($datum);
		}
	}
	else {
		$output = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
	}
	return $output;
}


/**
 * Remove any tag in $data
 * @param $data, data to sanitize
 */
function full_xss_clean($data) {
	if (is_object($data)) {
		if (get_class($data) === 'stdClass') {
			$output = (object)sanitize((array) $data);
		}
		else {
			$output = $data->sanitize();
		}
	}
	elseif (is_array($data)) {
		$output = array();
		foreach($data as $key=>$datum) {
			$output[$key] = sanitize($datum);
		}
	}
	else {
		$output = strip_tags($data);
	}
	return $output;
}
