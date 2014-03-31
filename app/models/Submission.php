<?php

class Submission extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "dirtygirlsubmissions_";
  protected $timestamps = false;

  /**
   * Mass Assignment
   */
  protected $fillable = [
    'first_name',
    'last_name',
    'email_address',
    'phone',
    'age',
    'where_are_you_from',
    'previous_pinup',
    'favorite_car',
    'favorite_pinup',
    'special_talent',
    'why_you',
    'biggest_turn_on',
    'biggest_turn_off',
    'favorite_quote',
    'image_1',
    'image_2',
    'image_3',
    'image_4',
    'image_5',
    'archive',
    'created_by',
    'created_at'
  ];
  protected $guarded = ['id'];
}