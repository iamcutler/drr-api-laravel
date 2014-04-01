<?php

class DirtyGirlSubmissionController extends \BaseController {

  public function __construct(DGRepositoryInterface $dirty_girls, Submission $submission, User $user)
  {
    $this->dirty_girls = $dirty_girls;
    $this->user = $user;
    $this->submission = $submission;
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
    // Set default status result
    $result['result'] = false;

    // Check if user was found and parameter validations passed
    if($validator->passes() && !is_null($user))
    {
      $result = $this->dirty_girls->newSubmission($user, $params);
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

  /**
   * Add image to existing resource in storage.
   *
   * $params int $id, int $img_number, $img
   * @return Response
   */
  public function uploadImage()
  {
    $params = Input::all();
    $user = $this->user->find_id_by_hash($params['user_hash']);
    $rules = [
      'id' => 'required',
      'num' => 'required',
      'file' => 'required|image|max:10000000'
    ];
    $file = Input::file('file');
    $validator = Validator::make($params, $rules);
    $result['result'] = false;

    if($validator->passes() && !is_null($user))
    {
      if(Input::hasFile('file'))
      {
        // Call dirty girls class to upload submission image
        $result = $this->dirty_girls->uploadSubmissionImage($file);

        // Check if file was uploaded
        if($result['result'])
        {
          $submission = $this->submission->find($params['id']);
          if(!is_null($submission))
          {
            $updatePath = $this->dirty_girls->updateSubmissionImagePath($submission, $params['num'], $result['file']['name']);
            if(!$updatePath)
            {
              $result['result'] = false;
            }
          }
        }
      }
    }

    return Response::json($result);
  }
}
