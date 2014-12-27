<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

require_once dirname(__FILE__)."/../core/Downloader.php";
require_once dirname(__FILE__)."/../core/feed2array.php";
require_once dirname(__FILE__)."/../core/favicons.php";
require_once dirname(__FILE__)."/../Models/Feed.php";


class InvalidFeed extends Exception {
	public $http_code = 0;
}
class FeedAlreadyExists extends Exception { }
class FeedHasMoved extends Exception {
	public $new_url = "";
}


/**
* Try to get the feed URL associated to a URL to a HTML page, by parsing the header.
*
* @param an array $urls of {'url'=>URL}
* @return an array {'urls', 'errors'}. `errors` is an array of URLs for which there could not be any fetched feed. `urls` is an array with input URLs as keys and f{'url'=>URL} as values.
 */
function get_feed_urls_from_html($url) {
	$downloader = new DownloaderL();
	$feed_urls = array();
	$downloader->get($url, function ($result) use (&$feed_urls) {
		if ($result->info['http_code'] != 200) {
			return false;
		}
		$content = $result->body;
		$content = substr($content, 0, strpos($content, '</head>')).'</head></html>'; // We don't need the full page, just the <head>
		$html = new DOMDocument();
		$html->strictErrorChecking = false;
		$html->loadHTML($content);
		if (false === $html) {
			throw new Exception("Invalid HTML content.");
		}
		$xpath = new DOMXPath($html);
		if (false === $xpath) {
			throw new Exception("Invalid HTML content.");
		}
		$feed_links = $xpath->query('/html/head//link[@rel="alternate" and (contains(@type, "rss") or contains(@type, "atom"))]/@href');
		foreach ($feed_links as $f) {
			$feed_urls[] = $f->value;
		}
	});
	return $feed_urls;
}


/**
 * Callback function to handle the downloaded feed and add it in db.
 */
function import_downloaded_feed($result, $feeds) {
	// TODO:
	//	* Handle microformats 2
	$user = "";
	if ($result->info['http_code'] != 200) {
		// If unable to fetch the feed, give up
		$e = new InvalidFeed("Invalid feed $url. Got HTTP code: $result->info['http_code'].");
		$e->http_code = $result->info['http_code'];
		throw $e;
	}

	$url = $result->info['url'];

	// First, try to parse as a standard RSS / ATOM feed
	$parsed_feed = feed2array($result->body);
	if ($parsed_feed === false || empty($parsed_feed['infos']) || empty($parsed_feed['items'])) {
		// Invalid feed, try to find an URL to the feed
		try {
			$feed_urls = get_feed_urls_from_html($url);
		}
		catch (Exception $e) {
			$feed_urls = array();
		}

		if (!empty($feed_urls)) {
			$downloader = new Downloader();
			foreach ($feed_urls as $feed_url) {
				try {
					$downloader->get($feed_url, function ($result) use ($feeds) {
						try {
							import_downloaded_feed($result, $feeds);
							throw new BreakLoop();
						}
						catch (InvalidFeed $e) {
						}
						catch (FeedAlreadyExists $e) {
							throw $e;
						}
						catch (FeedHasMoved $e) {
							throw $e;
						}
					});
				}
				catch (BreakLoop $e) {
					return;
				}
			}
			$e = new InvalidFeed("Invalid feed $url. Can't parse it.");
			throw $e;
		}
		else {
			$e = new InvalidFeed("Invalid feed $url. Can't parse it.");
			throw $e;
		}
	}

	// If feed already exists
	if (null !== R::findOne('user_feed', 'url = ? AND user = ?', [$result->info['url'], $user])) {  // TODO: user match
		$e = new FeedAlreadyExists("Feed $url already exists.");
		throw $e;
	}

	// Retrieve (or dispense) a base feed
	$base_feed = R::findOne('base_feed', 'url = ?', [$result->info['url']]);
	if (null === $base_feed) {
		$base_feed = R::dispense('base_feed');
		$base_feed->populate_from_array($parsed_feed, false);
		$base_feed->url = $url;
		$favicons = get_favicons($url);
		$base_feed->images[] = array_map($favicons['favicons'][$url], function ($e) {
			$f = array();
			$f['url'] = $e['favicon_url'];
			$f['title'] = 'favicon_'.$e['sizes'];
			return $f;
		});
		R::store($base_feed);
	}

	$user_feed = R::dispense('user_feed');
	$user_feed->user = $user;  // TODO
	$user_feed->feed = $base_feed;

	if (isset($feeds[$url]['ttl'])) {
		$user_feed->ttl = intval($feeds[$url]['ttl']);
	}
	if (isset ($feeds[$url]['post'])) {
		$user_feed->post = $feeds[$url]['post'];
	}

	if (isset ($feeds[$url]['import_tags_from_feeds'])) {
		$user_feed->post = $feeds[$url]['import_tags_from_feeds'];
	}

	R::store($user_feed);

	if ($result->info['redirect_count'] > 0) {
		$original_url = $result->info['original_url'];
		$e = new FeedHasMoved("Feed $original_url has moved to $url.");
		$e->new_url = $url;
		throw $e;
	}
}


