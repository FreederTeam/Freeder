<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

/**
 * This include file aims at providing functions used for entries sharing.
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
		$sharing_options[] = array('type'=>'wallabag', 'url'=>$config->wallabag_share.'?action=add&url=');
	}
	if (!empty($config->diaspora_share) && filter_var($config->diaspora_share, FILTER_VALIDATE_URL) !== false) {
		$sharing_options[] = array('type'=>'diaspora', 'url'=>$config->diaspora_share.'bookmarklet?url=');
	}

	return $sharing_options;
}
