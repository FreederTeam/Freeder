<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

/**
 * This includes file aims at providing functions to handle crontab and synchronization options.
 */



/**
 * Function to add the crontask in the crontab.
 */
function register_crontask($crontask) {
	$crontab = shell_exec('crontab -l');
	$cron_file = 'tmp/crontab.txt';
	if (!empty($crontab)) {
		$crontab = explode($crontab, PHP_EOL);
	}
	else {
		$crontab = array();
	}

	$already_existed = false;
	foreach ($crontab as $key=>$line) {
		if (strstr($line, $comment) !== FALSE) {
			$already_existed = true;
			$crontab[$key] = $crontask;
		}
	}
	if (!$already_existed) {
		$crontab[] = $crontask;
	}
	$crontab = trim(implode(PHP_EOL, $crontab)).PHP_EOL;

	file_put_contents($cron_file, $crontab);
	shell_exec("crontab $cron_file");
	unlink($cron_file);
}


/**
 * Function to remove the crontask from the crontab.
 */
function unregister_crontask($match) {
	$crontab = shell_exec('crontab -l');
	$cron_file = 'tmp/crontab.txt';

	if (!empty($crontab)) {
		$crontab = explode($crontab, PHP_EOL);
	}
	else {
		$crontab = array();
	}
	foreach ($crontab as $key=>$line) {
		if (strstr($line, $match) !== FALSE) {
			unset($crontab[$key]);
		}
	}
	$crontab = trim(implode(PHP_EOL, $crontab)).PHP_EOL;

	file_put_contents($cron_file, $crontab);
	shell_exec("crontab $cron_file");
	unlink($cron_file);
}
