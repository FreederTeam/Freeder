<?php
define('REDBEAN_MODEL_PREFIX', '\\Model\\');

R::setup("sqlite:".dirname(__FILE__)."/../data/db.sqlite3");
R::freeze(!$config->debug);
R::debug($config->debug);

$app = new \Slim\Slim();

require_once('Controllers/FeedsController.php');

$app->run();
R::close();
