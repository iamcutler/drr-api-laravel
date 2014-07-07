<?php

namespace DRR\Transformers;

class GroupTransformer extends Transformer {
  public function transform($group)
  {
    return [
      'id' => $group['id'],
      'ownerid' => $group['ownerid'],
      'category' => $group['category']['name'],
      'name' => $group['name'],
      'description' => $group['description'],
      'email' => $group['email'],
      'website' => $group['website'],
      'approvals' => $group['approvals'],
      'avatar' => ($group['avatar'] != '') ? "/{$group['avatar']}" : '',
      'thumbnail' => ($group['thumb'] != '') ? "/{$group['thumb']}" : '',
      'created' => $group['created'],
      'params' => json_decode($group['params'])
    ];
  }
}
