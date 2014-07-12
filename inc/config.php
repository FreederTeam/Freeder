<?php
function load_config() {
    /* Load the config options from the database and returns a config array */
    global $dbh;

    $default_config = array(
        'timezone'=>'Europe/Paris',
        'use_tags_from_feeds'=>1
    );

    $config = $dbh->query('SELECT option, value FROM config');
    return array_merge($default_config, $config->fetchall(PDO::FETCH_ASSOC));
}

function save_config($config) {
    /* Save $config (a config array) in database */
    global $dbh;

    $dbh->beginTransaction();
    // TODO : Same thing that the comment in feeds about UPSERT
    $query_insert = $dbh->prepare('INSERT OR IGNORE INTO config(option) VALUES(:option)');
    $query_insert->bindParam(':option', $option);
    $query_update = $dbh->prepare('UPDATE config SET value=:value WHERE option=:option');
    $query_update->bindParam(':value', $value);

    foreach($config as $option=>$value) {
        $query_insert->execute();
        $query_update->execute();
    }
    $dbh->commit();
}
