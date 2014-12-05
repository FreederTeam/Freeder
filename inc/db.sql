---
-- Create the table to handle users
---
CREATE TABLE IF NOT EXISTS users(
		id INTEGER PRIMARY KEY NOT NULL,
		login TEXT UNIQUE,
		password TEXT,
		salt TEXT,
		remember_token TEXT,
		is_admin INT DEFAULT 0
	);

---
-- Create the database to handle config
---
CREATE TABLE IF NOT EXISTS config(
		option TEXT UNIQUE COLLATE NOCASE,
		value TEXT
	);

---
-- Create the database to handle sharing options
---
CREATE TABLE IF NOT EXISTS sharing(
		type TEXT UNIQUE COLLATE NOCASE,
		url TEXT
	);

---
-- Create the table to store feeds
---
CREATE TABLE IF NOT EXISTS feeds(
		id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
		title TEXT,
		has_user_title INTEGER DEFAULT 0,  -- To specify wether the user edited the title manually or not
		url TEXT UNIQUE COLLATE NOCASE,  -- Feed URL
		links TEXT,  -- JSON array of links associated with the feed
		description TEXT,
		ttl INT DEFAULT 0,  -- This is the ttl of the feed, 0 means that it uses the config value
		has_user_ttl INT DEFAULT 0,  -- To specify wether the user edited the TTL manually or not
		image TEXT,
		post TEXT,
		import_tags_from_feed INTEGER DEFAULT 0 -- To specify wether to use tags from feed or not
	);
CREATE INDEX ix_feeds_url ON feeds (url);

---
-- Create the table to store entries
---
CREATE TABLE IF NOT EXISTS entries(
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
	);
CREATE INDEX ix_entries_guid ON entries (guid);

---
-- Create the table to store tags
---
CREATE TABLE IF NOT EXISTS tags(
		id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
		name TEXT UNIQUE COLLATE NOCASE
	);
CREATE INDEX ix_tags_name ON tags (name);

---
-- Create the table to associate tags and entries
---
CREATE TABLE IF NOT EXISTS tags_entries(
		tag_id INTEGER,
		entry_id INTEGER,
		auto_added_tag INTEGER DEFAULT 0,
		UNIQUE (tag_id, entry_id),
		FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE,
		FOREIGN KEY(entry_id) REFERENCES entries(id) ON DELETE CASCADE
	);
CREATE INDEX ix_tags_entries_tag_id ON tags_entries (tag_id);

---
-- Create the table to associate tags and feeds
---
CREATE TABLE IF NOT EXISTS tags_feeds(
		tag_id INTEGER,
		feed_id INTEGER,
		auto_added_tag INTEGER,
		UNIQUE (tag_id, feed_id),
		FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE,
		FOREIGN KEY(feed_id) REFERENCES feeds(id) ON DELETE CASCADE
	);
CREATE INDEX ix_tags_feeds_tag_id ON tags_feeds (tag_id);
