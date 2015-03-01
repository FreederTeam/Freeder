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

$app->get("/.*?", function () use ($config) {
	$app = \Slim\Slim::getInstance();
	try {
		$filename = $app->request->getResourceUri();
		if ($filename == "/") {
			$filename = "index.html";
		}
		$content = file_get_contents(dirname(__FILE__)."/../".$config->tpl_path.$filename);
		echo $content;
	}
	catch (Exception $e) {
		$app->notFound();
	}
});

$app->run();

R::close();
