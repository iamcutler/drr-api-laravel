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
  public function scopeCreateOrOverwriteOrRemoveLike($query, Array $args)
  {
    // Find an existing like or dislike
    $result = $query
      ->where('element', '=', $args['element'])
      ->where('uid', '=', $args['uid'])
      ->where('like', '=', $args['like'])
      ->whereOr('dislike', '=', $args['dislike'])
      ->first();

    if(is_null($result))
    {
      // Create like if not found
      $like = new Likes;

      $like->element = $args['element'];
      $like->uid = $args['uid'];
      $like->like = $args['like'];
      $like->dislike = $args['dislike'];

      $like->save();

      return true;
    }
    else
    {
      // Check if this is a like, if not, it must be a dislike
      if($args['type'] == 'like' && $result->like != '')
      {
        // Remove if found
        $result->delete();
        return true;
      }
      elseif($args['type'] == 'dislike' && $result->dislike != '')
      {
        $result->delete();
        return true;
      }
      else
      {
        // Replace existing like with a dislike or visa versa
        $result->like = $args['like'];
        $result->dislike = $args['dislike'];

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