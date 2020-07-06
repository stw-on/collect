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


use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/template'], function (Router $router) {
    $router->get('/{id}', 'TransferTemplateController@get');
    $router->post('/{id}/transfer', 'TransferController@create');
});

Route::group(['prefix' => '/transfer'], function (Router $router) {
    $router->get('/{id}', 'TransferController@get');
    $router->get('/{id}/{accessToken}', 'TransferController@getWithAccessToken');
    $router->get('/{id}/{accessToken}/{fileId}/download', 'TransferController@download');
    $router->post('/{id}/complete', 'TransferController@complete');
    $router->post('/{id}/{templateFieldId}/upload', 'TransferController@upload');
});
