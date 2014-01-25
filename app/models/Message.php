<?php

class Message extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_msg as msg";

  /**
   * Scoped queries
   */
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
}