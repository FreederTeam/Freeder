<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Small crontab management library.
 */


/**
 * Get the current crontab.
 * @return The crontab as an array of lines.
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
 * Write the crontab.
 * @param	$crontab	Array of lines in crontab.
 * @param	$cron_file	Tmp cron file path.
 * @return `true` on success, `false` otherwise
 */
function write_crontab($crontab, $cron_file='/tmp/crontab.txt') {
	$crontab = trim(implode(PHP_EOL, $crontab)) . PHP_EOL;

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
 * Add the crontask in the crontab.
 * @param	$crontask	Crontask line to add.
 * @param	$comment	Comment to add to the crontask (without leading `#`).
 * @return `true` on success, `false` otherwise
 */
function register_crontask ($crontask, $comment='FREEDER AUTOMATED CRONTASK') {
	if (preg_match(build_regex_validate_crontask(), $crontask) != 1) {
		return false;
	}
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


/**
 * Build the regex to validate a crontask line.
 * @author Jordi Salvat i Alabart - with thanks to Salir.com.
 */

function build_regex_validate_crontask() {
	$numbers= array(
		'min'=>'[0-5]?\d',
		'hour'=>'[01]?\d|2[0-3]',
		'day'=>'0?[1-9]|[12]\d|3[01]',
		'month'=>'[1-9]|1[012]',
		'dow'=>'[0-7]'
	);

	foreach($numbers as $field=>$number) {
		$range= "($number)(-($number)(\/\d+)?)?";
		$field_re[$field]= "\*(\/\d+)?|$range(,$range)*";
	}

	$field_re['month'].='|jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec';
	$field_re['dow'].='|mon|tue|wed|thu|fri|sat|sun';

	$fields_re= '('.join(')\s+(', $field_re).')';

	$replacements= '@reboot|@yearly|@annually|@monthly|@weekly|@daily|@midnight|@hourly';


	return '#^\s*('.
		'$'.
		'|\#'.
		'|\w+\s*='.
		"|$fields_re\s+\S".
		"|($replacements)\s+\S".
		')#';
}
