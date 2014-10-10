<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */



require_once('inc/init.php');
require_once('inc/entries.php');
require_once('inc/feeds.php');

$view = isset($_GET['view']) ? $_GET['view'] : '_home';
$page = isset($_GET['p']) ? (int) $_GET['p'] : 0;

$tpl->assign('view', $view);
$tpl->assign('entries', get_entries($view, $page), RainTPL::RAINTPL_XSS_SANITIZE);
$nb_entries = get_entries_count($view, $page);
$tpl->assign('nb_entries', intval($nb_entries));
$tpl->assign('nb_pages', intval($nb_entries / $config->entries_per_page) + 1, RainTPL::RAINTPL_XSS_SANITIZE);

$feeds = get_feeds();
function compare_feeds_title ($f1, $f2) { return strcasecmp ($f1['title'], $f2['title']); }
usort ($feeds, 'compare_feeds_title');
$tpl->assign('feeds', $feeds, RainTPL::RAINTPL_XSS_SANITIZE);

$tpl->draw('index');
