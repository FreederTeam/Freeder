<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to prevent XSS.
 */


/**
 * `htmlspecialchars` helper.
 *
 * @param	$data	array or string that will be `htmlspecialchars`ed.
 * @return The array or string after `htmlspecialchars` application.
 */
function htmlspecialchars_utf8($data) {
	if (is_array($data)) {
		return array_map('htmlspecialchars_utf8', $data);
	}
	return htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
}
