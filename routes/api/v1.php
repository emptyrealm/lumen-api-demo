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

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', ['namespace' => 'App\Http\Controllers\V1'], function ($api) {
	$api->group(['prefix'=>'v1'], function($api){

		$api->group(['prefix'=>'auth'], function($api){
			$api->post('/', 'AuthController@token');
			$api->put('/token', 'AuthController@refresh');
			$api->delete('/token', 'AuthController@destroy');
		});
		
	});
});