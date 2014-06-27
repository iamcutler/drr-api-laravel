<?php namespace DRR\Transformers;


class WallTransformer extends Transformer {
  public function transform($wall)
  {
    return [
      'id' => (int) $wall['id'],
      'type' => $wall['type'],
      'comment' => $wall['comment'],
      'date' => $wall['date'],
      'user' => [
        'id' => (int) $wall['user']['id'],
        'name' => $wall['user']['name'],
        'username' => $wall['user']['username'],
        'slug' => $wall['user']['comm_user']['alias'],
        'thumbnail' => $wall['user']['comm_user']['thumb'],
        'avatar' => $wall['user']['comm_user']['avatar']
      ]
    ];
  }
} 