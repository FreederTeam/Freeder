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
	protected $parent_feed_id;

	/**
	 * Entry authors
	 * @var array of Authors objects
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

	public function __construct($storage, $filters = array()) {
		if (! $storage instanceof AbstractStorage) {
			throw Exception("Storage argument is not a valid Storage instance.");
		}
		$this->storage = $storage;

		if (!empty($filters)) {
			foreach($filters as $key=>$value) {
				if (!property_exists(self, $field)) {
					throw Exception("Invalid field specified in filters.");
				}
			}
			$this->load_by($filters);
		}
	}

	public function get_id() {
		return $this->id;
	}

	public function set_id($id) {
		$this->id = $id;
	}

	public function get_parent_feed_id() {
		return $this->parent_feed_id;
	}

	public function set_parent_feed_id($parent_feed_id) {
		$this->parent_feed_id = $this->parent_feed_id;
	}

	public function get_authors() {
		return this->authors;
	}

	public function set_authors($authors) {
		$this->authors = $authors;
	}

	public function get_title() {
		return $this->title;
	}

	public function set_title($title) {
		$this->title = $title;
	}

	public function get_enclosures() {
		return $this->enclosures;
	}

	public function set_enclosures($enclosures) {
		$this->enclosures = $enclosures;
	}

	public function get_links() {
		return $this->links;
	}

	public function set_links($links) {
		$this->links = $links;
	}

	public function get_summary() {
		return $this->summary;
	}

	public function set_summary($summary) {
		$this->summary = $summary;
	}

	public function get_body() {
		return $this->body;
	}

	public function set_body($body) {
		$this->body = $body;
	}

	public function get_comments() {
		return $this->comments;
	}

	public function set_comments($comments) {
		$this->comments = $comments;
	}

	public function get_guid() {
		return $this->guid;
	}

	public function set_guid($guid) {
		$this->guid = $guid;
	}

	public function get_publication_date() {
		return $this->publication_date;
	}

	public function set_publication_date($publication_date) {
		$this->publication_date = $publication_date;
	}

	public function get_last_update() {
		return $this->last_update;
	}

	public function set_last_update($last_update) {
		$this->last_update = $this->last_update;
	}

	public function get_tags() {
		return $this->tags;
	}

	public function set_tags($tags) {
		$this->tags = $tags;
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
	 * Remove an entry and all associated tags.
	 *
	 * @return	`true` on successful deletion, `false` otherwise.
	 */
	public function delete() {
		if (!empty($this->id)) {
			$query = $dbh->prepare('DELETE FROM entries WHERE id=:id');
			$query->execute(array(':id'=>$this->id));
			if ($query->rowCount() == 0) {
				return false;
			}
			else {
				return true;
			}
		}
		elseif (!empty($this->parent_feed_id)) {
			$query = $dbh->prepare('DELETE FROM entries WHERE parent_feed_id=:parent_feed_id');
			$query->execute(array(':parent_feed_id'=>$this->parent_feed_id));
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
	 * Save an entry in the database, either by inserting or updating.
	 * @return	`true` on successful save, `false` otherwise.
	 */
	function save() {
		if (!empty($this->id)) {
			$query = 'UPDATE entries SET ';
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
			$query = $dbh->prepare('INSERT INTO entries(parent_feed_id, authors, title, enclosures, links, summary, body, comments, guid, publication_date, last_update, tags) VALUES(:parent_feed_id, :authors, :title, :enclosures, :links, :summary, :body, :comments, :guid, :publication_date, :last_update, :tags)');
			$query->bindParam(':import_tags_from_feed', $this->import_tags_from_feed);  // TODO
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
	 * Load an entry based on a field value.
	 * @param	$filters	Array of filtering fields (keys) and expected values.
	 */
	function load_by($filters) {
		$dbh = $this->storage->get_dbh;
		$query = "SELECT id, parent_feed_id, authors, title, enclosures, links, summary, body, comments, guid, publication_date, last_update, tags FROM entries";
		$i = true;
		foreach($fields as $field=>$value) {
			if ($i) {
				$query .= " WHERE "
			}
			else {
				$query .= " AND ";
			}
			$query .= $field."=:".$field;
			$i = false;
		}
		$query = $dbh->prepare($query);
		foreach($fields as $field=>$value) {
			$query->bindValue(':'.$field, $value);
		}
		$query->execute();
		$feed = $query->fetch(PDO::FETCH_ASSOC);

		$this->id = $feed['id'];  // TODO
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

		$query = $dbh->prepare('SELECT tags.id, tags.name FROM tags INNER JOIN tags_entries ON tags_entries.tag_id=tags.id WHERE tags_entries.entry_id=:id');
		$query->bindValue(':id', $this->id, PDO::PARAM_INT);
		$query->execute();
		$this->tags = $query->fetchAll(PDO::FETCH_ASSOC);

		$this->updated_feeds = array();
	}
}


