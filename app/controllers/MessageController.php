<?php

class MessageController extends \BaseController {

  public function __construct(Message $message, MessageRecepient $recepient, User $user)
  {
    $this->message = $message;
    $this->user = $user;
    $this->recepient = $recepient;
  }
  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {
    $user_hash = Input::get('user_hash');

    $user = $this->user->Find_id_by_hash($user_hash);
    $messages = $this->message->Find_all_by_id($user->id);
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
          $recep = $this->user->Find_friend_by_id($recepient)->first();

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
            $result[$key]['message']['posted_on'] = $val->posted_on;
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
    $params = Input::all();
    // Find users
    $user = $this->user->Find_id_by_hash($params['user_hash']);
    $rules = [
      'user' => 'required|integer',
      'recepient' => 'required|integer',
      'parent' => 'required|integer',
      'message' => 'required'
    ];
    $validator = Validator::make($params, $rules);
    $result = [];
    // Assign parent to param, or override if new thread
    $parent = $params['parent'];

    if(!$validator->fails())
    {
      if(!is_null($user))
      {
        // Get last message in the tread
        $last = $this->message->Find_latest($params['user'], $params['recepient'])->first();

        if(is_null($last))
        {
          if(!is_null(Input::get('subject')))
          {
            $subject = Input::get('subject');
          }
          else {
            $subject = "N/A";
          }
        }
        else {
          $subject = $last->subject;
        }

        // Save new message
        $save = $this->message->Create([
          'from' => $params['user'],
          'parent' => $parent,
          'deleted' => 0,
          'from_name' => $user->name,
          'subject' => $subject,
          'body' => $params['message'],
          'posted_on' => date("Y-m-d H:i:s")
        ]);

        if($save) {
          // If parent is 0, fetch new parent
          if($parent == 0)
          {
            // Update parent to new thread
            $save->parent = $save->id;
            $save->save();

            $parent = $save->id;
          }

          // Save relation
          $recepient = $this->recepient->Create([
            'msg_id' => $save->id,
            'msg_parent' => $parent,
            'msg_from' => $params['user'],
            'to' => $params['recepient']
          ]);

          //$save->recepient()->save($recepient);

          $result = ['status' => true];
        }
      }
    }
    else
    {
      $result = ['status' => false, 'message' => 'Missing parameters'];
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
    $user_hash = Input::get('user_hash');
    $user = $this->user->Find_id_by_hash($user_hash)->first();
    //$recepient = $this->user->Find_by_username($username);
    $messages = $this->message->Find_by_parent($id);
    $mess_recepient = $messages[0]->recepient();
    $result = [];

    // Check if user has permissions and finds recepient
    if(!is_null($user))
    {
      if($mess_recepient->msg_from = $user->id || $mess_recepient->to = $user->id)
      {
        $result['status'] = true;

        foreach($messages as $key => $value)
        {
          $msg_recepient = $value->recepient();
          $from = $msg_recepient->from();
          $from_comm = $from->comm_user()->first();
          $to = $msg_recepient->to();
          $to_comm = $to->comm_user()->first();

          // Message formatting
          $result['messages'][$key]['id'] = $value->id;
          $result['messages'][$key]['subject'] = $value->subject;
          $result['messages'][$key]['message'] = $value->body;
          $result['messages'][$key]['from'] = $msg_recepient->msg_from;
          $result['messages'][$key]['to'] = $msg_recepient->to;
          $result['messages'][$key]['bcc'] = $msg_recepient->bcc;
          $result['messages'][$key]['is_read'] = $msg_recepient->is_read;
          $result['messages'][$key]['posted_on'] = $value->posted_on;

          // From
          $result['messages'][$key]['user_from']['id'] = $from->id;
          $result['messages'][$key]['user_from']['name'] = $from->name;
          $result['messages'][$key]['user_from']['thumbnail'] = $from_comm->thumb;
          $result['messages'][$key]['user_from']['avatar'] = $from_comm->avatar;
          $result['messages'][$key]['user_from']['slug'] = $from_comm->alias;

          // To
          // From
          $result['messages'][$key]['user_to']['id'] = $to->id;
          $result['messages'][$key]['user_to']['name'] = $to->name;
          $result['messages'][$key]['user_to']['thumbnail'] = $to_comm->thumb;
          $result['messages'][$key]['user_to']['avatar'] = $to_comm->avatar;
          $result['messages'][$key]['user_to']['slug'] = $to_comm->alias;
        }
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