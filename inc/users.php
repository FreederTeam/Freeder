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
	global $dbh;

	$query = $dbh->prepare('SELECT id, password, salt, is_admin FROM users WHERE login=:login');
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


/**
 * Check wether login POST data has been provided and handle it to try and log the user in.
 * Set `$_SESSION['user']` value.
 */
function log_user_in() {
	if (!empty($_POST['login']) && !empty($_POST['password'])) {
		$user = check_and_get_user($_POST['login'], $_POST['password']);
		if ($user !== false) {
			$_SESSION['user'] = $user;
		}
		header('location: index.php');
	}
}


/**
 * Require connection if no anonymous view has been set.
 * Draw the login template and then exit.
 */
function check_anonymous_view() {
	global $tpl, $config;

	if (empty($_SESSION['user']) && $config->anonymous_access == 0) {
		$tpl->draw('login');
		exit();
	}
}


/**
 * Exit if unauthentified user.
 */
function require_auth() {
	if(!isset($_SESSION['user'])) {
		header('location: index.php');
		exit();
	}
}

