<?php

class CommWall extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_wall";
  public $timestamp = false;

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
}