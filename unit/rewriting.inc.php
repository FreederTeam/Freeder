<?php

function rewrite_url($url, $tag) {
	$base_url = 'base_url/';
	$tpl_dir = 'tpl_dir/';

	$protocol = 'http|https|ftp|file|apt|magnet';
	if ($tag == 'a') {
		$protocol .= '|mailto|javascript';
	}

	$no_change = "/(^($protocol)\:)|(#$)/i";
	if (preg_match($no_change, $url)) {
		return rtrim($url, '#');
	}

	$base_only = '/^\//';
	if ($tag == 'a' or $tag == 'form') {
		$base_only = '//';
	}
	if (preg_match($base_only, $url)) {
		return rtrim($base_url, '/') . '/' . ltrim($url, '/');
	}

	return $base_url . $tpl_dir . $url;
}


function path_replace($html) {
	$exp = array();
	$exp[] = '/<(link|a)(.*?)(href)="(.*?)"/i';
	$exp[] = '/<(img|script|input)(.*?)(src)="(.*?)"/i';
	$exp[] = '/<(form)(.*?)(action)="(.*?)"/i';

	return preg_replace_callback(
		$exp,
		function ($matches) {
			$tag  = $matches[1];
			$_    = $matches[2];
			$attr = $matches[3];
			$url  = $matches[4];
			$new_url = rewrite_url($url, $tag);

			return "<$tag$_$attr=\"$new_url\"";
		},
		$html
	);
}


