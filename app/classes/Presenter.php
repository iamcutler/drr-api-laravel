<?php

class Presenter implements PresenterRepositoryInterface {
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