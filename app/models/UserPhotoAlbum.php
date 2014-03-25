<?php

class UserPhotoAlbum extends Eloquent {
  /**
   * The database table used by the model.
   */
  protected $table = "community_photos_albums";
  public $timestamps = false;

  /**
   * Mass Assignment
   */
  protected $fillable = ['photoid', 'creator', 'name', 'description', 'permissions', 'created', 'path', 'type', 'groupid', 'hits', 'location', 'latitude', 'longitude', 'default', 'params'];
  protected $guarded = ['id'];

  /**
   * ORM relations
   */
  public function user_photo_album()
  {
    return $this->belongsTo('CommUser', 'userid');
  }

  public function photo()
  {
    return $this->hasMany('UserPhoto', 'albumid', 'id');
  }

  /**
   * Query scopes
   */
  public function scopeFind_all_by_user_id($query, $id)
  {
    return $query
      ->select([
        'id',
        'photoid',
        'name',
        'description',
        'permissions',
        'path',
        'hits',
        'location',
        'params',
        'default',
        'created'
      ])
      ->where('creator', '=', $id)
      ->orderBy('created', 'ASC')
      ->get();
  }

  public function scopeFindMobileAlbum($query, $id)
  {
    return $query
      ->where('creator', '=', $id)
      ->where('name', '=', 'Mobile Uploads')
      ->first();
  }
}