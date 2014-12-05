<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

require_once('serializers/JSONSerializer.class.php');
require_once('SerializationSwitch.class.php');

// Create serialization switch
$switch = new SerializationSwitch;
$json_serializer = new JSONSerializer;
$switch->register('application/json', $json_serializer);

// Decode request
$raw_body = file_get_contents('php://input');
$body_type = $_SERVER['CONTENT_TYPE'];
$body = $switch->deserialize($body, $body_type);


var_dump($body);


