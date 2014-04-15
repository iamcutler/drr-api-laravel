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
    $group = $this->group->eagerGroupData()->find($id);
    $results = [];

    if(!is_null($group))
    {
      // Format group payload
      $results['id'] = (int) $group->id;
      $results['ownerid'] = (int) $group->ownerid;
      $results['category'] = $group->category->name;
      $results['name'] = $group->name;
      $results['description'] = $group->description;
      $results['email'] = $group->email;
      $results['website'] = $group->website;
      $results['approvals'] = $group->approvals;
      $results['avatar'] = $group->avatar;
      $results['thumbnail'] = $group->thumb;
      $results['created'] = $group->created;

      // Group counts
      $results['counts']['discussions'] = (int) $group->discussion->count();
      $results['counts']['members'] = (int) $group->member->count();
      $results['params'] = json_decode($group->params);

      // Group stats
      $results['stats'] = [];
      $results['stats']['likes'] = (int) $group->likes->count();

      // Group members
      $results['members'] = [];
      foreach($group->member as $key => $val)
      {
        $results['members'][$key]['name'] = $val->user->name;
        $results['members'][$key]['avatar'] = $val->user->comm_user->avatar;
        $results['members'][$key]['thumbnail'] = $val->user->comm_user->thumb;
        $results['members'][$key]['slug'] = $val->user->comm_user->alias;
        $results['members'][$key]['approved'] = $val->approved;
        $results['members'][$key]['permissions'] = $val->permissions;
      }

      // Group announcements
      $results['announcements'] = [];
      foreach($group->bulletin as $key => $val)
      {
        $results['announcements'][$key]['id'] = $val->id;
        $results['announcements'][$key]['title'] = $val->title;
        $results['announcements'][$key]['message'] = $val->message;
        $results['announcements'][$key]['params'] = json_decode($val->params);
        $results['announcements'][$key]['date'] = $val->date;

        $results['announcements'][$key]['user']['name'] = $val->user->name;
        $results['announcements'][$key]['user']['avatar'] = $val->user->comm_user->avatar;
        $results['announcements'][$key]['user']['thumbnail'] = $val->user->comm_user->thumb;
        $results['announcements'][$key]['user']['slug'] = $val->user->comm_user->alias;
      }

      // Group discussions
      $results['discussions'] = [];
      foreach($group->discussion as $key => $val)
      {
        $results['discussions'][$key]['id'] = $val->id;
        $results['discussions'][$key]['content'] = $val->content;
        $results['discussions'][$key]['app'] = $val->app;
        $results['discussions'][$key]['cid'] = $val->cid;
        $results['discussions'][$key]['groupid'] = $val->groupid;
        $results['discussions'][$key]['group_access'] = $val->group_access;
        $results['discussions'][$key]['access'] = $val->access;
        $results['discussions'][$key]['params'] = json_decode($val->params);
        $results['discussions'][$key]['comment_id'] = $val->comment_id;
        $results['discussions'][$key]['comment_type'] = $val->comment_type;
        //$results['discussions'][$key]['comment_count'] = $val->discussion_replys($val->cid)->count();
        $results['discussions'][$key]['like_id'] = $val->like_id;
        $results['discussions'][$key]['like_type'] = $val->like_type;
        $results['discussions'][$key]['created'] = $val->created;

        // Actor
        $results['discussions'][$key]['user']['name'] = $val->userActor->name;
        $results['discussions'][$key]['user']['avatar'] = $val->userActor->comm_user->avatar;
        $results['discussions'][$key]['user']['thumbnail'] = $val->userActor->comm_user->thumb;
        $results['discussions'][$key]['user']['slug'] = $val->userActor->comm_user->alias;
      }

      // Group events
      $results['events'] = [];
      foreach($group->events as $key => $val)
      {
        $results['events'][$key]['title'] = $val->title;
        $results['events'][$key]['location'] = $val->location;
        $results['events'][$key]['summary'] = $val->summary;
        $results['events'][$key]['description'] = $val->description;
        $results['events'][$key]['startdate'] = $val->startdate;
        $results['events'][$key]['enddate'] = $val->enddate;
        $results['events'][$key]['permissions'] = $val->permission;
        $results['events'][$key]['avatar'] = $val->avatar;
        $results['events'][$key]['thumb'] = $val->thumb;
      }

      // Group Activity
      $results['activity'] = [];
    }

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
    else {
      $result = ['status' => false, 'message' => 'User not found'];
    }

    return Response::json($result);
  }
}