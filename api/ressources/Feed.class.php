<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */


/**
 * Feed represent a RSS feed.
 * This class stores a feed and handles routing of feed-related API requests.
 */
class Feed {
	/**
	 * Entry unique id
	 * @var int
	 */
	protected $id;

	/**
	 * Title of the feed
	 * @var string
	 */
	protected $title;

	/**
	 * Title, as defined by the user
	 * @var string
	 */
	protected $user_title;

	/**
	 * URL of the feed
	 * @var string
	 */
	protected $url;

	/**
	 * Links stored in the feed
	 * @var array
	 */
	protected $links;

	/**
	 * Description of the feed
	 * @var string
	 */
	protected $description;

	/**
	 * TTL of the feed
	 * @var int
	 */
	protected $ttl;

	/**
	 * TTL, as defined by the user
	 * @var int
	 */
	protected $user_ttl;

	/**
	 * Image of the feed, e.g. favicon
	 * @var string
	 */
	protected $image;

	/**
	 * Post parameters to send to refresh the feed
	 * @var string
	 */
	protected $post;

	/**
	 * Whether to import tags from feed
	 * @var int
	 */
	protected $import_tags_from_feed;

	/**
	 * Storage object
	 * @var Storage
	 */
	protected $storage;

	public function __construct($storage) {
		$this->storage = $storage;
	}


	/**
	 * Remove a feed and all associated tags / entries based on its id
	 *
	 * @param $id is the id of the feed to delete
	 * @todo
	 */
	public function delete() {
		$query = $dbh->prepare('DELETE FROM feeds WHERE id=:id');
		$query->execute(array(':id'=>$id));

		$query = $dbh->prepare('DELETE FROM feeds WHERE url=:url');
		$query->execute(array(':url'=>$url));
	}


	/**
	 * Edit a feed in the database and refresh it.
	 *
	 * @param $old_url is the current URL of the feed
	 * @param $new_url is the new URL to assign to this feed
	 * @param $new_title (optionnal) is the new title of the feed
	 * @return true upon success, false otherwise.
	 * @todo
	 */
	function edit_feed($old_url, $new_url, $new_title='') {
		global $dbh;
		if (filter_var($new_url, FILTER_VALIDATE_URL) && filter_var($old_url, FILTER_VALIDATE_URL)) {
			$query = $dbh->prepare('UPDATE feeds SET url=:url WHERE url=:old_url');
			$query->execute(array(':old_url'=>$old_url, 'new_url'=>$new_url));
			if ($query->rowCount() == 0) {
				return false;
			}
			else {
				refresh_feeds(array($dbh->lastInsertId()=>$new_url)); // TODO
				return true;
			}
		}
		else {
			return false;
		}
	}


	/**
	 * Return a feed based on its id
	 * @todo
	 */
	function get_feed($id) {
		$query = $dbh->prepare('SELECT id, title, url, links, description, ttl, image, post FROM feeds WHERE id=:id');
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute();
		$feed = $query->fetch(PDO::FETCH_ASSOC);
		$feed['links'] = json_decode($feed['links']);
		$feed['image'] = json_decode($feed['image']);
		$feed['post'] = json_decode($feed['post']);
		$feed['tags'] = array();
		$query = $dbh->prepare('SELECT tags.id, tags.name FROM tags INNER JOIN tags_feeds ON tags_feeds.tag_id=tags.id WHERE tags_feeds.feed_id=:id');
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute();
		$tags = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach($tags as $tag_id=>$tag) {
			$feed['tags'][$tag_id] = $tag;
		}
		return $feed;
	}
}


