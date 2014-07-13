<?php
$install_template =
'
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
<head>
    <meta charset="utf-8"/>
</head>
<body>
    <h1>Installation</h1>

    <form method="post" action="">
        <p><label for="login">Login: </label><input type="text" name="login" id="login"/></p>
        <p><label for="password">Password: </label><input type="password" name="password" id="password"/></p>
        <p><label for="timezone">Timezone : </label><input type="text" name="timezone" id="timezone" value="Europe/Paris"/></p>

        <p><input type="submit" value="Install !"/></p>
    </form>
</body>
</html>
';

$config_template =
"
<?php
define('DB_FILE', 'db.sqlite3');
?>
";


/**
 * Create data directory.
 */
function install_data_dir() {
	if (!file_exists(DATA_DIR)) {
		if (!mkdir(DATA_DIR) || !is_writable(DATA_DIR)) {
			die('error: Unable to create or write in data directory. Check the writing rights of Freeder root directory. The user who executes Freeder — www-data for instance — should be able to write in this directory. You may prefere to create the /data directory on your own and allow www-data to write only in /data instead of in the whole Freeder root.');
		}
	}
}


/**
 * Create configuration file in data directory.
 */
function install_config() {
	global $config_template;

    if (false === file_put_contents(DATA_DIR.'config.php', $config_template)) {
        die('error: Unable to create "'.DATA_DIR.'config.php". Check the writing rights in "'.DATA_DIR.'"');
    }
}


/**
 * Initialize database.
 */
function install_db() {
	// TODO: handle errors
	$dbh = new PDO('sqlite:'.DATA_DIR.DB_FILE);
    $dbh->query('PRAGMA foreign_keys = ON');

	$salt = uniqid(mt_rand(), true);
	$password = sha1($salt.$_POST['password']);

    $dbh->beginTransaction();

    // Create the table to handle users
    $dbh->query('CREATE TABLE IF NOT EXISTS users(
        id INTEGER PRIMARY KEY NOT NULL,
        login TEXT UNIQUE,
        password TEXT,
        salt TEXT,
        is_admin INT DEFAULT 0
    )');
	$query = $dbh->prepare('INSERT INTO users(id, login, password, salt, is_admin) VALUES("", :login, :password, :salt, 1)');
    $query->execute(array(
        ':login'=>$_POST['login'],
        ':password'=>$password,
        ':salt'=>$salt)
    );

    // Create the table to store config options
    $dbh->query('CREATE TABLE IF NOT EXISTS config(
        option TEXT UNIQUE COLLATE NOCASE,
        value TEXT
    )');
    // Insert timezone in the config
    $query = $dbh->prepare('INSERT INTO config(option, value) VALUES("timezone", :value)');
    $query->execute(array(':value'=>$_POST['timezone']));

	// Create the table to store feeds
	$dbh->query('CREATE TABLE IF NOT EXISTS feeds(
		id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
		title TEXT,
		url TEXT UNIQUE COLLATE NOCASE,
		links TEXT,
		description TEXT,
		ttl INT DEFAULT 0,
		image TEXT
	)');

	// Create table to store entries
	$dbh->query('CREATE TABLE IF NOT EXISTS entries(
		id INTEGER PRIMARY KEY NOT NULL,
		feed_id INTEGER NOT NULL,
		authors TEXT,
		title TEXT,
		links TEXT,
		description TEXT,
		content TEXT,
		enclosures TEXT,
		comments TEXT,
		guid TEXT UNIQUE,
		pubDate INTEGER,
		lastUpdate INTEGER,
		is_sticky INTEGER DEFAULT 0,
		is_read INTEGER DEFAULT 0,
		FOREIGN KEY(feed_id) REFERENCES feeds(id) ON DELETE CASCADE
	)');

	// Create table to store tags
	$dbh->query('CREATE TABLE IF NOT EXISTS tags(
		id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
		name TEXT UNIQUE COLLATE NOCASE,
		is_user_tag INTEGER DEFAULT 0
	)');

	// Create table to store association between tags and entries
	$dbh->query('CREATE TABLE IF NOT EXISTS tags_entries(
		tag_id INTEGER,
		entry_id INTEGER,
		FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE,
		FOREIGN KEY(entry_id) REFERENCES entries(id) ON DELETE CASCADE
	)');
	$dbh->query('CREATE TABLE IF NOT EXISTS tags_feeds(
		tag_id INTEGER,
		feed_id INTEGER,
		FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE,
		FOREIGN KEY(feed_id) REFERENCES feeds(id) ON DELETE CASCADE
	)');
	$dbh->commit();
	// TODO : Add indexes in db
}


/**
 * Proceed to Freeder installation.
 */
function install() {
    global $install_template;

    if (!empty($_POST['login']) && !empty($_POST['password']) && !empty($_POST['timezone'])) {
		install_data_dir();

		install_config();
        require(DATA_DIR.'config.php');

		install_db();

        header('location: index.php');
        exit();
    }
    else {
        echo $install_template;
    }
}

