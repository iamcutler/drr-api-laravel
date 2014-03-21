<?php

interface PresenterRepositoryInterface {
  static function User(User $user, Array $options = []);
  static function UserImage(UserPhoto $image);
  static function likeStats($likes);
  static function Wall($wall);
}