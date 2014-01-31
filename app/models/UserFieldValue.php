<?php

class UserFieldValue extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_fields_values";

  public $timestamps = false;

  /**
   * ORM
   */
  public function field()
  {
    return $this->belongsTo('UserField');
  }
}