<?php

class WallController extends \BaseController {

  public function __construct(CommWall $comment, Activity $activity, User $user, UserPhoto $photo, UserVideo $video)
  {
    $this->comment = $comment;
    $this->user = $user;
    $this->activity = $activity;
    $this->photo = $photo;
    $this->video = $video;
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
    $user = $this->user->find($params['user']);
    $comm_user = $user->comm_user()->first();

    if($validator->passes() && !is_null($user))
    {
      switch($params['app'])
      {
        case 'videos':
          $resource = $this->video->find($params['cid']);
          break;
        case 'photos':
          $resource = $this->photo->find($params['cid']);
          break;
      }

      // Check if resource exists
      if(!is_null($resource))
      {
        $result = [];

        $save = DB::transaction(function() use ($resource, $user, $params) {
          $comment = $this->comment->create([
            'contentid' => $params['cid'],
            'post_by' => $params['user'],
            'ip' => Request::getClientIp(),
            'comment' => $params['comment'],
            'date' => date('Y-m-d H:i:s'),
            'published' => 1,
            'type' => $params['app']
          ]);

          // Save comment activity
          $activity = $this->activity->create([
            'actor' => $user->id,
            'target' => 0,
            'content' => $params['comment'],
            'app' => $params['app'] . '.comment',
            'cid' => $resource->id,
            'created' => date("Y-m-d H:i:s"),
            'access' => 0,
            'params' => '',
            'archived' => 0,
            'location' => '',
            'comment_id' => $comment->id,
            'comment_type' => $params['app'] . '.wall.create',
            'like_id' => $comment->id,
            'like_type' => $params['app'] . '.wall.create',
            'actors' => ''
          ]);

          if($comment && $activity)
          {
            $result['result'] = true;
            $result['comment'] = $comment;
          }

          return $result;
        });

        if($save['result'])
        {
          $comment = $save['comment'];

          $result['result'] = true;
          $result['wall']['id'] = $comment->id;
          $result['wall']['type'] = $comment->type;
          $result['wall']['comment'] = $comment->comment;
          $result['wall']['created'] = $comment->date;

          $result['wall']['user']['id'] = $user->id;
          $result['wall']['user']['name'] = $user->name;
          $result['wall']['user']['thumbnail'] = $comm_user->thumb;
          $result['wall']['user']['avatar'] = $comm_user->avatar;
          $result['wall']['user']['slug'] = $comm_user->alias;
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