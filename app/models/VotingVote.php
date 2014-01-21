<?php

class VotingVote extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "sexy_votes";
  protected $primaryKey = "id_answer";

  // Disable table timestamps
  public $timestamps = false;

  /**
   * Mass assignment
   */
  protected $fillable = ['id_answer', 'ip', 'date', 'country', 'city', 'region', 'countrycode'];

  /**
   * Model validations
   */
  function Validation($input)
  {
    return Validator::make($input, [
      'id_answer' => 'required',
      'date' => 'required'
    ]);
  }

  /**
   * ORM relations
   */
  public function answer()
  {
    return $this->belongsTo('VotingAnswer');
  }

  /**
   * Scoped queries
   */
  public function scopeAnswer_vote_count($query, $id)
  {
    return $query
      ->select('id_answer')
      ->where('id_answer', '=', $id)
      ->groupBy('id_answer')
      ->count();
  }
}