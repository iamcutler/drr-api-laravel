<?php

class AppHelper {
  public static function uploadS3Imgs(User $user, $file, Array $options = [])
  {
    $result = false;

    $file_obj = Image::make($file->getRealPath());
    $S3 = App::make('aws')->get('s3');
    $image_path = "images/photos/{$user->id}/1/";
    $new_file_name = str_replace('/', '', Hash::make($file->getClientOriginalName()));
    $thumb_file_name = $new_file_name . '_thumb.' . $file->getClientOriginalExtension();

    if($options['thumb'] == true)
    {
      // Create thumbnail version
      $file_obj->resize(64, 64)->save(public_path() . '/' . $thumb_file_name);
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
      $result = true;
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