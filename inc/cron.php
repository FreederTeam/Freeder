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
function register_crontask ($crontask) {
	if (preg_match(build_regex_validate_crontask(), $crontask) != 1) {
		return false;
	}
	$crontab = get_crontab();
	$already_existed = false;

	foreach ($crontab as $key=>$line) {
		if ($line == $crontask) {
			$already_existed = true;
			$crontab[$key] = $crontask;
		}
	}
	if (!$already_existed) {
		$crontab[] = $crontask;
	}

	return write_crontab($crontab);
}


/**
 * Remove the crontask from the crontab.
 */
function unregister_crontask($crontask) {
	$crontab = get_crontab();

	foreach ($crontab as $key=>$line) {
		if ($line == $crontask) {
			unset($crontab[$key]);
		}
	}

	return write_crontab($crontab);
}


/**
 * Regex to validate crontask line
 * @author Jordi Salvat i Alabart - with thanks to <a href="www.salir.com">Salir.com</a>.
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
