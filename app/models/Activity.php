<?php

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
  public function actor()
  {
    return $this->belongsTo('User', 'actor')->first();
  }

  public function actor_comm()
  {
    return $this->hasOne('CommUser', 'userid', 'actor')->first();
  }

  public function wall()
  {
    return $this->hasMany('CommWall', 'contentid', 'id')
      ->where('published', '=', 1)
      ->get();
  }

  public function likes()
  {
    return $this->hasMany('Likes', 'uid', 'like_id');
  }
}