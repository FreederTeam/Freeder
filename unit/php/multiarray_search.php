<?php

/*
 * Copyright (c) 2014 Freeder
 * Released under a MIT License.
 * See the file LICENSE at the root of this repo for copying permission.
 */

/**
 * Unit test
 * inc/functions.php - multiarray_search
 * inc/functions.php - multiarray_search_key
 * inc/functions.php - multiarray_filter
 */

// Defining INC_DIR const.
define('INC_DIR', '../inc/');

// Including testing utils.
require_once('utils.php');

// Including inc/functions.php, where multiarray_search is located.
require_once(INC_DIR . 'functions.php');

/*
 * Initialize random numbers generator with a seed, so that the test is fully
 * deterministic.
 */
srand (78655809875);

// Create the array-in-array keys
$len_j = rand (10, 50);
$array_keys = array ();
for ($j = 0; $j < $len_j; $j++) {
  $array_keys [] = rand_val ($_int_string);
}

// Create a multiarray
$multiarray = array ();
$len_i = rand (10, 50);
for ($i = 0; $i < $len_i; $i++) {
  $array = array ();
  foreach ($array_keys as $key)
    // We don't want bools, matching with too many things
    $array[$key] = rand_val (array ('int', 'string', 'array'));
  $multiarray[rand_val($_int_string)] = $array;
}

$rnd_i = array_rand ($multiarray);
$rnd_j = $array_keys[array_rand ($array_keys)];

echo 'multiarray_search';
echo "\n";

// -1 can't be found since we don't have negative numbers nor booleans
$e = multiarray_search ($rnd_j, -1, $multiarray);
echo 'If not found, return false:          ';
var_dump ($e === false);

$v = rand_val ();
$e = multiarray_search ($rnd_j, -1, $multiarray, $v);
echo 'If not found, return default_value:  ';
var_dump ($e === $v);

$e = multiarray_search ($rnd_j, $multiarray[$rnd_i][$rnd_j], $multiarray);
echo 'If found, return sub_array:          ';
var_dump ($e === $multiarray[$rnd_i]);

echo "\n";
echo 'multiarray_search_key';
echo "\n";

$e = multiarray_search_key ($rnd_j, -1, $multiarray);
echo 'If not found, return -1:             ';
var_dump ($e === -1);

$e = multiarray_search_key ($rnd_j, $multiarray[$rnd_i][$rnd_j], $multiarray);
echo 'If found, return sub_array key:      ';
var_dump ($e === $rnd_i);

echo "\n";
echo 'multiarray_filter';
echo "\n";

$e = multiarray_filter ($rnd_j, -1, $multiarray);
echo 'If never found, return multiarray:   ';
var_dump ($e === $multiarray);

$e = multiarray_filter ($rnd_j, $multiarray[$rnd_i][$rnd_j], $multiarray);
echo 'If found, doesn\'t return multiarray: ';
var_dump ($e != $multiarray);
echo 'But can be completed:                ';
$e[$rnd_i] = $multiarray[$rnd_i];
var_dump ($e == $multiarray);
