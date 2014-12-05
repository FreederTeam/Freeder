<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

require_once('serializers/JSONSerializer.class.php');
require_once('serializers/FormSerializer.class.php');
require_once('SerializationSwitch.class.php');
require_once('Router.class.php');

// Create serialization switch
$switch = new SerializationSwitch;

$json_serializer = new JSONSerializer;
$switch->register('application/json', $json_serializer);

$form_serializer = new FormSerializer;
$switch->register('application/x-www-form-urlencoded', $form_serializer);

// Create router
$router = new Router;



// Decode request
$raw_body = file_get_contents('php://input');
if (!empty($raw_body)) {
	$body_type = $_SERVER['CONTENT_TYPE'];
	$body = $switch->deserialize($raw_body, $body_type);
} else {
	$body = $raw_body;
}

// Get API request path
if (isset($_GET['path'])) $path = $_GET['path']; // path parameter is prioritary
else if (isset($_GET['p'])) $path = $_GET['p'];
else $path = '/'; // Default to root


// Call router
$entrypoint = $router->handle($path);

// Perform requested action
$response = $entrypoint->run($body);


// Encode response according to request's Accept header
$accepted_types = SerializationSwitch::parse_accept_header($_SERVER['HTTP_ACCEPT']);
$response_type = $switch->best_registered_type($accepted_types);

$encoded_response = $switch->serialize($response, $response_type);

// Write response
header("Content-Type: $response_type");
echo($encoded_response);



