<?php

class UserVideo extends Eloquent {
  /**
   * The database table used by the model.
   */
  protected $table = "community_videos";
  public $timestamps = false;

  /**
   * ORM relations
   */
  public function user_video()
  {
    return $this->belongsTo('CommUser', 'userid');
  }

  /**
   * Query scopes
   */
  public function scopeFind_all_by_user_id($query, $id)
  {
    return $query
      ->where('creator', '=', $id)
      ->where('published', '=', 1);
  }
}