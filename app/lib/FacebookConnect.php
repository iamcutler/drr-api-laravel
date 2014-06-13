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

    if($existing)
    {
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
    else {
      // Save new user
      $user = $this->save_user($fb);

      if($user['status'])
      {
        $result = ['status' => true,
          'user' => [
            'id' => $user['user']->id,
            'name' => $user['user']->name,
            'slug' => $user['comm_user']->alias,
            'thumbnail' => $user['comm_user']->thumb,
            'username' => $user['user']->username,
            'hash' => $user['user']->user_hash
          ]
        ];
      }
    }

    return $result;
  }

  protected function save_user($fb)
  {
    $result['status'] = false;

    // Start transaction!
    DB::beginTransaction();

    try {
      $user = new User;
      $user->name = $fb['first_name'] ." ". $fb['last_name'];
      $user->username = User::checkOrOverrideUsername($fb['username']);
      $user->email = $fb['email'];
      $user->password = User::generate_password('facebookConnectDRR');
      $user->usertype = 2;
      $user->registerDate = date("Y-m-d H:i:s");
      $user->lastvisitDate = date("Y-m-d H:i:s");
      $user->params = '';
      $user->user_hash = User::generate_hash($fb['first_name'] . " " . $fb['last_name'], $fb['username']);
      $user->save();

      $comm_user = new CommUser;
      $comm_user->userid = $user->id;
      $comm_user->alias = $user->id . ":" . str_replace(' ', '-', $user->name);
      $comm_user->save();

      $connect = new ConnectUser;
      $connect->connectid = $fb['id'];
      $connect->type = 'facebook';
      $connect->userid = $user->id;
      $connect->save();
    }
    catch(\Exception $e) {
      DB::rollback();
      throw $e;
    }

    // Commit transaction queries
    DB::commit();

    $result['status'] = true;
    $result['user'] = $user;
    $result['comm_user'] = $comm_user;

    return $result;
  }
}