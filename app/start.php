<?php
define('REDBEAN_MODEL_PREFIX', '\\Model\\');

R::setup("sqlite:".dirname(__FILE__)."/../data/db.sqlite3");
R::freeze(!$config->debug);
R::debug($config->debug);

$app = new \Slim\Slim();
$app->config(array(
	"templates.path" => "../tpl/zen"
));

require_once('Controllers/AuthController.php');
require_once('Controllers/FeedsController.php');

$app->get('/', function () use ($app) {
	// href\s*=\s*['"]\{%\s*assets_url\s*%\}
	$app->render('index.html');
});

$app->run();
R::close();
