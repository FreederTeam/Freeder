<?php

namespace App\Transformers;

use App\Models\Feed;
use League\Fractal\TransformerAbstract;

class FeedTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        "entries"];
    protected $defaultIncludes = [];

    public function transform(Feed $feed)
    {
        return [
            'id'          => (int) $feed->id,
            'name'        => $feed->name,
            'url'         => $feed->url,
            'description' => (string) $feed->description
        ];
    }

    /**
     * Include entries.
     *
     * @return League\Fractal\ItemResource
     */
    public function includeEntries(Feed $feed)
    {
        $entries = $feed->entries;
        return $this->collection($entries, new EntryTransformer, \App\Models\Entry::$jsonApiType);
    }
}
