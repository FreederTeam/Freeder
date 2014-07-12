<?php
// TODO
//  * Tags for feeds
//  * Entry --guid-- => id
//  * Delete old tags

define('DATA_DIR', 'data/');
define('DB_FILE', 'db.sqlite3');

if(!is_file(DATA_DIR.DB_FILE)) {
    require('inc/install.inc.php');

    install();
    exit();
}

$bdd = new PDO('sqlite:'.DATA_DIR.DB_FILE);
$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// ===================================================
// Stuff for tests
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
// Initialize db
$query = $bdd->prepare('INSERT OR IGNORE INTO feeds(url) VALUES(:url)');
$query->bindParam(':url', $url);
foreach($feeds as $url) {
    $query->execute();
}
define('DEFAULT_TIMEZONE', 'Europe/Paris');  // TODO : Move it in the global conf
// ===================================================
// Real script again


date_default_timezone_set(DEFAULT_TIMEZONE);
require('inc/rain.tpl.class.php');
$tpl = new RainTPL;
require('inc/functions.inc.php');
require('inc/feeds.inc.php');

$time = microtime(true);
refresh_feeds($feeds);
var_dump(microtime(true) - $time);

$tpl->draw('index');
