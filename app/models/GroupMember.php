<?php

class GroupMember extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_groups_members";
  protected $primaryKey = "groupid";
  public $timestamps = false;

  /**
   * Mass assignment
   */
  protected $fillable = ['groupid', 'memberid', 'approved', 'permissions'];

  /**
   * ORM
   */
  public function group()
  {
    return $this->belongsTo('Group', 'groupid')->first();
  }

  public function user()
  {
    return $this->hasOne('User', 'id', 'memberid')->first();
  }

  /**
   * Scoped queries
   */
  public function scopeFind_by_user_id($query, $id)
  {
    return $query
      ->where('memberid', '=', $id)
      ->where('approved', '=', 1)
      ->get();
  }
}