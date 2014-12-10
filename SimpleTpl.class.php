<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

/**
 * Template manager.
 * Choose a theme and rewrite relative URLs
 */
class SimpleTpl {
	/**
	 * Last error that occured. Reset to null when methods use it (see doc)
	 * @var NULL | string
	 */
	public $error = NULL;


	/**
	 * Reset $error
	 */
	public function __construct() {
		$this->error = NULL;

		// Try to init
		if (!$this->init()) {
			// If it fails, try to install and then if it worked try to init again
			return $this->install() and $this->init();
		}

		return TRUE;
	}


	/**
	 * Install simple tpl (perform task that should never be repeated after that)
	 * Reset $error
	 * @return bool (whether installation worked)
	 * 
	 * @todo
	 */
	public function install() {

	}


	/**
	 * Initialize simple tpl (perform task that should never be repeated after that)
	 * Reset $error
	 * @return bool (whether initialization worked)
	 *
	 * @todo
	 */
	public function init() {

	}


	/**
	 * Render a given template page
	 * Reset $error
	 * @param $view_path: view to render (path relative to tpl/)
	 * @return bool (whether rendering worked)
	 */
	public function render($theme, $view) {
		$this->error = NULL;

		// Load raw view from file
		$view = @file_get_contents('tpl/'.$view_path);
		if ($view === FALSE) {
			$this->error = 'File not found: tpl/'.$view_path;
			return FALSE;
		}

		// Rewrite URLs
		$view = $this->rewrite_urls($view);
		if (!is_null($this->error)) return FALSE;

		// Render view
		echo($view);

		return TRUE;
	}

}


