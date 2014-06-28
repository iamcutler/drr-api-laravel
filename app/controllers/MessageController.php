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
            /** TODO: Use transformers for message output */
            // User formatting
            $result[$key]['user']['id'] = $recep->id;
            $result[$key]['user']['name'] = $recep->name;
            $result[$key]['user']['username'] = $recep->username;
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

    if($validator->passes())
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
          /** TODO: Use message transformer */
          // Message formatting
          $result['messages'][$key]['id'] = $value->id;
          $result['messages'][$key]['subject'] = $value->subject;
          $result['messages'][$key]['message'] = $value->body;
          $result['messages'][$key]['from'] = $value->recepient->msg_from;
          $result['messages'][$key]['to'] = $value->recepient->to;
          $result['messages'][$key]['bcc'] = $value->recepient->bcc;
          $result['messages'][$key]['is_read'] = $value->recepient->is_read;
          $result['messages'][$key]['posted_on'] = $value->posted_on;

          // From
          $result['messages'][$key]['user_from']['id'] = $value->recepient->userFrom->id;
          $result['messages'][$key]['user_from']['name'] = $value->recepient->userFrom->name;
          $result['messages'][$key]['user_from']['username'] = $value->recepient->userFrom->username;
          $result['messages'][$key]['user_from']['thumbnail'] = $value->recepient->userFrom->comm_user->thumb;
          $result['messages'][$key]['user_from']['avatar'] = $value->recepient->userFrom->comm_user->avatar;
          $result['messages'][$key]['user_from']['slug'] = $value->recepient->userFrom->comm_user->alias;

          // To
          $result['messages'][$key]['user_to']['id'] = $value->recepient->userTo->id;
          $result['messages'][$key]['user_to']['name'] = $value->recepient->userTo->name;
          $result['messages'][$key]['user_to']['username'] = $value->recepient->userTo->username;
          $result['messages'][$key]['user_to']['thumbnail'] = $value->recepient->userTo->comm_user->thumb;
          $result['messages'][$key]['user_to']['avatar'] = $value->recepient->userTo->comm_user->avatar;
          $result['messages'][$key]['user_to']['slug'] = $value->recepient->userTo->comm_user->alias;
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