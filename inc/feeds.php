<?php
/** Freeder
 *  -------
 *  @file
 *  @copyright Copyright (c) 2014 Freeder, MIT License, See the LICENSE file for copying permissions.
 *  @brief Functions to handle the feeds (includes feed2array)
 */


require('feed2array.php');

// TODO : Tags for feeds


/**
 * Downloads all the urls in the array $urls and returns an array with the results and the http status_codes.
 *
 * Mostly inspired by blogotext by timovn : https://github.com/timovn/blogotext/blob/master/inc/fich.php
 *
 *  TODO : If open_basedir or safe_mode, Curl will not follow redirections :
 *  https://stackoverflow.com/questions/24687145/curlopt-followlocation-and-curl-multi-and-safe-mode
 *
 *  @param an array $urls of URLs
 *  @return an array {'results', 'status_code'}, results being an array of the retrieved contents, indexed by URLs, and 'status_codes' being an array of status_code when different from 200, indexed by URL.
 */
function curl_downloader($urls) {
	$chunks = array_chunk($urls, 40, true);  // Chunks of 40 urls because curl has problems with too big "multi" requests
	$results = array();
	$status_codes = array();

	if (ini_get('open_basedir') == '' && ini_get('safe_mode') === false) { // Disable followlocation option if this is activated, to avoid warnings
		$follow_redirect = true;
	}
	else {
		$follow_redirect = false;
	}

	foreach ($chunks as $chunk) {
		$multihandler = curl_multi_init();
		$handlers = array();
		$total_feed_chunk = count($chunk) + count($results);

		foreach ($chunk as $i=>$url) {
			set_time_limit(20); // Reset max execution time
			$handlers[$i] = curl_init($url);
			curl_setopt_array($handlers[$i], array(
				CURLOPT_RETURNTRANSFER => TRUE,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_TIMEOUT => 15,
				CURLOPT_FOLLOWLOCATION => $follow_redirect,
				CURLOPT_MAXREDIRS => 5,
				CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],  // Add a user agent to prevent problems with some feeds
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


/**
 * Refresh the specified feeds and returns an array with URLs in error
 *
 * TODO:
 *	  * Get rid of feed ids
 *	  * If no entries for a feed, it might be an error
 *
 * @param $feeds should be an array of ids as keys and urls as values
 * @param $update_feeds_infos should be true to update the feed infos from values in the RSS / ATOM
 */
function refresh_feeds($feeds, $update_feeds_infos=false) {
    global $dbh, $config;

	$download = curl_downloader($feeds);
	$errors = array();
	foreach ($download['status_codes'] as $url=>$status_code) {
		// Keep the errors to return them and display them to the user
		if ($status_code != 200) {
			$errors[] = $url;
		}
	}

	$updated_feeds = $download['results'];

	// Put everything in a transaction to make it faster
	$dbh->beginTransaction();
	// Delete old tags which were not user added
	$dbh->query('DELETE FROM tags WHERE is_user_tag=0');

	if ($update_feeds_infos) {
		// Query to update feeds table with latest infos in the RSS / ATOM
		$query_feeds = $dbh->prepare('UPDATE feeds SET title=:title, links=:links, description=:description, ttl=:ttl, image=:image WHERE url=:old_url');
		$query_feeds->bindParam(':title', $feed_title);
		$query_feeds->bindParam(':links', $feed_links);
		$query_feeds->bindParam(':description', $feed_description);
		$query_feeds->bindParam(':ttl', $feed_ttl, PDO::PARAM_INT);
		$query_feeds->bindParam(':image', $image);
		$query_feeds->bindParam(':old_url', $url);
	}

	// Two queries, to upsert (update OR insert) entries : update the existing entry and insert a new one if the update errorred
	$query_entries = $dbh->prepare('UPDATE entries SET authors=:authors, title=:title, links=:links, description=:description, content=:content, enclosures=:enclosures, comments=:comments, pubDate=:pubDate, lastUpdate=:lastUpdate WHERE guid=:guid');
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
	$query_entries_fail = $dbh->prepare('INSERT INTO entries(feed_id, authors, title, links, description, content, enclosures, comments, pubDate, lastUpdate, guid) VALUES(:feed_id, :authors, :title, :links, :description, :content, :enclosures, :comments, :pubDate, :lastUpdate, :guid)');
	$query_entries_fail->bindParam(':feed_id', $feed_id);
	$query_entries_fail->bindParam(':authors', $authors);
	$query_entries_fail->bindParam(':title', $title);
	$query_entries_fail->bindParam(':links', $links);
	$query_entries_fail->bindParam(':description', $description);
	$query_entries_fail->bindParam(':content', $content);
	$query_entries_fail->bindParam(':enclosures', $enclosures);
	$query_entries_fail->bindParam(':comments', $comments);
	$query_entries_fail->bindParam(':guid', $guid);
	$query_entries_fail->bindParam(':pubDate', $pubDate, PDO::PARAM_INT);
	$query_entries_fail->bindParam(':lastUpdate', $last_update, PDO::PARAM_INT);

	if($config->get('use_tags_from_feeds') != 0) {
		// Query to insert tags if not already existing
		$query_insert_tag = $dbh->prepare('INSERT OR IGNORE INTO tags(name) VALUES(:name)');
		$query_insert_tag->bindParam(':name', $tag_name);

		// Finally, query to register the tags of the article
		$query_tags = $dbh->prepare('INSERT INTO tags_entries(tag_id, entry_id) VALUES((SELECT id FROM tags WHERE name=:name), (SELECT id FROM entries WHERE guid=:entry_guid))');
		$query_tags->bindParam(':name', $tag_name);
		$query_tags->bindParam(':entry_guid', $guid);
	}

	foreach ($updated_feeds as $url=>$feed) {
		$feed_id = array_search($url, $feeds);
		// Parse feed
		$parsed = @feed2array($feed);
		if (empty($parsed) || $parsed === false) { // If an error has occurred, keep a trace of it
			$errors[] = $url;
			continue;
		}

		if ($update_feeds_infos) {
			// Define feed params
			$feed_title = isset($parsed['infos']['title']) ? $parsed['infos']['title'] : '';
			$feed_links = isset($parsed['infos']['links']) ? json_encode(multiarray_filter('rel', 'self', $parsed['infos']['links'])) : '';
			$feed_description = isset($parsed['infos']['description']) ? $parsed['infos']['description'] : '';
			$feed_ttl = isset($parsed['infos']['ttl']) ? $parsed['infos']['ttl'] : 0;
			$feed_image = isset($parsed['infos']['image']) ? json_encode($parsed['infos']['image']) : '';
			$query_feeds->execute();
		}

		// Insert / Update entries
		$items = $parsed['items'];
		foreach ($items as $event) {
			$authors = isset($event['authors']) ? json_encode($event['authors']) : '';
			$title = isset($event['title']) ? $event['title'] : '';
			$links = isset($event['links']) ? json_encode(multiarray_filter('rel', 'self', $event['links'])) : '';
			$description = isset($event['description']) ? $event['description'] : '';
			$content = isset($event['content']) ? $event['content'] : '';
			$enclosures = isset($event['enclosures']) ? json_encode($event['enclosures']) : '';
			$comments = isset($event['comments']) ? $event['comments'] : ((isset($event['links'])) ? multiarray_search('rel', 'replies', $event['links'], array('href'=>''))['href'] : '');
			$guid = isset($event['guid']) ? $event['guid'] : '';
			$pubDate = isset($event['pubDate']) ? $event['pubDate'] : '';
			$last_update = isset($event['updated']) ? $event['updated'] : '';

            $query_entries->execute();
			if ($query_entries->rowCount() == 0) {
				$query_entries_fail->execute();
			}

			if($config->get('use_tags_from_feeds') != 0) {
				if (!empty($event['categories'])) {
					foreach ($event['categories'] as $tag_name) {
						// Create tags if needed, get their id and add bind the articles to these tags
						$query_insert_tag->execute();
						$query_tags->execute();
					}
				}
			}
		}
	}
	$dbh->commit();

	return $errors;
}


/**
 * Add feeds in the database and refresh them.
 *
 * @param $urls is an array of urls
 * @return errored urls in array
 */
function add_feeds($urls) {
    global $dbh;

	$errors = array();
	$added = array();
	$dbh->beginTransaction();
	$query = $dbh->prepare('INSERT OR IGNORE INTO feeds(url) VALUES(:url)');
	$query->bindParam(':url', $url);
	foreach($urls as $url) {
		if (filter_var($url, FILTER_VALIDATE_URL)) {
			$query->execute();
			$added[$dbh->lastInsertId()] = $url;
		}
		else {
			$errors[] = $url;
		}
	}
	$dbh->commit();
	$errors_refresh = refresh_feeds($added, true);
	foreach ($errors_refresh as $error) {
		delete_feed_url($error);
	}
	return array_merge($errors, $errors_refresh);
}


/**
 * Remove a feed and all associated tags / entries based on its id
 *
 * @param $id is the id of the feed to delete
 */
function delete_feed_id($id) {
    global $dbh;

	$query = $dbh->prepare('DELETE FROM feeds WHERE id=:id');
	$query->execute(array(':id'=>$id));
}


/**
 * Remove a feed and all associated tags / entries based on its url
 *
 * @param $url is the url of the feed to delete
 */
function delete_feed_url($url) {
    global $dbh;

	$query = $dbh->prepare('DELETE FROM feeds WHERE url=:url');
	$query->execute(array(':url'=>$url));
}


/**
 * Edit a feed in the database and refresh it.
 *
 * TODO :  Edit more than just the URL
 *
 * @param $old_url is the current URL of the feed
 * @param $new_url is the new URL to assign to this feed
 * @param $new_title (optionnal) is the new title of the feed
 * @return true upon success, false otherwise.
 */
function edit_feed($old_url, $new_url, $new_title='') {
    global $dbh;

	if (filter_var($new_url, FILTER_VALIDATE_URL) && filter_var($old_url, FILTER_VALIDATE_URL)) {
		$query = $dbh->prepare('UPDATE feeds SET url=:url WHERE url=:old_url');
		$query->execute(array(':old_url'=>$old_url, 'new_url'=>$new_url));

		if ($query->rowCount() == 0) {
			return false;
		}
		else {
			refresh_feeds(array($dbh->lastInsertId()=>$new_url));
			return true;
		}
	}
	else {
		return false;
	}
}


/**
 * Returns all the available feeds.
 *
 * TODO
 */
function get_feeds() {
    global $dbh;
	$query = $dbh->query('SELECT id, title, url, links, description, ttl, image FROM feeds');
	return $query->fetchAll(PDO::FETCH_ASSOC);
}
