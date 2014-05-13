<?php

interface PresenterRepositoryInterface {
  static function User(User $user, Array $options = []);
  static function UserImage(UserPhoto $image, Array $options = []);
  static function likeStats($likes);
  static function Wall($wall);
  public function profileFeed(User $user, Activity $value);
  public function getFeedResource(Activity $value);
}