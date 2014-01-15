<?php

class DirtyGirlController extends \BaseController {
  public function get_dirty_girls()
  {
    $results = DirtyGirl::orderBy('dirty_girl_name','ASC')->get();

    $girls = [];
    foreach($results as $key => $val)
    {
      $girls[$key]['id'] = $val['id'];
      $girls[$key]['campaign_month'] = $val['campaign_month'];
      $girls[$key]['campaign_year'] = $val['campaign_year'];
      $girls[$key]['name'] = $val['name'];
      $girls[$key]['biography'] = $val['bio'];
      $girls[$key]['type'] = $val['type'];
      $girls[$key]['order'] = $val['ordering'];
      $girls[$key]['media']['thumbnail'] = Config::get('constant.cdn_domain') . "/administrator/components/com_dirtygirlpages/uploads/" .$val['thumbnail'];
      $girls[$key]['media']['image_1'] = ($val['image_1'] != "") ? Config::get('constant.cdn_domain') . "/administrator/components/com_dirtygirlpages/uploads/" . $val['image_1'] : "";
      $girls[$key]['media']['image_2'] = ($val['image_2'] != "") ? Config::get('constant.cdn_domain') . "/administrator/components/com_dirtygirlpages/uploads/" . $val['image_2'] : "";
      $girls[$key]['media']['image_3'] = ($val['image_3'] != "") ? Config::get('constant.cdn_domain') . "/administrator/components/com_dirtygirlpages/uploads/" . $val['image_3'] : "";
      $girls[$key]['media']['image_4'] = ($val['image_4'] != "") ? Config::get('constant.cdn_domain') . "/administrator/components/com_dirtygirlpages/uploads/" . $val['image_4'] : "";
      $girls[$key]['media']['image_5'] = ($val['image_5'] != "") ? Config::get('constant.cdn_domain') . "/administrator/components/com_dirtygirlpages/uploads/" . $val['image_5'] : "";
    }

    return Response::json($girls);
  }
}