/**
 * Get a list of all the available feeds.
 */
$app->get('/feeds', function () {
	$feeds = R::findAll('feed');
	if (!empty($_GET['updated'])) {
		update_feeds($feeds);
	}
	var_dump($feeds); // TODO
});


/**
 * Get all the infos about the specific feed.
 */
$app->get('/feeds/:id', function ($id) {
	$feed = R::load('feed', $id);
	if (!empty($_GET['updated'])) {
		update_feeds($feed);
	}
	$feed = $feed->export();
	$feed['entries_url'] = "/feeds/$id/entries";  // TODO
	$feed['tags_url'] = "/feeds/$id/tags";  // TODO
	echo json_encode($feed);
});


/**
 * Get all the entries associated to specified feed.
 *
 * @todo:
 *	* ?filter
 *	* ?slice
 */
$app->get('/feeds/:id/entries', function ($id) {
	$entries = R::findAll('entry', 'feed_id = ? ORDER BY pub_date', [$id]);  // TODO; Sort with last update
	var_dump($entries);  // TODO
});


/**
 * Get all the tags associated to specified feed.
 */
$app->get('/feeds/:id/tags', function ($id) {
	$feed = R::load('feed', $id);
	$tags = R::tag($feed);
	echo json_encode($tags);
});


/**
 * Delete the specified feed and associated data.
 */
$app->delete('/feeds/:id', function ($id) {
	$feed = R::trash(R::load('feed', $id));
});


/**
 * Update the specified feed with the newly provided infos
 */
$app->patch('/feeds/:id', function ($id) {
	// TODO
});


/**
 * Add new feeds
 */
$app->post('/feeds/', function() use ($app) {
	$feed_errors = 0;

	$feeds_in = json_decode($_POST['feeds'], true);
	if ($feeds_in === NULL) {
		$app->response->setStatus(400);
		return;
	}
	if (count(array_filter($feeds_in, 'is_array')) != count($feeds_in)) {
		// If not an array of feed objects, create an array
		$feeds_in = array($feeds_in);
	}

	$feeds = [];
	foreach ($feeds as $feed) {
		$feeds[$feed['url']] = $feed;
	}

	// Check for invalid URLs provided
	$invalid_urls = array_filter($feeds, function ($f) { return false === filter_var($f['url'], FILTER_VALIDATE_URL); });
	if (count($invalid_urls) > 0) {
		$app->response->setStatus(404);
		return;
	}

	// Retrieve all the provided URLs
	$urls = array_keys($feeds);

	// Closure to handle extra parameters passing
	$import_downloaded_feed = function ($result) use ($feeds, &$feed_errors) {
		try {
			import_downloaded_feed($result, $feeds);
		}
		catch (InvalidFeed $e) {
			// TODO
			$feed_errors++;
		}
		catch (FeedAlreadyExists $e) {
			// TODO
			$feed_errors++;
		}
		catch (FeedHasMoved $e) {
			// TODO
		}
	};
	$downloader = new Downloader();
	$downloader->get($urls, $import_downloaded_feed);

	if ($feed_errors == 0) {
		$app->response->setStatus(201);
	}
	else {
		// TODO
	}
});
