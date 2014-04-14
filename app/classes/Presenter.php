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
}