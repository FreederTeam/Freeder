<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */


/**
 * Downloader tool
 * This downloader try to use Zebra_cURL if cURL is available. It falls back to
 * a sequential downloader if cURL is not available.
 */
class Downloader {
	/**
	 * Check whether `curl` PHP module is available or not.
	 *
	 * @return `true` if the `curl` module is available, `false` otherwise.
	 */
	static function has_curl() {
		return extension_loaded('curl');
	}

	/**
	 * Check whether cURL extension is available or not, and build the object
	 * accordingly
	 */
	public function __construct() {
		if (self::has_curl()) {
			$this->dl = new Zebra_cURL($htmlentities=false);
			$this->dl->cache('cache', 60);
		}
		else {
			$this->dl = NULL;
		}
	}

	/**
	 * Sequential downloader.
	 * @param	$url		A single URL.
	 * @param	$callback	Callback called with the result.
	 * @todo	Not a really good implementation…
	 */
	private function _sequential_download($url, $callback) {
		$result = new stdClass;
		$result->info = array();
		$result->headers = array("last_request"=>array(), "responses"=>$http_response_header);
		$result->body = file_get_contents($url);
		$callback($result);
	}

	/**
	 * Mimmick the behaviour of Zebra_cURL's get() using cURL if available,
	 * sequential download otherwise.
	 * @param	$url		Either a single URL or an array of URL.
	 * @param	$callback	Callback function called after download.
	 */
	public function get($url, $callback) {
		if ($this->dl !== NULL) {
			$this->dl->get($url, $callback);
		}
		else {
			if (is_array($url)) {
				foreach ($url as $u) {
					_sequential_download($u, $callback);
				}
			}
			elseif (false === filter_var($url, FILTER_VALIDATE_URL)) {
				_sequential_download($url, $callback);
			}
			else {
				return;
			}
		}
	}
}
