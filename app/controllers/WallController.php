<?php

class WallController extends \BaseController {

  public function __construct(CommWall $comment, Activity $activity, User $user)
  {
    $this->comment = $comment;
    $this->user = $user;
    $this->activity = $activity;
  }
  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {
    //
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
    $params = Input::all();
    $rules = [
      'cid' => 'required|integer',
      'user' => 'required|integer',
      'app' => 'required',
      'comment' => 'required'
    ];
    $validator = Validator::make($params, $rules);
    $result = ['result' => false];

    if($validator->passes())
    {
      switch($params['app'])
      {
        case 'videos':
          $activity = $this->activity->FindByCommentId($params['cid']);
          break;
        default:
          $activity = $this->activity->FindByCommentId($params['cid']);
      }

      if(!is_null($activity))
      {
        // Check and make sure app type comparison
        if($params['app'] == $activity->comment_type)
        {
          $act_access = $activity->access;

          $save = $this->comment->create([
            'contentid' => $params['cid'],
            'post_by' => $params['user'],
            'ip' => Request::getClientIp(),
            'comment' => $params['comment'],
            'date' => date('Y-m-d H:i:s'),
            'published' => 1,
            'type' => $activity->comment_type
          ]);

          if($save)
          {
            $user = $save->user()->first();
            $comm_user = $user->comm_user()->first();

            $result['result'] = true;
            $result['wall']['id'] = $save->id;
            $result['wall']['type'] = $save->type;
            $result['wall']['comment'] = $save->comment;
            $result['wall']['created'] = $save->date;

            $result['wall']['user']['id'] = $user->id;
            $result['wall']['user']['name'] = $user->name;
            $result['wall']['user']['thumbnail'] = $comm_user->thumb;
            $result['wall']['user']['avatar'] = $comm_user->avatar;
            $result['wall']['user']['slug'] = $comm_user->alias;
          }
        }
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
    //
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
    $comment = $this->comment->find($id);
    $result = ['result' => false];

    if(!is_null($comment))
    {
      $actor = $comment->user()->first();
      $activity = $comment->activity()->where('comment_type', '=', $comment->type)->first();
      $user = $this->user->Find_id_by_hash(Input::get('user_hash'));

      // Check requester permissions
      if($user->id == $actor->id || $user->id == $activity->actor)
      {
        // Remove comment resource
        $remove = $comment->delete();

        if($remove)
        {
          $result['result'] = true;
        }
      }
    }

    return Response::json($result);
  }
}