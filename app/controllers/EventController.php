<?php

class EventController extends \BaseController {

  public function __construct(Events $event, EventMember $member, User $user)
  {
    $this->event = $event;
    $this->event_member = $member;
    $this->user = $user;
  }
  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {
    $results = $this->event->Upcoming()->get();

    return Response::json($results);
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
    //
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function show($id)
  {
    $event = $this->event->find($id);
    $results = [];

    // Format output
    $results['event']['category'] = $event->category()->name;
    $results['event']['type'] = $event['type'];
    $results['event']['title'] = $event['title'];
    $results['event']['location'] = $event['location'];
    $results['event']['summary'] = $event['summary'];
    $results['event']['description'] = $event['description'];
    $results['event']['creator'] = $event['creator'];
    $results['event']['start_date'] = $event['startdate'];
    $results['event']['end_date'] = $event['enddate'];
    $results['event']['permission'] = $event['permission'];
    $results['event']['avatar'] = $event['avatar'];
    $results['event']['thumbnail'] = $event['thumb'];

    $results['event']['invite_counts']['invite'] = $event['invitedcount'];
    $results['event']['invite_counts']['confirmed'] = $event['confirmedcount'];
    $results['event']['invite_counts']['declined'] = $event['declinedcount'];
    $results['event']['invite_counts']['maybe'] = $event['maybecount'];

    $results['event']['ticket'] = $event['ticket'];
    $results['event']['allowinvite'] = $event['allowinvite'];
    $results['event']['hits'] = $event['hits'];
    $results['event']['published'] = $event['published'];
    $results['event']['latitude'] = $event['latitude'];
    $results['event']['longitude'] = $event['longitude'];
    $results['event']['offset'] = $event['offset'];
    $results['event']['allday'] = $event['allday'];
    $results['event']['repeat'] = $event['repeat'];
    $results['event']['repeatend'] = $event['repeatend'];
    $results['event']['created'] = $event['created'];

    // Event likes / dislikes
    $results['event']['stats']['likes'] = $event->likes()->count();
    $results['event']['stats']['dislikes'] = $event->dislikes()->count();

    // Event members
    $results['event']['members'] = [];
    foreach($event->member() as $key => $val)
    {
      $user = $val->user();
      $comm_user = $user->comm_user()->first();

      $results['event']['members'][$key]['id'] = $user->id;
      $results['event']['members'][$key]['name'] = $user->name;
      $results['event']['members'][$key]['avatar'] = $comm_user->avatar;
      $results['event']['members'][$key]['thumbnail'] = $comm_user->thumb;
      $results['event']['members'][$key]['slug'] = $comm_user->alias;
      $results['event']['members'][$key]['status'] = $val['status'];
      $results['event']['members'][$key]['permission'] = $val['permission'];
      $results['event']['members'][$key]['invited_by'] = $val['invited_by'];
      $results['event']['members'][$key]['approval'] = $val['approval'];
      $results['event']['members'][$key]['created'] = $val['created'];
    }

    // Event activity - Initial call will paginate 10 records.
    $results['activity'] = $this->get_feed_activity($event, 0);

    return Response::json($results);
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

  public function activity()
  {
    $input = Input::all();
    $rules = [
      'id' => 'required|integer',
      'offset' => 'required|integer'
    ];
    $validator = Validator::make($input, $rules);

    if(!$validator->fails())
    {
      $event = $this->event->find($input['id']);
      $result['activity'] = $this->get_feed_activity($event, $input['offset']);

      return Response::json($result);
    }
  }

  // Paginate event activity
  protected function get_feed_activity(Events $event, $offset)
  {
    // Event activity - Initial call will paginate 10 records.
    $results = [];
    foreach($event->activity($offset, 10) as $key => $val)
    {
      $comm_user = $val->actor_comm();

      $results[$key]['id'] = $val['id'];
      $results[$key]['user']['name'] = $val->actor()->name;
      $results[$key]['user']['avatar'] = $comm_user->avatar;
      $results[$key]['user']['thumbnail'] = $comm_user->thumb;

      $results[$key]['comments'] = [];
      foreach($val->wall() as $k => $value)
      {
        $user = $value->user();
        $comm_user = $user->comm_user()->first();

        $results[$key]['comments'][$k]['user']['name'] = $user->name;
        $results[$key]['comments'][$k]['user']['avatar'] = $comm_user->avatar;
        $results[$key]['comments'][$k]['user']['thumbnail'] = $comm_user->thumb;
        $results[$key]['comments'][$k]['user']['slug'] = $comm_user->alias;

        $results[$key]['comments'][$k]['comment'] = $value['comment'];
        $results[$key]['comments'][$k]['date'] = $value['date'];
      }

      $results[$key]['user']['slug'] = $comm_user->alias;
      $results[$key]['title'] = $val['title'];
      $results[$key]['comment_id'] = $val['comment_id'];
      $results[$key]['comment_type'] = $val['comment_type'];
      $results[$key]['like_id'] = $val['like_id'];
      $results[$key]['like_type'] = $val['like_type'];
      $results[$key]['created'] = $val['created'];

      // Get comment stats
      $results[$key]['stats'] = [];
      $results[$key]['stats']['likes'] = [];
      foreach($val->likes()->where('element', '=', 'events.wall')->get() as $i => $v)
      {
        // Likes
        $user = $v->user_like();

        if(!is_null($user))
        {
          $comm_user = $user->comm_user()->first();

          $results[$key]['stats']['likes']['user']['name'] = $user->name;
          $results[$key]['stats']['likes']['user']['avatar'] = $comm_user->avatar;
          $results[$key]['stats']['likes']['user']['thumbnail'] = $comm_user->thumb;
          $results[$key]['stats']['likes']['user']['slug'] = $comm_user->alias;
        }
      }
    }

    return $results;
  }
}