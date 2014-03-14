<?php

class UserActivity implements UserActivityRepositoryInterface {
  public function __construct(Likes $like)
  {
    $this->like = $like;
  }

  public function setLike(User $user, $element, $id, $type)
  {
    $record = false;
    $result = [];

    // Assign array for like or dislike
    $like = [
      'element' => $element,
      'uid' => $id,
      'user' => $user->id
    ];

    if($type == 1)
    {
      $record = $this->createOrOverwriteOrRemoveLike(1, $like);
    }
    elseif($type == 0) {
      $record = $this->createOrOverwriteOrRemoveLike(0, $like);
    }

    if($record)
    {
      $likes = 0;
      $dislikes = 0;

      // Loop through and assign incrementing to proper like type
      foreach($this->like->Find_likes($element, $id) as $val)
      {
        if($val->like != "")
        {
          $likes++;
        }
        elseif($val->dislike != "")
        {
          $dislikes++;
        }
      }

      $result = [
        'status' => true,
        'like' => [
          'likes' => $likes,
          'dislikes' => $dislikes
        ]
      ];
    }

    return $result;
  }

  protected function createOrOverwriteOrRemoveLike($type, Array $args)
  {
    $result = $this->like->Find_existing_like($args);

    if($result->count() == 0)
    {
      // Create like if not found
      $like = new Likes;

      $like->element = $args['element'];
      $like->uid = $args['uid'];

      if($type) {
        $like->like = $args['user'];
        $like->dislike = '';
      } else {
        $like->like = '';
        $like->dislike = $args['user'];
      }

      $like->save();

      return true;
    }
    else {
      // Check if this is a like, if not, it must be a dislike
      if($type == 1 && $result->like == $args['user'] || $type == 0 && $result->dislike == $args['user'])
      {
        // Remove if found
        $result->delete();

        return true;
      }
      else {
        if($type == 1) {
          $result->like = $args['user'];
          $result->dislike = '';
        }
        else {
          $result->like = '';
          $result->dislike = $args['user'];
        }

        $result->save();

        return true;
      }
    }

    return false;
  }
}