<?php

class Likes extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_likes";
  public $timestamps = false;

  /**
   * Mass assignment
   */
  protected $fillable = ['element', 'uid', 'like', 'dislike'];
  protected $guarded = ['id'];

  /**
   * ORM
   */
  public function user_like()
  {
    return $this->belongsTo('User', 'like', 'id')->first();
  }

  public function user_dislike()
  {
    return $this->belongsTo('User', 'like', 'id')->first();
  }
}