<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */


/**
 * Entries represent articles from RSS feeds.
 * This class store entries and handles routing of entry-related API requests.
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
}


