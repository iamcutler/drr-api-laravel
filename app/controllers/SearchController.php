<?php

class SearchController extends \BaseController {

  public function __construct(UserActivityRepositoryInterface $activity, Events $event, User $user)
  {
    $this->activity = $activity;
    $this->event = $event;
    $this->user = $user;
  }

  /**
   * @param string $q
   * @param string $type
   * @return Response
   */
  public function people()
  {
    $params = Input::all();
    $user = $this->user->find_id_by_hash($params['user_hash']);
    $rules = [
      'q' => 'required',
      'type' => 'required',
      'offset' => 'required|integer'
    ];
    $validation = Validator::make($params, $rules);
    $result = [];

    if($validation->passes())
    {
      // Run search on users
      $search = $this->activity->user_search($params['q'], $user->id, $params['type'], $params['offset']);

      if(!is_null($search))
      {
        $user_friends = $user->comm_user()->first()->friends;

        foreach($search as $key => $value)
        {
          $result[$key]['id'] = (int) $value->id;
          $result[$key]['name'] = $value->name;
          $result[$key]['avatar'] = $value->comm_user->avatar;
          $result[$key]['thumbnail'] = $value->comm_user->thumb;
          $result[$key]['slug'] = $value->comm_user->alias;

          // Return user relationship to requester
          $relation = 0;
          foreach(explode(',', $user_friends) as $friend)
          {
            if($friend == $value->id) { $relation++; }
          }

          // If increment is not 0, friend status is confirmed
          $result[$key]['friends'] = ($relation > 0) ? true : false;
        }
      }
    }

    return Response::json($result);
  }

  public function events()
  {
    $params = Input::all();
    $user = $this->user->find_id_by_hash($params['user_hash']);
    $rules = [
      'q' => 'required',
      'type' => 'required',
      'offset' => 'required|integer'
    ];
    $validation = Validator::make($params, $rules);
    $result = [];

    if($validation->passes())
    {
      $search = $this->event->searchEvents($params['q'], $params['type'], $params['offset'])->get();

      if(!is_null($search))
      {
        foreach($search as $key => $val)
        {
          $result[$key]['id'] = (int) $val->id;
          $result[$key]['title'] = $val->title;
          $result[$key]['location'] = $val->location;
          $result[$key]['startdate'] = $val->startdate;
          $result[$key]['enddate'] = $val->enddate;
          $result[$key]['avatar'] = $val->avatar;
          $result[$key]['thumbnail'] = $val->thumb;
        }
      }
    }

    return Response::json($result);
  }
}