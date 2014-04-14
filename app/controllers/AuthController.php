<?php

class AuthController extends \BaseController {

  public static function auth_user_hash_filter()
  {
    if(Input::has('user_hash')) {
      $hash = Input::get('user_hash');

      // Check if user has assigned hash
      if(User::Check_hash_uniqueness($hash)->count()) {
        return true;
      }
    }

    return false;
  }

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
        // Check if user has a hash assigned or generate
        if($user->user_hash == "")
        {
          $user->user_hash = User::generate_hash($user->name, $user->username);
          $user->save();
        }
        // Get relational comm_user data
        $comm_user = User::find($user->id)->comm_user()->first();

        $result = ['status' => true,
          'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'slug' => $comm_user->alias,
            'thumbnail' => $comm_user->thumb,
            'username' => $user->username,
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