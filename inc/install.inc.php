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

	if (!is_file(DATA_DIR.'config.php')) {
		if (false === file_put_contents(DATA_DIR.'config.php', $config_template)) {
			die('error: Unable to create "'.DATA_DIR.'config.php". Check the writing rights in "'.DATA_DIR.'"');
		}
	}
}


/**
 * Initialize database.
 */
function install_db() {
	// TODO: handle errors
	$dbh = new PDO('sqlite:'.DATA_DIR.DB_FILE);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	$salt = uniqid(mt_rand(), true);
	$password = sha1($salt.$_POST['password']);

	$dbh->beginTransaction();
	$dbh->query('CREATE TABLE IF NOT EXISTS users(id integer, login text, password text, salt text, admin int)');
	$query = $dbh->prepare('INSERT INTO users(id, login, password, salt, admin) VALUES("", :login, :password, :salt, 1)');
	$query->bindValue(':login', $_POST['login']);
	$query->bindValue(':password', $password);
	$query->bindValue(':salt', $salt);
	$query->execute();

	$dbh->query('PRAGMA foreign_keys = ON');
	$dbh->query('CREATE TABLE IF NOT EXISTS feeds(
		id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
		title TEXT,
		url TEXT UNIQUE COLLATE NOCASE,
		links TEXT,
		description TEXT,
		ttl INT DEFAULT 0,
		image TEXT
	)');
	$dbh->query('CREATE UNIQUE INDEX IF NOT EXISTS url ON feeds(url)');
	// TODO : skip ? language ? (not in Atom)
	$dbh->query('CREATE TABLE IF NOT EXISTS entries(
		feed_id INTEGER NOT NULL,
		authors TEXT,
		title TEXT,
		links TEXT,
		description TEXT,
		content TEXT,
		enclosures TEXT,
		comments TEXT,
		guid TEXT PRIMARY KEY NOT NULL,
		pubDate INTEGER,
		lastUpdate INTEGER,
		is_sticky INTEGER DEFAULT 0,
		is_read INTEGER DEFAULT 0,
		FOREIGN KEY(feed_id) REFERENCES feeds(id) ON DELETE CASCADE
	)');
	// TODO : comments ? (not in Atom ?)
	$dbh->query('CREATE TABLE IF NOT EXISTS tags(
		id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
		name TEXT UNIQUE COLLATE NOCASE
	)');
	$dbh->query('CREATE TABLE IF NOT EXISTS tags_entries(
		tag_id INTEGER,
		entry_guid TEXT,
		FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE,
		FOREIGN KEY(entry_guid) REFERENCES entries(guid) ON DELETE CASCADE
	)');
	$dbh->query('CREATE TABLE IF NOT EXISTS tags_feeds(
		tag_id INTEGER,
		feed_id INTEGER,
		FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE,
		FOREIGN KEY(feed_id) REFERENCES feeds(id) ON DELETE CASCADE
	)');
	// TODO : Add indexes in db
	$dbh->commit();
}


/**
 * Proceed to Freeder installation.
 */
function install() {
    global $install_template;

    if (!empty($_POST['login']) && !empty($_POST['password'])) {
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

