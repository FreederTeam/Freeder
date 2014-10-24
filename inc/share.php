<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions related to entry sharing.
 */


/**
 * Lists all the available sharing options
 */
function get_sharing_options() {
	global $config;

	$sharing_options = array();
	if ($config->facebook_share > 0) {
		$sharing_options[] = array('type'=>'facebook', 'url'=>'https://www.facebook.com/sharer.php?u=');
	}
	if ($config->twitter_share > 0) {
		$sharing_options[] = array('type'=>'twitter', 'url'=>'https://twitter.com/share?url=');
	}
	if (!empty($config->shaarli_share) && filter_var($config->shaarli_share, FILTER_VALIDATE_URL) !== false) {
		$sharing_options[] = array('type'=>'shaarli', 'url'=>$config->shaarli_share.'?post=');
	}
	if (!empty($config->wallabag_share) && filter_var($config->wallabag_share, FILTER_VALIDATE_URL) !== false) {
		$sharing_options[] = array('type'=>'wallabag', 'url'=>$config->wallabag_share.'?plainurl=');
	}
	if (!empty($config->diaspora_share) && filter_var($config->diaspora_share, FILTER_VALIDATE_URL) !== false) {
		$sharing_options[] = array('type'=>'diaspora', 'url'=>$config->diaspora_share.'bookmarklet?url=');
	}

	return $sharing_options;
}
