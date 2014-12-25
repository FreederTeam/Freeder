<?php
/**
 *  @copyright 2014-2015 Freeder Team
 *  @version   B0.1
 *  @license   MIT (See the LICENSE file for copying permissions)
 */

require_once dirname(__FILE__)."/../core/Downloader.php";
require_once dirname(__FILE__)."/../core/feed2array.php";
require_once dirname(__FILE__)."/../Models/Feed.php";


/**
 * Get all feeds
 */
$app->get('/feeds/', function () {
	$feeds = R::findAll('feed');
	print_r($feeds);  // TODO
});


/**
 * Get a feed by its id
 */
$app->get('/feeds/:id', function ($id) {
	$feed = R::load('feed', $id);
	echo json_encode($feed->export());
});


/**
 * Delete a feed by its id
 */
$app->delete('/feeds/:id', function ($id) {
	$feed = R::trash(R::load('feed', $id));
});


/**
 * Callback function to handle the downloaded feed and add it in db.
 */
function import_downloaded_feed($result) {
	$parsed_feed = feed2array($result->body);
	if ($parsed_feed === false || empty($parsed_feed['infos']) || empty($parsed_feed['items'])) {
		// TODO: Error handling
		return false;
	}

	if (null !== R::findOne('feed', 'url = ?', [$result->info['url']])) {
		// Already exists
		exit('Feed already exists.'); // TODO
	}

	// Populate a feed from the parsed body
	$feed = R::dispense('feed');
	$feed->populate_from_array($parsed_feed, false);  // TODO : import tags

	// Complete with extra params
	$feed->has_user_title = 0;
	$feed->url = $result->info['url'];  // TODO: Compare to original_url to find redirections
	$feed->has_user_ttl = 0;  // TODO
	$feed->post = array();  // TODO

	// TODO: Handle output of multiple import as JSON
	echo R::store($feed);
}


/**
 * Add new feeds
 * @todo
 *	* Handle user TTL
 *	* Handle user title
 *	* Handle POST
 *	* Handle import_tag_from_feed
 */
$app->post('/feeds/', function() use ($app) {
	if (!empty($_POST['url'])) {
		if (false === filter_var($_POST['url'], FILTER_VALIDATE_URL)) {
			$app->response->setStatus(400);
		}

		$downloader = new Downloader();
		$downloader->get($_POST['url'], 'import_downloaded_feed');
	}
	elseif (!empty($_POST['urls'])) {
		$urls = json_decode($_POST['urls'], true);
		if ($urls === NULL) {
			$app->response->setStatus(400);
		}
		$invalid_urls = array_filter($urls, function ($u) { return false === filter_var($_POST['url'], FILTER_VALIDATE_URL); });
		if (count($invalid_urls) == 0) {
			$app->response->setStatus(404);
		}

		$downloader = new Downloader();
		$downloader->get($_POST['urls'], 'import_downloaded_feed');
	}
	else {
		$app->response->setStatus(404);
	}
});
