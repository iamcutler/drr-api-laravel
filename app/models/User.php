<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

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
  * query scopes
   * Model methods
   */
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
    return $query->find($id)->comm_user()->get()[0];
  }

  public function scopeCheck_username_uniqueness($query, $username)
  {
    return $query->where('username', '=', $username)->take(1);
  }

  public function scopeCheck_email_uniqueness($query, $email)
  {
    return $query->where('email', '=', $email)->take(1);
  }

  public function scopeFind_User_Hash_by_id($query, $id)
  {
    return $query->where('id', '=', $id)->take(1);
  }
}