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
class Settings {
	/**
	 * Default configuration
	 * @var stdClass
	 */
	private static $default_settings = (object) array(
		// Template directory (relative to `tpl`)
		'theme' => (object) array('value'=>'zen', 'type'=>''),

		// Default method for feed synchronization.
		// (can be 'cron' or 'ajax')
		'synchronization_type' => (object) array('value'=>'cron', 'type'=>''),

		// Number of entries to keep per feed.
		// Set it to 0 if you want to keep all of them.
		'entries_per_feed' => (object) array('value'=>50, 'type'=>''),

		// Number of entries to display on a single page.
		// Set it to 0 if you want no limit (not recommended).
		'entries_per_page' => (object) array('value'=>20, 'type'=>''),

		// Display mode for entries.
		// (can be 'title', 'summary' or 'content')
		'entry_display_mode' => (object) array('value'=>'content', 'type'=>''),

		// Whether links to original articles must be opened in a new tab.
		'open_items_new_tab' => (object) array('value'=>0, 'type'=>''),

		// Whether articles must be marked as read whenever the user click on the
		// link to the original.
		'mark_read_click' => (object) array('value'=>0, 'type'=>''),

		// Whether home is publicly available.
		'anonymous_access' => (object) array('value'=>0, 'type'=>''),

		// Whether tags from feeds must be imported as freeder tags.
		'import_tags_from_feeds' => (object) array('value'=>0, 'type'=>''),

		// Date timezone
		'timezone' => (object) array('value'=>@date_default_timezone_get(), 'type'=>''),

		// Base URL of the current Freeder installation
		'base_url' => (object) array('value'=>rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/', 'type'=>''),
	);

	/**
	 * Current loaded configuration
	 * @var stdClass of Beans indexed by setting name
	 */
	private $settings = new stdClass;

	private $user;

	public function __construct($user) {
		$this->user = $user;
		// Try to init
		if (!$this->load()) {
			// If it fails, try to install and then if it worked try to init again
			return $this->install();
		}

		return true;
	}


	/**
	 * Install config (perform task that should never be repeated after that)
	 * @return bool (whether installation worked)
	 *
	 * @todo (call for storage installation)
	 */
	public function install() {

	}


	/**
	 * Retrieve setting value.
	 * @param	$name		Name of the setting. If not provided, all settings are retrieved.
	 */
	public function get($names="") {
		if (empty($names)) {
			$names = array_keys($default_settings);
		}
		elseif (!is_array($names)) {
			$names = array($names);
		}

		$return = [];
		foreach($names as $name) {
			if (!empty($this->settings->$name)) {
				$tmp = new stdClass();
				$tmp->name = $this->settings->$name->name;
				$tmp->value = $this->settings->$name->value;
				$tmp->type = self::$default_settings->$name->type;
				$return[] = $tmp;
			} else if (!empty(self::$default_settings->$name)) {
				$return[] =  self::$default_settings->$name;
			}
		}

		if (count($return) == 0) {
			return false;
		}
		elseif (count($return) == 1) {
			return $return[0];
		}
		else {
			return $return;
		}
	}


	/**
	 * Store setting value.
	 * Use `save` to store permanently.
	 * @param	$name	Name of the setting.
	 * @param	$value	New setting value.
	 */
	public function set($name, $value) {
		if (empty($this->settings->$name) && !empty(self::$default_settings->$name)) {
			$this->settings->$name = R::dispense('setting');
			$this->settings->$name->name = $name;
		}
		$this->settings->$name->value = $value;
		$this->settings->$name->user = $this->user;
	}


	/**
	 * Remove setting and delete it from db.
	 * If a default value exists, it will be used next time this option is
	 * read.
	 * @param	$name	Name of the setting to remove.
	 */
	public function remove($name) {
		if (!empty($this->setting->$name)) {
			R::trash($this->setting->$name);
			unset($this->setting->$name);
		}
	}


	/**
	 * Load settings relative to a specific user.
	 */
	public function load() {
		$settings = R::findAll('setting', 'user = ?', [$this->user]);
		foreach ($settings as $setting) {
			$name = $setting->name;
			$this->settings->$name = $setting;
		}
		return count($settings) > 0;
	}


	/**
	 * Save current settings to the current user.
	 */
	public function save() {
		foreach ($this->settings as $setting) {
			R::store($setting);
		}
	}
}


namespace Model;

class Setting extends \RedbeanPHP\SimpleModel {}
