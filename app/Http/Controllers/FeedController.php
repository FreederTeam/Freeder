<?php
namespace App\Http\Controllers;

use App\Models\Feed;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    /**
     * List all the available feeds.
     *
     * @return Response
     */
    public function index()
    {
        $feeds = Feed::all();
        return response()->json($feeds);
    }


    /**
     * Create a new feed instance.
     *
     * @param  Request  $request
     * @return Response
     */
    public function create(Request $request)
    {
        // Validate the request
        if (!$request->has("name") ||
            !$request->has("url") ||
            !$request->has("description") ||
            !$request->has("ttl") ||
            !filter_var($request->url, FILTER_VALIDATE_URL) ||
            !is_numeric($request->ttl)) {
            // Request not valid, return 400
            abort(400, "Invalid data.");
        }

        // Store in database
        $feed = new Feed;

        $feed->name = $request->name;
        $feed->url = $request->url;
        $feed->description = $request->description;
        $feed->ttl = intval($request->ttl);

        $feed->save();
    }


    /**
     * Get a specific feed.
     *
     * @param  Id       $id
     * @return Response
     */
    public function read($id)
    {
        $feed = Feed::find($id);
        return response()->json($feed);
    }


    /**
     * Update a feed instance.
     *
     * @param  Request  $request
     * @param  Id       $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        // Validate the request
        if (!$request->has("name") ||
            !$request->has("url") ||
            !$request->has("description") ||
            !$request->has("ttl") ||
            !filter_var($request->url, FILTER_VALIDATE_URL) ||
            !is_numeric($request->ttl)) {
            // Request not valid, return 400
            abort(400, "Invalid data.");
        }

        // Store in database
        $feed = Feed::find($id);

        $feed->name = $request->name;
        $feed->url = $request->url;
        $feed->description = $request->description;
        $feed->ttl = intval($request->ttl);

        $feed->save();
    }


    /**
     * Delete a given feed.
     *
     * @param  Id       $id
     * @return Response
     */
    public function delete($id)
    {
        $Feed = Feed::find($id);
        $Feed->delete();
    }
}
