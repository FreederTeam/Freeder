<?php
# Feed2array
# @author: bronco@warriordudimanche.net
# @version 0.2.1
# @license  free and opensource
# @inspired by  http://milletmaxime.net/syndexport/
# @use: $items=feed2array('http://sebsauvage.net/links/index.php?do=rss');
# @return: returns an (array)array, or (boolean)false if an exception occurs.

function feed2array($feed, $load=true, $debug=false) {
    $flux = array('infos' => array(), 'items' => array());
    try {
        if($feed_obj = new SimpleXMLElement($feed, LIBXML_NOCDATA, $data_is_url=$load)) {
            switch($feed_obj->getName()) {
                case 'rss':
                    $type = 'RSS';
                    if(empty($feed_obj->channel->item)) {
                        return false;
                    }
                    $items = $feed_obj->channel->item;
                    break;

                case 'feed':
                    $type = 'ATOM';
                    if(empty($feed_obj->entry)) {
                        return false;
                    }
                    $items = $feed_obj->entry;
                    break;

                default:
                    return false;
            }

            $flux['infos']['type'] = $type;
            $flux['infos']['version'] = $feed_obj->attributes()->version;

            if($type == "RSS") {
                if(!empty($feed_obj->attributes()->version)){
                    $flux['infos']['version'] = (string)$feed_obj->attributes()->version;
                }
                if(!empty($feed_obj->channel->title)) {
                    $flux['infos']['title'] = (string)$feed_obj->channel->title;
                }
                if(!empty($feed_obj->channel->link)){
                    $flux['infos']['link'] = array(
                        'href'=>(string)$feed_obj->channel->link,
                        'rel'=>'self',
                        'title'=>''
                    );
                }
                if(!empty($feed_obj->channel->description)) {
                    $flux['infos']['description'] = (string)$feed_obj->channel->description;
                }
                if(!empty($feed_obj->channel->language)) {
                    $flux['infos']['language'] = (string)$feed_obj->channel->language;
                }
                if(!empty($feed_obj->channel->copyright)) {
                    $flux['infos']['copyright'] = (string)$feed_obj->channel->copyright;
                }
                if(!empty($feed_obj->channel->pubDate)) {
                    $flux['infos']['pubDate'] = (string)$feed_obj->channel->pubDate;
                }
                if(!empty($feed_obj->channel->lastBuildDate)) {
                    $flux['infos']['lastBuildDate'] = (string)$feed_obj->channel->lastBuildDaye;
                }
                if(!empty($feed_obj->channel->category)) {
                    $flux['infos']['category'] = (string)$feed_obj->channel->category;
                }
                if(!empty($feed_obj->channel->ttl)) {
                    $flux['infos']['ttl'] = (string)$feed_obj->channel->ttl;
                }
                if(!empty($feed_obj->channel->image)) {
                    $flux['infos']['image_url'] = (string)$feed_obj->channel->image->url;
                    $flux['infos']['image_title'] = (string)$feed_obj->channel->image->title;
                    $flux['infos']['image_link'] = (string)$feed_obj->channel->image->link;
                }
                if(!empty($feed_obj->channel->skipHours)) {
                    foreach($feed_obj->channel->skipHours->children() as $hour) {
                        $flux['infos']['skipHours'] = (string)$hour;
                    }
                }
                if(!empty($feed_obj->channel->skipDays)) {
                    foreach($feed_obj->channel->skipDays->children() as $day) {
                        $flux['infos']['skipDays'] = (string)$day;
                    }
                }
            }
            elseif($type == "ATOM") {
                if(!empty($feed_obj->id)) {
                    $flux['infos']['id'] = (string)$feed_obj->id;
                }
                if(!empty($feed_obj->title)) {
                    $flux['infos']['title'] = (string)$feed_obj->title;
                }
                if(!empty($feed_obj->updated)) {
                    $flux['infos']['updated'] = (string)$feed_obj->updated;
                }
                if(!empty($feed_obj->author)) {
                    $flux['infos']['author'] = array();
                    foreach($feed_obj->author as $author) {
                        if(!empty($author['email'])) {
                            $email = (string)$author['email'];
                        }
                        else {
                            $email = '';
                        }
                        if(!empty($author['uri'])) {
                            $uri = (string)$author['uri'];
                        }
                        else {
                            $uri = '';
                        }
                        $flux['infos']['author'][] = array(
                            'name'=>(string)$author['name'],
                            'email'=>$email,
                            'uri'=>$uri
                        );
                    }
                }
                if(!empty($feed_obj->link)) {
                    $flux['infos']['link'] = array();
                    foreach($feed_obj->link as $link) {
                        if(!empty($link['title'])) {
                            $title = (string)$link['title'];
                        }
                        else {
                            $title = '';
                        }
                        if(!empty($link['rel'])) {
                            $rel = (string)$link['rel'];
                        }
                        else {
                            $rel = 'alternate';
                        }

                        if($rel != 'enclosure') {
                            $flux['info']['link'][] = array(
                                'href'=>(string)$link['href'],
                                'title'=>$title,
                                'rel'=>$rel
                            );
                        }
                    }
                }
                if(!empty($feed_obj->category)) {
                    $flux['infos']['category'] = array();
                    foreach($feed->obj->category as $tag) {
                        if(!empty($tag['label'])) {
                            $flux['infos']['category'][] = (string)$tag['label'];
                        }
                        else {
                            $flux['infos']['category'][] = (string)$tag['term'];
                        }
                    }
                }
                if(!empty($feed_obj->icon)) {
                    $flux['infos']['icon'] = (string)$feed_obj->updated;
                }
                if(!empty($feed_obj->rights)) {
                    $flux['infos']['copyright'] = (string)$feed_obj->rights;
                }
                if(!empty($feed_obj->subtitle)) {
                    $flux['infos']['subtitle'] = (string)$feed_obj->subtitle;
                }
            }


            foreach($items as $item) {
                $c = count($flux['items']);
                if($type == "RSS") {
                    if(!empty($item->title)) {
                        $flux['items'][$c]['title'] = (string)$item->title;
                    }
                    if(!empty($item->link)) {
                        $flux['items'][$c]['link'] = array(
                            'href'=>(string)$item->link,
                            'rel'=>'self',
                            'title'=>''
                        );
                    }
                    if(!empty($item->description)) {
                        $flux['items'][$c]['description'] = (string)$item->description;
                    }
                    if(!empty($item->author)) {
                        $flux['items'][$c]['author'] = array(
                            'name'=>'',
                            'email'=>(string)$item->author,
                            'uri'=>''
                        );
                    }
                    if(!empty($item->category)) {
                        $flux['items'][$c]['category'] = (string)$item->category;
                    }
                    if(!empty($item->comments)) {
                        $flux['items'][$c]['comments'] = (string)$item->comments;
                    }
                    if(!empty($item->enclosure)) {
                        $flux['items'][$c]['enclosure'][] = array(
                            'url'=>(string)$item->enclosure['url'],
                            'type'=>(string)$item->enclosure['type'],
                            'size'=>(string)$item->enclosure['length']
                        );
                    }
                    if(!empty($item->guid)) {
                        $flux['items'][$c]['guid'] = (string)$item->guid;
                        if(!empty($item->guid['isPermaLink'])) {
                            $flux['items'][$c]['guid_is_permalink'] = (bool)$item->guid['isPermaLink'];
                        }
                        else {
                            $flux['items'][$c]['guid_is_permalink'] = true;
                        }
                    }
                    if(!empty($item->pubDate)) {
                        $flux['items'][$c]['pubDate'] = (string)$item->pubDate;
                    }
                }
                elseif($type == "ATOM") {
                    if(!empty($item->id)) {
                        $flux['items'][$c]['id'] = (string)$item->id;
                    }
                    if(!empty($item->title)) {
                        $flux['items'][$c]['title'] = (string)$item->title;
                    }
                    if(!empty($item->updated)) {
                        $flux['items'][$c]['updated'] = (string)$item->updated;
                    }
                    if(!empty($item->author)) {
                        foreach($item->author as $author) {
                            if(!empty($author['email'])) {
                                $email = (string)$author['email'];
                            }
                            else {
                                $email = '';
                            }
                            if(!empty($author['uri'])) {
                                $uri = (string)$author['uri'];
                            }
                            else {
                                $uri = '';
                            }
                            $flux['items'][$c]['author'][] = array(
                                'name'=>(string)$author['name'],
                                'email'=>$email,
                                'uri'=>$uri
                            );
                        }
                    }
                    if(!empty($item->link)) {
                        foreach($item->link as $link) {
                            if(!empty($link['title'])) {
                                $title = (string)$link['title'];
                            }
                            else {
                                $title = '';
                            }
                            if(!empty($link['rel'])) {
                                $rel = (string)$link['rel'];
                            }
                            else {
                                $rel = 'alternate';
                            }

                            if($rel != 'enclosure') {
                                $flux['items'][$c]['link'][] = array(
                                    'href'=>(string)$link['href'],
                                    'title'=>$title,
                                    'rel'=>$rel
                                );
                            }
                            else {
                                if(!empty($link['type'])) {
                                    $type = (string)$link['type'];
                                }
                                else {
                                    $type = '';
                                }
                                if(!empty($link['length'])) {
                                    $length = (string)$link['length'];
                                }
                                else {
                                    $length = '';
                                }
                                $flux['items'][$c]['enclosure'][] = array(
                                    'url'=>(string)$link['href'],
                                    'type'=>$type,
                                    'size'=>$length
                                );
                            }
                        }
                    }
                    if(!empty($item->summary)) {
                        $flux['items'][$c]['description'] = (string)$item->summary;
                    }
                    if(!empty($item->category)) {
                        foreach($item->category as $tag) {
                            if(!empty($tag['label'])) {
                                $flux['items'][$c]['category'] = (string)$tag['label'];
                            }
                            else {
                                $flux['items'][$c]['category'] = (string)$tag['term'];
                            }
                        }
                    }
                    if(!empty($item->published)) {
                        $flux['items'][$c]['date'] = (string)$item->published;
                    }
                    if(!empty($item->rights)) {
                        $flux['items'][$c]['copyright'] = (string)$item->rights;
                    }
                }

                if(!empty($item->content)) {
                    $flux['items'][$c]['content'] = (string)$item->content;
                }
                // for the tricky <content:encoded> tag
                if(!empty($item->children('content', true)->encoded)) {
                    $flux['items'][$c]['content'] = (string)$item->children('content', true)->encoded;
                }
            }
            return $flux;
        }
        else {
            return false;
        }
    } catch (Exception $e) {
        if($debug) {
            echo 'Parse error XML: '.$feed.' : ' .$e->getMessage();
        }
        return false;
    }
}
?>
