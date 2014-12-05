<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Implementation of Facebook sharing.
 */

require_once("../share.class.php");


/**
 * Facebook sharing class.
 */
abstract class FacebookShare extends AbstractShare() {
	protected $type = "facebook";
	protected $url = "https://www.facebook.com/sharer.php?u=";
}
