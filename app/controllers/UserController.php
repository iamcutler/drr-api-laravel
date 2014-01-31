<?php

class UserController extends \BaseController {

  public function __construct(User $user)
  {
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

  }

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store()
  {
    // Registration of a new user
    $name = Input::get('name');
    $username = Input::get('username');
    $email = Input::get('email');
    $password = Input::get('password');
    $dob = Input::get('dob');

    // Check if username is taken
    if(!$this->user->Check_username_uniqueness($username)->count())
    {
      // Check if user email adrress has been used
      if(!$this->user->Check_email_uniqueness($email)->count())
      {
        $new_user = [
          'name' => $name,
          'username' => $username,
          'email' => $email,
          'password' => $this->user->generate_password($password),
          'usertype' => 2,
          'registerDate' => date("Y-m-d H:i:s"),
          'lastvisitDate' => date("Y-m-d H:i:s"),
          'params' => '',
          'user_hash' => $this->user->generate_hash($name, $username)
        ];

        // Save user data
        $save = $this->user->create($new_user);

        if($save)
        {
          // Send confirm / welcome email
          Email::send('emails.auth.welcome', $new_user, function($message) {
            $message->to($email, $name)->subject('Welcome to Dirty Rotten Rides');
          });

          $results = ['status' => true, 'name' => $name, 'username' => $username, 'slug' => $save->id .':'. str_replace(' ', '-', $name), 'hash' => $this->user->Find_hash_by_id($save->id)];
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
    $query = $this->user->Check_username_uniqueness($username)->count();

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