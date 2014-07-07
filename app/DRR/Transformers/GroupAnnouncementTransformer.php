<?php
namespace DRR\Transformers;

class GroupAnnouncementTransformer extends Transformer {

  protected $userTransformer;

  function __construct(UserTransformer $userTransformer)
  {
    $this->userTransformer = $userTransformer;
  }

  public function transform($announcement)
  {
    return [
      'id' => (int) $announcement['id'],
      'title' => $announcement['title'],
      'message' => $announcement['message'],
      'params' => json_decode($announcement['params']),
      'date' => $announcement['date'],
      'user' => $this->userTransformer->transform($announcement['user'])
    ];
  }
}
