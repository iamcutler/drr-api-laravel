<?php

class UserConnectionController extends \BaseController {

  public function __construct(UserConnection $connection, User $user, CommUser $comm, UserRepositoryInterface $userRepo)
  {
    $this->connection = $connection;
    $this->user = $user;
    $this->comm_user = $comm;
    $this->userRepo = $userRepo;
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
  public function store()
  {
    $input = Input::all();
    $rules = [
      'user' => 'required|integer'
    ];
    $validator = Validator::make($input, $rules);
    $result = ['result' => false];

    if($validator->passes())
    {
      $user = $this->user->find_id_by_hash($input['user_hash']);

      $request = $this->connection->updateOrCreateConnection([
        'connect_from' => $user->id,
        'connect_to' => $input['user'],
        'status' => 0,
        'group' => 0,
        'msg' => '',
        'created' => date('Y-m-d h:i:s')
      ]);

      if($request)
      {
        $result['result'] = true;
      }
    }

    return Response::json($result);
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
    $result = ['status' => false];

    if(!is_null($request) && !is_null($user))
    {
      // Get requester
      $requester = $this->user->find($request->connect_from);

      if(!is_null($requester))
      {
        // Update request
        if($request->status == 0)
        {
          $request->update([
            'status' => 1
          ]);

          // Create or update reverse connection to users
          $newConnection = $this->connection->UpdateOrCreateConnection([
            'connect_from' => $user->id,
            'connect_to' => $requester->id,
            'status' => 1,
            'group' => 0,
            'msg' => '',
            'created' => date("Y-m-d H:i:s")
          ]);

          // Add comm friend arrays
          if($newConnection)
          {
            $commUser = $user->comm_user()->first();
            $commRequester = $requester->comm_user()->first();

            // Save new friends array for user and add to friend count by 1
            $commUser->friends = (string) $this->userRepo->modifyFriendCommArray($commUser, $requester->id, 1);
            $commUser->increment('friendcount');
            $commRequester->friends = (string) $this->userRepo->modifyFriendCommArray($commRequester, $user->id, 1);
            $commRequester->increment('friendcount');

            if($commUser->save() && $commRequester->save())
            {
              $result['status'] = true;
            }
          }
        }
        else
        {
          $result['status'] = false;
        }
      }
    }
    else
    {
      $result['status'] = false;
    }

    return $result;
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

  public function remove_friend_connection($id)
  {
    $user = $this->user->Find_id_by_hash(Input::get('user_hash'));
    $friend = $this->user->find($id);
    $connection = $this->connection->Find_friend_connection_by_id($user->id, $id);
    $result = ['result' => false];

    if($friend && $connection->count() > 0)
    {
      $commUser = $user->comm_user()->first();
      $commFriend = $friend->comm_user()->first();

      // Save new friends array for user and friend being removed
      $commUser->friends = (string) $this->userRepo->modifyFriendCommArray($commUser, $id);
      $commUser->friendcount = $commUser->friendcount - 1;
      $commFriend->friends = (string) $this->userRepo->modifyFriendCommArray($commFriend, $user->id);
      $commFriend->friendcount = $commFriend->friendcount - 1;

      if($commUser->save() && $commFriend->save())
      {
        // Loop through connections and remove
        foreach($connection as $key => $value)
        {
          $value->delete();
        }

        $result['result'] = true;
      };
    }

    return Response::json($result);
  }
}