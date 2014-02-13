<?php

class LikeController extends \BaseController {

  public function __construct(Likes $like, User $user)
  {
    $this->user = $user;
    $this->like = $like;
  }

  public function like($element, $id, $type)
  {
    $input = Input::all();
    $result = ['status' => false];
    $validator = Validator::make([
        'element' => $element,
        'id' => $id,
        'type' => $type
      ],[
        'element' => 'required',
        'id' => 'required',
        'type' => 'required'
      ]);

    if(!$validator->fails())
    {
      // Get actor / user
      $user = $this->user->Find_id_by_hash($input['user_hash']);

      // Assign array for like or dislike based on type.
      if($type)
      {
        $like = [
          'element' => $element,
          'uid' => $id,
          'like' => $user->id,
          'dislike' => '',
          'type' => 'like'
        ];
      }
      else
      {
        $like = [
          'element' => $element,
          'uid' => $id,
          'like' => '',
          'dislike' => $user->id,
          'type' => 'dislike'
        ];
      }

      // Create, overwrite opposite or remove existing like/dislike
      $record = $this->like->CreateOrOverwriteOrRemoveLike($like);

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
    }

    return Response::json($result);
  }
}