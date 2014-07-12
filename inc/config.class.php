<?php
class Config {
    private $config = array(  // This is the default config
        'timezone'=>'Europe/Paris',
        'use_tags_from_feeds'=>1,
        'template'=>'default/',
        'synchronization_type'=>'cron'
    );

    public function __construct() {
        $this->load();
    }

    public function get($option) {
        return isset($this->config[$option]) ? $this->config[$option] : false;
    }

    public function set($option, $value) {
        $this->config[$option] = $value;
    }

    public function load() {
        $config = $GLOBALS['dbh']->query('SELECT option, value FROM config');
        $this->config = array_merge($this->config, $config->fetchall(PDO::FETCH_ASSOC));
    }

    public function save() {
        $GLOBALS['dbh']->beginTransaction();
        // TODO : Same thing that the comment in feeds about UPSERT
        $query_insert = $GLOBALS['dbh']->prepare('INSERT OR IGNORE INTO config(option) VALUES(:option)');
        $query_insert->bindParam(':option', $option);
        $query_update = $GLOBALS['dbh']->prepare('UPDATE config SET value=:value WHERE option=:option');
        $query_update->bindParam(':value', $value);

        foreach($this->config as $option=>$value) {
            $query_insert->execute();
            $query_update->execute();
        }
        $GLOBALS['dbh']->commit();
    }
}
