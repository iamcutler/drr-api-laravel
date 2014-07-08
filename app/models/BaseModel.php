<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class BaseModel extends Eloquent {
  public $errors;

  public function validate($params = [])
  {
    $v = Validator::make((empty($params)) ? $this->attributes : $params, static::$rules);
    if($v->passes()) return true;

    $this->errors = $v->messages();

    return false;
  }

  public function errors()
  {
    return $this->errors;
  }
} 