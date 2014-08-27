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

	$query = $dbh->prepare('SELECT id, login, password, salt, remember_token, is_admin FROM users WHERE login=:login');
	$query->execute(array(':login'=>$login));
	$user_db = $query->fetch(PDO::FETCH_ASSOC);

	if($user_db === false OR sha1($user_db['salt'].$pass) != $user_db['password']) {
		return false;
	}
	else {
		unset($user_db['password']);
		$user = (object) $user_db;

		return $user;
	}
}


/**
 * Check wether login POST data has been provided and handle it to try and log the user in.
 * Set `$_SESSION['user']` value.
 */
function log_user_in() {
	// If user alreadu connected, returns immediately
	if (!empty($_SESSION['user'])) {
		return;
	}
	// Else if form was submitted
	elseif (!empty($_POST['login']) && !empty($_POST['password'])) {
		$user = check_and_get_user($_POST['login'], $_POST['password']);
		if ($user !== false) {
			$_SESSION['user'] = $user;
		}
		// Handle "remember me" button
		if (isset($_POST['remember'])) {
			stay_connected($user);
		}
		header('location: index.php');
		exit();
	}
	// Else if remember cookie is present
	elseif (!empty($_COOKIE['freeder_remember_me'])) {
		$query = $dbh->prepare('SELECT id, password, salt, remember_token, is_admin FROM users WHERE remember_token=?');
		$query->execute(array($_COOKIE['freeder_remember_me']));

		if ($query->rowCount() !== 1) {
			remove_stay_connected();
			return false;
		}
		else {
			$_SESSION['user'] = $query->fetch();
			header('location: index.php');
			exit();
		}
	}
}


/**
 * Generates a secure token to be used for auth.
 */
function generate_auth_token($user) {
	global $dbh;

	$token = sha1($user->password.$user->salt.$user->login);  // TODO : Improve this

	$query = $dbh->prepare('UPDATE users SET remember_token=? WHERE login=?');
	$query->execute(array($token, $user->login));

	return $token;
}


/**
 * Set a "remember me" cookie
 */
function stay_connected($user) {
	setcookie('freeder_remember_me', generate_auth_token($user), time()+31536000);
}


/**
 * Deletes the "remember me" cookie
 */
function remove_stay_connected() {
	setcookie('freeder_remember_me', FALSE, 0);
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

