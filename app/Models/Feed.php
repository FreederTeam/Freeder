<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

namespace Model;
require_once dirname(__FILE__)."/Entry.php";

/*
 * TODO:
 * * favicons
 */


/**
 * BaseFeed represents a base feed, common to multiple users.
 */
class BaseFeed extends \RedbeanPHP\SimpleModel {
	/**
	 *  Handle JSON serialization and verifications before storage
	 */
	public function update() {
		if (false === filter_var($this->url, FILTER_VALIDATE_URL)) {
			throw new Exception('Feed URL is not a valid URL.');
		}

		$this->bean->links = json_encode($this->bean->links);
		$this->bean->image = json_encode($this->bean->image);
	}

	/**
	 *  Handle JSON deserialization at loading
	 */
	public function open() {
		$this->bean->links = json_decode($this->bean->links, true);
		$this->bean->image = json_decode($this->bean->image, true);
	}

	/**
	 * Populate this bean from an array description (as returned by feed2array)
	 * @param	$array			The parsed feed array.
	 * @param	$import_tags	Whether tags should be imported or not.
	 */
	public function populate_from_array($array, $import_tags=false) {
		$infos = $array['infos'];
		$this->bean->last_refresh = time();
		$this->bean->title = isset($infos['title']) ? $infos['title'] : '';
		$this->bean->links = isset($infos['links']) ? $infos['links'] : array();
		$this->bean->description = isset($infos['description']) ? $infos['description'] : '';
		$this->bean->ttl = isset($infos['ttl']) ? $infos['ttl'] : 0;
		$this->bean->image = isset($infos['image']) ? $infos['image'] : array();
		$this->xownEntryList = array();

		foreach ($array['items'] as $parsed_entry) {
			$entry = \R::dispense('entry');
			$entry->populate_from_array($parsed_entry, $import_tags=false);
			$this->bean->xownEntryList[] = $entry;
		}

		if ($import_tags && !empty($infos['categories'])) {
			\R::tag($this->bean, $infos['categories']);
		}
	}
}


/**
 * UserFeed represents a user feed, linked to a user and a specific BaseFeed.
 */
class UserFeed extends \RedbeanPHP\SimpleModel {
	/**
	 *  Handle JSON serialization and verifications before storage
	 */
	public function update() {
		$this->bean->post = json_encode($this->bean->post);
	}

	/**
	 *  Handle JSON deserialization at loading
	 */
	public function open() {
		$this->bean->post = json_decode($this->bean->post, true);
	}

	/**
	 * Check wether a feed has the tags `$tags` (provided as a list) or not.
	 * @param	$tags	A list of tags=array(.
	 * @param	$all	Whether all tags should be there or not.
	 * @return `true` if the entry has the tag `$tag`, `false` otherwise.
	 */
	public function has_tag($tags, $all=false) {
		return \R::hasTag($this->bean, $tags, $all);
	}
}
