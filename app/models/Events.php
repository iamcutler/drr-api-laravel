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
  public function creator()
  {
    return $this->belongsTo('User', 'creator');
  }

  public function member()
  {
    return $this->hasMany('EventMember', 'eventid');
  }

  public function category()
  {
    return $this->hasOne('EventCategory', 'id', 'catid');
  }

  public function activity($skip = 0, $limit = 10)
  {
    return $this->hasMany('Activity', 'eventid')
      ->where('app', '=', 'events.wall')
      ->orderBy('created', 'DESC')
      ->skip($skip)
      ->take($limit)
      ->get();
  }

  public function likes() {
    return $this
      ->hasOne('Likes', 'uid')
      ->where('element', '=', 'events');
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
      ->where('enddate', '>=', date('Y-m-d h:i:s'));
  }

  public function scopeSearchEvents($query, $name, $type, $offset = 0, $limit = 20)
  {
    $q = explode(' ', $name);

    $search = $query
      ->where('published', '=', 1)
      ->where('permission', '<=', 40)
      ->where('startdate', '>=', date("Y-m-d H:i:s"));

    // Loop through string array to add search conditionals
    foreach($q as $key => $val)
    {
      // Set statement based on type
      switch($type)
      {
        case 'name':
          $statement = ['title', 'LIKE', '%' . $val . '%'];
          break;
        case 'location':
          $statement = ['location', 'LIKE', '%' . $val . '%'];
          break;
        case 'startdate':
          $statement = ['startdate', 'LIKE', $val . '%'];
          break;
        default:
          $statement = ['title', 'LIKE', '%' . $val . '%'];
      }

      if($key == 0)
      {
        $search = $query->where($statement[0], $statement[1], $statement[2]);
      }
      else {
        $search = $query->orWhere($statement[0], $statement[1], $statement[2]);
      }
    }

    return $search
      ->skip($offset)
      ->take($limit)
      ->orderBy('startdate', 'ASC');
  }

  public function scopeEagerEventData($query)
  {
    return $query
      // Category
      ->with('category')

      // Members
      ->with(['member' => function($query) {
          $query->with(['user' => function($query) {
              $query
                ->orderBy('name', 'ASC')
                ->with('comm_user');
            }]);
        }])
        ->with('likes');
  }

  /**
   * @param int $offset
   * @param int $limit
   * @desc Find all events by name
   */
  public function scopeFindAllByDate($query, $offset = 0, $limit = 20)
  {
    return $query
      ->where('published', '=', 1)
      ->where('permission', '<=', 30)
      ->where('startdate', '>=', date('Y-m-d H:i:s'))
      ->with(['member' => function($query) {
          $query
            ->where('status', '=', 1)
            ->with(['user' => function($query) {
                $query
                  ->orderBy('name', 'ASC')
                  ->with('comm_user');
              }]);
        }])
      ->orderBy('created', 'DESC')
      ->skip($offset)
      ->take($limit);
  }
}