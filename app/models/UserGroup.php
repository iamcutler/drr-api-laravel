<?php

class UserGroup extends Eloquent {
  /**
   * Table used by the model.
   */
  protected $table = "community_groups";

  /**
   * Relations
   */
  public function user()
  {
    return $this->belongsTo('CommUser');
  }

  /**
   * Scoped queries
   */

  public function scopeFind_by_id($query, $id)
  {
    return $query->find($id)
      ->where('published', '=', 1)
      ->orderBy('created', 'DESC')
      ->first();
  }
}