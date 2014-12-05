<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

require_once('entrypoints/TestEntrypoint.class.php');

/**
 * Main API Router
 */
class Router {
	/**
	 * Route request to the appropriate API entrypoint.
	 * @param $path: Requested path
	 * @return entrypoint
	 * @todo
	 */
	public function handle($path) {
		return new TestEntrypoint;
	}
}