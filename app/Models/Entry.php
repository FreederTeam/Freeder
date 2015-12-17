<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    public static $jsonApiType = 'entries';

    /**
     * Get the feed that owns the entry.
     */
    public function feed()
    {
        return $this->belongsTo('App\Models\Feed');
    }
}
