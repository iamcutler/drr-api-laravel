<?php

class AuthController extends \BaseController {

  protected function login()
  {
    $username = Input::get('username');
    $password = Input::get('password');

    $user = User::where('username', '=', $username)->take(1);

    if($user->count() == 1)
    {
      $user = $user->get()[0];

      if(UserController::validate_user_password($password, $user['password']))
      {
        // Get relational comm_user data
        $comm_user = User::Find_comm_user($user->id);

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