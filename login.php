<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

require_once('inc/init.php');

if(isset($_SESSION['user'])) {
	header('location: index.php');
	exit();
}
else {
	$tpl->draw('login');
}

