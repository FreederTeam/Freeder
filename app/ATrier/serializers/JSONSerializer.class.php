<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

require_once('AbstractSerializer.class.php');

/**
 * JSON serializer used by API serialization switch
 */
class JSONSerializer extends AbstractSerializer {
	/**
	 * @override
	 */
	public static function serialize($raw_object) {
		return json_encode($raw_object);
	}

	/**
	 * @override
	 */
	public static function deserialize($json_object) {
		return json_decode($json_object);
	}
}

