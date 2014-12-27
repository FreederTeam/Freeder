<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to handle users (login, creation, deletion).
 */


/**
 * @todo
 */
function ldap_auth($host, $user_dn='', $password='') {
	if (!function_exists('ldap_connect')) {
		die ("LDAP extension is not available.");
	}
	$conn = ldap_connect($host);
	if (!$conn) {
		die ("Unable to connect to LDAP server.");
	}

	if (!ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3)) {
		die ("LDAPv3 is not available.");
	}

	if (ldap_bind($conn, $user_dn, $password)) {
		// Check auth
		return true;
	}
	else {
		return false;
	}
	ldap_close($conn);
}


/**
 * @todo
 */
function http_auth() {
	if (isset($_SERVER['PHP_AUTH_USER'])) {
		$user = new stdClass;
		$user->id = 0;
		$user->login = $_SERVER['PHP_AUTH_USER'];
		$user->is_admin = 1;
		return $user;
	}
	return false;
}


/**
 * Authenticate user against SQL backend.
 *
 * @param	$login		User login.
 * @param	$password	User password (as provided by the user).
 * @return `false` if the authentication failed. A `stdClass` with attributes `id`, `login` and `is_admin` otherwise.
 */
function sql_auth($login, $password) {
	global $dbh;
	if ($dbh === NULL) {
		die ("Unable to connect to database.");
	}

	$query = $dbh->prepare('SELECT id, login, password, salt, remember_token, is_admin FROM users WHERE login=:login');
	$query->execute(array(':login'=>$login));
	$user_db = $query->fetch(PDO::FETCH_ASSOC);

	if($user_db === false OR sha1($user_db['salt'].$password) != $user_db['password']) {
		return false;
	}
	$user = new stdClass;
	$user->id = $user_db['id'];
	$user->login = $user_db['login'];
	$user->is_admin = $user_db['is_admin'];
	return $user;
}


/**
 * Authenticate user using "remember me" cookie.
 *
 * @return `false` if the authentication failed. A `stdClass` with attributes `id`, `login` and `is_admin` otherwise.
 */
function remember_me_auth() {
	global $dbh;
	if ($dbh === NULL) {
		die ("Unable to connect to database.");
	}

	$query = $dbh->prepare('SELECT id, login, password, salt, remember_token, is_admin FROM users WHERE remember_token=?');
	$query->execute(array($_COOKIE['freeder_remember_me']));

	$user_db = $query->fetch();
	if (empty($user_db)) {
		unset_remember_me();
		return false;
	}
	else {
		$user = new stdClass;
		$user->id = $user_db['id'];
		$user->login = $user_db['login'];
		$user->is_admin = $user_db['is_admin'];
		return $user;
	}
}


/**
 * Logout handling
 */
function logout() {
	session_destroy();
}


/**
 * Generate a secure token to be used for auth.
 *
 * @param	$user	(optionnal) If specified, use it instead of the user from `$_SESSION['user']`.
 * @return	The generated token.
 */
function generate_auth_token($user_id=NULL) {
	global $dbh;
	if ($dbh === NULL) {
		die ("Unable to connect to database.");
	}

	if ($user_id === NULL) {
		$user_id = $_SESSION['user']->id;
	}
	$dbh->beginTransaction();
	$user_db = $dbh->prepare('SELECT password, salt, login FROM users WHERE id=:id');
	$user_db->execute(array('id', $user_id));
	$user_db->fetch;

	$token = sha1($user_db['password'].$user_db['salt'].$user_db['login']);

	$query = $dbh->prepare('UPDATE users SET remember_token=:token WHERE id=:id');
	$query->execute(array('token'=>$token, 'id'=>$user_id));
	$dbh->commit();

	return $token;
}


/**
 * Set a "remember me" cookie for the specified user.
 */
function set_remember_me() {
	setcookie('freeder_remember_me', generate_auth_token(), time()+31536000, '/', NULL, NULL, true);
}


/**
 * Delete the "remember me" cookie.
 */
function unset_remember_me() {
	setcookie('freeder_remember_me', FALSE, 0);
}


/**
 * Check that specified user exists, with the right password and returns its infos.
 *
 * @param	$login		User login.
 * @param	$password	User password (as provided by the user).
 * @return `false` if the authentication failed. `true` otherwise.
 */
function log_in($login, $password) {
	if (!empty($_SESSION['user'])) {
		// Already logged in
		return true;
	}
	elseif (!empty($_COOKIE['freeder_remember_me'])) {
		// Remember me cookie is set
		$user = remember_me_auth();
	}
	elseif (!empty($_POST['login']) && !empty($_POST['password'])) {
		// User sent the login form
		$user = sql_auth($login, $password);
	}
	else {
		return false;
	}

	if ($user === false) {
		return false;
	}
	$_SESSION['user'] = $user;
	// Handle "remember me" button
	if (isset($_POST['remember'])) {
		set_remember_me();
	}
	return true;
}
