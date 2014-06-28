<?php

use DRR\Transformers\VideoTransformer;
use DRR\Transformers\WallTransformer;
use DRR\Transformers\ProfileTransformer;

class ProfileController extends \BaseController {

  /**
   * @var DRR\Transformers\
   */
  protected $videoTransformer;
  protected $wallTransformer;
  protected $profileTransformer;

  public function __construct(User $user, CommUser $comm_user, UserVideo $video,
                              ProfileRepositoryInterface $profile, PresenterRepositoryInterface $presenter,
                              VideoTransformer $videoTransformer, WallTransformer $wallTransformer,
                              ProfileTransformer $profileTransformer)
  {
    $this->user = $user;
    $this->comm_user = $comm_user;
    $this->video = $video;
    $this->profile = $profile;
    $this->presenter = $presenter;
    $this->videoTransformer = $videoTransformer;
    $this->wallTransformer = $wallTransformer;
    $this->profileTransformer = $profileTransformer;
  }

  public function user_profile($slug)
  {
    $params = Input::all();
    $requester = $this->user->Find_id_by_hash($params['user_hash']);
    $profile = $this->user->EagerProfileData()->find($this->comm_user->where('alias', '=', $slug)->first()->userid);
    $result = [];

    if(!is_null($profile))
    {
      $profile['requester'] = $requester;
      $result = $this->profileTransformer->transform($profile->toArray());

      // Update profile views per hit
      $profile->comm_user->increment('view', 1);
    }

    return Response::json($result);
  }

  public function about($slug)
  {
    $user = $this->user->Find_user_profile_by_slug($slug);
    $result = $this->profile->about($user);

    return Response::json($result);
  }

  public function friends($slug)
  {
    $user = $this->user->Find_user_profile_by_slug($slug);
    $result = $this->profile->friends($user);

    return Response::json($result);
  }

  public function photo_albums($slug)
  {
    $user = $this->user->Find_user_profile_by_slug($slug);
    $result = $this->profile->albums($user);

    return Response::json($result);
  }

  public function album_photos($slug, $id)
  {
    $results = $this->profile->album($slug, $id);

    return Response::json($results);
  }

  /**
   * @param $slug
   * @desc Fetch user videos by slug
   * @return mixed
   */
  public function videos($slug)
  {
    $user = $this->comm_user->Find_by_slug($slug);
    $videos = [];

    if(!is_null($user))
    {
      $resource = $this->video->find_all_by_user_id($user->userid)->get();

      foreach($resource as $key => $val)
      {
        $videos[$key] = $this->videoTransformer->transform($val->toArray());
        $videos[$key]['comments'] = $this->wallTransformer->transformCollection($val->wall->toArray());
      }
    }

    return Response::json($videos);
  }

  /**
   * @param $slug
   * @param $id
   * @desc Fetch single user video
   * @return mixed
   */
  public function video($slug, $id)
  {
    $result = $this->profile->video($slug, $id);

    return Response::json($result);
  }

  public function feed($slug, $offset = 10, $limit = 10)
  {
    $result = [];
    $user = $this->user->findBySlug($slug)->first();

    if(!is_null($user))
    {
      $result = $this->profile->getFeed($user, $offset, $limit);
    }

    return Response::json($result);
  }

  public function photo($slug, $id)
  {
    $result = $this->profile->photo($slug, $id);

    return Response::json($result);
  }
}