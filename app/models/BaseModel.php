<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class BaseModel extends Eloquent {
  public $errors;

  public function validate()
  {
    $v = Validator::make($this->attributes, static::$rules);
    if($v->passes()) return true;

    $this->errors = $v->messages();

    return false;
  }

  public function errors()
  {
    return $this->errors;
  }
} 