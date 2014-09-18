<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Config class used to handle the config stored in database
 */

require_once(INC_DIR.'functions.php');


/**
 * Store the configuration retrieved from database.
 */
class Config {
	public static $versions = array('0.1');
	private static $default_config;

	public function __construct() {
		self::$default_config = array(  /** This is the default config */
			'timezone'=>'Europe/Paris',
			'import_tags_from_feeds'=>0,
			'template'=>DEFAULT_THEME . '/',
			'base_url'=>rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/',
			'synchronization_type'=>'cron',
			'anonymous_access'=>0,
			'entries_to_keep'=>50,  // Number of entries to keep, set to 0 if you want to keep all of them
			'display_entries'=>'description',
			'version'=>self::$versions[count(self::$versions) - 1],  // Current version
			'entries_per_page'=>20,
			'use_rewriting'=>get_url_rewriting(),
			'facebook_share'=>0,
			'twitter_share'=>0,
			'shaarli_share'=>"",
			'wallabag_share'=>"",
			'diaspora_share'=>""
		);
		$this->load();
	}

	public function get($option) {  /** You can use either Config->attribute or Config->get(attribute) */
		return isset($this->$option) ? $this->$option : false;
	}

	public function set($option, $value) {  /** You can use either Config->attribute=… or Config->set(attribute, value) */
		$this->$option = $value;
	}

	public function load() {  /** Load the config from the database into this Config object */
		global $dbh;
		$config_from_db = $dbh->query('SELECT option, value FROM config');
		$config_from_db = $config_from_db !== FALSE ? $config_from_db->fetchall(PDO::FETCH_ASSOC) : array();
		$config = array();
		foreach($config_from_db as $config_option) {
			$config[$config_option['option']] = $config_option['value'];
		}
		$config = array_merge(self::$default_config, $config);

		foreach($config as $option=>$value) {
			$this->$option = $value;
		}
	}

	public function save() {  /** Stores the current config in database */
		global $dbh;
		$dbh->beginTransaction();
		$query_insert = $dbh->prepare('INSERT OR IGNORE INTO config(option) VALUES(:option)');
		$query_insert->bindParam(':option', $option);
		$query_update = $dbh->prepare('UPDATE config SET value=:value WHERE option=:option');
		$query_update->bindParam(':value', $value);
		$query_update->bindParam(':option', $option);

		foreach(get_object_vars($this) as $option=>$value) {
			if(!isset(self::$default_config[$option])) {
				continue;
			}
			$query_insert->execute();
			$query_update->execute();
		}
		$dbh->commit();
	}

	public function sanitize() {  /** Sanitize the data for html display */
		$return = new stdClass();
		foreach(get_object_vars($this) as $option=>$value) {
			if(!isset(self::$default_config[$option])) {
				continue;
			}
			$option = htmlspecialchars($option);
			$return->$option = htmlspecialchars($value);
		}
		return $return;
	}

	public function xss_clean() {  /** Sanitize the data to prevent XSS */
		return $this->sanitize();
	}
}


