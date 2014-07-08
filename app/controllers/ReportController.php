<?php

class ReportController extends \BaseController {

  public function __construct(Report $report, User $user)
  {
    $this->report = $report;
    $this->user = $user;
  }

  public function bug()
  {
    if(Input::has('category') && Input::has('message') && Input::has('bug_type') && Input::has('user_hash'))
    {
      $params = Input::all();

      $validator = $this->report->validate($params);

      if($validator)
      {
        // Find user by passed in hash
        $user = $this->user->Find_id_by_hash($params['user_hash']);

        // Save model
        $this->report->create([
          'user_id' => $user->id,
          'category' => $params['category'],
          'message' => $params['message'],
          'ip' => Request::getClientIp(),
          'client' => $_SERVER['HTTP_USER_AGENT'],
          'report_type' => $params['bug_type']
        ]);

        // Data to pass to mailer
        $mailer = [
          'user_id' => $user->id,
          'user_name' => $user->name,
          'category' => $params['category'],
          'comments' => $params['message'],
          'report_type' => $params['bug_type'],
          'client' => $_SERVER['HTTP_USER_AGENT'],
          'ip' => Request::getClientIp()
        ];

        // Send notification email to us
        Mail::send('emails.report.bug', $mailer, function($message)
        {
          $message->to('allan@211enterprises.com', 'Allan Cutler')->subject('A mobile app bug has been reported');
        });

        return Response::json(['status' => true, 'message' => '']);
      }
      else
      {
        return Response::json(['status' => false, 'message' => 'Was not able to save new report']);
      }
    }
    else
    {
      return Response::json('Missing required parammeters.', 401);
    }
  }
}