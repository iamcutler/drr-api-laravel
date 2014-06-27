<?php

use DRR\Transformers\VideoTransformer;
use DRR\Transformers\WallTransformer;

class ProfileController extends \BaseController {

  /**
   * @var DRR\Transformers\
   */
  protected $videoTransformer;
  protected $wallTransformer;

  public function __construct(User $user, CommUser $comm_user, UserPhoto $photo, UserVideo $video,
                              UserConnection $connection, EventMember $eventMember, GroupMember $groupMember,
                              ProfileRepositoryInterface $profile, PresenterRepositoryInterface $presenter,
                              VideoTransformer $videoTransformer, WallTransformer $wallTransformer)
  {
    $this->user = $user;
    $this->comm_user = $comm_user;
    $this->photo = $photo;
    $this->video = $video;
    $this->connection = $connection;
    $this->eventMember = $eventMember;
    $this->groupMember = $groupMember;
    $this->profile = $profile;
    $this->presenter = $presenter;
    $this->videoTransformer = $videoTransformer;
    $this->wallTransformer = $wallTransformer;
  }

  public function user_profile($slug)
  {
    $params = Input::all();
    $requester = $this->user->Find_id_by_hash($params['user_hash']);
    $profile = $this->user->EagerProfileData()->find($this->comm_user->where('alias', '=', $slug)->first()->userid);
    $result = [];

    if(!is_null($profile))
    {
      $result['user']['id'] = (int) $profile->id;
      $result['user']['name'] = $profile->name;
      $result['user']['username'] = $profile->username;
      $result['user']['slug'] = $profile->comm_user->alias;
      $result['user']['avatar'] = $profile->comm_user->avatar;
      $result['user']['thumbnail'] = $profile->comm_user->thumb;

      // Relation array to tell relationship to requester
      $result['relation'] = [];
      $result['relation']['self'] = ($requester->id == $profile->id) ? true : false;
      $result['relation']['friends'] = false;
      $result['relation']['request_sent'] = false;

      // Check if requester and user are friends
      foreach(str_getcsv($profile->comm_user->friends, ',') as $key => $val)
      {
        if($val == $requester->id)
        {
          $result['relation']['friends'] = true;
        }
      }

      // Change friend request to true if detected
      if($this->connection->Find_existing_connection($profile->id, $requester->id)->count() > 0)
      {
        $result['relation']['request_sent'] = true;
      }

      // Profile array
      $result['profile']['status']['status'] = $profile->comm_user->status;
      $result['profile']['status']['created'] = $profile->comm_user->posted_on;

      $result['profile']['views'] = $profile->comm_user->view;
      $result['profile']['friends'] = $profile->comm_user->friends;
      $result['profile']['friend_count'] = (int) $profile->comm_user->friendcount;
      $result['profile']['last_visit'] = $profile->lastvisitDate;
      $result['profile']['registered'] = $profile->registerDate;

      $result['profile']['settings'] = json_decode($profile->comm_user->params);

      // Profile status
      $result['profile']['stats'] = $this->presenter->likeStats($profile->profile_likes, 0);

      // Count array
      $result['profile']['counts']['photos'] = (int) $profile->photo->count();
      $result['profile']['counts']['videos'] = (int) $profile->video->count();
      $result['profile']['counts']['events'] = (int) $profile->eventMember->count();
      $result['profile']['counts']['groups'] = (int) $profile->groupMember->count();

      $friend_count = 0;
      if($profile->comm_user->friends != "")
      {
        foreach(str_getcsv($profile->comm_user->friends, ',') as $val ) { $friend_count++; }
      }
      $result['profile']['counts']['friends'] = $friend_count;

      // Update profile views per hit
      $this->profile->addProfileView($profile->comm_user);
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