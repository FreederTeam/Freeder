<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Implementation of Twitter sharing.
 */

require_once("../share.class.php");


/**
 * Twitter sharing class.
 */
abstract class TwitterShare extends AbstractShare() {
	protected $type = "twitter";
	protected $url = "https://twitter.com/share?url=";
}
