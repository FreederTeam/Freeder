<?php

namespace App\Transformers;

use App\Models\Entry;
use League\Fractal\TransformerAbstract;

class EntryTransformer extends TransformerAbstract {
    protected $defaultIncludes = [
    ];

    public function transform(Entry $entry)
    {
        return [
            'id'          => (int) $entry->id,
            'title'       => $entry->title,
            'description' => $entry->description,
            'content'     => $entry->content
        ];
    }
}
