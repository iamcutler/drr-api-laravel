<?php

class MessageController extends \BaseController {

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {
    $user_hash = Input::get('user_hash');

    $user = User::Find_id_by_hash($user_hash);
    $messages = Message::Find_all_by_id($user->id);
    $result = [];

    // Check if user has permissions
    if(!is_null($user))
    {
      if(!is_null($messages))
      {
        // Format payload
        foreach($messages as $key => $val)
        {
          // Get other user
          if($user->id == $val->msg_from)
          {
            $recepient = $val->to;
          }
          else
          {
            $recepient = $val->msg_from;
          }

          // Get recepient data
          $recep = User::Find_friend_by_id($recepient)->first();

          // Make sure user is found
          if(!is_null($recep))
          {
            // User formatting
            $result[$key]['user']['id'] = $recep->id;
            $result[$key]['user']['name'] = $recep->name;
            $result[$key]['user']['avatar'] = $recep->avatar;
            $result[$key]['user']['thumbnail'] = $recep->thumbnail;
            $result[$key]['user']['slug'] = $recep->slug;

            // Message formatting
            $result[$key]['message']['id'] = $val->id;
            $result[$key]['message']['from'] = $val->msg_from;
            $result[$key]['message']['to'] = $val->to;
            $result[$key]['message']['parent'] = $val->parent;
            $result[$key]['message']['subject'] = $val->subject;
            $result[$key]['message']['message'] = $val->body;
            $result[$key]['message']['bcc'] = $val->bcc;
            $result[$key]['message']['read'] = $val->is_read;
            $result[$key]['message']['posted_on'] = strtotime($val->posted_on);
          }
          else
          {
            $result = ['status' => false, 'message' => 'Recepient not found'];
          }
        }
      }
    }
    else
    {
      $result = ['status' => false, 'message' => 'You don\'t have the correct permissions'];
    }

    return Response::json($result);
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
  public function show($username)
  {
    $user_hash = Input::get('user_hash');
    $user = User::Find_id_by_hash($user_hash)->first();
    $recepient = User::Find_by_username($username);

    $thread = Message::Find_thread_by_id($user->id, $recepient->id);
    $result = [];

    // Check if user has permissions and finds recepient
    if(!is_null($user) && !is_null($recepient))
    {
      // Fetch user
      $result['user']['id'] = $recepient->id;
      $result['user']['name'] = $recepient->name;
      $result['user']['avatar'] = $recepient->avatar;
      $result['user']['thumbnail'] = $recepient->thumb;
      $result['user']['slug'] = $recepient->alias;

      // Format messages collection
      foreach($thread as $key => $val)
      {
        // Message formatting
        $result['messages'][$key]['id'] = $val->id;
        $result['messages'][$key]['subject'] = $val->subject;
        $result['messages'][$key]['message'] = $val->body;
        $result['messages'][$key]['from'] = $val->msg_from;
        $result['messages'][$key]['to'] = $val->to;
        $result['messages'][$key]['bcc'] = $val->bcc;
        $result['messages'][$key]['is_read'] = $val->is_read;
        $result['messages'][$key]['posted_on'] = strtotime($val->posted_on);
      }
    }
    else
    {
      $result = ['status' => false, 'message' => 'No message thread found'];
    }

    return Response::json($result);
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

}