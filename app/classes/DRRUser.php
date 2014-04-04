<?php

class DRRUser implements UserRepositoryInterface {

  public function __construct(User $user, CommUser $comm_user)
  {
    $this->user = $user;
    $this->comm_user = $comm_user;
  }

  public function create(Array $options)
  {
    // Assign user attributes
    $options['usertype'] = 2;
    $options['registerDate'] = date("Y-m-d H:i:s");
    $options['lastvisitDate'] = date("Y-m-d H:i:s");
    $options['params'] = '';
    $options['user_hash'] = $options['username'] . $options['email'];

    $result = DB::transaction(function() use ($options) {
      // Create user
      $user = $this->user->create($options);

      // Create community user
      $comm_user = $this->comm_user->create([
        'userid' => $user->id,
        'alias' => $user->id . ":" . str_replace(' ', '-', $user->name)
      ]);

      if($user && $comm_user)
      {
        return $user->toArray();
      }
      else {
        return [];
      }
    });

    return $result;
  }
}