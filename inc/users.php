<?php

function check_and_get_user($login, $pass) {
    $query = $GLOBALS['dbh']->prepare('SELECT id, password, salt, is_admin FROM users WHERE login=:login');
    $query->execute(array(':login'=>$login));
    $user_db = $query->fetch(PDO::FETCH_ASSOC);

    if($user_db === false OR sha1($user_db['salt'].$pass) != $user_db['password']) {
        return false;
    }
    else {
        $user = new stdClass;
        $user->login = $login;
        $user->is_admin = (int) $user_db['is_admin'];

        return $user;
    }
}
