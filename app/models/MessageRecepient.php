<?php

class MessageRecepient extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_msg_recepient";
  protected $primaryKey = "msg_id";
  public $timestamps = false;

  /**
   * Mass assignment
   */
  public $fillable = ['msg_id', 'msg_parent', 'msg_from', 'to', 'bcc', 'is_read', 'deleted'];

  /**
   * ORM
   */
  public function recepient()
  {
    return $this->belongsTo('Message');
  }

  public function userFrom()
  {
    return $this->hasOne('User', 'id', 'msg_from');
  }

  public function userTo()
  {
    return $this->hasOne('User', 'id', 'to');
  }
}