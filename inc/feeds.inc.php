<?php
// TODO
//  * Tags for feeds
//  * Entry --guid-- => id
//  * Delete old tags

$feeds = array(
    1=>"http://www.0x0ff.info/feed/",
    2=>"http://a3nm.net/blog/feed.xml",
/*    "http://alias.codiferes.net/wordpress/index.php/feed/",
    "http://blog.exppad.com/feeds",
    "http://feeds.feedburner.com/codinghorror",
    "http://www.glazman.org/weblog/dotclear/index.php?feed/rss2",
    "http://www.maitre-eolas.fr/feed/atom",
    "http://feeds.feedburner.com/KorbensBlog-UpgradeYourMind?format=xml",
    "http://lkdjiin.github.io/atom.xml",
    "http://shebang.ws/feed.xml",
    "http://phyks.me/rss.xml",
    "http://sametmax.com/feed/",
    "http://standblog.org/blog/feed/rss2",
    "http://electrospaces.blogspot.com/feeds/posts/default",
    "http://feeds.feedburner.com/fubiz",
    "http://feeds.feedburner.com/ILoveTypography",
    "http://lehollandaisvolant.net/rss.php?mode=links",
    "http://reflets.info/feed/",
    "http://wtfevolution.tumblr.com/rss",
    "http://xkcd.com/atom.xml",
    "http://blog.idleman.fr/feed/",
    "http://jjacky.com/rss.xml",
    "http://lehollandaisvolant.net/rss.php?full",
    "http://sebsauvage.net/rss/updates.xml",
    "http://tomcanac.com/feed/",
    "http://blog.rom1v.com/feed/",
    "http://www.framablog.org/index.php/feed/atom",
    "https://www.archlinux.org/feeds/news/",
    "http://git.zx2c4.com/cgit/atom/?h=master",
    "http://blog.finalterm.org/feeds/posts/default",
    "https://github.com/tmos/greeder/commits/master.atom",
    "https://github.com/ldleman/Leed/commits/master.atom",
    "https://github.com/ldleman/Leed-market/commits/master.atom",
    "http://owncloud.org/feed/",
    "http://roundcube.net/feeds/atom.xml",
    "https://github.com/broncowdd/SnippetVamp/commits/master.atom",
    "http://www.websvn.info/news.atom.xml",*/
    3=>"https://phyks.me/rss.xml"
);
$bdd = new PDO('sqlite:data/db.sqlite');
$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$bdd->query('PRAGMA foreign_keys = ON');
$bdd->query('CREATE TABLE IF NOT EXISTS feeds(
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    title TEXT,
    url TEXT UNIQUE,
    links TEXT,
    description TEXT,
    ttl INT DEFAULT 0,
    image TEXT
)');
$bdd->query('CREATE UNIQUE INDEX IF NOT EXISTS url ON feeds(url)');
// TODO : skip ? language ? (not in Atom)
$bdd->query('CREATE TABLE IF NOT EXISTS entries(
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
$bdd->query('CREATE TABLE IF NOT EXISTS tags(
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    name TEXT UNIQUE
)');
$bdd->query('CREATE TABLE IF NOT EXISTS tags_entries(
    tag_id INTEGER,
    entry_guid TEXT,
    FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    FOREIGN KEY(entry_guid) REFERENCES entries(guid) ON DELETE CASCADE
)');
$bdd->query('CREATE TABLE IF NOT EXISTS tags_feeds(
    tag_id INTEGER,
    feed_id INTEGER,
    FOREIGN KEY(tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    FOREIGN KEY(feed_id) REFERENCES feeds(id) ON DELETE CASCADE
)');
// TODO : Add indexes in db


// Initialize db
$query = $bdd->prepare('INSERT OR IGNORE INTO feeds(url) VALUES(:url)');
$query->bindParam(':url', $url);
foreach($feeds as $url) {
    $query->execute();
}


date_default_timezone_set('Europe/Paris');

require('feed2array.inc.php');

function curl_downloader($urls) {
    /* Downloads all the urls in the array $urls and returns an array with the results and the http status_codes.
     *
     * Mostly inspired by blogotext by timovn : https://github.com/timovn/blogotext/blob/master/inc/fich.php
     */
    // Chunks of 40 urls because curl has problems with too big "multi" requests
    $chunks = array_chunk($urls, 40, true);
    $results = array();
    $status_codes = array();

    if(ini_get('open_basedir') == '' && ini_get('safe_mode') === false) {
        $follow_redirect = true;
    }
    else {
        $follow_redirect = false;
    }

    foreach($chunks as $chunk) {
        $multihandler = curl_multi_init();
        $handlers = array();
        $total_feed_chunk = count($chunk) + count($results);

        foreach ($chunk as $i=>$url) {
            set_time_limit(20); // Reset max execution time
            $handlers[$i] = curl_init($url);
            curl_setopt_array($handlers[$i], array(
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_CONNECTTIMEOUT => 10, // 0 = indefinitely
                CURLOPT_TIMEOUT => 15,
                CURLOPT_FOLLOWLOCATION => $follow_redirect,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
            ));
            curl_multi_add_handle($multihandler, $handlers[$i]);
        }

        do {
            curl_multi_exec($multihandler, $active);
            curl_multi_select($multihandler);
        } while ($active > 0);

        foreach ($chunk as $i=>$url) {
            $results[$url] = curl_multi_getcontent($handlers[$i]);
            $status_codes[$url] = curl_getinfo($handlers[$i], CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($multihandler, $handlers[$i]);
            curl_close($handlers[$i]);
        }
        curl_multi_close($multihandler);
    }

    return array('results'=>$results, 'status_codes'=>$status_codes);
}


function refresh_feeds($feeds) {
    /* Refresh the specified feeds and returns an array with URLs in error */
    global $bdd;

    $download = curl_downloader($feeds);
    $errors = array();
    foreach($download['status_codes'] as $url=>$status_code) {
        if($status_code != 200) {
            $errors[] = $url;
        }
    }

    $updated_feeds = $download['results'];

    $bdd->beginTransaction();
    $query_feeds = $bdd->prepare('UPDATE feeds SET title=:title, links=:links, description=:description, ttl=:ttl, image=:image WHERE url=:old_url');
    $query_feeds->bindParam(':title', $feed_title);
    $query_feeds->bindParam(':links', $feed_links);
    $query_feeds->bindParam(':description', $feed_description);
    $query_feeds->bindParam(':ttl', $feed_ttl, PDO::PARAM_INT);
    $query_feeds->bindParam(':image', $image);
    $query_feeds->bindParam(':old_url', $url);

    // Two queries, for update or insert
    $query_ensure_entries = $bdd->prepare('INSERT OR IGNORE INTO entries(feed_id, guid) VALUES(:feed_id, :guid)');
    $query_ensure_entries->bindParam(':guid', $guid);
    $query_ensure_entries->bindParam(':feed_id', $i, PDO::PARAM_INT);
    $query_entries = $bdd->prepare('UPDATE entries SET authors=:authors, title=:title, links=:links, description=:description, content=:content, enclosures=:enclosures, comments=:comments, pubDate=:pubDate, lastUpdate=:lastUpdate WHERE guid=:guid');
    $query_entries->bindParam(':authors', $authors);
    $query_entries->bindParam(':title', $title);
    $query_entries->bindParam(':links', $links);
    $query_entries->bindParam(':description', $description);
    $query_entries->bindParam(':content', $content);
    $query_entries->bindParam(':enclosures', $enclosures);
    $query_entries->bindParam(':comments', $comments);
    $query_entries->bindParam(':guid', $guid);
    $query_entries->bindParam(':pubDate', $pubDate, PDO::PARAM_INT);
    $query_entries->bindParam(':lastUpdate', $last_update, PDO::PARAM_INT);

    $query_insert_tag = $bdd->prepare('INSERT OR IGNORE INTO tags(name) VALUES(:name)');
    $query_insert_tag->bindParam(':name', $tag_name);
    $query_select_tag = $bdd->prepare('SELECT id FROM tags WHERE name=:name');
    $query_select_tag->bindParam(':name', $tag_name);

    $query_tags = $bdd->prepare('INSERT INTO tags_entries(tag_id, entry_guid) VALUES(:tag_id, :entry_guid)');
    $query_tags->bindParam(':tag_id', $tag_id, PDO::PARAM_INT);
    $query_tags->bindParam(':entry_guid', $guid);

    foreach($updated_feeds as $url=>$feed) {
        $i = array_search($url, $feeds);
        $parsed = feed2array($feed);
        if(empty($parsed) || $parsed === false) {
            $errors[] = $url;
        }
        $feed_title = isset($parsed['infos']['title']) ? $parsed['infos']['title'] : '';
        $feed_links = isset($parsed['infos']['links']) ? json_encode($parsed['infos']['links']) : '';
        $feed_description = isset($parsed['infos']['description']) ? $parsed['infos']['description'] : '';
        $feed_ttl = isset($parsed['infos']['ttl']) ? $parsed['infos']['ttl'] : 0;
        $feed_image = isset($parsed['infos']['image']) ? json_encode($parsed['infos']['image']) : '';
        $query_feeds->execute();

        // Update entries
        $items = $parsed['items'];
        foreach($items as $event) {
            $authors = isset($event['authors']) ? json_encode($event['authors']) : '';
            $title = isset($event['title']) ? $event['title'] : '';
            $links = isset($event['links']) ? json_encode($event['links']) : '';
            $description = isset($event['description']) ? $event['description'] : '';
            $content = isset($event['content']) ? $event['content'] : '';
            $enclosures = isset($event['enclosures']) ? json_encode($event['enclosures']) : '';
            $comments = isset($event['comments']) ? $event['comments'] : '';
            if(empty($event['guid'])) {
                continue;
            }
            $guid = $event['guid'];
            $pubDate = isset($event['pubDate']) ? $event['pubDate'] : '';
            $last_update = isset($event['updated']) ? $event['updated'] : '';

            $query_ensure_entries->execute();
            $query_entries->execute();
            if($query_entries->rowCount() == 0) {
                continue;
            }

            if(!empty($event['categories'])) {
                foreach($event['categories'] as $tag_name) {
                    $query_insert_tag->execute();
                    $query_select_tag->execute();
                    $query_select_tag->execute();
                    $tag_id = $query_select_tag->fetch();
                    $tag_id = $tag_id['id'];
                    $query_tags->execute();
                }
            }
        }
    }
    $bdd->commit();

    return $errors;
}
