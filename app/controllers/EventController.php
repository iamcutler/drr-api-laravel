<?php

use \DRR\Transformers\EventTransformer;
use \DRR\Transformers\EventMemberTransformer;
use \DRR\Transformers\UserLikesTransformer;

class EventController extends \BaseController {

  protected $eventTransformer;
  protected $eventMemberTransformer;
  protected $userStatsTransformer;

  public function __construct(User $user, Events $event, EventMember $member, CommUser $commUser, EventCategory $category,
                              Activity $activity, EventTransformer $eventTransformer, EventMemberTransformer $eventMemberTransformer,
                              UserLikesTransformer $userStatsTransformer)
  {
    $this->event = $event;
    $this->event_member = $member;
    $this->user = $user;
    $this->comm_user = $commUser;
    $this->event_category = $category;
    $this->activity = $activity;
    $this->eventTransformer = $eventTransformer;
    $this->eventMemberTransformer = $eventMemberTransformer;
    $this->userStatsTransformer = $userStatsTransformer;
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
    $params = Input::all();
    $user = $this->user->find_id_by_hash($params['user_hash']);
    $rules = [
      'app' => 'required',
      'catid' => 'required|integer',
      'title' => 'required',
      'location' => 'required',
      'startdate' => 'required',
      'enddate' => 'required',
      'repeat' => 'required'
    ];
    $validator = Validator::make($params, $rules);

    if($validator->passes())
    {
      switch($params['app']) {
        case 'event-status':
          // Create event transaction
          /*try {
            DB::transaction(function() use ($user, $params) {
              $event_save = $this->event->create([
                'parent' => '',
                'catid' => $params['catid'],
                'contentid' => 0,
                'type' => 'profile',
                'title' => $params['title'],
                'location' => $params['location'],
                'summary' => '',
                'description' => $params['description'],
                'creator' => $user->id,
                'startdate' => $params['startdate'],
                'enddate' => $params['enddate'],
                'permission' => 0,
                'created' => date("Y-m-d H:s:i"),
                'allday' => $params['allday'],
                'repeat' => $params['repeat'],
                'repeatend' => $params['repeatend']
              ]);

              $this->event_member->create([
                'eventid' => $event_save->id,
                'memberid' => $user->id,
                'status' => 1,
                'permission' => 1,
                'invited_by' => 0,
                'approval' => 0,
                'created' => date("Y-m-d H:s:i")
              ]);

              $activity = $this->activity->create([
                'actor' => $user->id,
                'target' => 0,
                'title' => '',
                'content' => '',
                'app' => 'events',
                'cid' => $event_save->id,
                'eventid' => $event_save->id,
                'created' => date("Y-m-d H:s:i"),
                'access' => 0,
                'params' => [
                  'action' => "events.create",
                  'event_url' => "index.php?option=com_community&view=events&task=viewevent&eventid={$event_save->id}",
                  'event_category_url' => "index.php?option=com_community&view=events&task=display&categoryid={$params['catid']}"
                ],
                'archived' => 0,
                'location' => $params['location'],
                'comment_id' => $event_save->id,
                'comment_type' => 'groups.event',
                'like_id' => $event_save->id,
                'like_type' => 'group.event',
                'actors' => ''
              ]);
            });
          }
          catch($e) {

          }*/
          break;
      };
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
    $requester = $this->user->find_id_by_hash(Input::get('user_hash'));
    $event = $this->event->eagerEventData()->find($id);
    $results = [];

    // Format output
    $results['event'] = $this->eventTransformer->transform($event->toArray());

    // Event likes / dislikes
    $results['event']['stats'] = $this->userStatsTransformer->transform($event->likes->toArray(), $requester->toArray());

    // Event members
    $results['event']['members'] = $this->eventMemberTransformer->transformCollection($event->member->toArray());

    // Event activity - Initial call will paginate 10 records.
    //$results['activity'] = $this->get_feed_activity($event);
    $results['activity'] = [];

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

  /**
   * Get events by user slug
   */
  public function user_events($slug)
  {
    $user = $this->comm_user->Find_by_slug($slug);
    $result = [];

    if(!is_null($user))
    {
      foreach($this->event_member->Find_by_user_id($user->userid) as $key => $val)
      {
        $event = $val->event()->first();

        if(!is_null($event))
        {
          $result[$key]['id'] = $event->id;
          $result[$key]['title'] = $event->title;
          $result[$key]['avatar'] = $event->avatar;
          $result[$key]['thumbnail'] = $event->thumb;
          $result[$key]['start_date'] = $event->startdate;
          $result[$key]['end_date'] = $event->enddate;
          $result[$key]['status'] = $val->status;
          $result[$key]['permission'] = $val->permission;
        }
      }
    }

    return Response::json($result);
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
      $result = $this->get_feed_activity($event, $input['offset']);

      return Response::json($result);
    }
  }

  // Paginate event activity
  protected function get_feed_activity(Events $event, $offset = 0)
  {
    // Event activity - Initial call will paginate 10 records.
    $results = [];
    foreach($event->activity($offset, 10) as $key => $val)
    {
      $comm_user = $val->actor_comm();
      $actor = $val->actor();

      $results[$key]['id'] = $val['id'];
      $results[$key]['user']['id'] = $actor->id;
      $results[$key]['user']['name'] = $actor->name;
      $results[$key]['user']['avatar'] = $comm_user->avatar;
      $results[$key]['user']['thumbnail'] = $comm_user->thumb;
      $results[$key]['user']['slug'] = $comm_user->alias;

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

      $results[$key]['app'] = $val['app'];
      $results[$key]['title'] = $val['title'];
      $results[$key]['comment_id'] = $val['comment_id'];
      $results[$key]['comment_type'] = $val['comment_type'];
      $results[$key]['like_id'] = $val['like_id'];
      $results[$key]['like_type'] = $val['like_type'];
      $results[$key]['created'] = $val['created'];

      // Get comment stats
      $results[$key]['stats'] = [];
      $results[$key]['stats']['likes'] = $val->event_likes()->count();

      foreach($val->event_likes()->get() as $i => $v)
      {
        // Likes
        $user = $v->user_like();

        if(!is_null($user))
        {
          $comm_user = $user->comm_user()->first();

          /*$results[$key]['stats']['likes']['user']['name'] = $user->name;
          $results[$key]['stats']['likes']['user']['avatar'] = $comm_user->avatar;
          $results[$key]['stats']['likes']['user']['thumbnail'] = $comm_user->thumb;
          $results[$key]['stats']['likes']['user']['slug'] = $comm_user->alias;*/
        }
      }
    }

    return $results;
  }

  public function categories()
  {
    $categories = $this->event_category->orderBy('name', 'ASC')->get();

    if(is_null($categories))
    {
      $categories = [];
    }

    return Response::json($categories);
  }
}