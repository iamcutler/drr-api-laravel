<?php

interface UserActivityRepositoryInterface {
  public function setLike(User $user, $element, $id, $type);
  public function saveTextStatus(User $user, Array $options);
}