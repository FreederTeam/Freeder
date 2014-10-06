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
	if ($dbh === NULL) return FALSE;

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
 * @return: Returns false if the user credentials are invalid. Returns true otherwise (user connected or ready to connect). Handles page redirection upon successful login.
 */
function log_user_in() {
	global $dbh;

	// If user already connected, returns immediately
	if (!empty($_SESSION['user'])) {
		return true;
	}
	// Else if form was submitted
	elseif (!empty($_POST['login']) && !empty($_POST['password'])) {
		$user = check_and_get_user($_POST['login'], $_POST['password']);
		if ($user !== false) {
			$_SESSION['user'] = $user;
		}
		else {
			return false;
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

		$user = $query->fetch();
		if (empty($user)) {
			remove_stay_connected();
			return true;
		}
		else {
			$_SESSION['user'] = $user;
			header('location: index.php');
			exit();
		}
	}
	return true;
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

	if (empty($_SESSION['user']) && $config->anonymous_access == 0 && !is_command_line()) {
		if ($tpl === NULL) assert(false);
		$tpl->draw('login');
		exit();
	}
}


/**
 * Exit if unauthentified user.
 */
function require_auth($redirect=true) {
	if(!isset($_SESSION['user'])) {
		if ($redirect) {
			header('location: index.php');
			exit();
		}
		else {
			return false;
		}
	}
	return true;
}


/**
 * Returns the password associated with a login.
 */
function get_password($login) {
	global $dbh;

	$query = $dbh->prepare('SELECT password FROM users WHERE login=?');
	$query->execute(array($login));

	return $query->fetch();
}

