<?php

class AppHelper {
  public static function uploadS3Imgs(User $user, $file, Array $options = [])
  {
    $result['result'] = false;

    $file_obj = Image::make($file->getRealPath());
    $S3 = App::make('aws')->get('s3');
    $image_path = "images/photos/{$user->id}/1/";
    $new_file_name = str_replace('/', '', Hash::make($file->getClientOriginalName()));
    $thumb_file_name = $new_file_name . '_thumb.' . $file->getClientOriginalExtension();

    if($options['thumb'] == true)
    {
      // Create thumbnail version
      $thumb_width = (array_key_exists('width', $options['thumb_size']) && is_int($options['thumb_size']['width'])) ? $options['thumb_size']['width'] : 64;
      $thumb_height = (array_key_exists('height', $options['thumb_size']) && is_int($options['thumb_size']['height'])) ? $options['thumb_size']['height'] : 64;

      $file_obj->resize($thumb_width, $thumb_height)->save(public_path() . '/' . $thumb_file_name);
    }

    // Upload file to AWS S3
    try {
      // Save image to AWS S3
      $S3->upload(Config::get('constant.AWS.bucket'), $image_path . $new_file_name . '.' . $file->getClientOriginalExtension(), fopen($file->getRealPath(), 'r'), 'public-read');

      if($options['thumb'] == true)
      {
        $S3->upload(Config::get('constant.AWS.bucket'), $image_path . $thumb_file_name, fopen(public_path() . '/' . $thumb_file_name, 'r'), 'public-read');
      }

      // Return truthy
      $result['result'] = true;

      $result['file'] = [
        'image_path' => $image_path,
        'name' => $new_file_name . '.' . $file->getClientOriginalExtension(),
        'thumbnail' => $thumb_file_name,
        'size' => $file->getSize()
      ];
    } catch(S3Exception $e) {

    }


    if($options['thumb'] == true)
    {
      // Destroy local copy of generated thumbnl
      unlink(public_path() . '/' . $thumb_file_name);
    }

    // Destroy file object
    $file_obj->destroy();

    return $result;
  }
}