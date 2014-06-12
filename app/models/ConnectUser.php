<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class ConnectUser extends Eloquent {
  // Table used by modal
  protected $table = 'community_connect_users';

  // Disable default timestamps
  public $timestamps = false;

  // Fillable Attributes
  protected $fillable = ['connectid', 'type', 'userid'];
}
