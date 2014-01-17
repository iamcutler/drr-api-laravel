<?php
class DirtyGirl extends Eloquent {
  /**
   * The database table used by the model.
   */
  protected $table = "dirtygirlpages_";

  /**
   * Scoped queries
   */

  public function scopeGet_all_girls($query)
  {
    return $query
      ->orderBy('dirty_girl_name','ASC')
      ->get();
  }
}