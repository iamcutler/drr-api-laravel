<?php namespace DRR\Transformers;

class LikesTransformer extends Transformer {
  public function transform($like)
  {
    return [
      'uid' => (int) $like['uid'],
      'likes' => $like['like'],
      'dislikes' => $like['dislike']
    ];
  }
} 