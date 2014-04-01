<?php

class VoteController extends \BaseController {
  
  public function __construct(VotingPoll $poll, VotingVote $vote, VotingRepositoryInterface $voting)
  {
    $this->poll = $poll;
    $this->vote = $vote;
    $this->voting = $voting;
  }

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {
    $current_poll = $this->poll->Get_current();

    $poll = [];
    if($current_poll->count() == 1)
    {
      $p = $current_poll->first();
      $poll['poll']['id'] = (int) $p->id;
      $poll['poll']['name'] = $p->name;
      $poll['poll']['question'] = $p->question;
      $poll['poll']['date_start'] = $p->date_start;
      $poll['poll']['date_end'] = $p->date_end;
      $poll['poll']['number_answers'] = (int) $p->number_answers;
      $poll['poll']['voting_period'] = (int) $p->voting_period;
      $poll['poll']['created'] = $p->created;

      // Get poll answers
      $poll['answers'] = [];
      $answers = $this->poll->Get_answers($p->id);
      if($answers->count() > 0)
      {
        foreach($answers->get() as $key => $val)
        {
          $poll['answers'][$key]['id'] = (int) $val->id;
          $poll['answers'][$key]['id_poll'] = (int) $val->id_poll;
          $poll['answers'][$key]['name'] = $val->name;
          $poll['answers'][$key]['thumbnail'] = $val->thumbnail;
          $poll['answers'][$key]['slug'] = '';
          $poll['answers'][$key]['caption'] = $val->caption;
          $poll['answers'][$key]['created'] = $val->created;
          //Current vote number for answers
          $poll['answers'][$key]['votes'] = $this->vote->Answer_vote_count($val->id);
        }
      }
    }

    return Response::json($poll);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return Response
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store()
  {
    $params = Input::all();
    $validator = Validator::make($params, ['id_answer' => 'required|integer']);

    if($validator->passes())
    {
      $this->voting->castVote($params['id_answer']);
    }
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function edit($id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function update($id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function destroy($id)
  {
    //
  }

}