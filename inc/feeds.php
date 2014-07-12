<?php
require('feed2array.php');

// TODO : Tags for feeds

function curl_downloader($urls) {
    /* Downloads all the urls in the array $urls and returns an array with the results and the http status_codes.
     *
     * Mostly inspired by blogotext by timovn : https://github.com/timovn/blogotext/blob/master/inc/fich.php
     *
     *  TODO : If open_basedir or safe_mode, Curl will not follow redirections :
     *  https://stackoverflow.com/questions/24687145/curlopt-followlocation-and-curl-multi-and-safe-mode
     */
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


function refresh_feeds($feeds, $update_feeds_infos=false) {
    /* Refresh the specified feeds and returns an array with URLs in error
     * $feeds should be an array of ids as keys and urls as values
     * $update_feeds_infos should be true to update the feed infos from values in the RSS / ATOM
     * */
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
    $GLOBALS['dbh']->beginTransaction();
    // Delete old tags which were not user added
    $GLOBALS['dbh']->query('DELETE FROM tags WHERE is_user_tag=0');

    if ($update_feeds_infos) {
        // Query to update feeds table with latest infos in the RSS / ATOM
        $query_feeds = $GLOBALS['dbh']->prepare('UPDATE feeds SET title=:title, links=:links, description=:description, ttl=:ttl, image=:image WHERE url=:old_url');
        $query_feeds->bindParam(':title', $feed_title);
        $query_feeds->bindParam(':links', $feed_links);
        $query_feeds->bindParam(':description', $feed_description);
        $query_feeds->bindParam(':ttl', $feed_ttl, PDO::PARAM_INT);
        $query_feeds->bindParam(':image', $image);
        $query_feeds->bindParam(':old_url', $url);
    }

    // Two queries, to upsert (update OR insert) entries : create a new entry (or ignore it if already exists) and update the necessary values
    // TODO : I believe this can be optimized, however I do not have any good idea for now…
    $query_ensure_entries = $GLOBALS['dbh']->prepare('INSERT OR IGNORE INTO entries(feed_id, guid) VALUES(:feed_id, :guid)');
    $query_ensure_entries->bindParam(':guid', $guid);
    $query_ensure_entries->bindParam(':feed_id', $i, PDO::PARAM_INT);
    $query_entries = $GLOBALS['dbh']->prepare('UPDATE entries SET authors=:authors, title=:title, links=:links, description=:description, content=:content, enclosures=:enclosures, comments=:comments, pubDate=:pubDate, lastUpdate=:lastUpdate WHERE guid=:guid');
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

    if($GLOBALS['config']->get('use_tags_from_feeds') != 0) {
        // Query to insert tags if not already existing
        $query_insert_tag = $GLOBALS['dbh']->prepare('INSERT OR IGNORE INTO tags(name) VALUES(:name)');
        $query_insert_tag->bindParam(':name', $tag_name);

        // Finally, query to register the tags of the article
        $query_tags = $GLOBALS['dbh']->prepare('INSERT INTO tags_entries(tag_id, entry_id) VALUES((SELECT id FROM tags WHERE name=:name), (SELECT id FROM entries WHERE guid=:entry_guid))');
        $query_tags->bindParam(':name', $tag_name);
        $query_tags->bindParam(':entry_guid', $guid);
    }

    foreach ($updated_feeds as $url=>$feed) {
        $i = array_search($url, $feeds);
        // Parse feed
        $parsed = feed2array($feed);
        if (empty($parsed) || $parsed === false) { // If an error has occurred, keep a trace of it
            $errors[] = $url;
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
            $comments = isset($event['comments']) ? $event['comments'] : ((isset($event['links'])) ? multiarray_search('rel', 'replies', $event['links'], '') : '');
            $guid = isset($event['guid']) ? $event['guid'] : '';
            $pubDate = isset($event['pubDate']) ? $event['pubDate'] : '';
            $last_update = isset($event['updated']) ? $event['updated'] : '';

            $query_ensure_entries->execute();
            $query_entries->execute();
            if ($query_entries->rowCount() == 0) {
                // If no queries were added or removed (constrains not satisfied for instance), skip the tag insertion
                continue;
            }

            if($GLOBALS['config']->get('use_tags_from_feeds') != 0) {
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
    $GLOBALS['dbh']->commit();

    return $errors;
}


function add_feed($url) {
    /* Add a feed in the database and refresh it.
     * Returns true upon success, false otherwise.
     */
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $query = $GLOBALS['dbh']->prepare('INSERT OR IGNORE INTO feeds(url) VALUES(:url)');
        $query->execute(array(':url'=>$url));

        if ($query->rowCount() == 0) {
            return false;
        }
        else {
            refresh_feeds(array($GLOBALS['dbh']->lastInsertedId()=>$url), true);
            return true;
        }
    }
    else {
        return false;
    }
}


function delete_feed($url) {
    /* Remove a feed and all associated tags / entries
     * Returns true in case of success, false otherwise
     */
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $query = $GLOBALS['dbh']->prepare('DELETE FROM feeds WHERE url=:url');
        $query->execute(array(':url'=>$url));
        return true;
    }
    else {
        return false;
    }
}


function edit_feed($old_url, $new_url, $new_title='') {
    /* Edit a feed in the database and refresh it.
     * Returns true upon success, false otherwise.
     */
    // TODO :  Edit more than just the URL
    if (filter_var($new_url, FILTER_VALIDATE_URL) && filter_var($old_url, FILTER_VALIDATE_URL)) {
        $query = $GLOBALS['dbh']->prepare('UPDATE feeds SET url=:url WHERE url=:old_url');
        $query->execute(array(':old_url'=>$old_url, 'new_url'=>$new_url));

        if ($query->rowCount() == 0) {
            return false;
        }
        else {
            refresh_feeds(array($GLOBALS['dbh']->lastInsertedId()=>$new_url));
            return true;
        }
    }
    else {
        return false;
    }
}
