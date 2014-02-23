<?php

class Likes extends Eloquent {
  /**
   * Table used by model
   */
  protected $table = "community_likes";
  public $timestamps = false;

  /**
   * Mass assignment
   */
  protected $fillable = ['element', 'uid', 'like', 'dislike'];
  protected $guarded = ['id'];

  /**
   * ORM
   */
  public function user_like()
  {
    return $this->belongsTo('User', 'like', 'id')->first();
  }

  public function user_dislike()
  {
    return $this->belongsTo('User', 'like', 'id')->first();
  }

  /**
   * Scoped queries
   */
  public function scopeCreateOrOverwriteOrRemoveLike($query, $type, Array $args)
  {
    // Find an existing like or dislike
    $result = $query
      ->where('element', '=', $args['element'])
      ->where('uid', '=', $args['uid'])
      ->where('like', '=', $args['user'])
      ->orWhere('dislike', '=', $args['user'])
      ->first();

    if(is_null($result))
    {
      // Create like if not found
      $like = new Likes;

      $like->element = $args['element'];
      $like->uid = $args['uid'];

      if($type) {
        $like->like = $args['user'];
        $like->dislike = '';
      } else {
        $like->like = '';
        $like->dislike = $args['user'];
      }

      $like->save();

      return true;
    }
    else
    {
      // Check if this is a like, if not, it must be a dislike
      if($type == 1 && $result->like == $args['user'] || $type == 0 && $result->dislike == $args['user'])
      {
        // Remove if found
        $result->delete();
        return true;
      }
      else
      {
        if($type == 1) {
          $result->like = $args['user'];
          $result->dislike = '';
        } else {
          $result->like = '';
          $result->dislike = $args['user'];
        }

        $result->save();
        return true;
      }
    }

    return false;
  }

  public function scopeFind_likes($query, $element, $id)
  {
    return $result = $query
      ->where('element', '=', $element)
      ->where('uid', '=', $id)
      ->get();
  }
}