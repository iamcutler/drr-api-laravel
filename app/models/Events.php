<?php

class Events extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_events";
  public $timestamps = false;

  /**
   * Mass assignment
   */
  protected $fillable = [
    'parent',
    'catid',
    'contentid',
    'type',
    'title',
    'location',
    'summary',
    'description',
    'creator',
    'startdate',
    'enddate',
    'permission',
    'avatar',
    'thumb',
    'invitedcount',
    'confirmedcount',
    'declindedcount',
    'maybecount',
    'wallcount',
    'ticket',
    'allowinvite',
    'created',
    'hits',
    'published',
    'latitude',
    'longitude',
    'offset',
    'allday',
    'repeat',
    'repeatend'
  ];

  protected $guarded = ['id'];

  /**
   * ORM
   */
  public function member()
  {
    return $this->hasMany('EventMember', 'eventid')->get();
  }

  public function category()
  {
    return $this->hasOne('EventCategory', 'id', 'catid')->first();
  }

  public function activity()
  {
    return $this->hasMany('Activity', 'eventid')
      ->where('app', '=', 'events.wall')
      ->orderBy('created', 'DESC')
      ->get();
  }

  public function likes() {
    return $this->hasMany('Likes', 'uid')
      ->where('like', '!=', '')
      ->get();
  }

  public function dislikes()
  {
    return $this->hasMany('Likes', 'uid')
      ->where('dislike', '!=', '')
      ->get();
  }

  /**
   * Scoped queries
   */
  public function scopeUpcoming($query)
  {
    return $query
      ->where('startdate', '>=', date('Y-m-d'));
  }
}