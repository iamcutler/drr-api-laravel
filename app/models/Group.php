<?php

class Group extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_groups";
  public $timestamps = false;

  /**
   * Mass assignment
   */
  protected $fillable = [
    'published',
    'ownerid',
    'categoryid',
    'name',
    'description',
    'email',
    'website',
    'approvals',
    'created',
    'avatar',
    'thumb',
    'discusscount',
    'wallcount',
    'membercount',
    'params',
    'storage'
  ];

  protected $guarded = ['id'];

  /**
   * ORM
   */
  public function category()
  {
    return $this->hasOne('GroupCategory', 'id', 'categoryid')->first();
  }

  public function member()
  {
    return $this->hasMany('GroupMember', 'groupid')->get();
  }

  public function bulletin()
  {
    return $this->hasMany('GroupBulletin', 'groupid')
      ->where('published', '=', 1)
      ->get();
  }

  public function likes()
  {
    return $this->hasMany('Likes', 'uid')
      ->where('element', '=', 'groups')
      ->where('like', '!=', '')
      ->get();
  }

  public function dislikes()
  {
    return $this->hasMany('Likes', 'uid')
      ->where('element', '=', 'groups')
      ->where('dislike', '!=', '')
      ->get();
  }
}