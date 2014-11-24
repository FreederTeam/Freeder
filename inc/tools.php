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
