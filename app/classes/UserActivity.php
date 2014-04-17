<?php

class UserActivity implements UserActivityRepositoryInterface {
  public function __construct(Likes $like, Activity $activity, User $user, UserPhoto $photo, UserVideo $video,
                              ProfileRepositoryInterface $profile, AWSRepositoryInterface $aws,
                              PresenterRepositoryInterface $presenter)
  {
    $this->like = $like;
    $this->activity = $activity;
    $this->user = $user;
    $this->profile = $profile;
    $this->photo = $photo;
    $this->video = $video;
    $this->AWS = $aws;
    $this->presenter = $presenter;
  }

  public function setLike(User $user, $element, $id, $type)
  {
    $record = false;
    $result = [];

    // Assign array for like or dislike
    $like = [
      'element' => $element,
      'uid' => $id,
      'user' => $user->id
    ];

    if($type == 1)
    {
      $record = $this->createOrOverwriteOrRemoveLike(1, $like);
    }
    elseif($type == 0) {
      $record = $this->createOrOverwriteOrRemoveLike(0, $like);
    }

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

    return $result;
  }

  public function saveTextStatus(User $user, Array $options)
  {
    $result = [];
    $result['result'] = false;
    $save = $this->activity->create($options);

    if($save)
    {
      $save->like_id = $save->id;
      $save->comment_id = $save->id;
      if($save->save())
      {
        // Save user status in user model
        $user_comm = $user->comm_user()->first();

        $user_comm->status = $options['title'];
        $user_comm->points = $user_comm->points + 1;
        $user_comm->posted_on = date("Y-m-d H:i:s");

        if($user_comm->save())
        {
          // Return true and saved record
          $result['result'] = true;
          $result['activity'] = $this->presenter->getFeedResource($save);
        }
      }
    }

    return $result;
  }

  protected function createOrOverwriteOrRemoveLike($type, Array $args)
  {
    $result = $this->like->Find_existing_like($args);

    if($result->count() == 0)
    {
      // Create like if not found
      $like = new Likes;

      $like->element = $args['element'];
      $like->uid = $args['uid'];

      if($type) {
        $like->like = $args['user'];
        $like->dislike = '';
      } else {
        $like->like = '';
        $like->dislike = $args['user'];
      }

      $like->save();

      return true;
    }
    else {
      // Check if this is a like, if not, it must be a dislike
      if($type == 1 && $result->like == $args['user'] || $type == 0 && $result->dislike == $args['user'])
      {
        // Remove if found
        $result->delete();

        return true;
      }
      else {
        if($type == 1) {
          $result->like = $args['user'];
          $result->dislike = '';
        }
        else {
          $result->like = '';
          $result->dislike = $args['user'];
        }

        $result->save();

        return true;
      }
    }

    return false;
  }

  public function user_search($q, $user_id, $type = 'name', $offset = 0, $limit = 20)
  {
    switch($type)
    {
      case 'username':
        break;
      case 'name':
        $result = $this->user->searchByName($q, $user_id, $offset);
        break;
      default:
        $result = $this->user->searchByName($q, $user_id, $offset);
    }

    return $result;
  }

  public function processPhotoStatusUpload($file, User $user, Array $options = [])
  {
    $result = [];
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

    // Upload/Generate uploaded files to AWS S3
    $upload = $this->AWS->S3ImgUpload($file, $file_options);

    if($upload['result'])
    {
      // Use image name if caption is null
      $caption = ($options['caption']) ? $options['caption'] : '';

      // Database transaction to save image/activity
      $trans = DB::transaction(function() use ($user, $mobile_album, $caption, $upload) {
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
        return true;
      }
    }

    return false;
  }

  public function processVideoStatusUpload($file, User $user, Array $options = [])
  {
    $result = false;
    // Use image name if caption is null
    $caption = (array_key_exists('caption', $options)) ? $options['caption'] : '';
    // Upload video to AWS S3
    $upload = $this->AWS->S3VideoUpload($file, $user);

    if($upload['result'])
    {
      // Database transaction to save image/activity
      $result = DB::transaction(function() use ($file, $upload, $user, $caption) {
        // Create photo record
        $newVideo = $this->video->create([
          'title' => $caption,
          'type' => 'file',
          'description' => 'No description',
          'creator' => $user->id,
          'category_id' => 0,
          'created' => date("Y-m-d H:i:s"),
          'thumb' => '',
          'path' => Config::get("constant.cdn_domain") . $upload['file']['path'] . $upload['file']['name'],
          'filesize' => $upload['file']['size'],
          'storage' => 'file',
          'location' => '',
          'status' => 'ready',
          'params' => "{}"
        ]);

        // Create activity record
        $newActivity = $this->activity->create([
          'actor' => $user->id,
          'target' => 0,
          'title' => $caption,
          'content' => '',
          'app' => 'videos',
          'verb' => '',
          'cid' => $newVideo->id,
          'created' => date("Y-m-d H:i:s"),
          'access' => 0,
          'params' => '',
          'archived' => 0,
          'location' => '',
          'comment_id' => $newVideo->id,
          'comment_type' => 'videos',
          'like_id' => $newVideo->id,
          'like_type' => 'videos',
          'actors' => ''
        ]);

        // Make sure all records are successful
        if($newActivity && $newVideo)
        {
          $newActivity->params = "{\"video_url\":\"index.php?option=com_community&view=videos&task=video&userid={$user->id}&videoid={$newVideo->id}\",\"style\":\"1\"}";
          if($newActivity->save())
          {
            return true;
          }
        }
        else {
          // Remove object from AWS bucket if unsuccessful transaction
          $this->AWS->deleteS3Object([
            'Bucket' => Config::get('constant.AWS.bucket'),
            'Key' => $upload['file']['path'] . $upload['file']['name']
          ]);

          return false;
        }
      });
    }

    return $result;
  }
}