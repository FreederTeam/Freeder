<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Provides the functions used to handle URL rewriting.
 */


/**
 * Check if url_rewriting is available
 * @return 1 if available, 0 otherwise
 */
function get_url_rewriting() {
	if (function_exists('apache_get_modules')) {
		return (int) in_array('mod_rewrite', apache_get_modules());
	}
	else {
		return getenv('HTTP_MOD_REWRITE') == 'On' ? 1 : 0;
	}
}
