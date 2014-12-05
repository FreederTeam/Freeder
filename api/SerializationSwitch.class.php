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
		$this->rules[$type] = $serializer;
	}


	/**
	 * Serialize data according to a given type.
	 * @param $content: Data to serialize
	 * @param $type: Type used to select serialization method
	 * @return serialized object
	 */
	public function serialize($content, $type) {
		foreach ($this->rules as $rule_type => $serializer) {
			if ($rule_type == $type) {
				return $serializer->serialize($content);
			}
		}

		throw new Exception("No available serializer for MIME type $type");
	}


	/**
	 * Deserialize data according to a given type.
	 * @param $content: Data to deserialize
	 * @param $type: Type used to select deserialization method
	 * @return raw PHP object
	 */
	public function deserialize($content, $type) {
		foreach ($this->rules as $rule_type => $serializer) {
			if ($rule_type == $type) {
				return $serializer->deserialize($content);
			}
		}

		throw new Exception("No available serializer for MIME type $type");
	}


	/**
	 * Determine what registered type fit Accept header the best.
	 * @param $accepted_types: Array of accepted types associated to priority.
	 * @return MIME type
	 */
	public function best_registered_type($accepted_types) {
		arsort($accepted_types);

		foreach ($accepted_types as $type => $q) {
			foreach ($this->rules as $rule_type => $serializer) {
				if ($q && SerializationSwitch::match_type($type, $rule_type)) return $rule_type;
			}
		}
		// no mime-type found
		return null;
	}


	/**
	 * Parse Accept header
	 * @param $accept_header: raw request's Accept header
	 * @return Accepted header list
	 * Inspired by http://stackoverflow.com/questions/1049401/how-to-select-content-type-from-http-accept-header-in-php
	 */
	public static function parse_accept_header($accept_header) {
		// Accept header is case insensitive, and whitespace isn’t important
		$accept = strtolower(str_replace(' ', '', $accept_header));
		// divide it into parts in the place of a ","
		$accept = explode(',', $accept);

		$accepted_types = Array();
		foreach ($accept as $a) {
			$q = 1; // default quality is 1
			if (strpos($a, ';q=')) { // check if there is a different quality
				list($a, $q) = explode(';q=', $a);
			}
			$accepted_types[$a] = $q;
		}

		return $accepted_types;
	}


	/**
	 * Check whether MIME types are compatible.
	 * For example, application/xhtml+xml is compatible with application/xhtml and application/*
	 * @param $type1: first MIME type
	 * @param $type2: second MIME type
	 * @return bool
	 * Warning: This function is incomplete but cover most common cases.
	 * @todo handle '+'
	 */
	public static function match_type($type1, $type2) {
		$type1 = explode('/', strtolower($type1));
		$type2 = explode('/', strtolower($type2));

		if ($type1[0] != $type2[0] && $type1[0] != '*' && $type2[0] != '*') return false;
		if ($type1[1] != $type2[1] && $type1[1] != '*' && $type2[1] != '*') return false;

		return true;
	}
}


