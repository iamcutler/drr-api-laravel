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
  return View::make('home');
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
// Login
Route::group(['prefix' => 'login'], function() {
  Route::post('user', 'AuthController@login');
  // Facebook authentication
  Route::post('fb', 'AuthController@facebook_login');
});
// Check username uniqueness
Route::post('/check/username/{username}', 'UserController@check_username_uniqueness');

/*
|--------------------------------------------------------------------------
| User
|--------------------------------------------------------------------------
*/
Route::post('/register', 'UserController@store');
//Route::resource('user', 'UserController', ['only' => ['store']]);

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
    // Rest connections
    Route::resource('connections', 'UserConnectionController', ['only' => ['index', 'store', 'update', 'destroy']]);
    // Remove user connections
    Route::delete('connections/remove/{id}', 'UserConnectionController@remove_friend_connection');

    Route::resource('messages', 'MessageController', ['only' => ['index', 'show', 'store']]);
    Route::resource('events', 'EventController');
    Route::resource('groups', 'GroupController');

    Route::group(['prefix' => 'like'], function() {
      Route::post('/', 'LikeController@like');
    });

    // Account
    Route::group(['prefix' => 'account'], function() {
      Route::get('settings', 'AccountController@profile_settings');
      Route::post('settings', 'AccountController@update_profile_settings');
    });

    // Search
    Route::group(['prefix' => 'search'], function() {
      Route::get('people', 'SearchController@people');
      Route::get('events', 'SearchController@events');
      Route::get('groups', 'SearchController@groups');
    });

    // Activity
    Route::resource('activity', 'ActivityController', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    Route::resource('wall', 'WallController', ['only' => ['index', 'store', 'destroy']]);
    Route::post('activity/event_attendance/{id}', 'ActivityController@event_attendance');

    Route::group(['prefix' => 'feed_activity'], function() {
      Route::get('{offset}', 'FeedController@index');
      Route::get('event', 'EventController@activity');
      Route::get('event-categories', 'EventController@categories');
      Route::get('media/{offset}', 'FeedController@media');
      Route::get('profile/{slug}/{offset}', 'ProfileController@feed');
    });

    // Profile
    Route::group(['prefix' => 'profile'], function() {
      Route::get('{slug}', 'ProfileController@user_profile');
      Route::get('about/{slug}', 'ProfileController@about');
      Route::get('friends/{slug}', 'ProfileController@friends');
      Route::get('albums/{slug}', 'ProfileController@photo_albums');
      Route::get('album/{slug}/{id}', 'ProfileController@album_photos');
      Route::get('videos/{slug}', 'ProfileController@videos');
      Route::get('video/{slug}/{id}', 'ProfileController@video');
      Route::get('photo/{slug}/{id}', 'ProfileController@photo');
      Route::get('groups/{slug}', 'GroupController@user_groups');
      Route::get('events/{slug}', 'EventController@user_events');
    });
  });

  /*
  |--------------------------------------------------------------------------
  | Dirty Girls
  |--------------------------------------------------------------------------
  */
  Route::group(['prefix' => 'dirty-girls'], function() {
    Route::get('/', 'DirtyGirlController@index');
    Route::resource('voting/current', 'VoteController', ['only' => ['index', 'store']]);

    Route::group(['prefix' => 'submission'], function() {
      Route::post('/', 'DirtyGirlSubmissionController@store');
      Route::post('image', 'DirtyGirlSubmissionController@uploadImage');
    });
  });

  /*
  |--------------------------------------------------------------------------
  | Application
  |--------------------------------------------------------------------------
  */
  // Report a bug
  Route::post('report/bug', 'ReportController@bug');
});
