<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

require_once('resources/Root.class.php');

/**
 * Main API Router
 */
class Router {
	/**
	 * Routes, associating URIs to ressources
	 * @var array
	 */
	protected $routes = array();


	/**
	 * Register route.
	 * @param $path: route path
	 * @param $ressource: ressource toward which redirect request
	 */
	public function register($path, $ressource) {
		$this->routes[$path] = $ressource;
	}


	/**
	 * Route request to the appropriate API entrypoint.
	 * @param $path: Requested path
	 * @return entrypoint
	 * @todo
	 */
	public function handle($path) {
		foreach ($this->routes as $route_path => $ressource) {
			if ($route_path == $path) {
				return $ressource->route($path);
			}
		}

		// Fall back to root
		return Root::get_instance()->route($path);
	}
}