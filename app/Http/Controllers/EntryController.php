<?php
namespace App\Http\Controllers;

use App\Models\Feed;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    /**
     * List all the available entries.
     *
     * @return Response
     */
    public function index()
    {
        $entries = Entry::all();
        return response()->json($entries);
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
        return response()->json($entry);
    }
}
