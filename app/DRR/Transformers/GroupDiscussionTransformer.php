<?php
namespace DRR\Transformers;

class GroupDiscussionTransformer extends Transformer {

  protected $userTransformer;

  function __construct(UserTransformer $userTransformer)
  {
    $this->userTransformer = $userTransformer;
  }

  public function transform($discussion)
  {
    return [
      'id' => (int) $discussion['id'],
      'content' => $discussion['content'],
      'app' => $discussion['app'],
      'cid' => (int) $discussion['cid'],
      'groupid' => (int) $discussion['groupid'],
      'group_access' => (int) $discussion['group_access'],
      'access' => (int) $discussion['access'],
      'params' => json_decode($discussion['params']),
      'comment_id' => (int) $discussion['comment_id'],
      'comment_type' => $discussion['comment_type'],
      'like_id' => (int) $discussion['like_id'],
      'like_type' => $discussion['like_type'],
      'created' => $discussion['created'],
      'user' => $this->userTransformer->transform($discussion['user_actor'])
    ];
  }
}