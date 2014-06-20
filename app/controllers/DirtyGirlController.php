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
    $img_path = '/administrator/components/com_dirtygirlpages/uploads/';

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
      $girls[$key]['media']['thumbnail'] = $img_path . $val->thumbnail_image;
      $girls[$key]['media']['image_1'] = ($val->image_1 != "") ? $img_path . $val->image_1 : "";
      $girls[$key]['media']['image_2'] = ($val->image_2 != "") ? $img_path . $val->image_2 : "";
      $girls[$key]['media']['image_3'] = ($val->image_3 != "") ? $img_path . $val->image_3 : "";
      $girls[$key]['media']['image_4'] = ($val->image_4 != "") ? $img_path . $val->image_4 : "";
      $girls[$key]['media']['image_5'] = ($val->image_5 != "") ? $img_path . $val->image_5 : "";
    }

    return Response::json($girls);
  }
}