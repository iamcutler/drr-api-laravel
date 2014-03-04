<?php

class UserField extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_fields";
  public $timestamps = false;

  /**
   * ORM
   */
  public function value($user)
  {
    return $this
      ->hasOne('UserFieldValue', 'field_id', 'id')
      ->where('user_id', '=', $user);
  }
}