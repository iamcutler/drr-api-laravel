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

  /**
   * Scoped queries
   */
  public function scopeFind_existing_like($query, Array $args)
  {
    // Find an existing like or dislike
    return $query
      ->where('element', '=', $args['element'])
      ->where('uid', '=', $args['uid']);
  }

  public function scopeFind_likes($query, $element, $id)
  {
    return $result = $query
      ->where('element', '=', $element)
      ->where('uid', '=', $id)
      ->get();
  }
}