<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

namespace Model;

require_once dirname(__FILE__)."/../core/tools.php";

/**
 * Entries represent articles from RSS feeds.
 * This class handles entries retrieval and management.
 */
class Entry extends \RedbeanPHP\SimpleModel {
	/**
	 *  Handle JSON serialization and verifications before storage
	 */
	public function update() {
		$this->bean->authors = json_encode($this->bean->authors);
		$this->bean->links = json_encode($this->bean->links);
		$this->bean->enclosures = json_encode($this->bean->enclosures);
	}


	/**
	 *  Handle JSON deserialization at loading
	 */
	public function open() {
		$this->bean->authors = json_decode($this->bean->authors, true);
		$this->bean->links = json_decode($this->bean->links, true);
		$this->bean->enclosures = json_decode($this->bean->enclosures, true);
	}


	/**
	 * Populate this bean from an array description (as returned by feed2array)
	 * @param	$array			The parsed entry array.
	 * @param	$import_tags	Whether tags should be imported or not.
	 */
	public function populate_from_array($array, $import_tags=false) {
		$this->bean->authors = isset($array['authors']) ? $array['authors'] : array();
		$this->bean->title = isset($array['title']) ? $array['title'] : '';
		$this->bean->links = isset($array['links']) ? $array['links'] : array();
		$this->bean->description = isset($array['description']) ? $array['description'] : '';
		$this->bean->content = isset($array['content']) ? $array['content'] : '';
		$this->bean->enclosures = isset($array['enclosures']) ? $array['enclosures'] : array();
		if (isset($array['comments'])) {
			$this->bean->comments = $array['comments'];
		}
		elseif (isset($array['links'])) {
			$tmp = multiarray_search(array('rel'=>'replies'), $array['links']);
			if ($tmp !== false) {
				$comments = isset($tmp['href']) ? $tmp['href'] : '';
			}
			else {
				$comments = '';
			}
		}
		else {
			$this->bean->comments = '';
		}
		$this->bean->guid = isset($array['guid']) ? $array['guid'] : '';
		$this->bean->pub_date = isset($array['pubDate']) ? $array['pubDate'] : new \DateTime("now");
		$this->bean->last_update = isset($array['updated']) ? $array['updated'] : new \DateTime("@0");

		if ($import_tags && !empty($array['categories'])) {
			\R::tag($this->bean, $array['categories']);
		}
	}


	/**
	 * Get a cleaned up `authors` attribute for entry.
	 * @return A new array of authors.
	 */
	public function get_clean_authors() {
		if ($authors == NULL) return array();

		$new_authors = array();
		foreach($this->authors as $author) {
			if (empty($author['name']) && !empty($author['email'])) {
				$explode = explode(' ', $author->email);
				if (count($explode) == 2 && filter_var(trim($explode[0], ' ()<>'), FILTER_VALIDATE_EMAIL)) {
					$name = trim($explode[1], ' ()');
					$new_authors[] = array('name'=>$name, 'email'=>$author->email);
				}
				elseif (count($explode) == 2 && filter_var(trim($explode[1], ' ()<>'), FILTER_VALIDATE_EMAIL)) {
					$name = trim($explode[0], ' ()');
					$new_authors[] = array('name'=>$name, 'email'=>$author->email);
				}
				else {
					$new_authors[] = array('name'=>$author->email, 'email'=>$author->email);
				}
			}
			else {
				$new_authors[] = $author;
			}
		}
		return $new_authors;
	}


	/**
	 * Get the link to the article associated with the entry.
	 * @return The link or `"#"` if none found.
	 */
	public function get_link() {
		foreach ($this->links as $link) {
			if ($link['rel'] == 'alternate') {
				return $link['href'];
			}
		}
		return '#';
	}


	/**
	 * Check wether an entry has the tags `$tags` (provided as a list) or not.
	 * @param	$tags	A list of tags.
	 * @param	$all	Whether all tags should be there or not.
	 * @return `true` if the entry has the tag `$tag`, `false` otherwise.
	 */
	public function has_tag($tags, $all=false) {
		return \R::hasTag($this->bean, $tags, $all);
	}
}


