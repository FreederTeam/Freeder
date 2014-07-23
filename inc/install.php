<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to install the script
 */

$default_timezone = @date_default_timezone_get();
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
		<p><label for="timezone">Timezone : </label><input type="text" name="timezone" id="timezone" value="'.$default_timezone.'"/></p>

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
 * Create a directory, checking writeable and the rights.
 */
function install_dir($dir) {
	if (!file_exists($dir)) {
		if (!mkdir($dir) || !is_writable($dir)) {
			$current_user = get_current_user();
			exit('error: Unable to create or write in data directory. Check the writing rights of Freeder root directory. The user who executes Freeder — '.$current_user.' — should be able to write in this directory. You may prefere to create the /data directory on your own and allow '.$current_user.' to write only in /data instead of in the whole Freeder root.');
		}
	}
}


/**
 * Create configuration file in data directory.
 */
function install_config() {
	global $config_template;

	if (false === file_put_contents(DATA_DIR.'config.php', $config_template)) {
		exit('error: Unable to create "'.DATA_DIR.'config.php". Check the writing rights in "'.DATA_DIR.'"');
	}
}


/**
 * Initialize database.
 *
 * @todo
 *	  * handle errors
 *	  * add indexes in db ?
 */
function install_db() {
	$dbh = new PDO('sqlite:'.DATA_DIR.DB_FILE);
	$dbh->query('PRAGMA foreign_keys = ON');

	$salt = uniqid(mt_rand(), true);
	$password = sha1($salt.$_POST['password']);

	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$dbh->beginTransaction();

	// Create the table to handle users
	$dbh->query('CREATE TABLE IF NOT EXISTS users(
		id INTEGER PRIMARY KEY NOT NULL,
		login TEXT UNIQUE,
		password TEXT,
		salt TEXT,
		is_admin INT DEFAULT 0
	)');
	$query = $dbh->prepare('INSERT INTO users(login, password, salt, is_admin) VALUES(:login, :password, :salt, 1)');
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
		has_user_title INTEGER DEFAULT 0,  -- To specify wether the user edited the title manually or not
		url TEXT UNIQUE COLLATE NOCASE,  -- Feed URL
		links TEXT,  -- JSON array of links associated with the feed
		description TEXT,
		ttl INT DEFAULT 0,  -- This is the ttl of the feed, 0 means that it uses the config value
		has_user_ttl INT DEFAULT 0,  -- To specify wether the user edited the TTL manually or not
		image TEXT,
		post TEXT
	)');

	// Create table to store entries
	$dbh->query('CREATE TABLE IF NOT EXISTS entries(
		id INTEGER PRIMARY KEY NOT NULL,
		feed_id INTEGER NOT NULL,
		authors TEXT,
		title TEXT,
		links TEXT,  -- JSON array of enclosed links
		description TEXT,
		content TEXT,
		enclosures TEXT,  -- JSON array of links to enclosures
		comments TEXT,  -- Link to comments
		guid TEXT UNIQUE,
		pubDate INTEGER,
		lastUpdate INTEGER,
		FOREIGN KEY(feed_id) REFERENCES feeds(id) ON DELETE CASCADE
	)');

	// Create table to store tags
	$dbh->query('CREATE TABLE IF NOT EXISTS tags(
		id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
		name TEXT UNIQUE COLLATE NOCASE
	)');
	$dbh->query('INSERT INTO tags(name) VALUES("_sticky")');
	$dbh->query('INSERT INTO tags(name) VALUES("_read")');
	$dbh->query('INSERT INTO tags(name) VALUES("_private")');
	$dbh->query('INSERT INTO tags(name) VALUES("_no_home")');

	// Create table to store association between tags and entries
	$dbh->query('CREATE TABLE IF NOT EXISTS tags_entries(
		tag_id INTEGER,
		entry_id INTEGER,
		auto_added_tag INTEGER DEFAULT 0,
		UNIQUE (tag_id, entry_id),
		FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE,
		FOREIGN KEY(entry_id) REFERENCES entries(id) ON DELETE CASCADE
	)');
	$dbh->query('CREATE TABLE IF NOT EXISTS tags_feeds(
		tag_id INTEGER,
		feed_id INTEGER,
		auto_added_tag INTEGER,
		UNIQUE (tag_id, feed_id),
		FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE,
		FOREIGN KEY(feed_id) REFERENCES feeds(id) ON DELETE CASCADE
	)');

	$dbh->query('DROP TABLE views');
	// Create the table to store views
	$dbh->query('CREATE TABLE IF NOT EXISTS views(
		id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
		name TEXT,
		rule TEXT, -- Specifies what to display. See RFF 4 for more info
		isPublic INT DEFAULT 0 -- Whether the view is publicly available
		-- theme TEXT,
		-- displayStyle INT (Title only, Summary, Full text)
	)');

	$dbh->query('INSERT INTO views(name, rule) VALUES
		("_home", "+$all -_read -_no_home BY -$pubDate"),
		("_public", "+$all -_private BY -$pubDate")
	');

	$dbh->commit();

}


/**
 * Proceed to Freeder installation.
 */
function install() {
	global $install_template;

	if (!empty($_POST['login']) && !empty($_POST['password']) && !empty($_POST['timezone'])) {
		install_dir(DATA_DIR);
		install_dir('tmp');

		install_config();
		require_once(DATA_DIR.'config.php');

		install_db();

		$_SESSION['user'] = new stdClass;
		$_SESSION['user']->login = $_POST['login'];
		$_SESSION['is_admin'] = 1;
	}
	else {
		echo $install_template;
		exit();
	}
}

