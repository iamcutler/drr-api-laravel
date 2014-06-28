<?php namespace DRR\Transformers;

class LikesTransformer extends Transformer {
  public function transform($like)
  {
    return [
      'likes' => (int) count(explode(',', $like['like'])),
      'dislikes' => (int) ($like['dislike'] == '') ? 0 : count(explode(',', $like['dislike']))
    ];
  }
} 