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
$app->bind('League\Fractal\Manager', function ($app) {
    $fractal = new \League\Fractal\Manager;
    $serializer = new \League\Fractal\Serializer\JsonApiSerializer('/api/v1');
    $fractal->setSerializer($serializer);
    return $fractal;
});

$app->get('/', function () use ($app) {
    return $app->welcome();
});


$app->get('/api', function () {
    return redirect("/api/v1/");
});

$app->get('/api/v1/', function () {
    return [
        "feeds" => "/api/v1/feeds",
        "entries" => "/api/v1/entries"
    ];
});

$app->get('/api/v1/feeds', 'FeedController@index');
$app->post('/api/v1/feeds', 'FeedController@create');
$app->get('/api/v1/feeds/{id}', 'FeedController@read');
$app->put('/api/v1/feeds/{id}', 'FeedController@update');
$app->delete('/api/v1/feeds/{id}', 'FeedController@delete');

$app->get('/api/v1/entries', 'EntryController@index');
$app->get('/api/v1/entries/{id}', 'EntryController@read');
