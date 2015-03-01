<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

require_once dirname(__FILE__)."/../Models/User.php";


function validateUserKey($login, $password) {
	if ($login == 'demo' && $password == 'demo') {
		$user = R::dispense('user');
		$user->login = $login;
		$user->password = $password;
		return $user;
	} else {
		return false;
	}
}


function httpAuthBasic() {
	$app = \Slim\Slim::getInstance();
	if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
		return false;
	}
	else {
		return validateUser($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
	}
}


function authenticationNeeded(\Slim\Route $route) {
	$app = \Slim\Slim::getInstance();

	$user_validation = httpAuthBasic();
	if ($user_validation === false) {
		$uid = $app->request->params('uid');
		$key = $app->request->params('key');

		$user_validation = validateUserKey($uid, $key);
		if ($user_validation === false) {
			$app->halt(401);
		}
	}
	$app->user = $user_validation;
}

function anonymousOrAuthenticationNeeded(\Slim\Route $route) {
	authenticationNeeded($route);
}
