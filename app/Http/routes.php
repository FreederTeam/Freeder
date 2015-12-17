<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

class FractalManager extends \League\Fractal\Manager
{
    public function __construct(Illuminate\Http\Request $request)
    {
        // Handle ?include= GET parameter
        if ($request->input("include")) {
            $this->parseIncludes($request->input("include"));
        }

        // Set default serializer to be JsonApi
        $serializer = new \League\Fractal\Serializer\JsonApiSerializer('/api/v1');
        $this->setSerializer($serializer);
    }
}

$app->bind('League\Fractal\Manager', 'FractalManager');

$app->get('/', function () use ($app) {
    return $app->welcome();
});


$app->get('/api', function () {
    return redirect("/api/v1/");
});

$app->get('/api/v1/', 'ApiController@root');

$app->get('/api/v1/feeds', 'FeedController@index');
$app->post('/api/v1/feeds', 'FeedController@create');
$app->get('/api/v1/feeds/{id}', 'FeedController@read');
$app->patch('/api/v1/feeds/{id}', 'FeedController@update');
$app->delete('/api/v1/feeds/{id}', 'FeedController@delete');

$app->get('/api/v1/entries', 'EntryController@index');
$app->get('/api/v1/entries/{id}', 'EntryController@read');
