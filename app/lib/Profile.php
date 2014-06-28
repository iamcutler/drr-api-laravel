<?php

class Profile implements ProfileRepositoryInterface {

  public function __construct(Activity $activity, User $user, UserField $field, UserPhoto $photo, UserPhotoAlbum $album, UserVideo $video,
                              PresenterRepositoryInterface $presenter)
  {
    $this->activity = $activity;
    $this->field = $field;
    $this->user = $user;
    $this->album = $album;
    $this->photo = $photo;
    $this->video = $video;
    $this->presenter = $presenter;
  }

  public function getFeed(User $user, $offset = 0, $limit = 10)
  {
    $activity = Cache::remember("profile-feed-{$user->id}-{$offset}", 30, function() use ($user, $offset, $limit) {
      return $user->profile_feed()->take($limit)->skip($offset)->get();
    });
    $result = [];

    foreach($activity as $key => $value)
    {
      $result[$key] = $this->presenter->profileFeed($user, $value);
    }

    return $result;
  }

  // Get user about fields
  public function about($user)
  {
    $fields = $this->field->where('visible', '=', 1)->where('published', '=', 1)->get();
    $result = [];

    foreach($fields as $key => $field)
    {
      $result[$key]['id'] = $field->id;
      $result[$key]['type'] = $field->type;
      $result[$key]['name'] = $field->name;
      $result[$key]['tip'] = $field->tips;
      $result[$key]['min'] = (int) $field->min;
      $result[$key]['max'] = (int) $field->max;
      $result[$key]['required'] = (int) $field->required;
      $result[$key]['fieldcode'] = $field->fieldcode;
      $result[$key]['params'] = json_decode($field->params);

      // Get field value
      $val = $field->value($user->id)->first();

      $result[$key]['value']['value'] = (is_null($val)) ? '' : $val->value;
      $result[$key]['value']['access'] = (is_null($val)) ? 0 : (int) $val->access;
    }

    return $result;
  }

  // Get profile friends
  public function friends($user)
  {
    $result = [];

    if(!is_null($user))
    {
      foreach($this->user->Find_profile_friends_by_id_array($user->friends) as $key => $value)
      {
        $result[$key]['name'] = $value->name;
        $result[$key]['username'] = $value->username;
        $result[$key]['avatar'] = $value->avatar;
        $result[$key]['thumbnail'] = $value->thumbnail;
        $result[$key]['alias'] = $value->alias;
      }
    }
    else {
      $result = ['status' => false, 'message' => 'User not found'];
    }

    return $result;
  }

