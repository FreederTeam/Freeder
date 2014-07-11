<?php
define('DATA_DIR', 'data/');

if(!is_file(DATA_DIR.'db.sqlite')) {
    require('inc/install.inc.php');

    install();
    exit();
}

$bdd = new PDO('sqlite:data/db.sqlite');
require('inc/rain.tpl.class.php');
$tpl = new RainTPL;
require('inc/functions.inc.php');
require('inc/feeds.inc.php');

$time = microtime(true);
refresh_feeds($feeds);
var_dump(microtime(true) - $time);

$tpl->draw('index');
