<?php namespace DRR\Transformers;

class DirtyGirlTransformer extends Transformer {
  public function transform($girl)
  {
    $img_path = '/administrator/components/com_dirtygirlpages/uploads/';

    return [
      'id' => (int) $girl['id'],
      'campaign_month' => $girl['campaign_month'],
      'campaign_year' => (int) $girl['campaign_year'],
      'name' => $girl['dirty_girl_name'],
      'biography' => $girl['dirty_girl_bio'],
      'type' => (int) $girl['dirty_type'],
      'order' => (int) $girl['ordering'],
      'media' => [
        'thumbnail' => $img_path . $girl['thumbnail_image'],
        'image_1' => ($girl['image_1'] != "") ? $img_path . $girl['image_1'] : "",
        'image_2' => ($girl['image_2'] != "") ? $img_path . $girl['image_2'] : "",
        'image_3' => ($girl['image_3'] != "") ? $img_path . $girl['image_3'] : "",
        'image_4' => ($girl['image_4'] != "") ? $img_path . $girl['image_4'] : "",
        'image_5' => ($girl['image_5'] != "") ? $img_path . $girl['image_5'] : ""
      ]
    ];
  }
}
