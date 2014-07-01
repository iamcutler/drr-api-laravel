<?php namespace DRR\Transformers;

class VideoTransformer extends Transformer {
  public function transform($video)
  {
    return [
      'id' => (int) $video['id'],
      'title' => $video['title'],
      'description' => $video['description'],
      'creator' => $video['creator'],
      'creator_type' => $video['creator_type'],
      'created' => $video['created'],
      'permissions' => $video['permissions'],
      'category_id' => $video['category_id'],
      'hits' => $video['hits'],
      'featured' => $video['featured'],
      'duration' => $video['duration'],
      'status' => $video['status'],
      'groupid' => $video['groupid'],
      'filesize' => $video['filesize'],
      'location' => $video['location'],
      'params' => json_decode($video['params']),

      'media' => [
        'video_id' => $video['video_id'],
        'type' => $video['type'],
        'thumbnail' => "/{$video['thumb']}",
        'path' => $video['path']
      ]
    ];
  }
} 