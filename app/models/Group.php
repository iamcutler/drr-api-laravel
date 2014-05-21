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
    return $this->hasOne('GroupCategory', 'id', 'categoryid');
  }

  public function member()
  {
    return $this->hasMany('GroupMember', 'groupid');
  }

  public function bulletin()
  {
    return $this->hasMany('GroupBulletin', 'groupid')
      ->where('published', '=', 1);
  }

  public function discussion()
  {
    return $this->hasMany('Activity', 'groupid');
  }

  public function discussion_replys($id)
  {
    return $this->hasMany('Activity', 'groupid')
      ->where('cid', '=', $id)
      ->where('app', '=', 'groups.discussion.reply');
  }

  public function events()
  {
    return $this->hasMany('Events', 'contentid');
  }

  public function likes()
  {
    return $this->hasMany('Likes', 'uid');
  }

  public function dislikes()
  {
    return $this->hasMany('Likes', 'uid')
      ->where('element', '=', 'groups')
      ->where('dislike', '!=', '')
      ->get();
  }

  /**
   * @summary Scoped queries
   */
  public function scopeEagerGroupData($query)
  {
    return $query
      // Category
      ->with('category')

      // Members
      ->with(['member' => function($query) {
        $query
          ->take(30)
          ->with(['user' => function($query) {
          $query
            ->orderBy('name', 'ASC')
            ->with('comm_user');
          }]);
        }])

      // Bulletins
      ->with(['bulletin' => function($query) {
        $query->take(30)
          ->with(['user' => function($query) {
            $query
              ->orderBy('name', 'ASC')
              ->with('comm_user');
          }]);
        }])

      // Discussions
      ->with(['discussion' => function($query) {
        $query
          ->where('app', '=', 'groups.discussion')
          ->take(30)
          ->with(['userActor' => function($query) {
            $query
              ->orderBy('name', 'ASC')
              ->with('comm_user');
          }]);
        }])

      // Events
      ->with(['events' => function($query) {
          $query
            ->where('type', '=', 'group')
            ->where('published', '=', 1);
        }])

      // Likes
      ->with(['likes' => function($query) {
          $query
            ->where('element', '=', 'groups')
            ->where('like', '!=', '');
        }]);
  }

  /**
   * @param $q
   * @param int $offset
   * @param int $limit
   * @summary search by name for groups
   * @return mixed
   */
  public function scopeFindByName($query, $q, $offset = 0, $limit = 20) {
    $q = explode(' ', $q);

    $search = $query
      ->where('published', '=', 1);

    // Loop through string array to add search conditionals
    foreach($q as $key => $val)
    {
      if($key == 0)
      {
        $search = $query->where('name', 'LIKE', '%' . $val . '%');
      }
      else {
        $search = $query->orWhere('name', 'LIKE', '%' . $val . '%');
      }
    }

    return $search
      ->with('category')
      ->with(['member' => function($query) {
        $query
          ->where('approved', '=', 1)
          ->with('user.comm_user');
      }])
      ->skip($offset)
      ->take($limit)
      ->orderBy('name', 'ASC');
  }

  /**
   * @param int $offset
   * @param int $limit
   * @summary Find all groups with eager loading
   * @return mixed
   */
  public function scopeFindAll($query, $offset = 0, $limit = 20)
  {
    return $query
      ->with('category')
      ->with(['member' => function($query) {
          $query
            ->where('approved', '=', 1)
            ->with('user.comm_user');
        }])
      ->skip($offset)
      ->take($limit)
      ->orderBy('name', 'ASC');
  }
}