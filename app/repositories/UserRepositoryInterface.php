<?php

interface UserRepositoryInterface {
  public function create(Array $options);
  public function modifyFriendCommArray(CommUser $user, $id, $action = 0);
}