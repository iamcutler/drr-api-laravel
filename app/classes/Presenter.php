<?php

class Presenter implements PresenterRepositoryInterface {
  static function User(User $user, Array $options = [])
  {
    $result = [];
    $result['id'] = (int) $user->id;
    $result['name'] = $user->name;
    $result['thumbnail'] = $user->thumb;
    $result['avatar'] = $user->avatar;
    $result['slug'] = $user->alias;

    // Pass in additional options for output
    foreach($options as $key => $val)
    {
      $result[$key] = $val;
    }

    return $result;
  }

  static function UserImage(UserPhoto $image, Array $options = [])
  {
    $result = [];
    $result['image'] = $image->image;
    $result['thumbnail'] = $image->thumbnail;
    $result['original'] = $image->original;
    $result['filesize'] = (int) $image->filesize;

    foreach($options as $key => $val)
    {
      $result[$key] = $val;
    }

    return $result;
  }

  static function likeStats($likes)
  {
    $result = [];

    $result['likes'] = (int) $likes->where('like', '!=', '')->count();
    $result['dislikes'] = (int) $likes->where('dislike', '!=', '')->count();

    return $result;
  }

  static function Wall($wall)
  {
    $result = [];
    foreach($wall as $key => $value)
    {
      $result[$key]['user']['id'] = $value->user->id;
      $result[$key]['user']['name'] = $value->user->name;
      $result[$key]['user']['avatar'] = $value->user->comm_user->avatar;
      $result[$key]['user']['thumbnail'] = $value->user->comm_user->thumb;
      $result[$key]['user']['slug'] = $value->user->comm_user->alias;

      $result[$key]['id'] = $value['id'];
      $result[$key]['type'] = $value['type'];
      $result[$key]['comment'] = $value['comment'];
      $result[$key]['date'] = $value['date'];
    }

    return $result;
  }

  public function profileFeed(Activity $value)
  {
    $result = [];
    // Resource
    $result['id'] = (int) $value->id;
    $result['title'] = $value->title;
    $result['type'] = $value->app;
    $result['comment_id'] = (int) $value->comment_id;
    $result['comment_type'] = $value->comment_type;
    $result['like_id'] = (int) $value->like_id;
    $result['like_type'] = $value->like_type;
    $result['created'] = $value->created;

    /// Resource owner
    $result['user']['id'] = (int) $value->userActor->id;
    $result['user']['name'] = $value->userActor->name;
    $result['user']['thumbnail'] = $value->userActor->comm_user->thumb;
    $result['user']['avatar'] = $value->userActor->comm_user->avatar;
    $result['user']['slug'] = $value->userActor->comm_user->alias;

    // Resource Target
    if($value->target == $value->actor || $value->target == 0)
    {
      $result['target']['id'] = (int) $value->userActor->id;
      $result['target']['name'] = $value->userActor->name;
      $result['target']['thumbnail'] = $value->userActor->comm_user->thumb;
      $result['target']['avatar'] = $value->userActor->comm_user->avatar;
      $result['target']['slug'] = $value->userActor->comm_user->alias;
    }
    else {
      $result['target']['id'] = (int) $value->userTarget->id;
      $result['target']['name'] = $value->userTarget->name;
      $result['target']['thumbnail'] = $value->userTarget->comm_user->thumb;
      $result['target']['avatar'] = $value->userTarget->comm_user->avatar;
      $result['target']['slug'] = $value->userTarget->comm_user->alias;
    }

    //Resource stats
    $result['stats']['likes'] = (int) $value->likes()->where('element', '=', $value->like_type)->where('like', '!=', '')->count();

    // Resource comments
    $result['comments'] = [];
    foreach($value->wall as $k => $val)
    {
      $result['comments'][$k]['user']['id'] = (int) $val->user->id;
      $result['comments'][$k]['user']['name'] = $val->user->name;
      $result['comments'][$k]['user']['avatar'] = $val->user->comm_user->avatar;
      $result['comments'][$k]['user']['thumbnail'] = $val->user->comm_user->thumb;
      $result['comments'][$k]['user']['slug'] = $val->user->comm_user->alias;

      $result['comments'][$k]['comment'] = $val->comment;
      $result['comments'][$k]['date'] = $val->date;
    }

    // Resource media
    $result['media'] = [];
    // Output media array based on activity type
    if($value->app == 'videos')
    {
      $media = $value->video;

      if(!is_null($media))
      {
        $result['media']['title'] = $media->title;
        $result['media']['type'] = $media->type;
        $result['media']['video_id'] = $media->video_id;
        $result['media']['description'] = $media->description;
        $result['media']['thumb'] = $media->thumb;
        $result['media']['path'] = $media->path;
        $result['media']['created'] = $media->created;
      }
    }
    elseif($value->app == 'photos') {
      $media = $value->photo;

      if(!is_null($media))
      {
        $result['media']['caption'] = $media->caption;
        $result['media']['image'] = $media->image;
        $result['media']['thumbnail'] = $media->thumbnail;
        $result['media']['original'] = $media->original;
        $result['media']['created'] = $media->created;
      }
    }

    return $result;
  }
}