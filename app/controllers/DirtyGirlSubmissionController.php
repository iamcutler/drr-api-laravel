<?php

class DirtyGirlSubmissionController extends \BaseController {

  public function __construct(Submission $submission, User $user)
  {
    $this->submission = $submission;
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
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store()
  {
    $params = Input::all();
    $user = $this->user->find_id_by_hash($params['user_hash']);
    $rules = [
      'first_name' => 'required',
      'last_name' => 'required',
      'email' => 'required|email',
      'phone' => 'required',
      'where_from' => 'required',
      'done_pinup' => 'required'
    ];
    $validator = Validator::make($params, $rules);
    $result = [];
    // Set default status result
    $result['result'] = false;

    // Check if user was found and parameter validations passed
    if($validator->passes() && !is_null($user))
    {
      try
      {
        $save = $this->submission->create([
          'first_name' => $params['first_name'],
          'last_name' => $params['last_name'],
          'email_address' => $params['email'],
          'phone' => $params['phone'],
          'age' => $params['age'],
          'where_are_you_from' => $params['where_from'],
          'previous_pinup' => $params['done_pinup'],
          'favorite_car' => $params['favorite_car'],
          'favorite_pinup' => $params['favorite_pinup'],
          'special_talents' => $params['special_talents'],
          'why_you' => $params['why_you'],
          'biggest_turn_on' => $params['turn_on'],
          'biggest_turn_off' => $params['turn_off'],
          'favorite_quote' => $params['favorite_quote'],
          'image_1' => '',
          'image_2' => '',
          'image_3' => '',
          'image_4' => '',
          'image_5' => '',
          'archive' => 0,
          'created_by' => $user->id,
          'created_at' => date('Y-m-d H:i:s')
        ]);

        // Check for successful save of the resource
        if($save)
        {
          // Set result status to true
          $result['result'] = true;
          // Display saved resource
          $result['submission'] = $this->submission->find($save->id);
        }
      }
      catch(Exception $exception) {
        Log::error($exception);
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
   * Update the specified resource in storage.
   *
   * @param  int  $id
   * @return Response
   */
  public function update($id)
  {
    //
  }
}
