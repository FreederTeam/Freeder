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
    unlink(DATA_DIR.'/config.php');
    header('location: index.php');
}
require('inc/config.class.php');
$GLOBALS['dbh'] = new PDO('sqlite:'.DATA_DIR.DB_FILE);
$dbh->query('PRAGMA foreign_keys = ON');

$GLOBALS['config'] = new Config();
date_default_timezone_set($config->timezone);
require('inc/rain.tpl.class.php');
RainTPL::$tpl_dir = TPL_DIR.$config->template;
$tpl = new RainTPL;
$tpl->assign('start_generation_time', microtime(true));
require('inc/functions.php');
require('inc/feeds.php');
require('inc/entries.php');

$do = isset($_GET['do']) ? $_GET['do'] : '';

$feeds = get_feeds();

switch($do) {
    case 'settings':
        if (!empty($_POST['synchronization_type']) && !empty($_POST['template']) && !empty($_POST['timezone']) && isset($_POST['use_tags_from_feeds'])) {
            $config->synchronization_type = $_POST['synchronization_type'];
            if (is_dir(TPL_DIR.$_POST['template'])) {
                $config->template = $_POST['template'];
                if(!endswith($config->template, '/')) {
                    $config->template = $config->template.'/';
                }
            }
            if (in_array($_POST['timezone'], timezone_identifiers_list())) {
                $config->timezone = $_POST['timezone'];
            }
            $config->use_tags_from_feeds = (int) $_POST['use_tags_from_feeds'];
            $config->save();
            header('location: index.php?do=settings');
            exit();
        }
        if (!empty($_POST['feed_url'])) {
            if(add_feed($_POST['feed_url'])) {
                header('location: index.php?do=settings');
                exit();
            }
            else {
                exit('Erreur - TODO');
            }
        }
        if (!empty($_GET['delete_feed'])) {
            delete_feed(intval($_GET['delete_feed']));
            header('location: index.php?do=settings');
            exit();
        }
        $tpl->assign('config', $config);
        $tpl->assign('templates', list_templates());
        $tpl->assign('feeds', $feeds);
        $tpl->draw('settings');
        break;

    case 'update':
        $feeds_to_refresh = array();
        foreach($feeds as $feed) {
            $feeds_to_refresh[$feed['id']] = $feed['url'];
        }
        refresh_feeds($feeds_to_refresh);
        header('location: index.php');
        exit();
        break;

    default:
        $tpl->assign('entries', get_entries());
        $tpl->draw('index');
        break;
}
