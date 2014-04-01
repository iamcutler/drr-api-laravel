<?php

interface AWSRepositoryInterface {
  public function S3ImgUpload($file, Array $options = []);
  public function deleteS3Object(Array $options = []);
}