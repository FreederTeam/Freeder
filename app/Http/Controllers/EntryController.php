<?php
namespace App\Http\Controllers;

use App\Models\Entry;
use App\Transformers\EntryTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
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
		return $this->respond($data);
    }


    /**
     * Get a specific entry.
     *
     * @param  Id       $id
     * @return Response
     */
    public function read($id)
    {
        $entry = Entry::find($id);
		if ($entry) {
			return response()->json($entry);
		} else {
			abort(404, "Resource does not exist.");
		}
    }
}
