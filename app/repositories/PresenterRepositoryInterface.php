<?php

interface PresenterRepositoryInterface {
  static function User(User $user, Array $options = []);
  static function Wall($wall);
}