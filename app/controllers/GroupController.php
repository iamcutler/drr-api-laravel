<?php

use \DRR\Transformers\GroupTransformer;
use \DRR\Transformers\GroupMemberTransformer;
use \DRR\Transformers\GroupAnnouncementTransformer;
use \DRR\Transformers\GroupDiscussionTransformer;
use \DRR\Transformers\GroupEventTransformer;
use \DRR\Transformers\UserLikesTransformer;

class GroupController extends \BaseController {

  protected $groupTransformer;
  protected $groupMemberTransformer;
  protected $groupAccouncementTransformer;
  protected $groupDiscussionTransformer;
  protected $groupEventTransformer;
  protected $userStatsTransformer;

  public function __construct(Group $group, User $user, CommUser $comm_user, GroupMember $member,
                              GroupTransformer $groupTransformer, GroupMemberTransformer $groupMemberTransformer, GroupAnnouncementTransformer $groupAnnouncementTransformer,
                              GroupDiscussionTransformer $groupDiscussionTransformer, GroupEventTransformer $groupEventTransformer, UserLikesTransformer $userStatsTransformer)
  {
    $this->group = $group;
    $this->user = $user;
    $this->comm_user = $comm_user;
    $this->group_member = $member;
    $this->groupTransformer = $groupTransformer;
    $this->groupMemberTransformer = $groupMemberTransformer;
    $this->groupAccouncementTransformer = $groupAnnouncementTransformer;
    $this->groupDiscussionTransformer = $groupDiscussionTransformer;
    $this->groupEventTransformer = $groupEventTransformer;
    $this->userStatsTransformer = $userStatsTransformer;
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
    $requester = $this->user->find_id_by_hash(Input::get('user_hash'));
    $group = $this->group->eagerGroupData()->find($id);
    $results = [];

    if(!is_null($group))
    {
      // Format group payload
      $results = $this->groupTransformer->transform($group);

      // Group counts
      $results['counts']['discussions'] = (int) $group->discussion->count();
      $results['counts']['members'] = (int) $group->member->count();

      // Group stats
      $results['stats'] = $this->userStatsTransformer->transform($group->likes->toArray()[0], $requester->toArray());

      // Group members
      $results['members'] = $this->groupMemberTransformer->transformCollection($group->member->toArray());

      // Group announcements
      $results['announcements'] = $this->groupAccouncementTransformer->transformCollection($group->bulletin->toArray());

      // Group discussions
      $results['discussions'] = $this->groupDiscussionTransformer->transformCollection($group->discussion->toArray());

      // Group events
      $results['events'] = $this->groupEventTransformer->transformCollection($group->events->toArray());

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

        $result[$key]['id'] = (int) $group->id;
        $result[$key]['name'] = $group->name;
        $result[$key]['description'] = $group->description;
        $result[$key]['avatar'] = ($group->avatar!= '') ? "/{$group->avatar}" : '';
        $result[$key]['thumbnail'] = ($group->thumb != '') ? "/{$group->thumb}" : '';
        $result[$key]['permissions'] = $val->permissions;
      }
    }
    else {
      $result = ['status' => false, 'message' => 'User not found'];
    }

    return Response::json($result);
  }
}