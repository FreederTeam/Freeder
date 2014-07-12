<?php
/* From FreshRSS : https://github.com/marienfressinaud/FreshRSS/ */
// TODO : Adapt to our code
function opml_export ($cats) {
    $txt = '';

    foreach ($cats as $cat) {
        $txt .= '<outline text="' . $cat['name'] . '">' . "\n";

        foreach ($cat['feeds'] as $feed) {
            $txt .= "\t" . '<outline text="' . $feed->name () . '" type="rss" xmlUrl="' . $feed->url () . '" htmlUrl="' . $feed->website () . '" description="' . htmlspecialchars($feed->description(), ENT_COMPAT, 'UTF-8') . '" />' . "\n";
        }

        $txt .= '</outline>' . "\n";
    }

    return $txt;
}

function opml_import ($xml) {
    $xml = html_only_entity_decode($xml);	//!\ Assume UTF-8

    $dom = new DOMDocument();
    $dom->recover = true;
    $dom->strictErrorChecking = false;
    $dom->loadXML($xml);
    $dom->encoding = 'UTF-8';

    $opml = simplexml_import_dom($dom);

    if (!$opml) {
        throw new FreshRSS_Opml_Exception ();
    }

    $catDAO = new FreshRSS_CategoryDAO();
    $catDAO->checkDefault();
    $defCat = $catDAO->getDefault();

    $categories = array ();
    $feeds = array ();

    foreach ($opml->body->outline as $outline) {
        if (!isset ($outline['xmlUrl'])) {
            // Catégorie
            $title = '';

            if (isset ($outline['text'])) {
                $title = (string) $outline['text'];
            } elseif (isset ($outline['title'])) {
                $title = (string) $outline['title'];
            }

            if ($title) {
                // Permet d'éviter les soucis au niveau des id :
                // ceux-ci sont générés en fonction de la date,
                // un flux pourrait être dans une catégorie X avec l'id Y
                // alors qu'il existe déjà la catégorie X mais avec l'id Z
                // Y ne sera pas ajouté et le flux non plus vu que l'id
                // de sa catégorie n'exisera pas
                $title = htmlspecialchars($title, ENT_COMPAT, 'UTF-8');
                $catDAO = new FreshRSS_CategoryDAO ();
                $cat = $catDAO->searchByName ($title);
                if ($cat === false) {
                    $cat = new FreshRSS_Category ($title);
                    $values = array (
                        'name' => $cat->name ()
                    );
                    $cat->_id ($catDAO->addCategory ($values));
                }

                $feeds = array_merge ($feeds, getFeedsOutline ($outline, $cat->id ()));
            }
        } else {
            // Flux rss sans catégorie, on récupère l'ajoute dans la catégorie par défaut
            $feeds[] = getFeed ($outline, $defCat->id());
        }
    }

    return array ($categories, $feeds);
}

