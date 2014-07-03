<?php

namespace DRR\Transformers;

class EventMemberTransformer extends Transformer {

  protected $userTransformer;

  function __construct(UserTransformer $userTransformer)
  {
    $this->userTransformer = $userTransformer;
  }

  public function transform($member)
  {
    return [
      'user' => $this->userTransformer->transform($member['user']),
      'status' => (int) $member['status'],
      'permission' => (int) $member['permission'],
      'invited_by' => (int) $member['invited_by'],
      'approval' => (int) $member['approval'],
      'created' => $member['created']
    ];
  }
}
