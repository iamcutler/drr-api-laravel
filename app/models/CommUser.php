<?php

class CommUser extends Eloquent {
  /**
   * The database table used by the model.
   */
  protected $table = "community_users";
  protected $primaryKey = 'userid';
  public $timestamps = false;

  /**
   * Mass assignment
   */
  protected $fillable = ['userid', 'friends', 'alias'];
  protected $guarded = [];

  /**
  * ORM
  */
  public function user()
  {
    return $this->belongsTo('User', 'userid');
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

  public function user_event()
  {
    return $this->hasMany('UserEvent', 'creator');
  }

  /**
   * Scope queries
   */
  public function scopeFind_friend_by_id($query, $id)
  {
    return $query->find($id)->first();
  }

  public function scopeFind_by_slug($query, $slug)
  {
    return $query->where('alias', '=', $slug)->first();
  }

  public static function detect_user_relationship($friends, $user)
  {
    $result = false;
    foreach(str_getcsv($friends) as $val)
    {
      if($val == $user)
      {
        $result = true;
      }
    }

    return $result;
  }
}