  // Get user albums
  public function albums($user)
  {
    $result = [];

    if(is_null($user))
    {
      $result = ['status' => false, 'message' => 'User not found'];
    }
    else {
      $albums = $this->album->Find_all_by_user_id($user->id);

      // Loop through and add albums to output
      foreach($albums as $key => $val)
      {
        $result[$key]['id'] = $val['id'];
        $result[$key]['name'] = $val['name'];

        // Get photo instance
        $photoObj = $val->photo();
        $photos = $photoObj->find($val['photoid']);

        if(!is_null($photos))
        {
          $result[$key]['thumbnail'] = '/' . $photos->thumbnail;
        }
        else {
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

    return $result;
  }

  // Get single photo album
  public function album($slug, $id)
  {
    // Find request user
    $requester = $this->user->find_id_by_hash(Input::get('user_hash'));
    // Find album
    $album = $this->album->find($id);
    // Find album creator
    $user = $this->user->find($album->creator);
    $user_comm = $user->comm_user()->first();
    // Get all album photos
    $photos = $album->photo()->Find_all_by_album_id($id)->get();
    $results = [];

    // Check if slug matches owner alias
    if($user_comm->alias == $slug)
    {
      // Output album information
      $results['id'] = $album->id;
      $results['name'] = $album->name;
      $results['permissions'] = $album->permissions;
      $results['album_owner'] = ($requester->id === $album->creator) ? true : false;

      $results['photos'] = [];

      // Loop output to photos array
      foreach($photos as $key => $val)
      {
        $results['photos'][$key]['id'] = $val['id'];
        $results['photos'][$key]['thumbnail'] = '/' . $val['thumbnail'];
        $results['photos'][$key]['image'] = '/' . $val['image'];
        $results['photos'][$key]['params'] = json_decode($val['params']);
        $results['photos'][$key]['permissions'] = $val['permissions'];
        $results['photos'][$key]['created'] = $val['created'];

        $results['photos'][$key]['stats'] = $this->presenter->likeStats($val->likes->first(), 0);
        $results['photos'][$key]['comments'] = $this->presenter->Wall($val->wall);
      }
    }

    return $results;
  }

  // Get single video resource and stats
  public function video($slug, $id)
  {
    $video = $this->video->with('wall.user.comm_user')->find($id);
    $user = $this->user->findBySlug($slug)->first();
    $result = [];

    if(!is_null($video))
    {
      // Check if video is owned by user
      if($video->creator == $user->id)
      {
        $result['id'] = (int) $video->id;
        $result['title'] = $video->title;
        $result['type'] = $video->type;
        $result['description'] = $video->description;
        $result['permissions'] = $video->permissions;
        $result['featured'] = (int) $video->featured;
        $result['location'] = $video->location;
        $result['created'] = $video->created;

        $activity = $video->activity();

        if(!is_null($activity))
        {
          $result['comment_id'] = (int) $activity->comment_id;
          $result['comment_type'] = $activity->comment_type;
          $result['like_id'] = (int) $activity->like_id;
          $result['like_type'] = $activity->like_type;
        }

        $result['media']['video_id'] = (int) $video->video_id;
        $result['media']['thumbnail'] = '/' . $video->thumb;
        $result['media']['path'] = $video->path;
        $result['media']['filesize'] = (int) $video->filesize;
        $result['media']['duration'] = (int) $video->duration;

        // Resource owner
        $result['user'] = $this->presenter->User($user);

        // Resource stats
        $likes = $video->likes()->where('element', '=', 'videos')->first();
        $result['stats'] = $this->presenter->likeStats($likes, 0);

        // Resource comments
        $result['comments'] = $this->presenter->Wall($video->wall);
      }
    }

    return $result;
  }

  public function photo($slug, $id)
  {
    $photo = $this->photo->with('wall.user.comm_user')->find($id);
    $user = $this->user->findBySlug($slug)->first();
    $result = [];

    if(!is_null($photo))
    {
      // Check if slug matches creator
      if($photo->creator == $user->id)
      {
        $result['id'] = $photo->id;
        $result['caption'] = $photo->caption;
        $result['permissions'] = $photo->permissions;
        $result['hits'] = $photo->hits;
        $result['published'] = (int) $photo->published;
        $result['created'] = $photo->created;

        // Media array
        $result['media'] = $this->presenter->UserImage($photo, ['caption' => $photo->caption]);

        // Resource owner
        $result['user'] = $this->presenter->User($user);

        // Resource comments
        $result['comments'] = $this->presenter->Wall($photo->wall);

        // Resource stats
        $likes = $photo->likes()->first();
        $result['stats'] = $this->presenter->likeStats($likes, 0);
      }
    }

    return $result;
  }

  public function findOrCreateMobileAlbum($user_id)
  {
    $album = $this->album->findMobileAlbum($user_id);
    $result = [];

    if($album->count() == 0)
    {
      // If mobile not found, create new
      $new_album = $this->album->create([
        'photoid' => 0,
        'creator' => $user_id,
        'name' => 'Mobile Uploads',
        'description' => '',
        'permissions' => 0,
        'created' => date("Y-m-d H:i:s"),
        'path' => '',
        'type' => 'user',
        'location' => '',
        'params' => ''
      ]);

      if($new_album)
      {
        // Save album path
        $new_album->path = "images/photos/{$user_id}/{$new_album->id}";

        if($new_album->save())
        {
          // Find, and return new album object
          $result = $this->album->find($new_album->id);
        }
      }
    }
    else {
      // If found, return album object
      $result = $album;
    }

    return $result;
  }
}