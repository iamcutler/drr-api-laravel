<?php

class Voting implements VotingRepositoryInterface {

  public function __construct(VotingVote $vote, VotingAnswer $answer)
  {
    $this->vote = $vote;
    $this->answer = $answer;
  }

  public function castVote($id)
  {
    // Find answer
    $answer = $this->answer->find($id);

    if(!is_null($answer))
    {
      // Get answer poll
      $poll = $answer->poll()->first();

      if(!is_null($poll))
      {
        // Save new vote
        $this->vote->create([
          'id_answer' => $answer->id,
          'ip' => Request::getClientIp(),
          'date' => date("Y-m-d H:i:s"),
          'country' => '',
          'city' => '',
          'region' => '',
          'countrycode' => ''
        ]);
      }
    }
  }
}