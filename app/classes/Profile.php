<?php

class Profile implements ProfileRepositoryInterface {

  public function __construct(Activity $activity, User $user, UserField $field, UserPhoto $photo, UserPhotoAlbum $album)
  {
    $this->activity = $activity;
    $this->field = $field;
    $this->user = $user;
    $this->album = $album;
    $this->photo = $photo;
  }

  public function getFeed($id, $offset = 0, $limit = 10)
  {
    $activity = $this->activity->profile_feed($id, $offset, $limit);
    $result = [];

    foreach($activity as $key => $value)
    {
      $user = $value->actor();
      $user_comm = $user->comm_user()->first();

      // Resource
      $result[$key]['id'] = (int) $value->id;
      $result[$key]['actor'] = $value->actor;
      $result[$key]['target'] = $value->target;
      $result[$key]['title'] = $value->title;
      $result[$key]['type'] = $value->app;
      $result[$key]['comment_id'] = (int) $value->comment_id;
      $result[$key]['comment_type'] = $value->comment_type;
      $result[$key]['like_id'] = (int) $value->like_id;
      $result[$key]['like_type'] = $value->like_type;
      $result[$key]['created'] = $value->created;

      // Resource owner
      $result[$key]['user']['id'] = (int) $user->id;
      $result[$key]['user']['name'] = $user->name;
      $result[$key]['user']['thumbnail'] = $user_comm->thumb;
      $result[$key]['user']['avatar'] = $user_comm->avatar;
      $result[$key]['user']['slug'] = $user_comm->alias;

      // Resource stats
      $result[$key]['stats']['likes'] = (int) $value->likes()->where('element', '=', $value->like_type)->where('like', '!=', '')->count();
      $result[$key]['stats']['dislikes'] = (int) $value->likes()->where('element', '=', $value->like_type)->where('dislike', '!=', '')->count();

      // Resource comments
      $result[$key]['comments'] = [];
      foreach($value->wall() as $k => $v)
      {
        $user = $v->user();
        $comm_user = $user->comm_user()->first();

        $results[$key]['comments'][$k]['user']['id'] = $user->id;
        $results[$key]['comments'][$k]['user']['name'] = $user->name;
        $results[$key]['comments'][$k]['user']['avatar'] = $comm_user->avatar;
        $results[$key]['comments'][$k]['user']['thumbnail'] = $comm_user->thumb;
        $results[$key]['comments'][$k]['user']['slug'] = $comm_user->alias;

        $results[$key]['comments'][$k]['comment'] = $value['comment'];
        $results[$key]['comments'][$k]['date'] = $value['date'];
      }

      // Resource media
      $result[$key]['media'] = [];

      // Output media array based on activity type
      if($value->app == 'videos')
      {
        $media = $value->video();

        if(!is_null($media))
        {
          $result[$key]['media']['title'] = $media->title;
          $result[$key]['media']['type'] = $media->type;
          $result[$key]['media']['video_id'] = $media->video_id;
          $result[$key]['media']['description'] = $media->description;
          $result[$key]['media']['thumb'] = $media->thumb;
          $result[$key]['media']['path'] = $media->path;
          $result[$key]['media']['created'] = $media->created;
        }
        else {
          $result[$key] = [];
        }
      }
      elseif($value->app == 'photos') {
        $media = $value->photo();

        if(!is_null($media))
        {
          $result[$key]['media']['caption'] = $media->caption;
          $result[$key]['media']['image'] = $media->image;
          $result[$key]['media']['thumbnail'] = $media->thumbnail;
          $result[$key]['media']['original'] = $media->original;
          $result[$key]['media']['created'] = $media->created;
        }
        else {
          $result[$key] = [];
        }
      }
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
          $result[$key]['thumbnail'] = $photos->thumbnail;
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
      $results['name'] = $album->name;
      $results['permissions'] = $album->permissions;
      $results['album_owner'] = ($requester->id === $album->creator) ? true : false;

      $results['photos'] = [];

      // Loop output to photos array
      foreach($photos as $key => $val)
      {
        $results['photos'][$key]['id'] = $val['id'];
        $results['photos'][$key]['thumbnail'] = $val['thumbnail'];
        $results['photos'][$key]['params'] = json_decode($val['params']);
        $results['photos'][$key]['permissions'] = $val['permissions'];
        $results['photos'][$key]['created'] = $val['created'];
      }
    }

    return $results;
  }
}