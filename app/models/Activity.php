<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class Activity extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_activities";
  public $timestamps = false;

  /**
   * Mass assignment
   */
  protected $fillable = [
    "actor", "target", "title", "content", "app", "verb", "cid", "groupid", "eventid", "group_access", "event_access", "created",
    "access", "params", "points", "archived", "location", "latitude", "longitude", "comment_id", "comment_type", "like_id", "like_type", "actors"
  ];

  /**
   * ORM
   */
  public function userActor()
  {
    return $this->belongsTo('User', 'actor');
  }

  public function actor_comm()
  {
    return $this->hasOne('CommUser', 'userid', 'actor');
  }

  public function userTarget()
  {
    return $this->hasOne('User', 'id', 'target');
  }

  public function activity_wall()
  {
    return $this->hasMany('CommWall', 'contentid', 'comment_id');
  }

  public function wall()
  {
    return $this->hasMany('CommWall', 'contentid', 'comment_id');
  }

  public function likes()
  {
    return $this->hasOne('Likes', 'uid', 'like_id');
  }

  public function event()
  {
    return $this->hasOne('Events', 'id', 'eventid')->first();
  }

  public function group()
  {
    return $this->hasOne('Group', 'id', 'groupid')->first();
  }

  public function event_likes()
  {
    return $this->hasMany('Likes', 'uid', 'like_id')
      ->where('element', '=', 'events.wall');
  }

  public function photo()
  {
    return $this->hasOne('UserPhoto', 'id', 'comment_id');
  }

  public function video()
  {
    return $this->hasOne('UserVideo', 'id', 'comment_id');
  }

  /**
   * Scoped queries
   */
  public function scopeNews_feed($query, User $user, $offset = 0, $limit = 10)
  {
    $comm_user = $user->comm_user()->first();
    // Friends array
    $friends = explode(',', $comm_user->friends);
    $friends[] = $comm_user->userid;

    return $query
      // Actors and targets
      ->with('userActor.comm_user', 'userTarget.comm_user')
      // Comments
      ->with(['wall' => function($query) {
          $query->orderBy('date', 'DESC')
                ->with('user.comm_user');
        }])
      // Media
      ->with('photo', 'video')
      ->whereIn('app', ['profile', 'profile.avatar.upload', 'videos', 'photos'])
      ->where('access', '<=', 30)
      ->whereIn('actor', $friends)
      ->orderBy('created', 'DESC')
      ->skip($offset)
      ->take($limit);

    // Original query to suit with jomsocial
    /*return $query
      ->where("group_access", "=", 0)
      ->orWhereIn("groupid", [''])
      ->orWhere("groupid", "=", 0)
      ->where("event_access", "=", 0)
      ->orWhereIn("eventid", [''])
      ->orWhere("eventid", "=", 0)
      ->where('access','<=', 10)
      ->groupBy('id')
      ->orderBy('created', 'DESC')
      ->orderBy('id', 'DESC')
      ->skip($offset)
      ->take($limit);*/
  }

  public function scopeMedia_feed($query, $offset = 0, $limit = 10)
  {
    return $query
      ->with('userActor.comm_user')
      // Wall
      ->with(['activity_wall' => function($query) {
        $query
          ->orderBy('date', 'DESC')
          ->with('user.comm_user');
      }])
      // Video && Photo
      ->with('video', 'photo')
      ->with(['likes' => function($query) {
          $query
            ->where('element', '=', 'photo')
            ->orWhere('element', '=', 'videos');
        }])
      ->where('access', '=', 0)
      ->where('app', '=', 'photos')
      ->where('like_type', '=', 'photo')
      ->orWhere('access', '=', 0)
      ->where('app', '=', 'videos')
      ->where('like_type', '=', 'videos')
      ->orderBy('created', 'DESC')
      ->skip($offset)
      ->take($limit);
  }

  public function scopeFind_by_like_id($query, $id)
  {
    return $query
      ->where('like_id', '=', $id)
      ->first();
  }

  // Fetch user profile feed
  public function scopeProfile_feed($query, $id, $offset = 0, $limit = 10)
  {
    $type = ['profile', 'profile.avatar.upload', 'videos', 'photos'];

    return $query
      ->whereIn('app', $type)
      ->where('actor', '=', $id)
      ->orWhere('app', '=', 'profile')
      ->where('actor', '=', $id)
      ->where('target', '=', $id)
      ->groupBy('community_activities.id')
      ->orderBy('created', 'DESC')
      ->skip($offset)
      ->take($limit)
      ->get();
  }

  public function scopeFindByCommentId($query, $id)
  {
    return $query->where('comment_id', '=', $id)->first();
  }
}