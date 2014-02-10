<?php

class EventMember extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_events_members";
  public $timestamps = false;

  /**
   * ORM
   */
  public function event()
  {
    return $this->belongsTo('Events', 'eventid')->get();
  }

  public function user()
  {
    return $this->hasOne('User', 'id', 'memberid')->first();
  }

  public function comm_user()
  {
    return $this->hasOne('CommUser', 'userid', 'memberid')->first();
  }

  /**
   * Mass Assignment
   */
  protected $fillable = [];
  protected $guarded = ['id'];

  /**
   * Scoped queries
   */
  public function scopeFind_by_event_id($query, $id)
  {
    return $query
      ->where('eventid', '=', $id)
      ->get();
  }
}