<?php

class FacebookConnect implements FacebookRepository {
  public function __construct(User $user, UserRepositoryInterface $userRepo)
  {
    $this->user = $user;
    $this->userRepo = $userRepo;
  }

  public function login($fb)
  {
    $result['status'] = false;

    // Check for existing facebook user
    $existing = $this->user->where('email', '=', $fb['email'])->first();

    if(!$existing)
    {
      // Save new user
      $user = $this->save_user($fb);


    }
    else {
      // Get relational comm_user data
      $comm_user = $existing->comm_user()->first();

      $result = ['status' => true,
        'user' => [
          'id' => $existing->id,
          'name' => $existing->name,
          'slug' => $comm_user->alias,
          'thumbnail' => $comm_user->thumb,
          'username' => $existing->username,
          'hash' => $existing->user_hash
        ]
      ];
    }

    return $result;
  }

  protected function save_user($fb)
  {
    $result['status'] = false;

    $transaction = DB::transaction(function() use ($fb) {
      $result['result'] = false;

      $user = $this->user->save([
        'name' => $fb['name'],
        'username' => $fb['username'],
        'email' => $fb['email'],
        'password' => User::generate_password('facebookConnectDRR'),
        'usertype' => 2,
        'registerDate' => date("Y-m-d H:i:s"),
        'lastvisitDate' => date("Y-m-d H:i:s"),
        'params' => '',
        'user_hash' => User::generate_hash($fb['first_name'] . " " . $fb['last_name'], $fb['username'])
      ]);

      if($user)
      {
        $comm_user = $user->comm_user()->save([
          'userid' => $user->id,
          'alias' => $user->id . ":" . str_replace(' ', '-', $user->name)
        ]);

        if($comm_user)
        {
          $result['result'] = true;
          $result['user'] = $user;
          $result['comm_user'] = $comm_user;
        }
      }

      return $result;
    });

    if($transaction['result'])
    {
      $connect = new ConnectUser;
      $connect->connectid = $fb->id;
      $connect->type = 'facebook';
      $connect->userid = $transaction['user']->id;
      $connect->save();

      $result['status'] = true;
      $result['user'] = $transaction['user'];
      $result['comm_user'] = $transaction['comm_user'];
    }

    return $result;
  }
}