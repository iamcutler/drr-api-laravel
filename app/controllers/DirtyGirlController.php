<?php

class DirtyGirlController extends \BaseController {

  public function __construct(DirtyGirl $DirtyGirl)
  {
    $this->DirtyGirl = $DirtyGirl;
  }

  // Get all dirty girls
  public function index()
  {
    $results = $this->DirtyGirl->Get_all_girls();

    $girls = [];
    foreach($results as $key => $val)
    {
      $girls[$key]['id'] = $val->id;
      $girls[$key]['campaign_month'] = $val->campaign_month;
      $girls[$key]['campaign_year'] = $val->campaign_year;
      $girls[$key]['name'] = $val->dirty_girl_name;
      $girls[$key]['biography'] = $val->dirty_girl_bio;
      $girls[$key]['type'] = $val->dirty_type;
      $girls[$key]['order'] = $val->ordering;
      $girls[$key]['media']['thumbnail'] = Config::get('constant.cdn_domain') . "/administrator/components/com_dirtygirlpages/uploads/" .$val->thumbnail_image;
    }

    return Response::json($girls);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
  public function show($id)
  {
    $query = $this->DirtyGirl->find($id);

    $result = [];
    $result['id'] = $query->id;
    $result['campaign_month'] = $query->campaign_month;
    $result['campaign_year'] = $query->campaign_year;
    $result['name'] = $query->dirty_girl_name;
    $result['biography'] = $query->dirty_girl_bio;
    $result['type'] = $query->dirty_type;
    $result['order'] = $query->ordering;
    $result['media']['thumbnail'] = Config::get('constant.cdn_domain') . "/administrator/components/com_dirtygirlpages/uploads/" .$query->thumbnail_image;
    $result['media']['image_1'] = ($query->image_1 != "") ? Config::get('constant.cdn_domain') . "/administrator/components/com_dirtygirlpages/uploads/" . $query->image_1 : "";
    $result['media']['image_2'] = ($query->image_2 != "") ? Config::get('constant.cdn_domain') . "/administrator/components/com_dirtygirlpages/uploads/" . $query->image_2 : "";
    $result['media']['image_3'] = ($query->image_3 != "") ? Config::get('constant.cdn_domain') . "/administrator/components/com_dirtygirlpages/uploads/" . $query->image_3 : "";
    $result['media']['image_4'] = ($query->image_4 != "") ? Config::get('constant.cdn_domain') . "/administrator/components/com_dirtygirlpages/uploads/" . $query->image_4 : "";
    $result['media']['image_5'] = ($query->image_5 != "") ? Config::get('constant.cdn_domain') . "/administrator/components/com_dirtygirlpages/uploads/" . $query->image_5 : "";

    return Response::json($result);
  }
}