<?php namespace DRR\Transformers;

class UserTransformer extends Transformer {
  public function transform($user)
  {
    return [
      'id' => (int) $user['id'],
      'name' => $user['name'],
      'username' => $user['username'],
      'email' => $user['email'],
      'thumbnail' => ($user['comm_user']['thumb'] != '') ? "/{$user['comm_user']['thumb']}" : '',
      'avatar' => ($user['comm_user']['avatar'] != '') ? "/{$user['comm_user']['avatar']}" : '',
      'slug' => $user['comm_user']['alias']
    ];
  }
} 