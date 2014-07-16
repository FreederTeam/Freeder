<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Various functions, not specific and widely used.
 */

/**
 * Check that specified user exists, with the right password and returns its infos.
 * @param $login the user login
 * @param $pass the user password (as provided by the user)
 * @return false if the user infos do not match.
 * @return A stdClass with attributes login and is_admin otherwise.
 */
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
