<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Storage class using SQLite backend, as a singleton pattern.
 */

require_once("../../constants.php");
require_once("../Storage.class.php");

class SQLiteStorage extends AbstractStorage {
	private $dbh = null;
	private static $instance = null;


	private function __construct() {
		$this->connect();
	}


	public function connect() {
		try {
			$this->dbh = new PDO('sqlite:'.dirname(__FILE__).'/../../'.DATA_DIR.DB_FILE);
			$this->dbh->query('PRAGMA foreign_keys = ON');
		} catch (Exception $e) {
			exit ('Unable to access to database: '.$e->getMessage().'.');
		}
	}


	public function disconnect() {
		$this->dbh = null;
	}


	public function get_dbh() {
		return $this->dbh;
	}


	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Build a SQL query from a view rule.
	 * @param	$rule		Rule as raw text.
	 * @param	$selection	Designates what field to get from SQL table. Warning: it is not escaped.
	 * @param	$limit		Specify the maximum number of items to return. Ignored if negative.
	 * @param	$offset		Offset to apply for limit. Ignored if negative.
	 * @return a PDO-ready query and the binding array.
	 *
	 * TODO: Refactor me!!!
	 */
	public static function rule2sql($rule, $selection='*', $limit=-1, $offset=-1) {
		$limit = (int)$limit;
		$ast = parse_rule($rule);
		$ast[] = array('by', 'by', ''); // Hacky
		$query = "SELECT $selection FROM entries E";
		$bindings = array();
		$subquery = '';
		$state = 'select';
		foreach ($ast as $word) {
			$prefix = strtolower($word[1]);
			$tag = $word[2];
			switch ($state) {
			case 'select':
				switch ($prefix) {
				case '+':
				case '-':
					$subquery = append_selection_query($prefix, $tag, $subquery, $bindings);
					break;
					// Go to next parsing state
				case 'by':
					if ($subquery != '') {
						$query .= " WHERE $subquery";
					}
					$subquery = '';
					$state = 'order';
					break;
				default:
					throw new ParseError("Unknown prefix `$prefix`");
				}
				break;
			case 'order':
				$bind = false;
				switch ($tag) {
				case '$pubDate':
					$tag_count = "E.pubDate";
					break;
				default:
					$tag_id = "(SELECT id FROM tags WHERE name = ?)";
					$tag_count = "(SELECT COUNT(*) FROM tags_entries WHERE tag_id = $tag_id AND entry_id = E.id)";
					$bind = true;
				}
				switch ($prefix) {
				case '+':
					if ($subquery != '') {
						$subquery .= ", ";
					}
					$subquery .= "$tag_count ASC";
					if ($bind) {
						$bindings[] = $tag;
					}
					break;
				case '-':
					if ($subquery != '') {
						$subquery .= ", ";
					}
					$subquery .= "$tag_count DESC";
					if ($bind) {
						$bindings[] = $tag;
					}
					break;
				case 'by':
					if ($subquery != '') {
						$query .= " ORDER BY $subquery";
					}
					$status = 'done';
					break;
				default:
					throw new Exception("Unknown prefix `$word[1]`");
				}
				break;
			}
		}
		if ($limit >= 0) {
			$query .= " LIMIT $limit";
		}
		if ($offset >= 0) {
			$query .= " OFFSET $offset";
		}
		return array($query, $bindings);
	}
}
