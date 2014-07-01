<?php

class UserVideo extends Eloquent {
  /**
   * The database table used by the model.
   */
  protected $table = "community_videos";
  public $timestamps = false;

  /**
   * Mass Assignment
   */
  protected $fillable = [
    'title',
    'type',
    'video_id',
    'description',
    'creator',
    'creator_type',
    'created',
    'permissions',
    'category_id',
    'hits',
    'published',
    'featured',
    'duration',
    'status',
    'thumb',
    'path',
    'groupid',
    'filesize',
    'storage',
    'location',
    'latitude',
    'longitude',
    'params'
  ];

  /**
   * ORM relations
   */
  public function user_video()
  {
    return $this->belongsTo('CommUser', 'userid');
  }

  public function activity()
  {
    return $this
      ->hasOne('Activity', 'cid')
      ->where('app', '=', 'videos')
      ->first();
  }

  public function likes()
  {
    return $this->hasOne('Likes', 'uid');
  }

  public function wall()
  {
    return $this->hasMany('CommWall', 'contentid')
      ->where('type', '=', 'videos')
      ->orderBy('date', 'DESC');
  }

  /**
   * Query scopes
   */
  public function scopeFind_all_by_user_id($query, $id)
  {
    return $query
      ->with(['wall' => function($q) {
          $q->with('user.comm_user');
        }])
      ->with(['likes' => function($likes) {
          $likes->where('element', '=', 'videos');
        }])
      ->where('creator', '=', $id)
      ->where('published', '=', 1);
  }
}