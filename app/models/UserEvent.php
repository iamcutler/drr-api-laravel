<?php

class UserEvent extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_events";

  /**
   * ORM relations
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
      ->orderBy('enddate', 'DESC')
      ->first();
  }
}