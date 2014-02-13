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

// Home
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
Route::post('/check/username/{username}', 'UserController@check_username_uniqueness');

/*
|--------------------------------------------------------------------------
| User
|--------------------------------------------------------------------------
*/
// Registration
Route::post('/user/new', 'UserController@create');

/*
|--------------------------------------------------------------------------
| Group all API routing to run hash and auth filters for permissions
|--------------------------------------------------------------------------
 */
Route::group(['before' => 'user-hash-auth'], function() {
  /*
  |--------------------------------------------------------------------------
  | User
  |--------------------------------------------------------------------------
  */
  Route::group(['prefix' => 'user'], function() {
    Route::resource('connections', 'UserConnectionController', ['only' => ['index', 'store', 'update', 'destroy']]);
    Route::resource('messages', 'MessageController', ['only' => ['index', 'show', 'store']]);
    route::resource('events', 'EventController');
    route::resource('groups', 'GroupController');
    Route::group(['prefix' => 'like'], function() {
      route::post('like/{element}/{id}/{type}', 'LikeController@like');
    });

    // Profile
    Route::group(['prefix' => 'profile'], function() {
      Route::get('{slug}', 'ProfileController@get_profile_by_slug');
      Route::get('about/{slug}', 'ProfileController@about');
      Route::get('friends/{slug}', 'ProfileController@friends');
      Route::get('albums/{slug}', 'ProfileController@photo_albums');
      Route::get('album/{slug}/{id}', 'ProfileController@album_photos');
      Route::get('videos/{slug}', 'ProfileController@videos');
    });
  });

  /*
  |--------------------------------------------------------------------------
  | Dirty Girls
  |--------------------------------------------------------------------------
  */
  Route::resource('dirty-girls', 'DirtyGirlController', ['only' => ['index', 'show']]);
  Route::group(['prefix' => 'dirty-girls'], function() {
    // Dirty girl voting
    Route::resource('voting/current', 'VoteController', ['only' => ['index', 'store']]);
  });

  /*
  |--------------------------------------------------------------------------
  | Application
  |--------------------------------------------------------------------------
  */
  // Report a bug
  Route::post('report/bug', 'ReportController@bug');
});
