<?php

class FriendRequest extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_connection";
  protected $primaryKey = "connection_id";

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
      ->orderBy('community_connection.created', 'DESC')
      ->get([
        'community_connection.connection_id as id',
        'users.name',
        'community_users.alias as slug',
        'community_users.avatar',
        'community_users.thumb as thumbnail',
        'community_connection.msg'
      ]);
  }
}