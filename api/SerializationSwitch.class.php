<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

/**
 * Select which serialization module to use according to registered rules.
 */
class SerializationSwitch {
	/**
	 * Selection rules: mime type => serialization object inheriting from AbstractSerializer
	 * @var array
	 */
	protected $rules = array();


	/**
	 * Register serializer selection rule.
	 * @param $type: MIME type to look for
	 * @param $serializer: Serializer (instance of AbstractSerializer) used when Content-Type is $type
	 */
	public function register($type, $serializer) {
		$this->$rules[$type] = $serializer;
	}


	/**
	 * Serialize data according to a given type
	 * @param $content: Data to serialize
	 * @param $type: Type used to select serialization method
	 * @return serialized object
	 */
	public function serialize($content, $type) {
		foreach ($this->$rules as $rule_type => $serializer) {
			if ($rule_type == $type) {
				return $serializer->serialize($content);
			}
		}

		throw new Exception("No available serializer for MIME type $type");
	}


	/**
	 * Deserialize data according to a given type
	 * @param $content: Data to deserialize
	 * @param $type: Type used to select deserialization method
	 * @return raw PHP object
	 */
	public function deserialize($content, $type) {
		foreach ($this->$rules as $rule_type => $serializer) {
			if ($rule_type == $type) {
				return $serializer->deserialize($content);
			}
		}

		throw new Exception("No available serializer for MIME type $type");
	}
}


