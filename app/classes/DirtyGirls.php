<?php

class DirtyGirls implements DGRepositoryInterface {

  public function __construct(Submission $submission, AWSRepositoryInterface $aws)
  {
    $this->submission = $submission;
    $this->AWS = $aws;
  }

  public function newSubmission(User $user, $params)
  {
    $result['result'] = false;

    try
    {
      // Save new submission
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
        $submission = $this->submission->find($save->id)->toArray();

        while(list($key, $val) = each($submission))
        {
          $result['submission'][$key] = $val;
        }
      }
    }
    catch(Exception $exception) {
      Log::error($exception);
    }

    return $result;
  }

  public function updateSubmissionImagePath(Submission $submission, $num, $path)
  {
    $result = false;

    switch($num)
    {
      case 1:
        $submission->image_1 = $path;
        break;
      case 2:
        $submission->image_2 = $path;
        break;
      case 3:
        $submission->image_3 = $path;
        break;
      case 4:
        $submission->image_4 = $path;
        break;
      case 5:
        $submission->image_5 = $path;
        break;
    }

    // Save new image path for existing submission
    if($submission->save())
    {
      $result = true;
    }

    return $result;
  }

  public function uploadSubmissionImage($file)
  {
    $result['result'] = false;
    $file_options = [
      'image_path' => "administrator/components/com_dirtygirlsubmissions/uploads/submissions/",
      'thumb' => false
    ];

    // Less than 10MB files
    if($file->getSize() <= 10000000)
    {
      // Upload/Generate uploaded files to AWS S3
      $upload = $this->AWS->S3ImgUpload($file, $file_options);

      if($upload['result'])
      {
        $result = $upload;
      }
    }

    return $result;
  }
}