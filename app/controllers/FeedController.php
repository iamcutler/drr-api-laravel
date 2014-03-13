<?php

class FeedController extends \BaseController {

  public function __construct(User $user, Activity $activity)
  {
    $this->user = $user;
    $this->activity = $activity;
  }

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index($offset = 0)
  {
    $params = Input::all();
    $user = $this->user->Find_id_by_hash($params['user_hash']);
    $result = [];

    $activity = $this->activity->news_feed($offset)->get();

    foreach($activity as $key => $value)
    {
      $actor = $value->actor();
      $actor_comm = $actor->comm_user()->first();

      $result[$key]['id'] = (int) $value->id;
      $result[$key]['title'] = $value->title;
      $result[$key]['content'] = $value->content;
      $result[$key]['app'] = $value->app;
      $result[$key]['cid'] = (int) $value->cid;
      $result[$key]['groupid'] = (int) $value->groupid;
      $result[$key]['eventid'] = (int) $value->eventid;
      $result[$key]['group_access'] = $value->group_access;
      $result[$key]['event_access'] = $value->event_access;
      $result[$key]['location'] = $value->location;
      $result[$key]['params'] = json_decode($value->params);

      $result[$key]['like_id'] = (int) $value->like_id;
      $result[$key]['like_type'] = $value->like_type;
      $result[$key]['comment_id'] = (int) $value->comment_id;
      $result[$key]['comment_type'] = $value->comment_type;

      $result[$key]['created'] = $value->created;

      // Resource owner
      $result[$key]['actor']['id'] = (int) $actor->id;
      $result[$key]['actor']['name'] = $actor->name;
      $result[$key]['actor']['thumbnail'] = $actor_comm->thumb;
      $result[$key]['actor']['avatar'] = $actor_comm->avatar;
      $result[$key]['actor']['slug'] = $actor_comm->alias;

      // Resource stats
      $result[$key]['stats']['likes'] = (int) $value->likes()->where('element', '=', $value->like_type)->where('like', '!=', '')->count();
      $result[$key]['stats']['dislikes'] = (int) $value->likes()->where('element', '=', $value->like_type)->where('dislike', '!=', '')->count();

      // Resource comments
      $result[$key]['comments'] = [];
      foreach($value->wall() as $k => $v)
      {
        $actor = $v->user();
        $actor_comm = $user->comm_user()->first();

        $result[$key]['comments'][$k]['user']['id'] = $actor->id;
        $result[$key]['comments'][$k]['user']['name'] = $actor->name;
        $result[$key]['comments'][$k]['user']['avatar'] = $actor_comm->avatar;
        $result[$key]['comments'][$k]['user']['thumbnail'] = $actor_comm->thumb;
        $result[$key]['comments'][$k]['user']['slug'] = $actor_comm->alias;

        $result[$key]['comments'][$k]['comment'] = $v['comment'];
        $result[$key]['comments'][$k]['date'] = $v['date'];
      }

      // Resource media
      $result[$key]['media'] = [];

      // Output media array based on activity type
      switch($value->app)
      {
        case 'photos':
          $media = $value->photo();

          if(!is_null($media))
          {
            $result[$key]['media']['caption'] = $media->caption;
            $result[$key]['media']['image'] = $media->image;
            $result[$key]['media']['thumbnail'] = $media->thumbnail;
            $result[$key]['media']['original'] = $media->original;
            $result[$key]['media']['created'] = $media->created;
          }
          break;

        case 'profile.avatar.upload':
          $result[$key]['media']['image'] = $actor_comm->thumb;
          break;
      }
    }

    return Response::json($result);
  }

  public function media($offset = 0)
  {
    $feed = $this->activity->Media_feed($offset)->get();
    $result = [];

    foreach($feed as $key => $value)
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
        $user = $value->user();
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

    return Response::json($result);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return Response
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store()
  {
    //
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function edit($id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function update($id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function destroy($id)
  {
    //
  }

}