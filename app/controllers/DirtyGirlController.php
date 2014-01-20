<?php

class DirtyGirlController extends \BaseController {
  public function get_dirty_girls()
  {
    $results = DirtyGirl::Get_all_girls();

    $girls = [];
    foreach($results as $key => $val)
    {
      $girls[$key]['id'] = $val->id;
      $girls[$key]['campaign_month'] = $val->campaign_month;
      $girls[$key]['campaign_year'] = $val->campaign_year;
      $girls[$key]['name'] = $val->dirty_girl_name;
      $girls[$key]['biography'] = $val->dirty_girl_bio;
      $girls[$key]['type'] = $val->dirty_type;
      $girls[$key]['order'] = $val->ordering;
      $girls[$key]['media']['thumbnail'] = Config::get('constant.cdn_domain') . "/administrator/components/com_dirtygirlpages/uploads/" .$val->thumbnail_image;
    }

    return Response::json($girls);
  }

  public function current_voting()
  {
    $current_poll = VotingPoll::Get_current();

    $poll = [];
    if($current_poll->count() == 1)
    {
      $p = $current_poll->first();
      $poll['poll']['id'] = $p->id;
      $poll['poll']['name'] = $p->name;
      $poll['poll']['question'] = $p->question;
      $poll['poll']['date_start'] = $p->date_start;
      $poll['poll']['date_end'] = $p->date_end;
      $poll['poll']['number_answers'] = $p->number_answers;
      $poll['poll']['voting_period'] = $p->voting_period;
      $poll['poll']['created'] = $p->created;

      // Get poll answers
      $poll['answers'] = [];
      $answers = VotingPoll::Get_answers($p->id);
      if($answers->count() > 0)
      {
        foreach($answers->get() as $key => $val)
        {
          $poll['answers'][$key]['id'] = $val->id;
          $poll['answers'][$key]['id_poll'] = $val->id_poll;
          $poll['answers'][$key]['name'] = $val->name;
          $poll['answers'][$key]['thumbnail'] = $val->thumbnail;
          $poll['answers'][$key]['username'] = $val->username;
          $poll['answers'][$key]['caption'] = $val->caption;
          $poll['answers'][$key]['created'] = $val->created;
          //Current vote number for answers
          $poll['answers'][$key]['votes'] = VotingVote::Answer_vote_count($val->id);
        }
      }
    }

    return Response::json($poll);
  }
}