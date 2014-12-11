<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

require_once('AbstractResource.class.php');

/**
 * Root API entrypoint
 * Presents links to other entrypoints
 * (singleton)
 */
class Root extends AbstractResource {
	private static $instance = null;
	private function __construct() {}

	public static function get_instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * @override
	 * @todo
	 */
	public function route($path) {
		$res = array(
			'status' => array(
				'installed' => false
				)
			);

		return function($body) use ($res) { return $res; };
	}
}


