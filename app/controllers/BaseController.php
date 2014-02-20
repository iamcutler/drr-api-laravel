<?php

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

  public function get_events($events)
  {
    foreach($events as $key => $val)
    {
      $results = [];

      $results[$key]['id'] = $val['id'];
      $results[$key]['category'] = $val->category()->name;
      $results[$key]['type'] = $val['type'];
      $results[$key]['title'] = $val['title'];
      $results[$key]['location'] = $val['location'];
      $results[$key]['summary'] = $val['summary'];
      $results[$key]['description'] = $val['description'];
      $results[$key]['creator'] = $val['creator'];
      $results[$key]['start_date'] = $val['startdate'];
      $results[$key]['end_date'] = $val['enddate'];
      $results[$key]['permission'] = $val['permission'];
      $results[$key]['avatar'] = $val['avatar'];
      $results[$key]['thumbnail'] = $val['thumb'];

      $results[$key]['invite_counts']['invite'] = $val['invitedcount'];
      $results[$key]['invite_counts']['confirmed'] = $val['confirmedcount'];
      $results[$key]['invite_counts']['declined'] = $val['declinedcount'];
      $results[$key]['invite_counts']['maybe'] = $val['maybecount'];

      $results[$key]['ticket'] = $val['ticket'];
      $results[$key]['allowinvite'] = $val['allowinvite'];
      $results[$key]['hits'] = $val['hits'];
      $results[$key]['published'] = $val['published'];
      $results[$key]['latitude'] = $val['latitude'];
      $results[$key]['longitude'] = $val['longitude'];
      $results[$key]['offset'] = $val['offset'];
      $results[$key]['allday'] = $val['allday'];
      $results[$key]['repeat'] = $val['repeat'];
      $results[$key]['repeatend'] = $val['repeatend'];
      $results[$key]['created'] = $val['created'];

      return $results;
    }
  }
}