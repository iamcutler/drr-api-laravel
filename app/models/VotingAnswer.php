<?php

class VotingAnswer extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "sexy_answers";

  /**
   * ORM relations
   */
  public function poll()
  {
    return $this->belongsTo('VotingPoll');
  }
}