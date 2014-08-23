<?php

/**
 *  Freeder Rewrite Engine
 *  ----------------------
 *
 *  @file
 *  @brief RewriteEngine class
 *  @version 0.1
 *  @copyright 2014 Freeder Team
 *  @license MIT (See the LICENSE file for copying permissions)
 */

class RewriteEngine {
	/**
	 * Path from domain root to freeder root.
	 *
	 * @var string
	 */
	static $rewrite_base = '/';


	/**
	 * URL-rewriting rules.
	 * A rule is made of 
	 *
	 * @var array
	 */
	protected $rules = array();


	/**
	 * Initialize rules.
	 */
	public function __construct() {
		$this->rules['tag/(.+)'] = 'index.php?view=\\$tag_$1';
		$this->rules['feed/(.+)'] = 'index.php?view=\\$feed_$1';
	}


	/**
	 * Use internal rules to rewrite url.
	 *
	 * @param $url URL to be rewritten
	 * @return New URL
	 */
	public function rewrite($url) {
		return $url;
	}


	/**
	 * Generate the htaccess rewrite rules
	 *
	 * @return htaccess rules
	 */
	public function generate_htaccess() {
		$rewrite_base = self::$rewrite_base;

		$rules = "<IfModule mod_rewrite.c>\n";
		$rules .= "  RewriteEngine On\n";
		$rules .= "  RewriteBase $rewrite_base\n";

		foreach($this->rules as $match => $query) {
			// Apache 1.3 does not support the reluctant (non-greedy) modifier.
			$match = str_replace('.+?', '.+', $match);
			$rules .= '  RewriteRule ^' . $match . '$ ' . $query . "\n";
		}
		$rules .= "</IfModule>";

		return $rules;
	}


	/**
	 * Write the rewrite rules in .htaccess file.
	 *
	 * @return null if everything is ok. An error message in other cases.
	 */
	public function write_htaccess() {
		$htaccess_filename = ROOT_DIR . '.htaccess';
		
		// Fail if file can't be written
		if (!is_writable($htaccess_filename) && (!is_writable(ROOT_DIR) || file_exists($htaccess_filename)))
			return "Unable to write in .htaccess file ($htaccess_filename)";

		$rules = $this->generate_htaccess();

		$begin_tag = '# BEGIN Freeder generated';
		$end_tag = '# END Freeder generated';

		$old_file = file_get_contents($htaccess_filename);

		// Change freeder content or append at the end of file.
		if (preg_match("/$begin_tag\n.*?$end_tag/s", $old_file)) {
			$tmp = explode($begin_tag, $old_file, 2);
			$before = rtrim($tmp[0], "\n");
			$tmp2 = explode($end_tag, $tmp[1], 2);
			$after = $tmp2[1];
		} else {
			$before = $old_file;
			$after = '';
		}

		$file = "$before\n\n$begin_tag\n$rules\n$end_tag\n$after";

		echo("$rules");

		file_put_contents($htaccess_filename, $file);

		return null;
		
	}

}