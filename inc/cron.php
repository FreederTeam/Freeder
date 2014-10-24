<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Provides functions to handle crontab and synchronization options.
 */


/**
 * Function to add the crontask in the crontab.
 */
function register_crontask($crontask, $comment="FREEDER AUTOMATED CRONTASK") {
	// TODO : Unit test
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
		if (preg_match('#\s'.$comment.'\s$', $line) === 1) {
			$already_existed = true;
			$crontab[$key] = $crontask;
		}
	}
	if (!$already_existed) {
		$crontab[] = $crontask." # ".$comment;
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
	// TODO : Unit test
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
