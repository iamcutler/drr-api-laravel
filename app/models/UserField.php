<?php

class UserField extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_fields";

  // Disable timestamps
  public $timestamps = false;

  /**
   * ORM
   */
  public function value()
  {
    return $this->masOne('UserFieldValue');
  }
}