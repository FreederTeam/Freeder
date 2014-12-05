<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

require_once('AbstractEntrypoint.class.php');

/**
 * Testing entrypoint
 */
class TestEntrypoint extends AbstractEntrypoint {
	/**
	 * @override
	 */
	public function run($req) {
		return array('foo' => 'bar');
	}
}