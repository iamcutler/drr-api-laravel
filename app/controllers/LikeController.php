<?php

class LikeController extends \BaseController {

  public function __construct(User $user, Activity $activity, UserActivityRepositoryInterface $user_activity)
  {
    $this->user = $user;
    $this->activity = $activity;
    $this->user_activity = $user_activity;
  }

  public function like($element, $id, $type)
  {
    $params = Input::all();
    $result = ['status' => false];

    // Get actor / user
    $user = $this->user->Find_id_by_hash($params['user_hash']);
    // Find activity to like
    $activity = $this->activity->find_by_like_id($id);

    if(!is_null($activity))
    {
      // Call user activity class to set and fetch like stats
      $result = $this->user_activity->setLike($user, $element, $id, $type);
    }

    return Response::json($result);
  }
}