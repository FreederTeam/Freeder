<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

require_once('constants.php');

require_once(COMPOSER_AUTOLOAD);
require_once(LIB_DIR.'Redbean/rb.php');

/* DEBUG CODE */
$config = new stdClass;
$config->debug = true;
$config->tpl_path = "tpl/zen/";
$config->timezone = "Europe/Paris";
date_default_timezone_set($config->timezone);
/* END OF DEBUG CODE */

require_once('app/start.php');
