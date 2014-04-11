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
      ->with(['answer' => function($query) {
          $query
            ->with('votes', 'user.user')
            ->where('published', '=', 1)
            ->orderBy('name', 'ASC');
        }])
      ->where('date_start', '<=', date("Y-m-d H:i:s"))
      ->where('date_end', '>=', date("Y-m-d H:i:s"))
      ->where('published', '=', 1);
  }
}