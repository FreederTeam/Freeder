<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

/**
 * An API Entrypoint is a computation unit of the API.
 * This is what perform
 */
abstract class AbstractEntrypoint {
	/**
	 * Execute main entrypoint role.
	 * @param $req: request body (PHP object)
	 * @return raw response object
	 */
	abstract public function run($req);
}