<?php

class VotingAnswer extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "sexy_answers";
  public $timestamps = false;

  /**
   * ORM relations
   */
  public function poll()
  {
    return $this->belongsTo('VotingPoll', 'id_poll');
  }

  public function votes()
  {
    return $this->hasMany('VotingVote', 'id_answer');
  }

  public function user()
  {
    return $this->hasOne('CommUser', 'alias', 'username');
  }
}