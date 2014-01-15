<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('hello');
});
/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
// Login
Route::post('/user/login', 'AuthController@login');

// Check username uniqueness
Route::get('/check/username/{username}', 'UserController@check_username_uniqueness');