<?php

class CommUser extends Eloquent {
  /**
   * The database table used by the model.
   */
  protected $table = "community_users";

  /**
  * Belongs to User
  */
  public function user()
  {
    return $this->belongsTo('User');
  }
}