<?php
R::setup("sqlite:/tmp/db.sqlite3");
R::freeze(!$config->debug);
R::debug($config->debug);

$app = new \Slim\Slim();

require_once('Controllers/Feed.php');

$app->run();
R::close();
