<?php

class Profile implements ProfileRepositoryInterface {

  public function __construct(Activity $activity)
  {
    $this->activity = $activity;
  }

  public function getFeed($id, $offset = 0, $limit = 10)
  {
    $activity = $this->activity->profile_feed($id, $offset, $limit);
    $result = [];

    foreach($activity as $key => $value)
    {
      $user = $value->actor();
      $user_comm = $user->comm_user()->first();

      // Resource
      $result[$key]['id'] = (int) $value->id;
      $result[$key]['type'] = $value->app;
      $result[$key]['comment_id'] = (int) $value->comment_id;
      $result[$key]['comment_type'] = $value->comment_type;
      $result[$key]['like_id'] = (int) $value->like_id;
      $result[$key]['like_type'] = $value->like_type;
      $result[$key]['created'] = $value->created;

      // Resource owner
      $result[$key]['user']['id'] = (int) $user->id;
      $result[$key]['user']['name'] = $user->name;
      $result[$key]['user']['thumbnail'] = $user_comm->thumb;
      $result[$key]['user']['avatar'] = $user_comm->avatar;
      $result[$key]['user']['slug'] = $user_comm->alias;

      // Resource stats
      $result[$key]['stats']['likes'] = (int) $value->likes()->where('element', '=', $value->like_type)->where('like', '!=', '')->count();
      $result[$key]['stats']['dislikes'] = (int) $value->likes()->where('element', '=', $value->like_type)->where('dislike', '!=', '')->count();

      // Resource comments
      $result[$key]['comments'] = [];
      foreach($value->wall() as $k => $v)
      {
        $user = $v->user();
        $comm_user = $user->comm_user()->first();

        $results[$key]['comments'][$k]['user']['id'] = $user->id;
        $results[$key]['comments'][$k]['user']['name'] = $user->name;
        $results[$key]['comments'][$k]['user']['avatar'] = $comm_user->avatar;
        $results[$key]['comments'][$k]['user']['thumbnail'] = $comm_user->thumb;
        $results[$key]['comments'][$k]['user']['slug'] = $comm_user->alias;

        $results[$key]['comments'][$k]['comment'] = $value['comment'];
        $results[$key]['comments'][$k]['date'] = $value['date'];
      }

      // Resource media
      $result[$key]['media'] = [];

      // Output media array based on activity type
      if($value->app == 'videos')
      {
        $media = $value->video();

        if(!is_null($media))
        {
          $result[$key]['media']['title'] = $media->title;
          $result[$key]['media']['type'] = $media->type;
          $result[$key]['media']['video_id'] = $media->video_id;
          $result[$key]['media']['description'] = $media->description;
          $result[$key]['media']['thumb'] = $media->thumb;
          $result[$key]['media']['path'] = $media->path;
          $result[$key]['media']['created'] = $media->created;
        }
        else {
          $result[$key] = [];
        }
      }
      elseif($value->app == 'photos') {
        $media = $value->photo();

        if(!is_null($media))
        {
          $result[$key]['media']['caption'] = $media->caption;
          $result[$key]['media']['image'] = $media->image;
          $result[$key]['media']['thumbnail'] = $media->thumbnail;
          $result[$key]['media']['original'] = $media->original;
          $result[$key]['media']['created'] = $media->created;
        }
        else {
          $result[$key] = [];
        }
      }
    }

    return $result;
  }
}