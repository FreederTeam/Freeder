<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Implementation of Disapora sharing.
 */

require_once("../share.class.php");


/**
 * Diaspora sharing class.
 */
abstract class DiasporaShare extends AbstractShare() {
	protected $type = "diaspora";
	protected $url = "";  // TODO
}
