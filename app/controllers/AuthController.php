<?php

class AuthController extends \BaseController {

  protected function login()
  {
    $username = Input::get('username');
    $password = Input::get('password');

    $user = User::where('username', '=', $username);

    if($user->count() == 1)
    {
      $user = $user->first();

      if(User::validate_user_password($password, $user['password']))
      {
        // Get relational comm_user data
        $comm_user = User::find($user->id)->comm_user()->first();

        $result = ['status' => true,
          'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'slug' => $comm_user->alias,
            'thumbnail' => $comm_user->thumb,
            'hash' => $user->user_hash
          ]
        ];
      }
        else
      {
        $result = ['status' => false];
      }
    }
    else
    {
      $result = ['status' => false];
    }

    return Response::json($result);
  }
}