<?php
namespace App\Http\Controllers;

use App\Models\Feed;
use App\Transformers\FeedTransformer;
use App\Jobs\UpdateFeed;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
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

        $this->setStatusCode(IlluminateResponse::HTTP_OK);
        return $this->respond($data);
    }


    /**
     * Create a new feed instance.
     *
     * @param  Request  $request
     * @return Response
     */
    public function create(Manager $fractal, FeedTransformer $feedTransformer, Request $request)
    {
        // Validate the request
        if ($request->input('data.type') != \App\Models\Feed::$jsonApiType ||
            !filter_var($request->input('data.url'), FILTER_VALIDATE_URL)) {
            // Abort with IlluminateResponse::HTTP_BAD_REQUEST
            $this->setStatusCode(IlluminateResponse::HTTP_BAD_REQUEST);
            return $this->respond(null);
        }

        // Store in database
        $feed = new Feed;
        $feed->url = $request->input('data.url');
        $feed->name = $request->input('data.name') ?: $feed->url;
        $feed->description = $request->input('data.description') ?: '';
        $feed->save();

        // Defer update of the feed
        $this->dispatch(new UpdateFeed($feed));

        // Respond the resource with Location header to the resource
        $item = new Item($feed, $feedTransformer, \App\Models\Feed::$jsonApiType);
        $data = $fractal->createData($item)->toArray();
        // Respond with IlluminateResponse::HTTP_OK  as processing is not
        // yet finished at this time
        $this->setStatusCode(IlluminateResponse::HTTP_OK);
        return $this->respond($data, array("Location"=>"/api/v1/feeds/" + $feed->id));
    }


    /**
     * Get a specific feed.
     *
     * @param  Id       $id
     * @return Response
     */
    public function read(Manager $fractal, FeedTransformer $feedTransformer, $id)
    {
        $feed = Feed::find($id);
        if (!$feed) {
            // Abort with IlluminateResponse::HTTP_NOT_FOUND
            $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND);
            return $this->respond(null);
        }

        $item = new Item($feed, $feedTransformer, \App\Models\Feed::$jsonApiType);
        $data = $fractal->createData($item)->toArray();

        $this->setStatusCode(IlluminateResponse::HTTP_OK);
        return $this->respond($data);
    }


    /**
     * Update a feed instance.
     *
     * @param  Request  $request
     * @param  Id       $id
     * @return Response
     */
    public function update(Manager $fractal, FeedTransformer $feedTransformer, Request $request, $id)
    {
        // Validate the request
        if ($request->input('data.type') != \App\Models\Feed::$jsonApiType ||
            !$request->has('data.id') ||
            ($request->has('data.url') && !filter_var($request->input('data.url'), FILTER_VALIDATE_URL))) {
            // Abort with IlluminateResponse::HTTP_BAD_REQUEST
            $this->setStatusCode(IlluminateResponse::HTTP_BAD_REQUEST);
            return $this->respond(null);
        }

        // Update the database
        $feed = Feed::find($id);
        // Check that resource exist
        if (!$feed) {
            // Abort with IlluminateResponse::HTTP_NOT_FOUND
            $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND);
            return $this->respond(null);
        }

        $feed->url = $request->input('data.url') ?: $feed->url;
        $feed->name = $request->input('data.name') ?: $feed->name;
        $feed->description = $request->input('data.description') ?: '';
        $feed->save();

        // Defer update of the feed
        $this->dispatch(new UpdateFeed($feed));

        // Respond the resource with Location header to the resource
        $item = new Item($feed, $feedTransformer, \App\Models\Feed::$jsonApiType);
        $data = $fractal->createData($item)->toArray();
        // Respond with IlluminateResponse::HTTP_OK as processing is not
        // yet finished at this time
        $this->setStatusCode(IlluminateResponse::HTTP_OK);
        return $this->respond($data, array("Location"=>"/api/v1/feeds/" + $feed->id));
    }


    /**
     * Delete a given feed.
     *
     * @param  Id       $id
     * @return Response
     */
    public function delete($id)
    {
        $feed = Feed::find($id);

        // Check that resource exist
        if (!$feed) {
            // Abort with IlluminateResponse::HTTP_NOT_FOUND
            $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND);
            return $this->respond(null);
        }

        $feed->delete();

        // Respond an empty body (then, with IlluminateResponse::HTTP_NO_CONTENT No Content status code)
        $this->setStatusCode(IlluminateResponse::HTTP_NO_CONTENT);
        return $this->respond(null);
    }
}
