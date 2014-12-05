<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

/**
 * Serializing interface.
 * All classes used by API serialization switch must inherit from it.
 */
abstract class AbstractSerializer {
	/**
	 * Serialize object
	 * @param $raw_object: object to serialize (usually associative array)
	 * @return serialized object
	 */
	abstract public static function serialize($raw_object);

	/**
	 * Deserialize object serialized by self::serialize
	 * @param $serialized_object: serialize object
	 * @return raw PHP object
	 */
	abstract public static function deserialize($serialized_object);
}

