<?php

class UserConnectionController extends \BaseController {

  public function __construct(UserConnection $connection, User $user)
  {
    $this->connection = $connection;
    $this->user = $user;
  }

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {
    if(Input::has('user_hash'))
    {
      $hash = Input::get('user_hash');

      return Response::json($this->connection->Find_by_id($this->user->Find_id_by_hash($hash)->id));
    }
  }

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store($id)
  {
    $user_hash = Input::get('user_hash');
    $user = $this->user->find_id_by_hash($user_hash);


  }

  /**
   * Update the specified resource in storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function update($id)
  {
    $user_hash = Input::get('user_hash');
    $request = $this->connection->find($id);
    $user = $this->user->Find_id_by_hash($user_hash);

    if(!is_null($request->first()) && !is_null($user->first()))
    {
      // Update request
      if($request->status == 0)
      {
        $request->update([
          'status' => 1
        ]);

        // Create or update reverse connection to users
        $this->connection->UpdateOrCreateConnection([
          'connect_from' => $user->id,
          'connect_to' => $request->connect_from,
          'status' => 1,
          'group' => 0,
          'msg' => '',
          'created' => date("Y-m-d H:i:s")
        ]);

        return ['status' => true];
      }
      else
      {
        return ['status' => false];
      }
    }
    else
    {
      return ['status' => false];
    }
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function destroy($id)
  {
    // Get params hash and validate it is the correct user
    $user_hash = Input::get('user_hash');

    // Query existing request and find user for comparison
    $request = $this->connection->find($id);
    $user = $this->user->Find_id_by_hash($user_hash);

    if(!is_null($request))
    {
      if($request->connect_to == $user->id)
      {
        if($request->delete())
        {
          $result = ['status' => true];
        }
        else
        {
          $result = ['status' => false, 'message' => 'Error removing friend request'];
        }
      }
      else
      {
        $result = ['status' => false, 'message' => 'User did not pass validation'];
      }
    }
    else
    {
      $result = ['status' => false, 'message' => 'Could not locate this friend request'];
    }

    return Response::json($result);
  }

}