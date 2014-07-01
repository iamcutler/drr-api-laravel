<?php

use \DRR\Transformers\UserTransformer;
use \DRR\Transformers\WallTransformer;
use \DRR\Transformers\UserLikesTransformer;

class FeedController extends \BaseController {

  protected $userTransformer;
  protected $wallTransformer;
  protected $userLikesTransformer;

  public function __construct(User $user, Activity $activity, UserActivityRepositoryInterface $activityRepo,
                              PresenterRepositoryInterface $presenter, UserTransformer $userTransformer,
                              WallTransformer $wallTransformer, UserLikesTransformer $userLikesTransformer)
  {
    $this->user = $user;
    $this->activity = $activity;
    $this->activityRepo = $activityRepo;
    $this->presenter = $presenter;
    $this->userTransformer = $userTransformer;
    $this->wallTransformer = $wallTransformer;
    $this->userLikesTransformer = $userLikesTransformer;
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

    $activity = Cache::remember("news-feed-{$user->id}-{$offset}", 30, function() use ($user, $offset) {
      return $this->activity->news_feed($user, $offset)->get();
    });

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
      $result[$key]['actor'] = $this->userTransformer->transform($value->userActor->toArray());

      // Resource Target
      if($value->target != 0 && $value->actor != $value->target)
      {
        $target = $value->userTarget;
        if($target)
        {
          $target_comm = $value->userTarget->comm_user;
        }
      }
      else {
        $target = $value->userActor;
        if($target)
        {
          $target_comm = $value->userActor->comm_user;
        }
      }

      if($target)
      {
        $result[$key]['target']['id'] = (int) $target->id;
        $result[$key]['target']['name'] = $target->name;
        $result[$key]['target']['username'] = $target->username;
        $result[$key]['target']['thumbnail'] = '/' . $target_comm->thumb;
        $result[$key]['target']['avatar'] = '/' . $target_comm->avatar;
        $result[$key]['target']['slug'] = $target_comm->alias;
      }

      // Resource stats
      $resource_like = $value->likes()->where('element', '=', $value->like_type)->first();
      // If like is NOT found
      $resource_like = ($resource_like) ? $resource_like->toArray() : [];
      $result[$key]['stats'] = $this->userLikesTransformer->transform($resource_like, $user->toArray());

      // Resource comments
      $result[$key]['comments'] = [];
      foreach($value->wall as $k => $v)
      {
        $result[$key]['comments'][$k] = $this->wallTransformer->transform($v);
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
            $result[$key]['media']['image'] = '/'. $media->image;
            $result[$key]['media']['thumbnail'] = '/'. $media->thumbnail;
            $result[$key]['media']['original'] = '/'. $media->original;
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
            $result[$key]['media']['thumb'] = '/'. $media->thumb;
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
    $params = Input::all();
    $user = $this->user->Find_id_by_hash($params['user_hash']);
    $feed = Cache::remember("media-feed-{$offset}", 30, function() use ($offset) {
      return $this->activity->media_feed($offset)->get();
    });
    $result = [];

    foreach($feed as $key => $value)
    {
      // Resource
      $result[$key]['id'] = (int) $value->id;
      $result[$key]['app'] = $value->app;
      $result[$key]['comment_id'] = (int) $value->comment_id;
      $result[$key]['comment_type'] = $value->comment_type;
      $result[$key]['like_id'] = (int) $value->like_id;
      $result[$key]['like_type'] = $value->like_type;
      $result[$key]['created'] = $value->created;

      // Resource owner
      $result[$key]['actor']['id'] = (int) $value->userActor->id;
      $result[$key]['actor']['name'] = $value->userActor->name;
      $result[$key]['actor']['username'] = $value->userActor->username;
      $result[$key]['actor']['thumbnail'] = '/'. $value->userActor->comm_user->thumb;
      $result[$key]['actor']['avatar'] = '/'. $value->userActor->comm_user->avatar;
      $result[$key]['actor']['slug'] = $value->userActor->comm_user->alias;

      // Resource stats
      if($value->likes)
      {
        $result[$key]['stats'] = $this->userLikesTransformer->transform($value->likes->toArray(), $user->toArray());
      }
      else {
        $result[$key]['stats'] = $this->userLikesTransformer->transform([], $user->toArray());
      }

      // Resource comments
      $result[$key]['comments'] = $this->wallTransformer->transformCollection($value->activity_wall->toArray());

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
          $result[$key]['media']['thumb'] = '/'. $media->thumb;
          $result[$key]['media']['path'] = $media->path;
          $result[$key]['media']['created'] = $media->created;
        }
      }
      elseif($value->app == 'photos') {
        $media = $value->photo;

        if(!is_null($media))
        {
          $result[$key]['media']['caption'] = $media->caption;
          $result[$key]['media']['image'] = '/'. $media->image;
          $result[$key]['media']['thumbnail'] = '/'. $media->thumbnail;
          $result[$key]['media']['original'] = '/'. $media->original;
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