<?php
define('DATA_DIR', 'data/');
define('TPL_DIR', 'tpl/');
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
require('inc/config.class.php');
$GLOBALS['dbh'] = new PDO('sqlite:'.DATA_DIR.DB_FILE);
$dbh->query('PRAGMA foreign_keys = ON');

$GLOBALS['config'] = new Config();
date_default_timezone_set($config->get('timezone'));
require('inc/rain.tpl.class.php');
RainTPL::$tpl_dir = TPL_DIR.$config->get('template');
$tpl = new RainTPL;
$tpl->assign('start_generation_time', microtime(true));
require('inc/functions.php');
require('inc/feeds.php');
require('inc/entries.php');

$do = isset($_GET['do']) ? $_GET['do'] : '';

$feeds = get_feeds();

switch($do) {
    case 'settings':
        if(!empty($_POST['feed_url'])) {
            if(add_feed($_POST['feed_url'])) {
                header('location: index.php?do=settings');
                exit();
            }
            else {
                exit('Erreur - TODO');
            }
        }
        $tpl->assign('config', $config);
        $tpl->assign('templates', list_templates());
        $tpl->assign('feeds', $feeds);
        $tpl->draw('settings');
        break;

    case 'update':
        if(DEBUG) {
            require('inc/tests.php');
            refresh_feeds($test_feeds);
        }
        header('location: index.php');
        exit();
        break;

    default:
        $tpl->assign('entries', get_entries());
        $tpl->draw('index');
        break;
}
