<?php

namespace DRR\Transformers;

class EventTransformer extends Transformer {
  public function transform($event)
  {
    return [
      'id' => (int) $event['id'],
      'category' => $event['category']['name'],
      'type' => $event['type'],
      'title' => $event['title'],
      'location' => $event['location'],
      'summary' => $event['summary'],
      'description' => $event['description'],
      'creator' => $event['creator'],
      'start_date' => $event['startdate'],
      'end_date' => $event['enddate'],
      'permission' => $event['permission'],
      'avatar' => "/{$event['avatar']}",
      'thumbnail' => "/{$event['thumb']}",
      'invite_counts' => [
        'invite' => (int) $event['invitedcount'],
        'confirmed' => (int) $event['confirmedcount'],
        'declinded' => (int) $event['declinedcount'],
        'maybe' => (int) $event['maybecount']
      ],
      'ticket' => $event['ticket'],
      'allowinvite' => $event['allowinvite'],
      'hits' => $event['hits'],
      'published' => (int) $event['published'],
      'latitude' => $event['latitude'],
      'longitude' => $event['longitude'],
      'offset' => $event['offset'],
      'allday' => $event['allday'],
      'repeat' => $event['repeat'],
      'repeatend' => $event['repeatend'],
      'created' => $event['created']
    ];
  }
}
