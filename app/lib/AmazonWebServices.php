<?php

class AmazonWebServices implements AWSRepositoryInterface {

  public function __construct()
  {
    $this->AWS = App::make('aws');
    $this->AWS->bucket = Config::get('constant.AWS.bucket');
  }

  public function S3ImgUpload($file, Array $options = [])
  {
    $result['result'] = false;
    $S3 = $this->AWS->get('s3');

    $file_path = $file->getRealPath();
    $file_obj = Image::make($file_path);
    $new_file_name = str_replace('/', '', Hash::make($file->getClientOriginalName()));
    $thumb_file_name = $new_file_name . '_thumb.' . $file->getClientOriginalExtension();

    // Resize image if larger than 800 pixels wide
    if($file_obj->width > 800)
    {
      $file_path = public_path() . '/' . $new_file_name . $file->getClientOriginalExtension();
      $file_obj->resize(800, null, true)->save($file_path);
    }

    // Create thumbnail version
    if($options['thumb'] == true)
    {
      $thumb_width = (array_key_exists('width', $options['thumb_size']) && is_int($options['thumb_size']['width'])) ? $options['thumb_size']['width'] : 64;
      $thumb_height = (array_key_exists('height', $options['thumb_size']) && is_int($options['thumb_size']['height'])) ? $options['thumb_size']['height'] : 64;

      $file_obj->resize($thumb_width, $thumb_height)->save(public_path() . '/' . $thumb_file_name);
    }

    // Upload file to AWS S3
    try {
      // Save image to AWS S3
      $S3->upload($this->AWS->bucket, $options['image_path'] . $new_file_name . '.' . $file->getClientOriginalExtension(), fopen($file_path, 'r'), 'public-read');

      if($options['thumb'] == true)
      {
        $S3->upload($this->AWS->bucket, $options['image_path'] . $thumb_file_name, fopen(public_path() . '/' . $thumb_file_name, 'r'), 'public-read');
      }

      // Return truthy
      $result['result'] = true;

      // Return file information
      if($options['thumb'])
      {
        $result['file'] = [
          'image_path' => $options['image_path'],
          'name' => $new_file_name . '.' . $file->getClientOriginalExtension(),
          'thumbnail' => $thumb_file_name,
          'size' => $file->getSize()
        ];
      }
      else {
        $result['file'] = [
          'image_path' => $options['image_path'],
          'name' => $new_file_name . '.' . $file->getClientOriginalExtension(),
          'size' => $file->getSize()
        ];
      }
    } catch(S3Exception $e) {
      Log::error($e);
    }

    // Destroy temp files if exists after upload
    if($options['thumb'] == true && file_exists(public_path() . '/' . $thumb_file_name))
    {
      // Destroy local copy of generated thumbnl
      unlink(public_path() . '/' . $thumb_file_name);
    }

    if($file_obj->width > 800 && file_exists($file_path))
    {
      unlink($file_path);
    }

    // Destroy file object
    $file_obj->destroy();

    return $result;
  }

  // Video upload to S3
  public function S3VideoUpload($file, User $user, Array $options = [])
  {
    $S3 = $this->AWS->get('s3');
    $result = [];
    $file_path = $file->getRealPath();
    $new_file_name = str_replace('/', '', Hash::make($file->getClientOriginalName()));
    $upload_path = (array_key_exists('upload_path', $options)) ? $options['upload_path'] : "/images/originalvideos/{$user->id}/";
    $upload = $S3->upload($this->AWS->bucket, $upload_path . $new_file_name . '.' . $file->getClientOriginalExtension(), fopen($file_path, 'r'), 'public-read');

    if($upload)
    {
      $result['result'] = true;
      // Return video information
      $result['file'] = [
        'path' => $upload_path,
        'name' => $new_file_name . '.' . $file->getClientOriginalExtension(),
        'size' => $file->getSize()
      ];
    }
    else {
      $result['result'] = false;
    }

    return $result;
  }

  public function deleteS3Object(Array $options = [])
  {
    $S3 = $this->AWS->get('s3');
    $result = false;

    $remove = $S3->deleteObject([
      'Bucket' => $options['Bucket'],
      'Key' => $options['Key']
    ]);

    if($remove)
    {
      $result = true;
    }

    return $result;
  }
}