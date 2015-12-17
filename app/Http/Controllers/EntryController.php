<?php
namespace App\Http\Controllers;

use App\Models\Entry;
use App\Transformers\EntryTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

use League\Fractal\Serializer\JsonApiSerializer;

class EntryController extends ApiController
{
    /**
     * List all the available entries.
     *
     * @return Response
     */
    public function index(Manager $fractal, EntryTransformer $entryTransformer)
    {
        $entries = Entry::all();

        $collection = new Collection($entries, $entryTransformer, \App\Models\Entry::$jsonApiType);
        $data = $fractal->createData($collection)->toArray();

        $this->setStatusCode(200);
        return $this->respond($data);
    }


    /**
     * Get a specific entry.
     *
     * @param  Id       $id
     * @return Response
     */
    public function read(Manager $fractal, EntryTransformer $entryTransformer, $id)
    {
        $entry = Entry::find($id);
        if (!$entry) {
            // Abort with 404
            $this->setStatusCode(404);
            return $this->respond(null);
        }

        $item = new Item($entry, $entryTransformer, \App\Models\Entry::$jsonApiType);
        $data = $fractal->createData($item)->toArray();

        $this->setStatusCode(200);
        return $this->respond($data);
    }
}
