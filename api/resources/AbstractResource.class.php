<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

/**
 * API REST Resource
 */
abstract class AbstractResource {
	/**
	 * Resource internal router.
	 * @param $route: request route
	 * @return endpoint function
	 */
	abstract public function route($path);
}