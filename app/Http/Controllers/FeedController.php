<?php
namespace App\Http\Controllers;

use App\Models\Feed;
use App\Transformers\FeedTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

use League\Fractal\Serializer\JsonApiSerializer;

class FeedController extends ApiController
{
    /**
     * List all the available feeds.
     *
     * @return Response
     */
    public function index(Manager $fractal, FeedTransformer $feedTransformer)
    {
        $feeds = Feed::all();

		$collection = new Collection($feeds, $feedTransformer, \App\Models\Feed::$jsonApiType);
		$data = $fractal->createData($collection)->toArray();
		return $this->respond($data);
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
		if ($feed) {
			return response()->json($feed);
		} else {
			abort(404, "Resource does not exist.");
		}
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

		// Check that resource exist
		if ($feed) {
			return response()->json($feed);
		} else {
			abort(404, "Resource does not exist.");
		}

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

		// Check that resource exist
		if ($feed) {
			return response()->json($feed);
		} else {
			abort(404, "Resource does not exist.");
		}

        $Feed->delete();
    }
}
