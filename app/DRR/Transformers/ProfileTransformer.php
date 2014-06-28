<?php namespace DRR\Transformers;

use \UserConnection as Connection;

class ProfileTransformer extends Transformer {

  protected $userTransformer;
  protected $likesTransformer;
  protected $connection;

  function __construct(UserTransformer $userTransformer, LikesTransformer $likesTransformer, Connection $connection)
  {
    $this->userTransformer = $userTransformer;
    $this->likesTransformer = $likesTransformer;
    $this->connection = $connection;
  }

  /**
   * @param $profile
   * @desc transform profile. **Must include requester in profile array to run relations**
   * @return array
   */
  public function transform($profile)
  {
    // Find user friends count and friend status to requester
    $friend_count = 0;
    $friend_status = false;
    $request_sent = false;

    if($profile['comm_user']['friends'] != "")
    {
      foreach(str_getcsv($profile['comm_user']['friends'], ',') as $val )
      {
        if($val == $profile['requester']['id'])
        {
          $friend_status = true;
        }

        $friend_count++;
      }
    }

    // Change friend request to true if detected
    if($this->connection->find_existing_connection($profile['id'], $profile['requester']['id'])->count() > 0)
    {
      $request_sent = true;
    }


    return [
      'user' => $this->userTransformer->transform($profile),
      'profile' => [
        'views' => (int) $profile['comm_user']['view'],
        'friends' => $profile['comm_user']['friends'],
        'friend_count' => (int) $profile['comm_user']['friendcount'],
        'last_visit' => $profile['lastvisitDate'],
        'registered' => $profile['registerDate'],
        'settings' => json_decode($profile['comm_user']['params']),
        'status' => [
          'status' => $profile['comm_user']['status'],
          'created' => $profile['comm_user']['posted_on']
        ],
        // Counts
        'counts' => [
          'photos' => (int) count($profile['photo']),
          'videos' => (int) count($profile['video']),
          'events' => (int) count($profile['event_member']),
          'groups' => (int) count($profile['group_member']),
          'friends' => $friend_count
        ],
        // Relationship to requester
        'relation' => [
          'self' => (bool) ($profile['requester']['id'] == $profile['id']) ? true : false,
          'friends' => (bool) $friend_status,
          'request_sent' => (bool) $request_sent
        ],
        // Profile status
        'stats' => $this->likesTransformer->transform($profile['profile_likes'])
    ]
    ];
  }
}
