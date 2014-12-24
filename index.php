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
date_default_timezone_set("Europe/Paris");
/* END OF DEBUG CODE */

require_once('app/start.php');
