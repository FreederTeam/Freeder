<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

require_once('inc/init.php');
require_once('inc/entries.php');

// Test
require_once('inc/views.php');
$r = rule2sql('+$all\ of\ us -_read\ me -_ho_home BY -_sticky -$pubDate');
print_r($r);

$tpl->assign('entries', get_entries());
$tpl->draw('index');
