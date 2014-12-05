<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

require_once('AbstractSerializer.class.php');

/**
 * x-www-form-data serializer used by API serialization switch
 */
class FormSerializer extends AbstractSerializer {
	/**
	 * @override
	 */
	public static function serialize($raw_object) {
		return urlencode($raw_object);
	}

	/**
	 * @override
	 */
	public static function deserialize($json_object) {
		return urldecode($json_object);
	}
}

