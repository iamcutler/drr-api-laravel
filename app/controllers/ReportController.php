<?php

class ReportController extends \BaseController {
  public function bug()
  {
    if(Input::has('category') && Input::has('message') && Input::has('bug_type') && Input::has('user_hash'))
    {
      $input = Input::all();

      $validator = Report::validate($input);

      if($validator->passes())
      {
        // Save model
        Report::create([
          'user_id' => User::Find_id_by_hash($input['user_hash'])->id,
          'category' => $input['category'],
          'message' => $input['message'],
          'ip' => Request::getClientIp(),
          'client' => $_SERVER['HTTP_USER_AGENT'],
          'report_type' => $input['bug_type']
        ]);

        // Data to pass to mailer
        $mailer = [
          'user_id' => $user->id,
          'user_name' => $user->name,
          'category' => $input['category'],
          'comments' => $input['message'],
          'report_type' => $input['bug_type'],
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