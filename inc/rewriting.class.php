<?php

/**
 *  Freeder Rewrite Engine
 *  ----------------------
 *
 *  @file
 *  @brief RewriteEngine class
 *  @version 0.1
 *  @copyright 2014 Freeder Team
 *  @license MIT (See the LICENSE file for copying permissions)
 */

class RewriteEngine {
	/**
	 * URL-rewriting rules.
	 * A rule is made of 
	 *
	 * @var array
	 */
	protected rules = array();

	/**
	 * Use internal rules to rewrite url.
	 *
	 * @param $url URL to be rewritten
	 * @return New URL
	 */
	public function rewrite($url) {
		return $url;
	}

}
