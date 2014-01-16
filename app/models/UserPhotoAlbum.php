<?php

class UserPhotoAlbum extends Eloquent {
  /**
   * The database table used by the model.
   */
  protected $table = "community_photos_albums";
  protected $primaryKey = 'creator';

  /**
   * ORM relations
   */
  public function user_photo_album()
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
      ->get([
      'id',
      'name',
      'description',
      'path',
      'hits',
      'location',
      'default',
      'created'
    ]);
  }
}