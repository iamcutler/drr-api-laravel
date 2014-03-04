<?php

class AccountController extends \BaseController {

  public function __construct(User $user, UserField $field)
  {
    $this->user = $user;
    $this->field = $field;
  }

  public function profile_settings()
  {
    $user = $this->user->find_id_by_hash(Input::get('user_hash'));
    $fields = $this->field->all();
    $result = [];

    foreach($fields as $key => $field)
    {
      $result[$key]['id'] = $field->id;
      $result[$key]['type'] = $field->type;
      $result[$key]['name'] = $field->name;
      $result[$key]['tip'] = $field->tips;
      $result[$key]['min'] = (int) $field->min;
      $result[$key]['max'] = (int) $field->max;
      $result[$key]['required'] = (int) $field->required;
      $result[$key]['options'] = explode("\n", $field->options);
      $result[$key]['fieldcode'] = $field->fieldcode;
      $result[$key]['params'] = json_decode($field->params);

      // Get field value
      $val = $field->value($user->id)->first();

      $result[$key]['value']['value'] = (is_null($val)) ? '' : $val->value;
      $result[$key]['value']['access'] = (is_null($val)) ? 0 : (int) $val->access;
    }

    return Response::json($result);
  }
}