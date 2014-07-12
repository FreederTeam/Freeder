<?php
function load_config() {
    global $dbh;

    $default_config = array(
        'timezone'=>'Europe/Paris'
    );

    $config = $dbh->query('SELECT option, value FROM config');
    return array_merge($default_config, $config->fetchall(PDO::FETCH_ASSOC));
}
