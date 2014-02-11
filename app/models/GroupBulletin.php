<?php

class GroupBulletin extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_groups_bulletins";
  public $timestamps = false;

  /**
   * Mass assignment
   */
  protected $fillable = ['groupid', 'created_by', 'published', 'title', 'message', 'date', 'params'];
  protected $guarded = ['id'];

  /**
   * ORM
   */
  public function user()
  {
    return $this->hasOne('User', 'id', 'created_by')->first();
  }
}