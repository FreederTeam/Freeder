<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Implementation of shaarli sharing.
 */

require_once("../share.class.php");


/**
 * Shaarli sharing class.
 */
abstract class ShaarliShare extends AbstractShare() {
	protected $type = "shaarli";
	protected $url = "";  // TODO
}
