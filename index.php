<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */



require_once('inc/init.php');
require_once('inc/entries.php');
require_once('inc/feeds.php');

$view = isset($_GET['view']) ? $_GET['view'] : '_home';

$tpl->assign('entries', get_entries($view), RainTPL::RAINTPL_XSS_SANITIZE);
$tpl->assign('feeds', get_feeds(), RainTPL::RAINTPL_XSS_SANITIZE);
$tpl->draw('index');
