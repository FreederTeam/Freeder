<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
	public static $jsonApiType = 'feeds';

    /**
     * Get the entries for the feed.
     */
    public function entries()
    {
        return $this->hasMany('App\Models\Entry');
    }
}
