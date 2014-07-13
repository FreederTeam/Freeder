<?php
class Config {
    private $default_config = array(  // This is the default config
        'timezone'=>'Europe/Paris',
        'use_tags_from_feeds'=>1,
        'template'=>'default/',
        'synchronization_type'=>'cron'
    );

    public function __construct() {
        $this->load();
    }

    public function get($option) {
        return isset($this->$option) ? $this->$option : false;
    }

    public function set($option, $value) {
        $this->$option = $value;
    }

    public function load() {
        $config = $GLOBALS['dbh']->query('SELECT option, value FROM config');
        $config = array_merge($this->default_config, $config->fetchall(PDO::FETCH_ASSOC));

        foreach($config as $option=>$value) {
            $this->$option = $value;
        }
    }

    public function save() {
        $GLOBALS['dbh']->beginTransaction();
        // TODO : Same thing that the comment in feeds about UPSERT
        $query_insert = $GLOBALS['dbh']->prepare('INSERT OR IGNORE INTO config(option) VALUES(:option)');
        $query_insert->bindParam(':option', $option);
        $query_update = $GLOBALS['dbh']->prepare('UPDATE config SET value=:value WHERE option=:option');
        $query_update->bindParam(':value', $value);

        foreach($this->config as $option=>$value) {
            if($option == 'default_config') {
                continue;
            }
            $query_insert->execute();
            $query_update->execute();
        }
        $GLOBALS['dbh']->commit();
    }
}
