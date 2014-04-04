<?php

class UserController extends \BaseController {

  public function __construct(User $user, UserRepositoryInterface $userInt)
  {
    $this->user = $user;
    $this->user_int = $userInt;
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
   * Store a newly created resource in storage.
   *
   * @param string $name
   * @param string $username
   * @param string $email
   * @param string $password
   * @return Response
   */
  public function store()
  {
    // Registration of a new user
    $params = Input::all();
    $rules = [
      'name' => 'required',
      'username' => 'required|unique:users',
      'email' => 'required|email|unique:users',
      'password' => 'required|min:6'
    ];
    $validator = Validator::make($params, $rules);
    // Set default status to false
    $result['status'] = false;

    if($validator->passes())
    {
      $new_user = [
        'name' => $params['name'],
        'username' => $params['username'],
        'email' => $params['email'],
        'password' => $params['password']
      ];

      // Call create on user class and return array instance
      $save = $this->user_int->create($new_user);

      if(!empty($save))
      {
        $result['status'] = true;
        $result['name'] = $save['name'];
        $result['username'] = $save['username'];
        $result['slug'] = $save['id'] .':'. str_replace(' ', '-', $save['name']);
        $result['hash'] = $save['user_hash'];

        // Send confirm / welcome email
        Mail::send('emails.auth.welcome', $new_user, function($message) use ($save) {
          $message->to($save['email'], $save['name'])->subject('Welcome to Dirty Rotten Rides');
        });
      }
      else
      {
        $result['code'] = '1001';
        $result['message'] = ['Something went wrong during registration. Please report this bug. Sorry for the inconvenience'];
      }
    }
    else {
      $result['code'] = '1002';

      // Format error messages
      foreach($validator->errors()->toArray() as $key => $value)
      {
        $result['message'][$key] = $value[0];
      }
    }

    return Response::json($result);
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