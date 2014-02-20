<?php

class WallController extends \BaseController {

  public function __construct(CommWall $comment, User $user)
  {
    $this->comment = $comment;
    $this->user = $user;
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
    //
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
      $actor = $comment->user();
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