<?php

class FriendRequestController extends \BaseController {

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

      return Response::json(FriendRequest::Find_by_id(User::Find_id_by_hash($hash)->id));
    }
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
    // Get params hash and validate it is the correct user
    $user_hash = Input::get('user_hash');

    // Query existing request and find user for comparison
    $request = FriendRequest::find($id);
    $user = User::Find_id_by_hash($user_hash);

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