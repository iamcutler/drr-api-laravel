<?php

class ActivityController extends \BaseController {

  public function __construct(Activity $activity, User $user)
  {
    $this->activity = $activity;
    $this->user = $user;
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
    $input = Input::all();
    $rules = ['app' => 'required'];
    $validator = Validator::make($input, $rules);
    $result = ['result' => false];

    if(!$validator->fails())
    {
      $user = $this->user->Find_id_by_hash($input['user_hash']);
      $activity = $this->activity->find($id);

      if(!is_null($activity))
      {
        if($activity->app == $input['app'])
        {
          if($this->check_user_permissions($user, $activity))
          {
            $activity->delete();
            $result = ['result' => true];
          }
        }
      }
    }

    return Response::json($result);
	}

  protected function check_user_permissions(User $user, Activity $activity)
  {
    // Do a switch of the app type to find the right user permissions
    switch($activity->app)
    {
      // Events
      case 'events.wall':
        $event = $activity->event();
        $members = $event->member();
        foreach($members as $key => $member)
        {
          if($member->memberid == $user->id || $member->permission == 1)
          {
            return true;
          }
        }
        break;
      // Groups
      case 'groups.wall':
        $group = $activity->group();
        $members = $group->member();
        foreach($members as $key => $member)
        {
          if($member->memberid == $user->id || $member->permissions == 1)
          {
            return true;
          }
        }
        break;
    }

    return false;
  }
}