<?php
namespace DRR\Transformers;

class GroupEventTransformer extends Transformer {
  public function transform($event)
  {
    return [
      'id' => (int) $event['id'],
      'title' => $event['title'],
      'location' => $event['location'],
      'summary' => $event['summary'],
      'description' => $event['description'],
      'startdate' => $event['startdate'],
      'enddate' => $event['enddate'],
      'permissions' => (int) $event['permission'],
      'avatar' => ($event['avatar'] != '') ? "/{$event['avatar']}" : '',
      'thumbnail' => ($event['thumb'] != '') ? "/{$event['thumb']}" : ''
    ];
  }
}
