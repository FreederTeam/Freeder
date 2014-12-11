<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

/**
 * The following constants can be modified to fit your needs
 */


// Default user configuration
$DEFAULT_CONFIG = array(
	// Template directory (relative to `tpl`)
	'theme' => 'zen',

	// Default method for feed synchronization.
	// (can be 'cron' or 'ajax')
	'synchronization_type' => 'cron',

	// Number of entries to keep per feed.
	// Set it to 0 if you want to keep all of them.
	'entries_per_feed' => 50,

	// Number of entries to display on a single page.
	// Set it to 0 if you want no limit (not recommended).
	'entries_per_page' => 20,

	// Display mode for entries.
	// (can be 'title', 'summary' or 'content')
	'entry_display_mode' => 'content',

	// Whether links to original articles must be opened in a new tab.
	'open_items_new_tab' => 0,

	// Whether articles must be marked as read whenever the user click on the
	// link to the original.
	'mark_read_click' => 0,

	// Whether home is publicly available.
	'anonymous_access' => 0,

	// Whether tags from feeds must be imported as freeder tags.
	'import_tags_from_feeds' => 0,
);


// Enable debug information
define('DEBUG', true);

// Current version number
define('CURRENT_VERSION', 'B0.1');

// Data directory (relative path from Freeder root)
define('DATA_DIR', 'data/');

// Database file (relative to DATA_DIR)
define('DB_FILE', 'db.sqlite3');


