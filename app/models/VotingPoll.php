<?php

class VotingPoll extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "sexy_polls";

  /**
   * ORM relations
   */
  public function answer()
  {
    return $this->hasMany('VotingAnswer', 'id_poll');
  }

  /**
   * Scoped queries
   */
  public function scopeGet_current($query)
  {
    return $query
      ->where('date_start', '<=', date("Y-m-d H:i:s"))
      ->where('date_end', '>=', date("Y-m-d H:i:s"))
      ->where('published', '=', 1)
      ->take(1);
  }

  public function scopeGet_answers($query, $id)
  {
    return $query
      ->find($id)
      ->answer()
      ->where('published', '=', 1);
  }
}