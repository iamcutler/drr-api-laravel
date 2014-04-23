<?php

class FeedController extends \BaseController {

  public function __construct(User $user, Activity $activity, PresenterRepositoryInterface $presenter)
  {
    $this->user = $user;
    $this->activity = $activity;
    $this->presenter = $presenter;
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
    $user_comm = $user->comm_user()->first();
    $result = [];

    $activity = $this->activity->news_feed(explode(',', $user_comm->friends), $offset)->get();

    foreach($activity as $key => $value)
    {
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
      $result[$key]['actor']['id'] = (int) $value->userActor->id;
      $result[$key]['actor']['name'] = $value->userActor->name;
      $result[$key]['actor']['thumbnail'] = '/' . $value->userActor->comm_user->thumb;
      $result[$key]['actor']['avatar'] = '/' . $value->userActor->comm_user->avatar;
      $result[$key]['actor']['slug'] = $value->userActor->comm_user->alias;

      // Resource Target
      if($value->target != 0 && $value->actor != $value->target)
      {
        $target = $value->userTarget;
        $target_comm = $value->userTarget->comm_user;
      }
      else {
        $target = $value->userActor;
        $target_comm = $value->userActor->comm_user;
      }

      $result[$key]['target']['id'] = (int) $target->id;
      $result[$key]['target']['name'] = $target->name;
      $result[$key]['target']['thumbnail'] = '/' . $target_comm->thumb;
      $result[$key]['target']['avatar'] = '/' . $target_comm->avatar;
      $result[$key]['target']['slug'] = $target_comm->alias;

      // Resource stats
      $result[$key]['stats'] = $this->presenter->likeStats($value->likes()->where('element', '=', $value->like_type)->first(), 0);

      // Resource comments
      $result[$key]['comments'] = [];
      foreach($value->wall as $k => $v)
      {
        $result[$key]['comments'][$k]['user']['id'] = (int) $v->user->id;
        $result[$key]['comments'][$k]['user']['name'] = $v->user->name;
        $result[$key]['comments'][$k]['user']['avatar'] = '/' . $v->user->comm_user->avatar;
        $result[$key]['comments'][$k]['user']['thumbnail'] = '/' . $v->user->comm_user->thumb;
        $result[$key]['comments'][$k]['user']['slug'] = $v->user->comm_user->alias;

        $result[$key]['comments'][$k]['comment'] = $v->comment;
        $result[$key]['comments'][$k]['date'] = $v->date;
      }

      // Resource media
      $result[$key]['media'] = [];

      // Output media array based on activity type
      switch($value->app)
      {
        case 'photos':
          $media = $value->photo;

          if(!is_null($media))
          {
            $result[$key]['media']['caption'] = $media->caption;
            $result[$key]['media']['image'] = '/' . $media->image;
            $result[$key]['media']['thumbnail'] = '/' . $media->thumbnail;
            $result[$key]['media']['original'] = '/' . $media->original;
            $result[$key]['media']['created'] = $media->created;
          }
          break;
        case 'videos':
          $media = $value->video;

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
        case 'profile.avatar.upload':
          $result[$key]['media']['image'] = $value->userActor->comm_user->thumb;
          break;
      }
    }

    return Response::json($result);
  }

  public function media($offset = 0)
  {
    $feed = $this->activity->media_feed($offset)->get();
    $result = [];

    foreach($feed as $key => $value)
    {
      // Resource
      $result[$key]['id'] = (int) $value->id;
      $result[$key]['type'] = $value->app;
      $result[$key]['comment_id'] = (int) $value->comment_id;
      $result[$key]['comment_type'] = $value->comment_type;
      $result[$key]['like_id'] = (int) $value->like_id;
      $result[$key]['like_type'] = $value->like_type;
      $result[$key]['created'] = $value->created;

      // Resource owner
      $result[$key]['user']['id'] = (int) $value->userActor->id;
      $result[$key]['user']['name'] = $value->userActor->name;
      $result[$key]['user']['thumbnail'] = '/'.$value->userActor->comm_user->thumb;
      $result[$key]['user']['avatar'] = '/'.$value->userActor->comm_user->avatar;
      $result[$key]['user']['slug'] = $value->userActor->comm_user->alias;

      // Resource stats
      $result[$key]['stats'] = [];
      $result[$key]['stats'] = $this->presenter->likeStats($value->likes()->where('element', '=', $value->like_type)->first(), 0);

      // Resource comments
      $result[$key]['comments'] = [];
      foreach($value->activity_wall as $k => $val)
      {
        $result[$key]['comments'][$k]['user']['id'] = $val->user->id;
        $result[$key]['comments'][$k]['user']['name'] = $val->user->name;
        $result[$key]['comments'][$k]['user']['avatar'] = $val->user->comm_user->avatar;
        $result[$key]['comments'][$k]['user']['thumbnail'] = $val->user->comm_user->thumb;
        $result[$key]['comments'][$k]['user']['slug'] = $val->user->comm_user->alias;

        $result[$key]['comments'][$k]['comment'] = $val->comment;
        $result[$key]['comments'][$k]['date'] = $val->date;
      }

      // Resource media
      $result[$key]['media'] = [];
      // Output media array based on activity type
      if($value->app == 'videos')
      {
        $media = $value->video;

        if(!is_null($media))
        {
          $result[$key]['media']['title'] = $media->title;
          $result[$key]['media']['type'] = $media->type;
          $result[$key]['media']['video_id'] = $media->video_id;
          $result[$key]['media']['description'] = $media->description;
          $result[$key]['media']['thumb'] = '/' . $media->thumb;
          $result[$key]['media']['path'] = $media->path;
          $result[$key]['media']['created'] = $media->created;
        }
      }
      elseif($value->app == 'photos') {
        $media = $value->photo;

        if(!is_null($media))
        {
          $result[$key]['media']['caption'] = $media->caption;
          $result[$key]['media']['image'] = '/' . $media->image;
          $result[$key]['media']['thumbnail'] = '/' . $media->thumbnail;
          $result[$key]['media']['original'] = '/' . $media->original;
          $result[$key]['media']['created'] = $media->created;
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