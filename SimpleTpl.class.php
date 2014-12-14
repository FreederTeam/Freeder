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
	 * View file extension
	 * @var string
	 */
	public $view_extension = '.html';

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
	 * Load file content
	 * Reset $error
	 * @param $path: path to the file
	 * @return file content
	 */
	public function load($view_path) {
		$this->error = NULL;

		$filename = 'tpl/'.$view_path.$this->view_extension;
		$view = @file_get_contents($filename);
		if ($view === FALSE) {
			$this->error = 'File not found: '.$filename;
			return FALSE;
		}

		return $view;
	}


	/**
	 * Check whether the given path does not contain dangerous patterns
	 * such as .. that would allow the user to explore server filesystem
	 * Reset $error
	 * @param $view_path: view to render (path relative to tpl/)
	 * @return bool (whether rendering worked)
	 */
	public function check_view_path($view_path) {
		$this->error = NULL;

		// We forbid the use of '..' inside path. It is a little bit restrictive but
		// should never be a problem (a regular path with .. in it would be weired)
		if (preg_match('/\\.\\./', $view_path)) {
			$this->error = 'Invalid view path: '.$view_path.' (contains `..`)';
			return FALSE;
		}

		return TRUE;
	}


	/**
	 * Load view dependencies signaled by {include "foo"}
	 * Reset $error
	 * @param $url_prefix: prefix to add to relative URLs
	 * @param $view: view content to process
	 * @return full view
	 */
	public function load_included($url_prefix, $view) {
		$this->error = NULL;

		preg_replace_callback('/\\{include="(([^}]|\\\\\\})*)"\\}/', function ($matches) {
			$view = $this->load($matches[1]);
			var_dump(self::clean_path($matches[1]));
			if (!is_null($this->error)) return "{}";
			return $view;
		}, $view);

		return $view;
	}


	/**
	 * Check whether the given path does not contain dangerous patterns
	 * such as .. that would allow the user to explore server filesystem
	 * Reset $error
	 * @param $view: view content to process
	 * @return rewriten view
	 *
	 * @todo
	 */
	public function rewrite_urls($view) {
		$this->error = NULL;

		return $view;
	}


	/**
	 * Render a given template page
	 * Reset $error
	 * @param $view_path: view to render (path relative to tpl/)
	 * @return bool (whether rendering worked)
	 */
	public function render($view_path) {
		$this->error = NULL;

		// Check view path integrity
		$this->check_view_path($view_path);
		if (!is_null($this->error)) return FALSE;

		// Load raw view from file
		$view = $this->load($view_path);
		if (!is_null($this->error)) return FALSE;

		// Load included templates
		$view = $this->load_included($view);
		if (!is_null($this->error)) return FALSE;

		// Rewrite URLs
		$view = $this->rewrite_urls($view);
		if (!is_null($this->error)) return FALSE;

		// Evaluate variables (todo)
		//$view = $this->eval_variables($view);
		//if (!is_null($this->error)) return FALSE;

		// Render view
		echo($view);

		return TRUE;
	}


	/**
	 * Clean path by removing useless .. and .
	 * @param $path: Path to clean
	 * @return clean path
	 */
	public static function clean_path($path) {
		return preg_replace_callback(
			'#^((?:\\.\\./)*)(.*)#', 
			function($matches){
				$prefix = $matches[1]; // Leading ../
				$path = $matches[2];   // Remaining path

				// Remove ./
				$path = preg_replace('#/\\./#', '/', $path);
				$path = preg_replace('#(^\\./)|(/\\.$)#', '', $path);

				// Remove ../
				while (preg_match('#(^|/)\\.\\.(/|$)#', $path)) {
					$path = preg_replace('#(^|/)[^/]*/\\.\\.(?:/|$)#', '$1', $path);
				}

				return $prefix.$path;
			},
			$path
		);
	}

}


