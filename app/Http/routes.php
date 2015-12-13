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

$app->get('/', function () use ($app) {
    return $app->welcome();
});

$app->get('/api/v1/feeds', 'FeedController@index');
$app->post('/api/v1/feeds', 'FeedController@store');
$app->delete('/api/v1/feeds/{id}', 'FeedController@delete');
