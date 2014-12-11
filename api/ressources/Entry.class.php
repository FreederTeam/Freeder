<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */


/**
 * Entries represent articles from RSS feeds.
 * This class stores entries and handles routing of entry-related API requests.
 */
class Entry {
	/**
	 * Entry unique id
	 * @var int
	 */
	protected $id;

	/**
	 * Feed from which the entry come from
	 * @var feed
	 */
	protected $parent_feed;

	/**
	 * Entry authors
	 * @var array
	 */
	protected $authors;

	/**
	 * Entry title
	 * @var string
	 */
	protected $title;

	/**
	 * Enclosure links from entry
	 * (https://en.wikipedia.org/wiki/RSS_enclosure)
	 * @var array
	 */
	protected $enclosures;

	/**
	 * links from entry (other than enclosures)
	 * @var array
	 */
	protected $links;

	/**
	 * Entry summary
	 * @var string
	 */
	protected $summary;

	/**
	 * Entry body
	 * @var string
	 */
	protected $body;

	/**
	 * Link to comments feed
	 * @var string
	 */
	protected $comments;

	/**
	 * Entry guid as specified in original feed
	 * @var string
	 */
	protected $guid;

	/**
	 * Publication date
	 * @var int
	 */
	protected $publication_date;

	/**
	 * Date of last update
	 * @var int
	 */
	protected $last_update;

	/**
	 * Tags
	 * @var array
	 */
	protected $tags;

	/**
	 * Storage object
	 * @var Storage
	 */
	protected $storage;

	public function __construct($storage) {
		$this->storage = $storage;
	}


	/**
	 * Clean up `authors` attribute of entry.
	 */
	public function clean_authors() {
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
	 * Check wether an entry has the tag `$tag` or not.
	 * @return `true` if the entry has the tag `$tag`, `false` otherwise.
	 */
	public function has_tag($tag) {
		return multiarray_search(array('name'=>$tag), $this->tags) !== false;
	}


	/**
	 * Get the full feed associated with an entry.
	 */
	function get_feed() {
		return new Feed($storage, 'id', $this->parent_id);
	}
}


