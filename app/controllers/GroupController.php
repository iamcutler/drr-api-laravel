<?php

class GroupController extends \BaseController {

  public function __construct(Group $group, User $user, CommUser $comm_user, GroupMember $member)
  {
    $this->group = $group;
    $this->user = $user;
    $this->comm_user = $comm_user;
    $this->group_member = $member;
  }

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {
    //
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
    $group = $this->group->find($id);
    $results = [];

    // Format group payload
    $results['id'] = $group->id;
    $results['ownerid'] = $group->ownerid;
    $results['category'] = $group->category()->name;
    $results['name'] = $group->name;
    $results['description'] = $group->description;
    $results['email'] = $group->email;
    $results['website'] = $group->website;
    $results['approvals'] = $group->approvals;
    $results['avatar'] = $group->avatar;
    $results['thumbnail'] = $group->thumb;
    $results['created'] = $group->created;

    // Group counts
    $results['counts']['discussions'] = $group->discusscount;
    $results['counts']['wall'] = $group->wallcount;
    $results['counts']['members'] = $group->membercount;
    $results['params'] = json_decode($group->params);

    // Group stats
    $results['stats'] = [];
    $results['stats']['likes'] = $group->likes()->count();
    $results['stats']['dislikes'] = $group->dislikes()->count();

    // Group members
    $results['members'] = [];
    foreach($group->member() as $key => $val)
    {
      $user = $val->user();
      $comm_user = $user->comm_user()->first();

      $results['members'][$key]['name'] = $user->name;
      $results['members'][$key]['avatar'] = $comm_user->avatar;
      $results['members'][$key]['thumbnail'] = $comm_user->thumb;
      $results['members'][$key]['slug'] = $comm_user->alias;
      $results['members'][$key]['approved'] = $val->approved;
      $results['members'][$key]['permissions'] = $val->permissions;
    }

    // Group announcements
    $results['announcements'] = [];
    foreach($group->bulletin()->take(30) as $k => $v)
    {
      $user = $v->user();
      $comm_user = $user->comm_user()->first();

      $results['announcements'][$k]['id'] = $v->id;
      $results['announcements'][$k]['title'] = $v->title;
      $results['announcements'][$k]['message'] = $v->message;
      $results['announcements'][$k]['params'] = json_decode($v->params);
      $results['announcements'][$k]['date'] = $v->date;

      $results['announcements'][$k]['user']['name'] = $user->name;
      $results['announcements'][$k]['user']['avatar'] = $comm_user->avatar;
      $results['announcements'][$k]['user']['thumbnail'] = $comm_user->thumb;
      $results['announcements'][$k]['user']['slug'] = $comm_user->alias;
    }

    // Group discussions
    $results['discussions'] = [];
    foreach($group->discussion() as $discuss_key => $discuss)
    {
      $user = $this->user->find($discuss->actor);
      $comm_user = $user->comm_user()->first();

      $results['discussions'][$discuss_key]['id'] = $discuss->id;
      $results['discussions'][$discuss_key]['content'] = $discuss->content;
      $results['discussions'][$discuss_key]['app'] = $discuss->app;
      $results['discussions'][$discuss_key]['cid'] = $discuss->cid;
      $results['discussions'][$discuss_key]['groupid'] = $discuss->groupid;
      $results['discussions'][$discuss_key]['group_access'] = $discuss->group_access;
      $results['discussions'][$discuss_key]['access'] = $discuss->access;
      $results['discussions'][$discuss_key]['params'] = json_decode($discuss->params);
      $results['discussions'][$discuss_key]['comment_id'] = $discuss->comment_id;
      $results['discussions'][$discuss_key]['comment_type'] = $discuss->comment_type;
      $results['discussions'][$discuss_key]['comment_count'] = $group->discussion_replys($discuss->cid)->count();
      $results['discussions'][$discuss_key]['like_id'] = $discuss->like_id;
      $results['discussions'][$discuss_key]['like_type'] = $discuss->like_type;
      $results['discussions'][$discuss_key]['created'] = $discuss->created;

      // Actor
      $results['discussions'][$discuss_key]['user']['name'] = $user->name;
      $results['discussions'][$discuss_key]['user']['avatar'] = $comm_user->avatar;
      $results['discussions'][$discuss_key]['user']['thumbnail'] = $comm_user->thumb;
      $results['discussions'][$discuss_key]['user']['slug'] = $comm_user->alias;
    }

    // Group events
    $results['events'] = $this->get_events($group->events());

    // Group Activity
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
   * Get groups by user slug
   */
  public function user_groups($slug)
  {
    $user = $this->comm_user->Find_by_slug($slug);
    $result = [];

    if(!is_null($user))
    {
      foreach($this->group_member->Find_by_user_id($user->userid) as $key => $val)
      {
        $group = $val->group();

        $result[$key]['id'] = $group->id;
        $result[$key]['name'] = $group->name;
        $result[$key]['description'] = $group->description;
        $result[$key]['avatar'] = $group->avatar;
        $result[$key]['thumbnail'] = $group->thumb;
        $result[$key]['permissions'] = $val->permissions;
      }
    }
    else
    {
      $result = ['status' => false, 'message' => 'User not found'];
    }

    return Response::json($result);
  }
}