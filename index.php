<?php
define('DATA_DIR', 'data/');
define('DEBUG', true);

if(!is_file(DATA_DIR.'config.php')) {
    require('inc/install.php');

    install();
    exit();
}

require(DATA_DIR.'config.php');
if(!is_file(DATA_DIR.DB_FILE)) {
    unlink('inc/config.php');
    header('location: index.php');
}
require('inc/config.php');
$dbh = new PDO('sqlite:'.DATA_DIR.DB_FILE);
$dbh->query('PRAGMA foreign_keys = ON');

$config = load_config();
date_default_timezone_set($config['timezone']);
require('inc/rain.tpl.class.php');
$tpl = new RainTPL;
require('inc/functions.php');
require('inc/feeds.php');

if(DEBUG) {
    require('inc/tests.php');
    $time = microtime(true);
    refresh_feeds($feeds);
    var_dump(microtime(true) - $time);
}

$tpl->draw('index');
