<?php

class UserConnection extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_connection";
  protected $primaryKey = "connection_id";

  // Disable default model timestamps
  public $timestamps = false;

  /*
   * Mass Assignment
   */
  public $fillable = ['connect_to', 'connect_from', 'status', 'group', 'msg', 'created'];

  /**
   * Scoped queries
   */
  public function scopeFind_by_id($query, $id)
  {
    return $query
      ->join('users', 'users.id', '=', 'community_connection.connect_from')
      ->join('community_users', 'users.id', '=', 'community_users.userid')
      ->where('community_connection.connect_to', '=', $id)
      ->where('community_connection.status', '=', 0)
      ->where('users.block', '=', 0)
      ->orderBy('community_connection.created', 'ASC')
      ->get([
        'community_connection.connection_id as id',
        'users.name',
        'community_users.alias as slug',
        'community_users.avatar',
        'community_users.thumb as thumbnail',
        'community_connection.msg'
      ]);
  }

  public function scopeFind_existing_connection($query, $to, $from, $status = 0)
  {
    return $query
      ->where('connect_from', '=', $to)
      ->where('connect_to', '=', $from)
      ->where('status', '', $status)
      ->orWhere(function($query) use ($to, $from, $status) {
        $query->where('connect_to', '=', $to)
              ->Where('connect_from', '=', $from)
              ->where('status', '', $status);
      });
  }

  public static function updateOrCreateConnection(Array $connection)
  {
    $result = UserConnection::where('connect_to', '=', $connection['connect_to'])->where('connect_from', '=', $connection['connect_from']);

    // Check if connection is found
    if(is_null($result->first()))
    {
      // Create new connection
      UserConnection::create($connection);
    }
    else
    {
      // Update existing connection7
      $result->update([ 'status' => $connection['status'] ]);
    }
  }
}