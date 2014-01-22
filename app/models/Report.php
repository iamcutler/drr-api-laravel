<?php

class Report extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "mobile_reports";

  protected $fillable = ['user_id', 'category', 'message', 'ip', 'client', 'report_type'];

  /**
   * Validations
   */
  protected static $rules = [
    'category' => 'required',
    'message' => 'required',
    'bug_type' => 'required'
  ];

  public static function validate($input)
  {
    return Validator::make($input, static::$rules);
  }
}