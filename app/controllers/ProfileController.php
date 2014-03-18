<?php

class ProfileController extends \BaseController {

  public function __construct(User $user, CommUser $comm_user, UserPhotoAlbum $album, UserPhoto $photo, UserVideo $video,
                              UserConnection $connection, EventMember $eventMember, GroupMember $groupMember,
                              ProfileRepositoryInterface $profile)
  {
    $this->user = $user;
    $this->comm_user = $comm_user;
    $this->photo = $photo;
    $this->album = $album;
    $this->video = $video;
    $this->connection = $connection;
    $this->eventMember = $eventMember;
    $this->groupMember = $groupMember;
    $this->profile = $profile;
  }

  public function get_profile_by_slug($slug) {

    $requester = $this->user->Find_id_by_hash(Input::get('user_hash'));
    $user = $this->user->Find_user_profile_by_slug($slug);

    // Profile array
    $profile = [];

    if(!is_null($user) && !is_null($requester))
    {
      // Get user instance
      $user_profile = $this->user->find($user->id);

      $profile['user']['id'] = $user->id;
      $profile['user']['name'] = $user->name;
      $profile['user']['username'] = $user->username;
      $profile['user']['slug'] = $user->slug;
      $profile['user']['avatar'] = $user->avatar;
      $profile['user']['thumbnail'] = $user->thumbnail;

      // Relation array to tell relationship to requester
      $profile['relation'] = [];
      $profile['relation']['self'] = false;
      $profile['relation']['friends'] = false;
      $profile['relation']['request_sent'] = false;

      // Detect if requester is also the user
      if($requester->id == $user->id)
      {
        $profile['relation']['self'] = true;
      }

      // Check if requester and user are friends
      foreach(str_getcsv($user->friends, ',') as $key => $val)
      {
        if($val == $requester->id)
        {
          $profile['relation']['friends'] = true;
        }
      }

      // Change friend request to true if detected
      if($this->connection->Find_existing_connection($user->id, $requester->id)->count() > 0)
      {
        $profile['relation']['request_sent'] = true;
      }

      // Profile array
      $profile['profile']['status']['status'] = $user->status;
      $profile['profile']['status']['created'] = $user->posted_on;

      $profile['profile']['views'] = $user->view;
      $profile['profile']['friends'] = $user->friends;
      $profile['profile']['friend_count'] = $user->friendcount;
      $profile['profile']['last_visit'] = $user->last_visit;
      $profile['profile']['registered'] = $user->registered;

      $profile['profile']['settings'] = json_decode($user->profile_params);

      // Profile status
      $profile['profile']['stats']['likes'] = (int) $user_profile->profile_likes()->count();
      $profile['profile']['stats']['dislikes'] = (int) $user_profile->profile_dislikes()->count();

      // Count array
      $profile['profile']['counts']['photos'] = $this->photo->Find_all_by_user_id($user->id)->count();
      $profile['profile']['counts']['videos'] = $this->video->Find_all_by_user_id($user->id)->count();

      // Profile Feed
      $profile['profile']['feed'] = $this->profile->getFeed($user->id);

      // User upcoming events
      $event_count = 0;
      foreach($this->eventMember->Find_by_user_id($user->id) as $k => $v)
      {
        foreach($v->event() as $val)
        {
          $event_count++;
        }
      }

      $profile['profile']['counts']['events'] = $event_count;
      $profile['profile']['counts']['groups'] = $this->groupMember->Find_by_user_id($user->id)->count();

      $friend_count = 0;
      foreach(str_getcsv($user->friends, ',') as $val ) { $friend_count++; }
      $profile['profile']['counts']['friends'] = $friend_count;
    }
    else
    {
      $profile['status'] = false;
      $profile['message'] = 'User was not found';
    }

    return Response::json($profile);
  }

  public function about($slug)
  {
    $user = $this->user->Find_user_profile_by_slug($slug);
    $result = $this->profile->about($user);

    return Response::json($result);
  }

  public function friends($slug)
  {
    $user = $this->user->Find_user_profile_by_slug($slug);
    $result = $this->profile->friends($user);

    return Response::json($result);
  }

  public function photo_albums($slug)
  {
    $user = $this->user->Find_user_profile_by_slug($slug);
    $result = [];

    if(is_null($user))
    {
      $result = ['status' => false, 'message' => 'User not found'];
    }
    else
    {
      foreach($this->album->Find_all_by_user_id($user->id) as $key => $val)
      {
        $result[$key]['id'] = $val['id'];
        $result[$key]['name'] = $val['name'];

        if(!is_null($this->photo->find($val['photoid'])))
        {
          $result[$key]['thumbnail'] = $this->photo->find($val['photoid'])->thumbnail;
        }
        else
        {
          $result[$key]['thumbnail'] = null;
        }

        $result[$key]['hits'] = $val['hits'];
        $result[$key]['location'] = $val['location'];
        $result[$key]['photo_count'] = $this->photo->Find_all_by_album_id($val['id'])->count();
        $result[$key]['permissions'] = $val['permissions'];
        $result[$key]['params'] = json_decode($val['params']);
        $result[$key]['default'] = $val['default'];
        $result[$key]['created'] = $val['created'];
      }
    }

    return Response::json($result);
  }

  public function album_photos($slug, $id)
  {
    $album = $this->album->find($id);
    $user = $this->comm_user->find($album->creator);
    $photos = $this->photo->Find_all_by_album_id($id);
    $results = [];

    // Output album information
    $results['name'] = $album->name;
    $results['permissions'] = $album->permissions;
    $results['album_owner'] = false;

    if($user->alias == $slug)
    {
      $results['album_owner'] = true;
    }

    $results['photos'] = [];
    // Loop output to photos array
    foreach($photos->get() as $key => $val)
    {
      $results['photos'][$key]['id'] = $val['id'];
      $results['photos'][$key]['thumbnail'] = $val['thumbnail'];
      $results['photos'][$key]['params'] = json_decode($val['params']);
      $results['photos'][$key]['permissions'] = $val['permissions'];
      $results['photos'][$key]['created'] = $val['created'];
    }

    return Response::json($results);
  }

  public function videos($slug)
  {
    $user = $this->comm_user->Find_by_slug($slug);
    $result = [];

    if(!is_null($user))
    {
      $result = $this->video->Find_all_by_user_id($user->userid)->get();
    }

    return Response::json($result);
  }
}