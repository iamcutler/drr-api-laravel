<?php

class AuthController extends \BaseController {

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

  protected function login()
  {
    $username = Input::get('username');
    $password = Input::get('password');

    $user = User::where('username', '=', $username)->take(1);

    if($user->count() == 1)
    {
      $user = $user->get()[0];

      if(UserController::validate_user_password($password, $user['password']))
      {
        // Get relational comm_user data
        $comm_user = User::Find_comm_user($user->id);

        $result = ['status' => true,
          'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'slug' => $comm_user->alias,
            'thumbnail' => $comm_user->thumb,
            'hash' => $user->user_hash
          ]
        ];
      }
        else
      {
        $result = ['status' => false];
      }
    }
    else
    {
      $result = ['status' => false];
    }

    return Response::json($result);
  }
}