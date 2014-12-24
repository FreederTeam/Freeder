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
$serializer = new SerializationSwitch;
$serializer->register('application/json', new JSONSerializer);
$serializer->register('application/x-www-form-urlencoded', new FormSerializer);

// Create router
$router = new Router;



// Decode request
$body = file_get_contents('php://input');
if (!empty($raw_body)) {
	$body_type = $_SERVER['CONTENT_TYPE'];
	$body = $serializer->deserialize($body, $body_type);
}

// Get API request path (`path` or `p` variable from querystring)
if (isset($_GET['path'])) $path = $_GET['path']; // path parameter is prioritary
else if (isset($_GET['p'])) $path = $_GET['p'];
else $path = '/'; // Default to root


// Call router
$entrypoint = $router->handle($path, $body);

// Perform API action
$response = $entrypoint($body);


// Encode response according to request's Accept header
$accepted_types = SerializationSwitch::parse_accept_header($_SERVER['HTTP_ACCEPT']);
$response_type = $serializer->best_registered_type($accepted_types);

$encoded_response = $serializer->serialize($response, $response_type);

// Write response
header("Content-Type: $response_type");
echo($encoded_response);


