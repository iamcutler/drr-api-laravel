<?php

class ActivityController extends \BaseController {

  public function __construct(Activity $activity, User $user)
  {
    $this->activity = $activity;
    $this->user = $user;
  }

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {

  }

  /**
   * Show the form for creating a new resource.
   *
   * @return Response
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store()
  {
    $input = Input::all();
    $user = $this->user->Find_id_by_hash($input['user_hash']);
    $rules = [
      'event_id' => 'required|integer',
      'app' => 'required',
      'actor' => 'required|integer',
      'message' => 'required'
    ];
    $validator = Validator::make($input, $rules);
    $result = ['result' => false];

    if(!$validator->fails())
    {
      switch($input['app'])
      {
        case 'events.wall':
          $save = $this->activity->create([
            'actor' => $input['actor'],
            'target' => 0,
            'title' => $input['message'],
            'content' => '',
            'app' => $input['app'],
            'verb' => '',
            'cid' => $input['event_id'],
            'groupid' => 0,
            'eventid' => $input['event_id'],
            'created' => date('Y-m-d h:i:s'),
            'access' => 0,
            'params' => '',
            'archived' => 0,
            'location' => '',
            'comment_id' => 0,
            'comment_type' => $input['app'],
            'like_id' => 0,
            'like_type' => $input['app'],
            'actors' => ''
          ]);

          if($save)
          {
            $save->like_id = $save->id;
            $save->comment_id = $save->id;
            $save->save();

            // Return true and saved record
            $result['result'] = true;
            $result['activity'] = $this->get_activity($save);
          }
          break;
      }
    }

    return Response::json($result);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function show($id)
  {

  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function edit($id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function update($id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function destroy($id)
  {
    $input = Input::all();
    $rules = ['app' => 'required'];
    $validator = Validator::make($input, $rules);
    $result = ['result' => false];

    if(!$validator->fails())
    {
      $user = $this->user->Find_id_by_hash($input['user_hash']);
      $activity = $this->activity->find($id);

      if(!is_null($activity))
      {
        if($activity->app == $input['app'])
        {
          if($this->check_user_permissions($user, $activity))
          {
            $activity->delete();
            $result = ['result' => true];
          }
        }
      }
    }

    return Response::json($result);
  }

  protected function check_user_permissions(User $user, Activity $activity)
  {
    // Do a switch of the app type to find the right user permissions
    switch($activity->app)
    {
      // Events
      case 'events.wall':
        $event = $activity->event();
        $members = $event->member();
        foreach($members as $key => $member)
        {
          if($member->memberid == $user->id || $member->permission == 1)
          {
            return true;
          }
        }
        break;
      // Groups
      case 'groups.wall':
        $group = $activity->group();
        $members = $group->member();
        foreach($members as $key => $member)
        {
          if($member->memberid == $user->id || $member->permissions == 1)
          {
            return true;
          }
        }
        break;
    }

    return false;
  }

  // Paginate event activity
  protected function get_activity(Activity $activity)
  {
    // Event activity - Initial call will paginate 10 records.
    $results = [];
    $key = 0;
    $comm_user = $activity->actor_comm();
    $actor = $activity->actor();

    $results[$key]['id'] = $activity->id;
    $results[$key]['user']['id'] = $actor->id;
    $results[$key]['user']['name'] = $actor->name;
    $results[$key]['user']['avatar'] = $comm_user->avatar;
    $results[$key]['user']['thumbnail'] = $comm_user->thumb;
    $results[$key]['user']['slug'] = $comm_user->alias;

    $results[$key]['comments'] = [];
    foreach($activity->wall() as $k => $value)
    {
      $user = $value->user();
      $comm_user = $user->comm_user()->first();

      $results[$key]['comments'][$k]['user']['name'] = $user->name;
      $results[$key]['comments'][$k]['user']['avatar'] = $comm_user->avatar;
      $results[$key]['comments'][$k]['user']['thumbnail'] = $comm_user->thumb;
      $results[$key]['comments'][$k]['user']['slug'] = $comm_user->alias;

      $results[$key]['comments'][$k]['comment'] = $value['comment'];
      $results[$key]['comments'][$k]['date'] = $value['date'];
    }

    $results[$key]['app'] = $activity->app;
    $results[$key]['title'] = $activity->title;
    $results[$key]['comment_id'] = $activity->comment_id;
    $results[$key]['comment_type'] = $activity->comment_type;
    $results[$key]['like_id'] = $activity->like_id;
    $results[$key]['like_type'] = $activity->like_type;
    $results[$key]['created'] = $activity->created;

    // Get comment stats
    $results[$key]['stats'] = [];
    $results[$key]['stats']['likes'] = $activity->event_likes()->count();

    foreach($activity->event_likes()->get() as $i => $v)
    {
      // Likes
      $user = $v->user_like();

      if(!is_null($user))
      {
        $comm_user = $user->comm_user()->first();

        /*$results[$key]['stats']['likes']['user']['name'] = $user->name;
        $results[$key]['stats']['likes']['user']['avatar'] = $comm_user->avatar;
        $results[$key]['stats']['likes']['user']['thumbnail'] = $comm_user->thumb;
        $results[$key]['stats']['likes']['user']['slug'] = $comm_user->alias;*/
      }
    }

    return $results;
  }
}