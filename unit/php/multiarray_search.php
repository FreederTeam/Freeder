<?php

/*
 * Copyright (c) 2014 Freeder
 * Released under a MIT License.
 * See the file LICENSE at the root of this repo for copying permission.
 */

/**
 * Unit test
 * inc/functions.php - multiarray_search
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

// Create a multiarray
$multiarray = array ();
$length = rand (10, 100);
for ($j = 0; $j < $length; $j++)
  $multiarray[rand_val($_int_string)] = rand_array ();

var_dump (multiarray_search (-1, rand_val (), $multiarray));
var_dump (multiarray_search (-1, rand_val (), $multiarray, rand_val ()));

var_dump (multiarray_search (array (), rand_val (), $multiarray));

var_dump (multiarray_search (rand_val($_int_string), rand_val (), array ()));

$mkey = array_rand ($multiarray);
$key = array_rand ($multiarray[$mkey]);
var_dump (multiarray_search ($key, rand_val (), $multiarray));
var_dump (multiarray_search ($key, $multiarray[$mkey][$key], $multiarray));
