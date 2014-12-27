<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */


/**
 * Get all the settings
 */
$app->get('/settings', function () {
	$user = "";
	$settings = new Settings($user);
	$settings = $settings->get();
	foreach ($settings as $setting) {
		$settings[$settings->name.'_url'] = '/settings/:setting';
	}
	echo json_encode($settings);
});


/**
 * Get a specific setting value
 */
$app->get('/settings/:setting', function ($setting) {
	$user = "";
	$settings = new Settings($user);
	echo json_encode($settings->get($setting));
});


/**
 * Set a specific setting value
 */
$app->patch('/settings/:setting', function ($setting) {
	$user = "";
	$settings = new Settings($user);
	$settings->set($setting, $_POST['value']);
});


/**
 * Set settings
 */
$app->patch('/settings', function () use ($app) {
	$user = "";
	$settings = new Settings($user);
	$settings_post = json_decode($_POST['settings'], true);
	if (null === $settings_post) {
		$app->response->setStatus(400);
	}
	foreach ($settings_post as $setting) {
		$settings->set($setting['name'], $setting['value']);
	}
});
