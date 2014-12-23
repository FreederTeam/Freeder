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
	 * Base URL to prefix absolute local URLs
	 * @var string
	 */
	public $base_url = '';

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
	 * @return bool: whether installation worked
	 * 
	 * @todo
	 */
	public function install() {

	}


	/**
	 * Initialize simple tpl (perform task that should never be repeated after that)
	 * Reset $error
	 * @return bool: whether initialization worked
	 *
	 * @todo
	 */
	public function init() {
	}


	/**
	 * Load file content
	 * Reset $error
	 * @param string $path: path to the file
	 * @return string: file content
	 */
	protected function load($view_path) {
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
	 * @param string $view_path: view to render (path relative to tpl/)
	 * @return bool: whether rendering worked
	 */
	protected function check_view_path($view_path) {
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
	 * @param string $url_prefix: prefix to add to relative URLs
	 * @param string $view: view content to process
	 * @return string: full view
	 */
	protected function load_included($url_prefix, $view) {
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
	 * Replace URL according to the following rules:
	 * http://url => http://url
	 * url# => url
	 * /url => base_dir/url
	 * url => path/url (where path generally is base_url/template_dir)
	 * (The last one is => base_dir/url for <a> href)
	 *
	 * @param string $url Url to rewrite.
	 * @param string $tag Tag in which the url has been found.
	 * @param string $path Path to prepend to relative URLs.
	 * @return string: rewritten url
	 */
	protected function rewrite_url($url, $tag, $path) {
		// Make protocol list. It is a little bit different for <a>.
		$protocol = 'http|https|ftp|file|apt|magnet';
		if ($tag == 'a') {
			$protocol .= '|mailto|javascript';
		}

		// Regex for URLs that should not change (except the leading #)
		$no_change = "/(^($protocol)\:)|(#$)/i";
		if (preg_match($no_change, $url)) {
			return rtrim($url, '#');
		}

		// Regex for URLs that need only base url (and not template dir)
		$base_only = '/^\//';
		if ($tag == 'a' or $tag == 'form') {
			$base_only = '//';
		}
		if (preg_match($base_only, $url)) {
			return rtrim($this->base_url, '/') . '/' . ltrim($url, '/');
		}

		// Other URLs
		return $path . $url;
	}


	/**
	 * replace the path of image src, link href and a href.
	 * Reset $error
	 * @see rewrite_url for more information about how paths are replaced.
	 *
	 * @param string $view: view content
	 * @param string $view_path: path to view
	 * @return string: content with rewritten URLs
	 */
	protected function rewrite_urls($view, $view_path) {
		$this->error = NULL;

		$path = $this->base_url . 'tpl/' . dirname($view_path) . '/';

		$url = '(?:(?:\\{.*?\\})?[^{}]*?)*?'; // allow " inside {} for cases in which url contains {function="foo()"}

		$exp = array();
		$exp[] = '/<(link|a)(.*?)(href)="(' . $url . ')"/i';
		$exp[] = '/<(img|script|input)(.*?)(src)="(' . $url . ')"/i';
		$exp[] = '/<(form)(.*?)(action)="(' . $url . ')"/i';

		return preg_replace_callback($exp, function($matches) use ($path) {
			$tag  = $matches[1];
			$_    = $matches[2];
			$attr = $matches[3];
			$url  = $matches[4];
			$new_url = $this->rewrite_url($url, $tag, $path);

			return "<$tag$_$attr=\"$new_url\"";
		}, $view);

	}


	/**
	 * Render a given template page
	 * Reset $error
	 * @param string $view_path: view to render (path relative to tpl/)
	 * @return bool: whether rendering worked
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
		//chdir(dirname(__FILE__));
		//chdir(dirname($view));
		//$view = $this->load_included($view);
		//if (!is_null($this->error)) return FALSE;

		// Rewrite URLs
		$view = $this->rewrite_urls($view, $view_path);
		if (!is_null($this->error)) return FALSE;

		// Evaluate variables (todo)
		//$view = $this->eval_variables($view);
		//if (!is_null($this->error)) return FALSE;

		// Render view
		echo($view);

		return TRUE;
	}


}


