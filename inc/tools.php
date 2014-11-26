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
