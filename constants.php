<?php

// The following constants can be modified to fit your needs
define('RELATIVE_DATA_DIR', 'data/'); // Data directory (relative path from Freeder root)
define('RELATIVE_TPL_DIR',  'tpl/');  // Template directory (relative path from Freeder root)

define('DB_FILE', 'db.sqlite3'); // Database file (relative to DATA_DIR)

define('DEFAULT_TEMPLATE', 'default'); // Default template dir (relative to TPL_DIR)

define('DEBUG', true);


// Beyond this point, constants are defined for technical purpose or depend on the previous one.
define('INC_DIR',  ROOT_DIR . 'inc/'); // Should not be modified!
define('DATA_DIR', ROOT_DIR . RELATIVE_DATA_DIR);
define('TPL_DIR',  ROOT_DIR . RELATIVE_TPL_DIR);


