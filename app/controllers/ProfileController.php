<?php

class ProfileController extends \BaseController {
  public function __construct(User $user)
  {
    $this->user = $user;
  }

  public function get_profile_by_slug($slug) {
    $user = $this->user->Find_user_profile_by_slug($slug);

    // Profile array
    $profile = [];
    $profile['user']['id'] = $user->id;
    $profile['user']['name'] = $user->name;
    $profile['user']['username'] = $user->username;
    $profile['user']['slug'] = $user->slug;
    $profile['user']['avatar'] = $user->avatar;
    $profile['user']['thumbnail'] = $user->thumbnail;
    // Profile array
    $profile['profile']['status'] = $user->status;
    $profile['profile']['views'] = $user->view;
    $profile['profile']['friends'] = $user->friends;
    $profile['profile']['friend_count'] = $user->friendcount;
    $profile['profile']['last_visit'] = $user->last_visit;
    $profile['profile']['registered'] = $user->registered;
    // Friends array
    $profile['friends'] = [];
    if($user->friends != NULL) {
      foreach(str_getcsv($user->friends, ',') as $key => $val) {
        $friend = User::Find_friend_by_id($val);

        $profile['friends'][$key] = $friend;
      }
    }
    // Media array
    $profile['media'] = [];
    // Photos albums array
    $profile['media']['photo_albums'] = [];
    foreach(UserPhotoAlbum::Find_all_by_user_id($user->id) as $key => $val) {
      $profile['media']['photo_albums'][$key]['id'] = $val->id;
      $profile['media']['photo_albums'][$key]['name'] = $val->name;
      $profile['media']['photo_albums'][$key]['description'] = $val->description;
      $profile['media']['photo_albums'][$key]['path'] = $val->path;
      $profile['media']['photo_albums'][$key]['hits'] = $val->hits;
      $profile['media']['photo_albums'][$key]['location'] = $val->location;
      $profile['media']['photo_albums'][$key]['default'] = $val->default;
      $profile['media']['photo_albums'][$key]['created'] = $val->created;
    }
    // Photos array
    $profile['media']['photos'] = [];
    foreach(UserPhoto::Find_all_by_user_id($user->id) as $key => $val) {
      $profile['media']['photos'][$key]['id'] = $val->id;
      $profile['media']['photos'][$key]['albumid'] = $val->albumid;
      $profile['media']['photos'][$key]['caption'] = $val->caption;
      $profile['media']['photos'][$key]['image'] = $val->image;
      $profile['media']['photos'][$key]['thumbnail'] = $val->thumbnail;
      $profile['media']['photos'][$key]['original'] = $val->original;
      $profile['media']['photos'][$key]['hits'] = $val->hits;
      $profile['media']['photos'][$key]['created'] = $val->created;
    }
    // Videos array
    $profile['media']['videos'] = [];
    foreach(UserVideo::Find_all_by_user_id($user->id) as $key => $val) {
      $profile['media']['videos'][$key]['id'] = $val->id;
      $profile['media']['videos'][$key]['title'] = $val->title;
      $profile['media']['videos'][$key]['type'] = $val->type;
      $profile['media']['videos'][$key]['video_id'] = $val->video_id;
      $profile['media']['videos'][$key]['description'] = $val->description;
      $profile['media']['videos'][$key]['permissions'] = $val->permissions;
      $profile['media']['videos'][$key]['category_id'] = $val->category_id;
      $profile['media']['videos'][$key]['hits'] = $val->hits;
      $profile['media']['videos'][$key]['featured'] = $val->featured;
      $profile['media']['videos'][$key]['duration'] = $val->duration;
      $profile['media']['videos'][$key]['status'] = $val->status;
      $profile['media']['videos'][$key]['thumbnail'] = $val->thumb;
      $profile['media']['videos'][$key]['path'] = $val->path;
      $profile['media']['videos'][$key]['groupid'] = $val->groupid;
      $profile['media']['videos'][$key]['location'] = $val->location;
      $profile['media']['videos'][$key]['created'] = $val->created;
    }
    // Groups array
    $profile['groups'] = [];
    if($user->groups != NULL) {
      foreach(str_getcsv($user->groups, ',') as $key => $val) {
        $group = UserGroup::Find_by_id($val);

        $profile['groups'][$key]['ownerid'] = $group->ownerid;
        $profile['groups'][$key]['categoryid'] = $group->categoryid;
        $profile['groups'][$key]['name'] = $group->name;
        $profile['groups'][$key]['description'] = $group->description;
        $profile['groups'][$key]['email'] = $group->email;
        $profile['groups'][$key]['website'] = $group->website;
        $profile['groups'][$key]['approvals'] = $group->approvals;
        $profile['groups'][$key]['avatar'] = $group->avatar;
        $profile['groups'][$key]['thumbnail'] = $group->thumb;
        $profile['groups'][$key]['discusscount'] = $group->discusscount;
        $profile['groups'][$key]['wallcount'] = $group->wallcount;
        $profile['groups'][$key]['membercount'] = $group->membercount;
        $profile['groups'][$key]['params'] = $group->params;
        $profile['groups'][$key]['created'] = $group->created;
      }
    }
    /* Events array
    $profile['events'] = [];
    if($user->events != NULL) {
      foreach(str_getcsv($user->events, ',') as $key => $val) {
        $event = UserEvent::Find_by_id($val);

        $profile['events'][$key]['id'] = $event->id;
      }
    }*/

    return Response::json($profile);
  }
}