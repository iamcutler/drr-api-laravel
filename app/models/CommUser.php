<?php

class CommUser extends Eloquent {
  /**
   * The database table used by the model.
   */
  protected $table = "community_users";

  /**
  * ORM
  */
  public function user()
  {
    return $this->belongsTo('User');
  }

  public function user_photo_album()
  {
    return $this->hasMany('UserPhotoAlbum', 'creator');
  }

  public function user_photo()
  {
    return $this->hasMany('UserPhoto', 'creator');
  }

  public function user_video()
  {
    return $this->hasMany('UserVideo', 'creator');
  }

  public function user_group()
  {
    return $this->hasMany('UserGroup', 'ownerid');
  }

  public function user_event()
  {
    return $this->hasMany('UserEvent', 'creator');
  }

}