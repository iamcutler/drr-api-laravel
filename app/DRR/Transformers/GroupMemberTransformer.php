<?php

namespace DRR\Transformers;

class GroupMemberTransformer extends Transformer {
  public function transform($member)
  {
    return [
      'name' => $member['user']['name'],
      'avatar' => ($member['user']['comm_user']['avatar'] != '') ? "/{$member['user']['comm_user']['avatar']}" : '',
      'thumbnail' => ($member['user']['comm_user']['thumb'] != '') ? "/{$member['user']['comm_user']['thumb']}" : '',
      'slug' => $member['user']['comm_user']['alias'],
      'approved' => (int) $member['approved'],
      'permissions' => (int) $member['permissions']
    ];
  }
}
