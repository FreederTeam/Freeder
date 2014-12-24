<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

require_once dirname(__FILE__)."/Entry.php";

// TODO: Namespacing

/**
 * Feed represent a RSS feed.
 * This class handles feed retrieval and management.
 */
class Model_Feed extends Redbean_SimpleModel {
	/**
	 *  Handle JSON serialization and verifications before storage
	 */
	public function update() {
		if (false === filter_var($this->url, FILTER_VALIDATE_URL)) {
			throw new Exception('Feed URL is not a valid URL.');
		}

		$this->bean->links = json_encode($this->bean->links);
		$this->bean->image = json_encode($this->bean->image);
		$this->bean->post = json_encode($this->bean->post);
	}

	/**
	 *  Handle JSON deserialization at loading
	 */
	public function open() {
		$this->bean->links = json_decode($this->bean->links, true);
		$this->bean->image = json_decode($this->bean->image, true);
		$this->bean->post = json_decode($this->bean->post, true);
	}

	/**
	 * Populate this bean from an array description (as returned by feed2array)
	 */
	public function populate_from_array($array) {
		$infos = $array['infos'];
		$this->bean->title = isset($infos['title']) ? $infos['title'] : '';
		$this->bean->links = isset($infos['links']) ? $infos['links'] : array();
		$this->bean->description = isset($infos['description']) ? $infos['description'] : '';
		$this->bean->ttl = isset($infos['ttl']) ? $infos['ttl'] : 0;
		$this->bean->image = isset($infos['image']) ? $infos['image'] : array();

		foreach ($array['items'] as $parsed_entry) {
			$entry = R::dispense('entry');
			$entry->populate_from_array($parsed_entry);
			$this->bean->ownEntryList[] = $entry;
		}

		// TODO: Tags
	}
}
