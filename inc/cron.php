<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Small crontab management library
 */


/**
 * Write the crontab
 * @param $crontab, array of lines in crontab
 * @return true on success, false otherwise
 */
function write_crontab($crontab) {
	$crontab = trim(implode(PHP_EOL, $crontab)) . PHP_EOL;
	$cron_file = 'tmp/crontab.txt';

	if (file_put_contents($cron_file, $crontab) === false) {
		return false;
	}
	if (shell_exec("crontab $cron_file 2>&1 > /dev/null; echo $?") != 0) {
		unlink($cron_file);
		return false;
	}
	unlink($cron_file);

	return true;
}

/**
 * Get the current crontab
 * @return Array of lines in crontab
 */
function get_crontab() {
	$crontab = shell_exec('crontab -l');

	if (!empty ($crontab)) {
		$crontab = explode(PHP_EOL, $crontab);
	}
	else {
		$crontab = array ();
	}

	return $crontab;
}


/**
 * Add the crontask in the crontab.
 */
function register_crontask ($crontask, $comment='FREEDER AUTOMATED CRONTASK') {
	$crontab = get_crontab();
	$already_existed = false;

	foreach ($crontab as $key=>$line) {
		if (preg_match('#\#\s*'.$comment.'\s*$#', $line) === 1) {
			$already_existed = true;
			$crontab[$key] = $crontask . ' # ' . $comment;
		}
	}
	if (!$already_existed) {
		$crontab[] = $crontask . ' # ' . $comment;
	}

	return write_crontab($crontab);
}



/**
 * Remove the crontask from the crontab.
 */
function unregister_crontask($match) {
	$crontab = get_crontab();

	foreach ($crontab as $key=>$line) {
		if (strstr($line, $match) !== false) {
			unset($crontab[$key]);
		}
	}

	return write_crontab($crontab);
}
