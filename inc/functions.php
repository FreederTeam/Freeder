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
 * List all available templates.
 * @return An array {path, name, current} where path is the template path, name is the template name and current is true if this is the current template, false otherwise.
 */
function list_templates() {
	$paths = array_filter(scandir(TPL_DIR), function($item) { return is_dir(TPL_DIR.$item) && !startswith($item, '.'); });
	$names = array_map('ucfirst', $paths);
	$templates = array();
	foreach($paths as $key=>$path) {
		$path .= '/';
		$templates[] = array(
			'path'=>$path,
			'name'=>$names[$key],
			'current'=>$path == $GLOBALS['config']->template
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
	$round = round(microtime(true) - (int)$start_generation_time, 2).'s';
	if($round == '0s') {
		$round = round((microtime(true) - $start_generation_time)*1000, 3).'ms';
	}
	return $round;
}
