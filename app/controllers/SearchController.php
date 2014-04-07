<?php

class SearchController extends \BaseController {

  public function __construct(UserActivityRepositoryInterface $activity, User $user)
  {
    $this->activity = $activity;
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
        foreach($search as $key => $value)
        {
          $comm_user = $value->comm_user()->first();

          if(!is_null($comm_user))
          {
            $result[$key]['id'] = (int) $value['id'];
            $result[$key]['name'] = $value['name'];
            $result[$key]['avatar'] = $comm_user['avatar'];
            $result[$key]['thumbnail'] = $comm_user['thumb'];
            $result[$key]['slug'] = $comm_user['alias'];

            // Return user relationship to requester
            $relation = 0;
            foreach(explode(',', $user->comm_user()->first()->friends) as $friend)
            {
              if($friend == $value['id']) { $relation++; }
            }

            // If increment is not 0, friend status is confirmed
            $result[$key]['friends'] = ($relation > 0) ? true : false;
          }
        }
      }
    }

    return Response::json($result);
  }

  public function events()
  {

  }
}
