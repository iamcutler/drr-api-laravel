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
  public function actor()
  {
    return $this->belongsTo('User', 'actor')->first();
  }

  public function actor_comm()
  {
    return $this->hasOne('CommUser', 'userid', 'actor')->first();
  }

  public function target()
  {
    return $this->hasOne('User', 'id', 'target')->first();
  }

  public function wall()
  {
    return $this->hasMany('CommWall', 'contentid', 'id')
      ->where('published', '=', 1)
      ->orderBy('date', 'DESC')
      ->get();
  }

  public function likes()
  {
    return $this->hasMany('Likes', 'uid', 'like_id');
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
    return $this->hasOne('UserPhoto', 'id', 'comment_id')
      ->where('permissions', '<=', 10)
      ->where('published', '=', 1)
      ->first();
  }

  public function video()
  {
    return $this->hasOne('UserVideo', 'id', 'comment_id')
      ->where('permissions', '=', 0)
      ->where('published', '=', 1)
      ->first();
  }

  /**
   * Scoped queries
   */
  public function scopeNews_feed($query, $offset = 0, $limit = 10)
  {
    return $query
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
      ->take($limit);
  }

  public function scopeMedia_feed($query, $offset = 0, $limit = 10)
  {
    return $query
      ->where('access', '=', 0)
      ->where('app', '=', 'photos')
      ->orWhere('app', '=', 'videos')
      ->orderBy('created', 'DESC')
      ->take($limit)
      ->skip($offset);
  }
}