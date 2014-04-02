<?php

class ActivityController extends \BaseController {

  public function __construct(Activity $activity, User $user, Events $event, UserPhoto $photo, UserPhotoAlbum $album,
                              AWSRepositoryInterface $amazon, ProfileRepositoryInterface $profile, UserActivityRepositoryInterface $activityInterface)
  {
    $this->AWS = $amazon;
    $this->activity = $activity;
    $this->user = $user;
    $this->event = $event;
    $this->photo = $photo;
    $this->photo_album = $album;
    $this->profile = $profile;
    $this->activityInterface = $activityInterface;
  }

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {

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
    $params = Input::all();
    $user = $this->user->Find_id_by_hash($params['user_hash']);
    $result = ['result' => false];

    // Switch on app type
    switch($params['app'])
    {
      // Text status
      case 'status':
        $rules = [
          'app' => 'required',
          'status' => 'required'
        ];
        $validator = Validator::make($params, $rules);

        if($validator->passes())
        {
          $result = $this->activityInterface->saveTextStatus($user, [
            'actor' => $user->id,
            'target' => $user->id,
            'title' => $params['status'],
            'content' => '',
            'app' => 'profile',
            'verb' => '',
            'cid' => $user->id,
            'groupid' => 0,
            'eventid' => 0,
            'created' => date('Y-m-d H:i:s'),
            'access' => 0,
            'params' => '',
            'archived' => 0,
            'location' => '',
            'comment_id' => 0,
            'comment_type' => 'profile.status',
            'like_id' => 0,
            'like_type' => 'profile.status',
            'actors' => ''
          ]);
        }
        break;

      case 'photo-status':
        $params = Input::all();
        $rules = [
          'app' => 'required',
          'file' => 'required|image|max:10000000'
        ];
        $validator = Validator::make($params, $rules);

        if(!$validator->fails())
        {
          // Check if file was uploaded
          if(Input::hasFile('file'))
          {
            $file = Input::file('file');

            // Find or create mobile uploads album
            $mobile_album = $this->profile->findOrCreateMobileAlbum($user->id);

            $file_options = [
              'image_path' => "images/photos/{$user->id}/{$mobile_album->id}/",
              'thumb' => true,
              'thumb_size' => [
                'width' => 120,
                'height' => 120
              ]
            ];

            // Less than 10MB files
            if($file->getSize() <= 10000000)
            {
              // Upload/Generate uploaded files to AWS S3
              $upload = $this->AWS->S3ImgUpload($file, $file_options);

              if($upload['result'])
              {
                // Use image name if caption is null
                $caption = (Input::has('caption')) ? $params['caption'] : $file->getClientOriginalName();

                // Database transaction to save image/activity
                $trans = DB::transaction(function() use ($params, $user, $mobile_album, $caption, $upload) {
                  // Create photo record
                  $newPhoto = $this->photo->create([
                    'albumid' => $mobile_album->id,
                    'caption' => $caption,
                    'published' => 1,
                    'creator' => $user->id,
                    'permissions' => 0,
                    'image' => $upload['file']['image_path'] . $upload['file']['name'],
                    'thumbnail' => $upload['file']['image_path'] . $upload['file']['thumbnail'],
                    'original' => $upload['file']['image_path'] . $upload['file']['name'],
                    'filesize' => $upload['file']['size'],
                    'storage' => 'file',
                    'created' => date("Y-m-d H:i:s"),
                    'status' => 'ready',
                    'params' => '{}'
                  ]);

                  // Create activity record
                  $newActivity = $this->activity->create([
                    'actor' => $user->id,
                    'target' => 0,
                    'title' => $caption,
                    'content' => '',
                    'app' => 'photos',
                    'verb' => '',
                    'cid' => $mobile_album->id,
                    'created' => date("Y-m-d H:i:s"),
                    'access' => 0,
                    'params' => '',
                    'archived' => 0,
                    'location' => '',
                    'comment_id' => $newPhoto->id,
                    'comment_type' => 'photos',
                    'like_id' => $newPhoto->id,
                    'like_type' => 'photo',
                    'actors' => ''
                  ]);

                  // Make sure all records are successful
                  if($newActivity && $newPhoto)
                  {
                    return true;
                  }
                });

                if($trans)
                {
                  $result['result'] = true;
                }
                else {
                  $result['status'] = 102;
                }
              }
              else {
                $result['code'] = 101;
              }
            }
          }
          else {
            $result['code'] = 100;
          }
        }
        break;

      case 'events.wall':
        $rules = [
          'event_id' => 'required|integer',
          'app' => 'required',
          'actor' => 'required|integer',
          'message' => 'required'
        ];
        $validator = Validator::make($params, $rules);

        if(!$validator->fails())
        {
          $save = $this->activity->create([
            'actor' => $params['actor'],
            'target' => 0,
            'title' => $params['message'],
            'content' => '',
            'app' => $params['app'],
            'verb' => '',
            'cid' => $params['event_id'],
            'groupid' => 0,
            'eventid' => $params['event_id'],
            'created' => date('Y-m-d H:i:s'),
            'access' => 0,
            'params' => '',
            'archived' => 0,
            'location' => '',
            'comment_id' => 0,
            'comment_type' => $params['app'],
            'like_id' => 0,
            'like_type' => $params['app'],
            'actors' => ''
          ]);

          if($save)
          {
            $save->like_id = $save->id;
            $save->comment_id = $save->id;
            $save->save();

            // Return true and saved record
            $result['result'] = true;
            $result['activity'] = $this->get_activity($save);
          }
        }
        break;
    }

    return Response::json($result);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function show($id)
  {
    $activity = $this->activity->find($id);
    $results = [];

    if(!is_null($activity))
    {
      $user = $activity->actor();
      $comm_user = $user->comm_user()->first();

      $results['activity']['id'] = $activity->id;
      $results['activity']['title'] = $activity->title;
      $results['activity']['content'] = $activity->content;
      $results['activity']['app'] = $activity->app;
      $results['activity']['verb'] = $activity->verb;
      $results['activity']['cid'] = $activity->cid;
      $results['activity']['groupid'] = $activity->groupid;
      $results['activity']['eventid'] = $activity->eventid;
      $results['activity']['group_access'] = $activity->group_access;
      $results['activity']['event_access'] = $activity->event_access;
      $results['activity']['access'] = $activity->access;
      $results['activity']['params'] = json_decode($activity->params);
      $results['activity']['points'] = $activity->points;
      $results['activity']['archived'] = $activity->archived;
      $results['activity']['location'] = $activity->location;
      $results['activity']['comment_id'] = $activity->comment_id;
      $results['activity']['comment_type'] = $activity->comment_type;
      $results['activity']['like_id'] = $activity->like_id;
      $results['activity']['like_type'] = $activity->like_type;
      $results['activity']['actors'] = $activity->actors;
      $results['activity']['created'] = $activity->created;

      $results['activity']['user']['id'] = $user->id;
      $results['activity']['user']['name'] = $user->name;
      $results['activity']['user']['avatar'] = $comm_user->avatar;
      $results['activity']['user']['thumbnail'] = $comm_user->thumb;
      $results['activity']['user']['slug'] = $comm_user->alias;

      $results['activity']['target'] = [];
      if($activity->target != 0 || $activity->actor == $activity->target)
      {
        $target = $activity->target();
        $target_comm = $target->comm_user()->first();

        $results['activity']['target']['id'] = $target->id;
        $results['activity']['target']['name'] = $target->name;
        $results['activity']['target']['avatar'] = $target_comm->avatar;
        $results['activity']['target']['thumbnail'] = $target_comm->thumb;
        $results['activity']['target']['slug'] = $target_comm->alias;
      }
      else {
        $results['activity']['target']['id'] = $user->id;
        $results['activity']['target']['name'] = $user->name;
        $results['activity']['target']['avatar'] = $comm_user->avatar;
        $results['activity']['target']['thumbnail'] = $comm_user->thumb;
        $results['activity']['target']['slug'] = $comm_user->alias;
      }

      // Activity Stats
      $results['activity']['stats']['likes'] = (int) $activity->likes()->where('element', '=', $activity->like_type)->where('like', '!=', '')->count();
      $results['activity']['stats']['dislikes'] = (int) $activity->likes()->where('element', '=', $activity->like_type)->where('dislike', '!=', '')->count();

      // Activity comments
      $results['activity']['comments'] = [];
      foreach($activity->wall() as $key => $value)
      {
        $user = $value->user();
        $comm_user = $user->comm_user()->first();

        $results['activity']['comments'][$key]['id'] = $value->id;
        $results['activity']['comments'][$key]['comment'] = $value->comment;
        $results['activity']['comments'][$key]['date'] = $value->date;
        $results['activity']['comments'][$key]['type'] = $value->type;

        $results['activity']['comments'][$key]['user']['id'] = $user->id;
        $results['activity']['comments'][$key]['user']['name'] = $user->name;
        $results['activity']['comments'][$key]['user']['thumbnail'] = $comm_user->thumb;
        $results['activity']['comments'][$key]['user']['avatar'] = $comm_user->avatar;
        $results['activity']['comments'][$key]['user']['slug'] = $comm_user->alias;
        $results['activity']['comments'][$key]['user']['slug'] = $comm_user->alias;

        // Comment stats
        $results['activity']['comments'][$key]['stats']['likes'] = (int) $value->activity_likes()->where('element', '=', $activity->comment_type)->where('like', '!=', '')->count();
        $results['activity']['comments'][$key]['stats']['dislikes'] = (int) $value->activity_likes()->where('element', '=', $activity->comment_type)->where('dislike', '!=', '')->count();
      }
    }

    return Response::json($results);
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
    $input = Input::all();
    $rules = ['app' => 'required'];
    $validator = Validator::make($input, $rules);
    $result = ['result' => false];

    if(!$validator->fails())
    {
      $user = $this->user->Find_id_by_hash($input['user_hash']);
      $activity = $this->activity->find($id);

      if(!is_null($activity))
      {
        if($activity->app == $input['app'])
        {
          if($this->check_user_permissions($user, $activity))
          {
            $activity->delete();
            $result = ['result' => true];
          }
        }
      }
    }

    return Response::json($result);
  }

  protected function check_user_permissions(User $user, Activity $activity)
  {
    // Do a switch of the app type to find the right user permissions
    switch($activity->app)
    {
      // Events
      case 'events.wall':
        $event = $activity->event();
        $members = $event->member();
        foreach($members as $key => $member)
        {
          if($member->memberid == $user->id || $member->permission == 1)
          {
            return true;
          }
        }
        break;
      // Groups
      case 'groups.wall':
        $group = $activity->group();
        $members = $group->member();
        foreach($members as $key => $member)
        {
          if($member->memberid == $user->id || $member->permissions == 1)
          {
            return true;
          }
        }
        break;
    }

    return false;
  }

  public function event_attendance($id)
  {
    $user = $this->user->Find_id_by_hash(Input::get('user_hash'));
    $event = $this->event->find($id);
    $result = ['result' => false];
    $attending = 0;

    if(!is_null($event))
    {
      foreach($event->member() as $key => $value)
      {
        if($value->memberid == $user->id)
        {
          if($value->status != 1)
          {
            $attending = 1;
          }

          // Save new attendance for event member
          $value->status = $attending;
          $value->save();

          $result['result'] = true;
          $result['attending'] = $attending;
        }
      }
    }

    return $result;
  }

  // Paginate event activity
  protected function get_activity(Activity $activity)
  {
    // Event activity - Initial call will paginate 10 records.
    $results = [];
    $key = 0;
    $comm_user = $activity->actor_comm();
    $actor = $activity->actor();

    $results[$key]['id'] = $activity->id;
    $results[$key]['user']['id'] = $actor->id;
    $results[$key]['user']['name'] = $actor->name;
    $results[$key]['user']['avatar'] = $comm_user->avatar;
    $results[$key]['user']['thumbnail'] = $comm_user->thumb;
    $results[$key]['user']['slug'] = $comm_user->alias;

    $results[$key]['comments'] = [];
    foreach($activity->wall() as $k => $value)
    {
      $user = $value->user();
      $comm_user = $user->comm_user()->first();

      $results[$key]['comments'][$k]['user']['name'] = $user->name;
      $results[$key]['comments'][$k]['user']['avatar'] = $comm_user->avatar;
      $results[$key]['comments'][$k]['user']['thumbnail'] = $comm_user->thumb;
      $results[$key]['comments'][$k]['user']['slug'] = $comm_user->alias;

      $results[$key]['comments'][$k]['comment'] = $value['comment'];
      $results[$key]['comments'][$k]['date'] = $value['date'];
    }

    $results[$key]['app'] = $activity->app;
    $results[$key]['title'] = $activity->title;
    $results[$key]['comment_id'] = $activity->comment_id;
    $results[$key]['comment_type'] = $activity->comment_type;
    $results[$key]['like_id'] = $activity->like_id;
    $results[$key]['like_type'] = $activity->like_type;
    $results[$key]['created'] = $activity->created;

    // Get comment stats
    $results[$key]['stats'] = [];
    $results[$key]['stats']['likes'] = $activity->event_likes()->count();

    foreach($activity->event_likes()->get() as $i => $v)
    {
      // Likes
      $user = $v->user_like();

      if(!is_null($user))
      {
        $comm_user = $user->comm_user()->first();

        /*$results[$key]['stats']['likes']['user']['name'] = $user->name;
        $results[$key]['stats']['likes']['user']['avatar'] = $comm_user->avatar;
        $results[$key]['stats']['likes']['user']['thumbnail'] = $comm_user->thumb;
        $results[$key]['stats']['likes']['user']['slug'] = $comm_user->alias;*/
      }
    }

    return $results;
  }
}