<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Implementation of Wallabag sharing.
 */

require_once("../share.class.php");


/**
 * Wallabag sharing class.
 */
abstract class WallabagShare extends AbstractShare() {
	protected $type = "wallabag";
	protected $url = "";  // TODO
}
