<?php

class SearchController extends \BaseController {

  public function __construct(UserActivityRepositoryInterface $activity, Events $event, Group $group, User $user)
  {
    $this->activity = $activity;
    $this->event = $event;
    $this->group = $group;
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
          $result[$key]['username'] = $value->username;
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

  /**
   * @params string q
   * @params int offset
   * @return array
   */
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

  /**
   * @params string q
   * @params int offset
   * @return array
   */
  public function groups()
  {
    $params = Input::all();
    //$user = $this->user->find_id_by_hash($params['user_hash']);
    $rules = [
      'offset' => 'required|integer'
    ];
    $validation = Validator::make($params, $rules);
    $result = [];

    if($validation->passes())
    {
      // Run search on groups
      if($params['q'] != '' || $params['q'])
      {
        $search = $this->group->findByName($params['q'], $params['offset'])->get();
      }
      else {
        $search = $this->group->findAll($params['offset'])->get();
      }

      if(!is_null($search))
      {
        foreach($search as $key => $value)
        {
          $result[$key]['id'] = (int) $value->id;
          $result[$key]['category'] = $value->category->name;
          $result[$key]['name'] = $value->name;
          $result[$key]['description'] = $value->description;
          $result[$key]['avatar'] = '/'. $value->avatar;
          $result[$key]['thumbnail'] = '/'. $value->thumb;
          $result[$key]['params'] = json_decode($value->params);
          $result[$key]['created'] = $value->created;

          $result[$key]['members'] = [];
          foreach($value->member as $k => $v)
          {
            $result[$key]['members'][$k]['name'] = $v->user->name;
            $result[$key]['members'][$k]['username'] = $v->user->username;
            $result[$key]['members'][$k]['avatar'] = $v->user->comm_user->avatar;
            $result[$key]['members'][$k]['thumbnail'] = $v->user->comm_user->thumb;
            $result[$key]['members'][$k]['slug'] = $v->user->comm_user->alias;
          }
        }
      }
    }

    return Response::json($result);
  }
}
