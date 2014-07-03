<?php namespace DRR\Transformers;

class UserLikesTransformer {

  public function transform($like, $user)
  {
    if(is_null($like)) $like = [];

    return [
      'likes' => (int) (array_key_exists('like', $like)) ? $this->getCounts($like['like']) : 0,
      'dislikes' => (int) (array_key_exists('dislike', $like)) ? $this->getCounts($like['dislike']) : 0,
      'user' => [
        'like' => (bool) (array_key_exists('like', $like)) ? $this->userStatus($like['like'], $user) : false,
        'dislike' => (bool) (array_key_exists('dislike', $like)) ? $this->userStatus($like['dislike'], $user ) : false
      ]
    ];
  }

  /**
   * @param $like
   * @desc Loop though likes to get current like count
   * @return int
   */
  private function getCounts($like)
  {
    // Set initial counter
    $count = 0;

    if($like != '') {
      foreach(explode(',', $like) as $val)
      {
        $count++;
      }
    }

    return $count;
  }

  /**
   * @param $like
   * @param array $user
   * @desc Find if user has liked/disliked content
   * @return bool
   */
  private function userStatus($like, array $user)
  {
    $status = false;

    foreach(explode(',', $like) as $val)
    {
      if($val == $user['id'])
      {
        $status = true;
      }
    }

    return $status;
  }
} 