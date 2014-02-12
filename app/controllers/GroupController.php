<?php

class GroupController extends \BaseController {

  public function __construct(Group $group, CommUser $comm_user, GroupMember $member)
  {
    $this->group = $group;
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

    // Group Bulletin
    $results['bulletin'] = [];
    foreach($group->bulletin() as $k => $v)
    {
      $user = $v->user();
      $comm_user = $user->comm_user()->first();

      $results['bulletin'][$k]['title'] = $v->title;
      $results['bulletin'][$k]['message'] = $v->message;
      $results['bulletin'][$k]['params'] = json_decode($v->params);
      $results['bulletin'][$k]['date'] = $v->date;

      $results['bulletin'][$k]['user']['name'] = $user->name;
      $results['bulletin'][$k]['user']['avatar'] = $comm_user->avatar;
      $results['bulletin'][$k]['user']['thumbnail'] = $comm_user->thumb;
      $results['bulletin'][$k]['user']['slug'] = $comm_user->alias;
    }

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