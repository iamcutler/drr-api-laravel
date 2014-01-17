<?php

class UserController extends \BaseController {

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
    $name = Input::get('name');
    $username = Input::get('username');
    $email = Input::get('email');
    $password = Input::get('password');
    $dob = Input::get('dob');

    // Check if username is taken
    if(!User::Check_username_uniqueness($username)->count())
    {
      // Check if user email adrress has been used
      if(!User::Check_email_uniqueness($email)->count())
      {
        // Save user data
        $save = User::create([
          'name' => $name,
          'username' => $username,
          'email' => $email,
          'password' => User::generate_password($password),
          'usertype' => 2,
          'registerDate' => date("Y-m-d H:i:s"),
          'lastvisitDate' => date("Y-m-d H:i:s"),
          'params' => '',
          'user_hash' => User::generate_hash($name, $username)
        ]);

        if($save)
        {
          $results = ['status' => true, 'name' => $name, 'username' => $username, 'slug' => $save->id .':'. $name, 'user_hash' => User::Find_hash_by_id($save->id)];
        }
        else
        {
          $results = ['status' => false, 'code' => '1001', 'message' => 'something went wrong during registration. Please report this bug. Sorry for the inconvenience'];
        }
      }
      else
      {
        $results = ['status' => false, 'code' => '101', 'message' => 'email address has already been used'];
      }
    }
    else
    {
      $results = ['status' => false, 'code' => '100', 'message' => 'username is already taken'];
    }

    return Response::json($results);
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

  // Check if username is unique
  protected function check_username_uniqueness($username)
  {
    $query = User::Check_username_uniqueness($username)->count();

    if(!$query)
    {
      $result = ['unique' => true];
    }
    else
    {
      $result = ['unique' => false];
    }

    return Response::json($result);
  }
}