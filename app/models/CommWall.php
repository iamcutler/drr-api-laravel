<?php

class CommWall extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_wall";
  public $timestamps = false;

  /**
   * Mass assignment
   */
  protected $fillable = ['contentid', 'post_by', 'ip', 'comment', 'date', 'published', 'type'];
  protected $guarded = ['id'];

  /**
   * ORM
   */
  public function user()
  {
    return $this->belongsTo('User', 'post_by', 'id')->first();
  }

  public function comm_user()
  {
    return $this->hasOne('CommUser', 'userid', 'post_by')->first();
  }

  public function likes()
  {
    return $this->hasMany('Likes', 'uid', 'contentid')
      ->where('element', '=', 'events.wall')->get();
  }

  public function activity_likes()
  {
    return $this->hasMany('Likes', 'uid');
  }

  public function activity()
  {
    return $this->hasOne('Activity', 'id', 'contentid');
  }
}