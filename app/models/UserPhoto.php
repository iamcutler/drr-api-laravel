<?php

class UserPhoto extends Eloquent {
  /**
   * The database table used by the model.
   */
  protected $table = "community_photos";
  public $timestamps = false;

  /**
   * ORM relations
   */
  public function user_photo()
  {
    return $this->belongsTo('CommUser', 'userid');
  }

  /**
   * Query scopes
   */
  public function scopeFind_all_by_user_id($query, $id)
  {
    return $query->where('creator', '=', $id)
      ->where('published', '=', 1)
      ->orderBy('ordering')
      ->get([
        'id',
        'albumid',
        'caption',
        'image',
        'thumbnail',
        'original',
        'hits',
        'created'
      ]);
  }

  public function scopeFind_all_by_album_id($query, $id)
  {
    return $query
      ->where('albumid', '=', $id)
      ->where('published', '=', 1)
      ->orderBy('created', 'DESC');
  }
}