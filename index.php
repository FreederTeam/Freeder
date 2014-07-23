<?php
/*	Copyright (c) 2014 Freeder
 *	Released under a MIT License.
 *	See the file LICENSE at the root of this repo for copying permission.
 */

require_once('inc/init.php');
require_once('inc/entries.php');

// Test
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//$q = $dbh->query("INSERT INTO tags_entries(tag_id, entry_id) VALUES ((SELECT id FROM tags WHERE name = '_read'), '1')");
$q = $dbh->query("SELECT tag_id FROM tags_entries WHERE tag_id = (SELECT id FROM tags WHERE name = '_read') AND entry_id = 1");
$q->execute();
print_r($q->fetchAll(PDO::FETCH_ASSOC));

require_once('inc/views.php');
//$r = rule2sql('+$all -_read -_ho_home BY -_sticky -$pubDate');
$r = rule2sql('+$all -_read');
print_r($r);

$q = $dbh->query($r[0]);
$q->execute($r[1]);
$res = $q->fetchAll(PDO::FETCH_ASSOC);
var_dump($res);

$tpl->assign('entries', get_entries());
$tpl->draw('index');
