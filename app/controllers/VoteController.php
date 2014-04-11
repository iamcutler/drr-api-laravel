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
    $current_poll = $this->poll->Get_current()->first();

    $poll = [];
    if(!is_null($current_poll))
    {
      $poll['poll']['id'] = (int) $current_poll->id;
      $poll['poll']['name'] = $current_poll->name;
      $poll['poll']['question'] = $current_poll->question;
      $poll['poll']['date_start'] = $current_poll->date_start;
      $poll['poll']['date_end'] = $current_poll->date_end;
      $poll['poll']['number_answers'] = (int) $current_poll->answer->count();
      $poll['poll']['voting_period'] = (int) $current_poll->voting_period;
      $poll['poll']['created'] = $current_poll->created;

      // Get poll answers
      $poll['answers'] = [];
      foreach($current_poll->answer as $key => $val)
      {
        $poll['answers'][$key]['id'] = (int) $val->id;
        $poll['answers'][$key]['id_poll'] = (int) $val->id_poll;
        $poll['answers'][$key]['name'] = $val->name;
        $poll['answers'][$key]['thumbnail'] = $val->thumbnail;
        $poll['answers'][$key]['caption'] = $val->caption;
        $poll['answers'][$key]['created'] = $val->created;

        // User
        $poll['answers'][$key]['user'] = [];
        if(!is_null($val->user))
        {
          $poll['answers'][$key]['user']['name'] = $val->user->user->name;
          $poll['answers'][$key]['user']['thumbnail'] = $val->user->thumb;
          $poll['answers'][$key]['user']['avatar'] = $val->user->avatar;
          $poll['answers'][$key]['user']['slug'] = $val->user->alias;
        }

        //Current vote number for answers
        $poll['answers'][$key]['votes'] = $val->votes->count();
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