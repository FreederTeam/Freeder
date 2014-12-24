<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

require_once('../../constants.php');

/**
 * Configuration manager
 * If no user is loaded, use default values
 */
class Config {
	/**
	 * Default configuration
	 * @var array
	 */
	private static $default_config;

	/**
	 * Current loaded configuration
	 * @var array
	 */
	private $config;

	/**
	 * Last error that occured. Reset to null when methods use it (see doc)
	 * @var NULL | string
	 */
	public $error = NULL;


	/**
	 * Reset $error
	 */
	public function __construct() {
		global $DEFAULT_CONFIG;
		$this->error = NULL;

		self::$default_config = $DEFAULT_CONFIG;
		self::$default_config['timezone'] = @date_default_timezone_get();
		self::$default_config['base_url'] = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';
		self::$default_config['theme'] = rtrim(self::$default_config['theme'], '/') . '/';

		// Try to init
		if (!$this->init()) {
			// If it fails, try to install and then if it worked try to init again
			return $this->install() and $this->init();
		}

		return TRUE;
	}


	/**
	 * Install config (perform task that should never be repeated after that)
	 * Reset $error
	 * @return bool (whether installation worked)
	 *
	 * @todo (call for storage installation)
	 */
	public function install() {

	}


	/**
	 * Initialize config (perform task that should never be repeated after that)
	 * Reset $error
	 * @return bool (whether initialization worked)
	 *
	 * @todo (call for storage init)
	 */
	public function init() {

	}


	/**
	 * Retrieve option from loaded configuration.
	 * @param $option: Option to get from configuration
	 */
	public function get($option) {
		if (isset($this->config[$option])) {
			return $this->config[$option];
		} else if (isset(self::$default_config[$option])) {
			return self::$default_config[$option];
		}
		return FALSE;
	}


	/**
	 * Store option into loaded configuration.
	 * Use `save` to store permanently.
	 * @param $option: Option to set
	 * @param $value: New option value
	 */
	public function set($option, $value) {
		$this->config[$option] = $value;
	}


	/**
	 * Remove option entry.
	 * If a default value exists, it will be used next time this option is
	 * read.
	 * @param $option: Option to remove
	 */
	public function remove($option) {
		unset($this->$config[$option]);
	}


	/**
	 * Load configuration relative to a specific user.
	 * @param $user: User from which load config
	 */
	public function load($user) {
		$this->set('username', $user);
	}


	/**
	 * Save current configuration to the current user.
	 * You can set the user with `set('username', 'foo')`.
	 */
	public function save() {

	}
}


