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

  static function Wall($wall)
  {
    $result = [];
    foreach($wall as $key => $value)
    {
      $user = $value->user();
      $comm_user = $user->comm_user()->first();

      $result[$key]['user']['id'] = $user->id;
      $result[$key]['user']['name'] = $user->name;
      $result[$key]['user']['avatar'] = $comm_user->avatar;
      $result[$key]['user']['thumbnail'] = $comm_user->thumb;
      $result[$key]['user']['slug'] = $comm_user->alias;

      $result[$key]['id'] = $value['id'];
      $result[$key]['type'] = $value['type'];
      $result[$key]['comment'] = $value['comment'];
      $result[$key]['date'] = $value['date'];
    }

    return $result;
  }
}