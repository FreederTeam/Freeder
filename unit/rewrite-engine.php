<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

/**
 * This is a unit test file to check .htaccess generation.
 */

define('INC_DIR', '../inc/');

require_once(INC_DIR.'rewriting.class.php');
$rew = new RewriteEngine;
echo($rew->generate_htaccess());

