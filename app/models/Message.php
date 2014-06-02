<?php

class Message extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_msg";
  public $timestamps = false;

  /**
   * Mass assignment
   */
  public $fillable = ['from', 'parent', 'deleted', 'from_name', 'posted_on', 'subject', 'body'];

  /**
   * ORM
   */
  public function recepient()
  {
    return $this->hasOne('MessageRecepient', 'msg_id');
  }

  /**
   * Scoped queries
   */
  public function scopeFind_by_parent($query, $id)
  {
    return $query
      ->with(['recepient' => function($query) {
          $query
            ->with('userFrom.comm_user', 'userTo.comm_user')
            // Update messages to read
            ->update(['is_read' => 1]);
        }])
      ->where('parent', '=', $id)
      ->orderBy('posted_on', 'DESC')
      ->get();
  }

  public function scopeFind_all_by_id($query, $id)
  {
    return DB::select('SELECT
      msg.id,
      rep.msg_from,
      rep.to,
      msg.parent,
      msg.subject,
      msg.body,
      rep.bcc,
      rep.is_read,
      msg.posted_on
      FROM drr_community_msg_recepient as rep
      inner join drr_community_msg as msg
      on msg.id = rep.msg_id
      WHERE rep.msg_id IN (
          SELECT MAX(msg_id)
          FROM drr_community_msg_recepient
          WHERE rep.msg_from = ? or rep.to = ?
          GROUP BY msg_parent
      )
      ORDER BY posted_on DESC', [ $id, $id ]);
  }

  public function scopeFind_thread_by_id($query, $user, $recepient)
  {
    return $query
      ->select(
        'id',
        'parent',
        'subject',
        'body',
        'community_msg_recepient.to',
        'community_msg_recepient.msg_from',
        'community_msg_recepient.bcc',
        'community_msg_recepient.is_read',
        'posted_on'
      )
      ->join('community_msg_recepient', function($join) {
        $join->on('community_msg_recepient.msg_id', '=', 'id');
      })
      ->where('community_msg_recepient.msg_from', '=', $recepient)
      ->Where('community_msg_recepient.to', '=', $user)
      ->orWhere(function($query) use ($user, $recepient) {
        $query->Where('community_msg_recepient.msg_from', '=', $user)
              ->Where('community_msg_recepient.to', '=', $recepient);
      })
      ->where('community_msg_recepient.deleted', '=', 0)
      ->orderBy('posted_on', 'DESC')
      ->get();
  }

  public function scopeFind_latest($query, $user, $recepient) {
    return $query
      ->join('community_msg_recepient', function($join) {
        $join->on('community_msg_recepient.msg_id', '=', 'id');
      })
      ->where('community_msg_recepient.msg_from', '=', $recepient)
      ->Where('community_msg_recepient.to', '=', $user)
      ->orWhere(function($query) use ($user, $recepient) {
        $query->Where('community_msg_recepient.msg_from', '=', $user)
          ->Where('community_msg_recepient.to', '=', $recepient);
      })
      ->orderBy('posted_on', 'DESC');
  }
}