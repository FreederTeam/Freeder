<?php

/*
 * Copyright (c) 2014 Freeder
 * Released under a MIT License.
 * See the file LICENSE at the root of this repo for copying permission.
 */

/**
 * Unit test
 * inc/functions.php - startswith
 * inc/functions.php - endswith
 */

// Defining INC_DIR const.
define('INC_DIR', '../inc/');

// Including inc/functions.php, where multiarray_search is located.
require_once(INC_DIR . 'functions.php');

echo 'startswith';
echo "\n";

$haystack = 'moié/n@fmdàsçc';
$needles = array ( 'moié',
		   '',
		   $haystack . '+',
		   'nope' );

foreach ($needles as $needle) {
  echo '"' . $haystack . '" starts with "' . $needle . '": ';
  var_dump (startswith ($haystack, $needle));
}

echo "\n";
echo 'endswith';
echo "\n";

$needles = array ( 'sçc',
		   '',
		   '+' . $haystack,
		   'nope' );

foreach ($needles as $needle) {
  echo '"' . $haystack . '" ends with "' . $needle . '": ';
  var_dump (endswith ($haystack, $needle));
}

