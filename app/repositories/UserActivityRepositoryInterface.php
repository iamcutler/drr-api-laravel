<?php

interface UserActivityRepositoryInterface {
  public function setLike(User $user, $element, $id, $type);
}