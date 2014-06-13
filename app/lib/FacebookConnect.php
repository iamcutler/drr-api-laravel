<?php

class FacebookConnect implements FacebookRepository {
  public function __construct(User $user, UserRepositoryInterface $userRepo)
  {
    $this->user = $user;
    $this->userRepo = $userRepo;
  }

  public function login($fb)
  {
    $result['status'] = false;

    // Check for existing facebook user
    $existing = $this->user->where('email', '=', $fb['email'])->first();

    if($existing)
    {
      // Get relational comm_user data
      $comm_user = $existing->comm_user()->first();

      $result = ['status' => true,
        'user' => [
          'id' => $existing->id,
          'name' => $existing->name,
          'slug' => $comm_user->alias,
          'thumbnail' => $comm_user->thumb,
          'username' => $existing->username,
          'hash' => $existing->user_hash
        ]
      ];
    }
    else {
      // Save new user
      $user = $this->save_user($fb);

      if($user['status'])
      {
        $result = ['status' => true,
          'user' => [
            'id' => $user['user']->id,
            'name' => $user['user']->name,
            'slug' => $user['comm_user']->alias,
            'thumbnail' => $user['comm_user']->thumb,
            'username' => $user['user']->username,
            'hash' => $user['user']->user_hash
          ]
        ];
      }
    }

    return $result;
  }

  protected function save_user($fb)
  {
    $result['status'] = false;

    // Start transaction!
    DB::beginTransaction();

    try {
      $user = new User;
      $user->name = $fb['first_name'] ." ". $fb['last_name'];
      $user->username = User::checkOrOverrideUsername($fb['username']);
      $user->email = $fb['email'];
      $user->password = User::generate_password('facebookConnectDRR');
      $user->usertype = 2;
      $user->registerDate = date("Y-m-d H:i:s");
      $user->lastvisitDate = date("Y-m-d H:i:s");
      $user->params = '';
      $user->user_hash = User::generate_hash($fb['first_name'] . " " . $fb['last_name'], $fb['username']);
      $user->save();

      $comm_user = new CommUser;
      $comm_user->userid = $user->id;
      $comm_user->params = json_encode([
        "notifyEmailSystem" => 1,
        "privacyProfileView" => 0,
        "privacyPhotoView" => 0,
        "privacyFriendsView" => 0,
        "privacyGroupsView" => "",
        "privacyVideoView" => 0,
        "notifyEmailMessage" => 1,
        "notifyEmailApps" => 1,
        "notifyWallComment" => 0,
        "notif_groups_notify_admin" => 1,
        "etype_groups_notify_admin" => 1,
        "notif_user_profile_delete" => 1,
        "etype_user_profile_delete" => 1,
        "notif_system_reports_threshold" => 1,
        "etype_system_reports_threshold" => 1,
        "notif_profile_activity_add_comment" => 1,
        "etype_profile_activity_add_comment" => 1,
        "notif_profile_activity_reply_comment" => 1,
        "etype_profile_activity_reply_comment" => 1,
        "notif_profile_status_update" => 1,
        "etype_profile_status_update" => 1,
        "notif_profile_like" => 1,
        "etype_profile_like" => 1,
        "notif_profile_stream_like" => 1,
        "etype_profile_stream_like" => 1,
        "notif_friends_request_connection" => 1,
        "etype_friends_request_connection" => 1,
        "notif_friends_create_connection" => 1,
        "etype_friends_create_connection" => 1,
        "notif_inbox_create_message" => 1,
        "etype_inbox_create_message" => 1,
        "notif_groups_invite" => 1,
        "etype_groups_invite" => 1,
        "notif_groups_discussion_reply" => 1,
        "etype_groups_discussion_reply" => 1,
        "notif_groups_wall_create" => 1,
        "etype_groups_wall_create" => 1,
        "notif_groups_create_discussion" => 1,
        "etype_groups_create_discussion" => 1,
        "notif_groups_create_news" => 1,
        "etype_groups_create_news" => 1,
        "notif_groups_create_album" => 1,
        "etype_groups_create_album" => 1,
        "notif_groups_create_video" => 1,
        "etype_groups_create_video" => 1,
        "notif_groups_create_event" => 1,
        "etype_groups_create_event" => 1,
        "notif_groups_sendmail" => 1,
        "etype_groups_sendmail" => 1,
        "notif_groups_member_approved" => 1,
        "etype_groups_member_approved" => 1,
        "notif_groups_member_join" => 1,
        "etype_groups_member_join" => 1,
        "notif_groups_notify_creator" => 1,
        "etype_groups_notify_creator" => 1,
        "notif_groups_discussion_newfile" => 1,
        "etype_groups_discussion_newfile" => 1,
        "notif_groups_activity_add_comment" => 0,
        "etype_groups_activity_add_comment" => 0,
        "notif_events_notify_admin" => 1,
        "etype_events_notify_admin" => 1,
        "notif_events_invite" => 1,
        "etype_events_invite" => 1,
        "notif_events_invitation_approved" => 1,
        "etype_events_invitation_approved" => 1,
        "notif_events_sendmail" => 1,
        "etype_events_sendmail" => 1,
        "notif_event_notify_creator" => 0,
        "etype_event_notify_creator" => 0,
        "notif_event_join_request" => 1,
        "etype_event_join_request" => 1,
        "notif_events_activity_add_comment" => 0,
        "etype_events_activity_add_comment" => 0,
        "notif_videos_submit_wall" => 1,
        "etype_videos_submit_wall" => 1,
        "notif_videos_reply_wall" => 1,
        "etype_videos_reply_wall" => 1,
        "notif_videos_tagging" => 1,
        "etype_videos_tagging" => 1,
        "notif_videos_like" => 1,
        "etype_videos_like" => 1,
        "notif_photos_submit_wall" => 1,
        "etype_photos_submit_wall" => 1,
        "notif_photos_reply_wall" => 1,
        "etype_photos_reply_wall" => 1,
        "notif_photos_tagging" => 1,
        "etype_photos_tagging" => 1,
        "notif_photos_like" => 1,
        "etype_photos_like" => 1,
        "notif_system_bookmarks_email" => 1,
        "etype_system_bookmarks_email" => 1,
        "notif_system_messaging" => 1,
        "etype_system_messaging" => 1,
        "postFacebookStatus" => 1
      ]);
      $comm_user->alias = $user->id . ":" . str_replace(' ', '-', $user->name);
      $comm_user->save();

      $connect = new ConnectUser;
      $connect->connectid = $fb['id'];
      $connect->type = 'facebook';
      $connect->userid = $user->id;
      $connect->save();
    }
    catch(\Exception $e) {
      DB::rollback();
      throw $e;
    }

    // Commit transaction queries
    DB::commit();

    $result['status'] = true;
    $result['user'] = $user;
    $result['comm_user'] = $comm_user;

    return $result;
  }
}