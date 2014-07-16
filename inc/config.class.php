<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Config class used to handle the config stored in database
 */


/**
 * Store the configuration retrieved from database.
 *
 * @todo Use stdClass ?
 */
class Config {
    private static $default_config = array(  /** This is the default config */
		'timezone'=>'Europe/Paris',
		'use_tags_from_feeds'=>1,
		'template'=>'default/',
		'synchronization_type'=>'cron',
        'anonymous_access'=>0,
        'entries_to_keep'=>50  // Number of entries to keep, set to 0 if you want to keep all of them
	);

	public function __construct() {
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

		foreach($this as $option=>$value) {
			if(!isset($this->default_config[$option])) {
				continue;
			}
			$query_insert->execute();
			$query_update->execute();
		}
		$dbh->commit();
	}
}
