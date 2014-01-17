<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {

	// The database table used by the model.
	protected $table = 'users';

  // Fillable Attributes
  protected $fillable = ['name', 'username', 'email', 'password', 'usertype', 'registerDate', 'lastvisitDate', 'params', 'user_hash'];

  // Disable table timestamps
  public $timestamps = false;

  /**
  * Assign Relationships
  */
  public function comm_user()
  {
    return $this->hasOne('CommUser', 'userid');
  }

  /**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password');

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

  public function scopeCheck_hash_uniqueness($query, $hash) {
    return $query->where('user_hash', '=', $hash)->get(['user_hash'])->take(1);
  }
}