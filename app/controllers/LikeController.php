<?php

class LikeController extends \BaseController {

  public function __construct(User $user, Activity $activity, UserActivityRepositoryInterface $user_activity)
  {
    $this->user = $user;
    $this->activity = $activity;
    $this->user_activity = $user_activity;
  }

  public function like()
  {
    $params = Input::all();
    $rules = [
      'element' => 'required',
      'id'      => 'required|integer',
      'type'    => 'required|integer'
    ];
    $validator = Validator::make($params, $rules);
    $result = ['status' => false];

    if($validator->passes())
    {
      // Get actor / user
      $user = $this->user->Find_id_by_hash($params['user_hash']);

      // Call user activity class to set and fetch like stats
      $result = $this->user_activity->setLike($user, $params['element'], $params['id'], $params['type']);
    }

    return Response::json($result);
  }
}