<?php

interface AWSRepositoryInterface {
  public function S3ImgUpload(User $user, $file, Array $options = []);
}