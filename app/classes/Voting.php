<?php

class Voting implements VotingRepositoryInterface {

  public function __construct(VotingVote $vote, VotingAnswer $answer)
  {
    $this->vote = $vote;
    $this->answer = $answer;
  }

  public function castVote($params)
  {
    // Find answer
    $answer = $this->answer->find($params['id_answer']);

    if(!is_null($answer))
    {
      // Get answer poll
      $poll = $answer->poll()->first();

      if(!is_null($poll))
      {
        // Client ip
        $ip = Request::getClientIp();

        // Get ip geo information
        try {
          $client = json_decode(file_get_contents("http://ipinfo.io/{$ip}"), true);
        } catch(Exception $e) {
          $client = [
            'country' => 'Unknown',
            'city' => 'Unknown',
            'region' => 'Unknown',
            'countrycode' => 'Unknown'
          ];

          // Log exception error
          Log::error($e);
        }

        // Save new vote
        $this->vote->create([
          'id_answer' => $answer->id,
          'ip' => $ip,
          'date' => $params['date'],
          'country' => strtoupper($client['country']),
          'city' => strtoupper($client['city']),
          'region' => strtoupper($client['region']),
          'countrycode' => strtoupper($client['country'])
        ]);
      }
    }
  }
}