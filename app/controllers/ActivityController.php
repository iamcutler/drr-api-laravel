<?php

class ActivityController extends \BaseController {

  public function __construct(Activity $activity, User $user, Events $event)
  {
    $this->activity = $activity;
    $this->user = $user;
    $this->event = $event;
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
    $activity = $this->activity->find($id);
    $results = [];

    if(!is_null($activity))
    {
      $user = $activity->actor();
      $comm_user = $user->comm_user()->first();

      $results['activity']['id'] = $activity->id;
      $results['activity']['title'] = $activity->title;
      $results['activity']['content'] = $activity->content;
      $results['activity']['app'] = $activity->app;
      $results['activity']['verb'] = $activity->verb;
      $results['activity']['cid'] = $activity->cid;
      $results['activity']['groupid'] = $activity->groupid;
      $results['activity']['eventid'] = $activity->eventid;
      $results['activity']['group_access'] = $activity->group_access;
      $results['activity']['event_access'] = $activity->event_access;
      $results['activity']['access'] = $activity->access;
      $results['activity']['params'] = json_decode($activity->params);
      $results['activity']['points'] = $activity->points;
      $results['activity']['archived'] = $activity->archived;
      $results['activity']['location'] = $activity->location;
      $results['activity']['comment_id'] = $activity->comment_id;
      $results['activity']['comment_type'] = $activity->comment_type;
      $results['activity']['like_id'] = $activity->like_id;
      $results['activity']['like_type'] = $activity->like_type;
      $results['activity']['actors'] = $activity->actors;
      $results['activity']['created'] = $activity->created;

      $results['activity']['user']['id'] = $user->id;
      $results['activity']['user']['name'] = $user->name;
      $results['activity']['user']['avatar'] = $comm_user->avatar;
      $results['activity']['user']['thumbnail'] = $comm_user->thumb;
      $results['activity']['user']['slug'] = $comm_user->alias;

      $results['activity']['target'] = [];
      if($activity->target != 0 || $activity->actor == $activity->target)
      {
        $target = $activity->target();
        $target_comm = $target->comm_user()->first();

        $results['activity']['target']['id'] = $target->id;
        $results['activity']['target']['name'] = $target->name;
        $results['activity']['target']['avatar'] = $target_comm->avatar;
        $results['activity']['target']['thumbnail'] = $target_comm->thumb;
        $results['activity']['target']['slug'] = $target_comm->alias;
      }
      else {
        $results['activity']['target']['id'] = $user->id;
        $results['activity']['target']['name'] = $user->name;
        $results['activity']['target']['avatar'] = $comm_user->avatar;
        $results['activity']['target']['thumbnail'] = $comm_user->thumb;
        $results['activity']['target']['slug'] = $comm_user->alias;
      }

      // Activity Stats
      $results['activity']['stats']['likes'] = (int) $activity->likes()->where('like', '!=', '')->count();
      $results['activity']['stats']['dislikes'] = (int) $activity->likes()->where('dislike', '!=', '')->count();

      // Activity comments
      $results['activity']['comments'] = [];
      foreach($activity->wall() as $key => $value)
      {
        $user = $value->user();
        $comm_user = $user->comm_user()->first();

        $results['activity']['comments'][$key]['id'] = $value->id;
        $results['activity']['comments'][$key]['comment'] = $value->comment;
        $results['activity']['comments'][$key]['date'] = $value->date;
        $results['activity']['comments'][$key]['type'] = $value->type;

        $results['activity']['comments'][$key]['user']['id'] = $user->id;
        $results['activity']['comments'][$key]['user']['name'] = $user->name;
        $results['activity']['comments'][$key]['user']['thumbnail'] = $comm_user->thumb;
        $results['activity']['comments'][$key]['user']['avatar'] = $comm_user->avatar;
        $results['activity']['comments'][$key]['user']['slug'] = $comm_user->alias;
        $results['activity']['comments'][$key]['user']['slug'] = $comm_user->alias;

        // Comment stats
        $results['activity']['comments'][$key]['stats']['likes'] = (int) $value->activity_likes()->where('element', '=', $activity->comment_type)->where('like', '!=', '')->count();
        $results['activity']['comments'][$key]['stats']['dislikes'] = (int) $value->activity_likes()->where('element', '=', $activity->comment_type)->where('dislike', '!=', '')->count();
      }
    }

    return Response::json($results);
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

  public function event_attendance($id)
  {
    $user = $this->user->Find_id_by_hash(Input::get('user_hash'));
    $event = $this->event->find($id);
    $result = ['result' => false];
    $attending = 0;

    if(!is_null($event))
    {
      foreach($event->member() as $key => $value)
      {
        if($value->memberid == $user->id)
        {
          if($value->status != 1)
          {
            $attending = 1;
          }

          // Save new attendance for event member
          $value->status = $attending;
          $value->save();

          $result['result'] = true;
          $result['attending'] = $attending;
        }
      }
    }

    return $result;
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