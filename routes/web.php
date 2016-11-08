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

$app->post('sync/{queue}', [
    'as' => 'sync/{queue}',
    'uses' => 'MessageQueueController@sync'
]);

$app->group(['prefix' => 'example-response', 'namespace' => 'App\Http\Controllers'], function ($app) {
    $app->post('success', [
        'as' => 'example-response/success',
        'uses' => 'ExampleResponseController@success'
    ]);
    $app->post('failed', [
        'as' => 'example-response/failed',
        'uses' => 'ExampleResponseController@failed'
    ]);

    $app->post('form_params', [
        'as' => 'example-response/form_params',
        'uses' => 'ExampleResponseController@form_params'
    ]);
});

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->get('outbound/testZoho', 'MessageQueueController@testZoho');
