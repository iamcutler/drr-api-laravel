<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class User extends Eloquent {

	// The database table used by the model.
	protected $table = 'users';

  // Fillable Attributes
  protected $fillable = ['name', 'username', 'email', 'password', 'usertype', 'registerDate', 'lastvisitDate', 'params', 'user_hash'];

  // Disable table timestamps
  public $timestamps = false;

  /**
  * ORM
  */
  public function comm_user()
  {
    return $this->hasOne('CommUser', 'userid');
  }

  public function connections($id)
  {
    return $this->hasMany('UserConnection')
      ->where('status', '=', 1)
      ->where('connect_from', '=', $id)
      ->whereOr('connect_to', '=', $id)
      ->get();
  }

  public function profile_likes() {
    return $this->hasMany('Likes', 'uid');
  }

  public function profile_dislikes()
  {
    return $this->hasMany('Likes', 'id', 'dislike')
      ->where('element', '=', 'profile')
      ->get();
  }

  public function photo()
  {
    return $this->hasMany('UserPhoto', 'creator');
  }

  public function photo_album()
  {
    return $this->hasMany('UserPhotoAlbum', 'id', 'creator');
  }

  public function video()
  {
    return $this->hasMany('UserVideo', 'creator');
  }

  public function events()
  {
    return $this->hasMany('Events', 'creator');
  }

  public function eventMember()
  {
    return $this->hasMany('EventMember', 'memberid');
  }

  public function groupMember()
  {
    return $this->hasMany('GroupMember', 'memberid');
  }

  public function scopeProfile_feed()
  {
    return Activity::with('userActor.comm_user')
      // Target
      ->with('userTarget.comm_user')

      // Likes
      ->with(['likes' => function($query) {
          $query->where('like', '!=', '');
        }])

      // Photo
      ->with(['photo' => function($query) {
          $query
            ->where('permissions', '<=', 10)
            ->where('published', '=', 1);
        }])

      // Video
      ->with(['video' => function($query) {
          $query
            ->where('permissions', '<=', 0)
            ->where('published', '=', 1);
        }])

      // Comments
      ->with(['wall' => function($query) {
          $query
            ->with('user.comm_user')
            ->where('published', '=', 1)
            ->orderBy('date', 'DESC');
        }])
      ->where('actor', '=', $this->id)
      ->orWhere('target', '=', $this->id);
  }

  /*
   * Accessors & Mutators
   */
  public function setUsernameAttribute($username)
  {
    $this->attributes['username'] = strtolower($username);
  }

  public function setPasswordAttribute($password)
  {
    $this->attributes['password'] = $this->generate_password($password);
  }

  public function setUserHashAttribute($user)
  {
    $this->attributes['user_hash'] = Hash::make($user);
  }

  /**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array();

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier()
	{
		return $this->getKey();
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->password;
	}

	/**
	 * Get the e-mail address where password reminders are sent.
	 *
	 * @return string
	 */
	public function getReminderEmail()
	{
		return $this->email;
	}

  /**
   * Model methods
   */
  public static function generate_hash($name, $username)
  {
    $saltLength = 9;
    $salt = substr(md5(uniqid(rand(), true)), 0, $saltLength);
    $hash = $salt . sha1($salt . $name . rand(5, 20) . $username . date("Y-m-d"));

    // Check if hash is unique, if not, generate new hash till a unique hash is found
    if(User::Check_hash_uniqueness($hash)->count()) {
      return User::generate_hash($name, $username);
    } else {
      return $hash;
    }
  }

  public static function generate_password($password)
  {
    $salt = AuthHelper::genRandomPassword(32);
    $crypt = AuthHelper::getCryptedPassword($password, $salt);
    return $crypt . ':' . $salt;
  }

  public static function validate_user_password($userPass, $systemPass)
  {
    $salt = substr($systemPass, strpos($systemPass, ":") + 1);
    $userPass = md5($userPass . $salt) . ":" . $salt;
    // Compare passwords
    if($userPass === $systemPass)
    {
      return true;
    }

    return false;
  }

  /**
  * Query scopes
  */
  public function scopeFind_comm_user($query, $id)
  {
    return $query->find($id)->comm_user()->first();
  }

  public function scopeCheck_username_uniqueness($query, $username)
  {
    return $query->where('username', '=', $username);
  }

  public function scopeCheck_email_uniqueness($query, $email)
  {
    return $query->where('email', '=', $email);
  }

  public function scopeFind_hash_by_id($query, $id)
  {
    return $query->where('id', '=', $id)->first(['user_hash'])->user_hash;
  }

  public function scopeFind_id_by_hash($query, $hash)
  {
    return $query->where('user_hash', '=', $hash)->first();
  }

  public function scopeCheck_hash_uniqueness($query, $hash)
  {
    return $query->where('user_hash', '=', $hash)->get(['user_hash'])->take(1);
  }

  public function scopeFind_profile_data_by_slug($query, $slug)
  {
    return $query->where('alias', '=', $slug)->first();
  }

  public function scopeFind_user_profile_by_slug($query, $slug)
  {
    return DB::table('community_users')
      ->join('users', function($join)
      {
        $join->on('community_users.userid', '=', 'users.id');
      })->where('alias', '=', $slug)->first([
        'users.id',
        'users.name',
        'users.username',
        'community_users.status',
        'community_users.posted_on',
        'community_users.points',
        'community_users.avatar',
        'community_users.thumb as thumbnail',
        'users.params',
        'community_users.view',
        'community_users.friends',
        'community_users.groups',
        'community_users.events',
        'community_users.friendcount',
        'community_users.alias as slug',
        'community_users.params as profile_params',
        'users.user_hash',
        'users.lastvisitDate as last_visit',
        'users.registerDate as registered'
      ]);
  }

  public function scopeFind_friend_by_id($query, $id)
  {
    return DB::table('community_users')
      ->select([
        'users.id',
        'users.name',
        'community_users.avatar',
        'community_users.thumb as thumbnail',
        'community_users.status',
        'community_users.alias as slug'
      ])
      ->join('users', function($join)
      {
        $join->on('community_users.userid', '=', 'users.id');
      })->where('id', '=', $id);
  }

  public function scopeFind_comm_by_hash($query, $hash)
  {
    return DB::table('community_users')
      ->select([
        'users.id',
        'users.name',
        'community_users.avatar',
        'community_users.thumb as thumbnail',
        'community_users.status',
        'community_users.alias as slug'
      ])
      ->join('users', function($join)
      {
        $join->on('community_users.userid', '=', 'users.id');
      })->where('user_hash', '=', $hash);
  }

  public function scopeFind_all($query, $offset, $limit)
  {
    return $query
      ->join('community_users', function($join)
      {
        $join->on('users.id', '=', 'community_users.userid');
      })->skip($offset)->take($limit)->get([
        'users.id',
        'users.name',
        'community_users.avatar',
        'community_users.thumb as thumbnail',
        'community_users.status',
        'community_users.alias as slug'
      ]);
  }

  public function scopeFind_by_username($query, $username)
  {
    return $query
      ->join('community_users', function($join) {
        $join->on('community_users.userid', '=', 'id');
      })
      ->where('community_users.alias', '=', $username)
      ->first();
  }

  public function scopeFind_profile_friends_by_id_array($query, $ids)
  {
    return $query
      ->select([
        'name',
        'username',
        'community_users.avatar',
        'community_users.thumb as thumbnail',
        'community_users.alias'
      ])
      ->join('community_users', function($join)
      {
        $join->on('userid', '=', 'id');
      })
      ->whereIn('id', explode(',', $ids))
      ->orderBy('name', 'ASC')
      ->get();
  }

  public function scopeFindBySlug($query, $slug)
  {
    return $query
      ->join('community_users', function($join) {
        $join->on('userid', '=', 'id');
      })
      ->where('alias', '=', $slug);
  }

  public function scopeSearchByName($query, $name, $user_id, $offset = 0, $limit = 20)
  {
    $q = explode(' ', $name);

    $search = $query
    ->where('id', '!=', $user_id)
    ->where('block', '=', 0);

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
      ->where('id', '!=', $user_id)
      ->where('block', '=', 0)
      ->skip($offset)
      ->take($limit)
      ->orderBy('name', 'ASC')
      ->get();
  }

  // Eager load profile data
  public function scopeEagerProfileData($query)
  {
    return $query
      ->with('comm_user')

      // Photos
      ->with('photo')

      // Videos
      ->with('video')

      // Events
      ->with(['eventMember' => function($query) {
          $query
            ->where('status', '=', 1)
            ->groupBy('eventid');
        }])

      // Groups
      ->with(['groupMember' => function($query) {
          $query
            ->where('approved', '=', 1)
            ->groupBy('groupid');
        }])

      // Likes
      ->with(['profile_likes' => function($query) {
          $query->where('element', '=', 'profile');
        }]);
  }
}