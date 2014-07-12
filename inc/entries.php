<?php
function get_entries() {
    $query = $GLOBALS['dbh']->query('SELECT id, feed_id, authors, title, links, description, content, enclosures, comments, guid, pubDate, lastUpdate, is_sticky, is_read FROM entries ORDER BY pubDate DESC');
    $entries = $query->fetchall(PDO::FETCH_ASSOC);
    return $entries;
}
