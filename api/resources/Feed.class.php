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
	 * @var array
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

	/**
	 * Stores the changed fields since creation / load.
	 */
	protected $updated_feeds = array();


	public function __construct($storage, $field="", $value="") {
		$this->storage = $storage;

		if (!empty($field) && !empty($value)) {
			$this->load_by($field, $value);
		}
	}

	public function get_id() {
		return $this->id;
	}

	public function set_id($id) {
		$this->id = $id;
	}

	public function get_title() {
		return $this->title;
	}

	public function set_title($title) {
		$this->title = $title;
	}

	public function get_user_title() {
		return $this->$user_title;
	}

	public function set_user_title($user_title) {
		$this->$user_title = $user_title;
	}

	public function get_url() {
		return $this->$url;
	}

	public function set_url($url) {
		$this->$url = $url;
	}

	public function get_links() {
		return $this->$links;
	}

	public function set_links($links) {
		$this->$links = $links;
	}

	public function get_description() {
		return $this->$description;
	}

	public function set_description($description) {
		$this->$description = $description;
	}

	public function get_ttl() {
		return $this->$ttl;
	}

	public function set_ttl($ttl) {
		$this->$ttl = $ttl;
	}

	public function get_user_ttl() {
		return $this->$user_ttl;
	}

	public function set_user_ttl($user_ttl) {
		$this->$user_ttl = $user_ttl;
	}

	public function get_image() {
		return $this->$image;
	}

	public function set_image($image) {
		$this->$image = $image;
	}

	public function get_post() {
		return $this->$post;
	}

	public function set_post($post) {
		$this->$post = $post;
	}

	public function get_import_tags_from_feed() {
		return $this->$import_tags_from_feed;
	}

	public function set_import_tags_from_feed($import_tags_from_feed) {
		$this->$import_tags_from_feed = $import_tags_from_feed;
	}


	/**
	 * Remove a feed and all associated tags / entries.
	 *
	 * @return	`true` on successful deletion, `false` otherwise.
	 */
	public function delete() {
		if (!empty($this->id)) {
			$query = $dbh->prepare('DELETE FROM feeds WHERE id=:id');
			$query->execute(array(':id'=>$this->id));
			if ($query->rowCount() == 0) {
				return false;
			}
			else {
				return true;
			}
		}
		elseif (!empty($this->url)) {
			$query = $dbh->prepare('DELETE FROM feeds WHERE url=:url');
			$query->execute(array(':url'=>$this->url));
			if ($query->rowCount() == 0) {
				return false;
			}
			else {
				return true;
			}
		}
		else {
			return false;
		}
	}


	/**
	 * Save a feed in the database, either by inserting or updating.
	 * @return	`true` on successful save, `false` otherwise.
	 */
	function save() {
		if (false === filter_var($this->url, FILTER_VALIDATE_URL)) {
			return false;
		}

		if (!empty($this->id)) {
			$query = 'UPDATE feeds SET ';
			foreach($updated_fields as $i=>$field) {
				if ($i > 0) {
					$query .= ', ';
				}
				$query .= $field.':'.$field;
			}
			$query = $dbh->prepare($query);
			foreach($updated_fields as $field) {
				$query->bindParam(':'.$field, $this->$field);
			}
		}
		else {
			$query = $dbh->prepare('INSERT INTO feeds(title, user_title, url, links, description, ttl, user_ttl, image, post, import_tags_from_feed) VALUES(:title, :user_title, :url, :links, :description, :ttl, :user_ttl, :image, :post, :import_tags_from_feed)');
			$query->bindParam(':title', $this->title);
			$query->bindParam(':user_title', $this->user_title);
			$query->bindParam(':url', $this->url);
			$query->bindParam(':links', json_encode($this->links));
			$query->bindParam(':description', $this->description);
			$query->bindParam(':ttl', $this->ttl);
			$query->bindParam(':user_ttl', $this->user_ttl);
			$query->bindParam(':image', json_encode($this->image));
			$query->bindParam(':post', json_encode($this->post));
			$query->bindParam(':import_tags_from_feed', $this->import_tags_from_feed);
		}
		$query->execute();
		if ($query->rowCount() == 0) {
			return false;
		}
		else {
			return true;
		}
	}


	/**
	 * Load a feed based on a field value.
	 * @param	$field	Filtering field.
	 * @parma	$value	Expected value for the field.
	 */
	function load_by($field, $value) {
		$dbh = $this->storage->get_dbh;
		$dbh->prepare('SELECT id, title, user_title, url, links, description, ttl, user_ttl, image, post, import_tag_from_feed FROM feeds WHERE '.$field.'=:'.$field);
		$query->bindValue(':'.$field, $value);
		$query->execute();
		$feed = $query->fetch(PDO::FETCH_ASSOC);

		$this->id = $feed['id'];
		$this->title = $feed['title'];
		$this->user_title = $feed['user_title'];
		$this->url = $feed['url'];
		$this->links = json_decode($feed['links'], true);
		$this->description = $feed['description'];
		$this->ttl = $feed['ttl'];
		$this->user_ttl = $feed['user_ttl'];
		$this->image = json_decode($feed['image'], true);
		$this->post = json_decode($feed['post'], true);
		$this->import_tag_from_feed = $feed['import_tag_from_feed'];

		$query = $dbh->prepare('SELECT tags.id, tags.name FROM tags INNER JOIN tags_feeds ON tags_feeds.tag_id=tags.id WHERE tags_feeds.feed_id=:id');
		$query->bindValue(':id', $this->id, PDO::PARAM_INT);
		$query->execute();
		$this->tags = $query->fetchAll(PDO::FETCH_ASSOC);

		$this->updated_feeds = array();
	}
}


