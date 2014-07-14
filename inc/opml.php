<?php
/* Modified version of the functions from FreshRSS : https://github.com/marienfressinaud/FreshRSS/ */

function opml_export($cats) {
    /* Generate an OPML file to export the feeds.
     * TODO: Adapt to our code.
     */
    $now = new Datetime();
    $txt = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $txt .= '<opml version="2.0">'."\n";
    $txt .= "\t".'<head>'."\n";
    $txt .= "\t\t".'<title>Freeder</title>'."\n";
    $txt .= "\t\t".'<dateCreated>'.$now->format(DateTime::RFC822).'</dateCreated>'."\n";
    $txt .= "\t".'</head>'."\n";
    $txt .= "\t".'<body>'."\n";

    foreach ($cats as $cat) {
        $txt .= "\t\t".'<outline text="'.$cat['name'].'">'."\n";

        foreach ($cat['feeds'] as $feed) {
            $txt .= "\t\t\t".'<outline text="'.$feed->name.'" type="rss" xmlUrl="'.$feed->url.'" htmlUrl="'.$feed->website.'" description="'.htmlspecialchars($feed->description, ENT_COMPAT, 'UTF-8').'" />'."\n";
        }

        $txt .= "\t\t".'</outline>'."\n";
    }
    $txt .= "\t".'</body>'."\n";
    $txt .= '</opml>';

    return $txt;
}

function opml_import($xml) {
    /* Parse an OPML file.
     * Returns an array of feeds with url, title and associated tags.
     */
    $opml = simplexml_load_string($xml);

    if (!$opml) {
        return false;
    }

    $categories = array ();
    $feeds = array ();

    foreach ($opml->body->outline as $outline) {
        if (!isset ($outline['xmlUrl'])) {  // Folder
            $tag = '';

            if (isset ($outline['text'])) {
                $tag = (string) $outline['text'];
            }
            elseif (isset ($outline['title'])) {
                $tag = (string) $outline['title'];
            }

            if ($tag) {
                foreach($outline->outline as $feed) {
                    if(!isset($feed['xmlUrl'])) {
                        continue;
                    }

                    $search = multiarray_search('url', (string) $feed['xmlUrl'], $feeds, false);
                    if($search === FALSE) {
                        // Feed was not yet encountered, so add it first
                        if(isset($feed['title'])) {
                            $feed_title = (string) $feed['title'];
                        }
                        elseif(isset($feed['text'])) {
                            $feed_title = (string) $feed['text'];
                        }
                        else {
                            $feed_title = '';
                        }

                        $feeds[] = array(
                            'url'=>(string) $feed['xmlUrl'],
                            'title'=>$feed_title,
                            'tags'=>array()
                        );
                        $key = count($feeds) - 1;
                    }
                    else {
                        // Else, append categories to the existing feed
                        $key = array_search($search, $feeds);
                    }
                    $feeds[$key]['tags'][] = $tag;
                }
            }
        }
        else {  // This is a RSS feed without any folder
            if(isset($outline['title'])) {
                $title = (string) $outline['title'];
            }
            elseif(isset($outline['text'])) {
                $title = (string) $outline['text'];
            }
            else {
                $title = '';
            }

            if(multiarray_search('url', (string) $outline['xmlUrl'], $feeds, false) !== false) {
                $feeds[] = array(
                    'url'=>(string) $outline['xmlUrl'],
                    'title'=>$title,
                    'tags'=>array()
                );
            }
        }
    }

    return $feeds;
}
