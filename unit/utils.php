<?php

/*
 * rand_bool
 */
function rand_bool () {
  if ( rand (0,1) == 0 )
    return true;
  else
    return false;
}

/*
 * rand_int
 */
function rand_int () {
  return rand ();
}

/*
 * rand_string
 * Returns a random string of random length (between 10 and 100 chars).
 */
function rand_string () {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $length = rand (10, 100);
  $randomString = '';
  for ($i = 0; $i < $length; $i++)
    $randomString .= $characters [rand (0, strlen ($characters) - 1)];
  return $randomString;
}

/*
 * rand_array
 * Returns a random array filled with random values.
 */
function rand_array () {
  $array = array ();
  $length = rand (0, 10);
  for ($i = 0; $i < $length; $i++)
    $array[rand_val (array ('int', 'string'))] = rand_val ();
  return $array;
}

/*
 * rand_val
 * Returns a random value. Can be a random int, a random string, …
 * @param $value: an array of authorized values
 */
function rand_val ($values = array ('bool', 'int', 'string', 'array', 'null')) {
  $val_type = $values [ rand(0, count($values)-1) ];
  switch ($val_type) {
    case 'bool': return rand_bool ();
    case 'int': return rand_int ();
    case 'string': return rand_string ();
    case 'array': return rand_array ();
    default: return null;
  }
}
$_int_string = array('int', 'string');
