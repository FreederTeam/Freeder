<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */



require_once('Config.class.php');
require_once('SimpleTpl.class.php');


// Initialize config
$config = new Config;
if (!is_null($config->error)) die($config->error);
if (isset($_SESSION['user'])) {
	$config->load($_SESSION['user']);
}


// Initialize template engine
$tpl = new SimpleTpl;
if (!is_null($tpl->error)) die($tpl->error);


// Render webapp
$theme = $config->get('theme');
$tpl->render($theme.'index');
if (!is_null($tpl->error)) die($tpl->error);
