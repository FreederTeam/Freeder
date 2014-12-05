<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Abstract sharing class to implement sharing options.
 */


/**
 * Base abstract class for sharing articles.
 */
abstract class AbstractShare() {
	protected $type;
	protected $url;

	public function get_type() {
		return $this->type;
	};

	public function get_URL() {
		return $this->url;
	};
}
