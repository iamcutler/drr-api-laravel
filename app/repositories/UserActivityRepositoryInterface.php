<?php

interface UserActivityRepositoryInterface {
  public function setLike(User $user, $element, $id, $type);
  public function saveTextStatus(User $user, Array $options);
  public function user_search($q, $user_id, $type = 'name', $offset = 0, $limit = 20);
  public function processPhotoStatusUpload($file, User $user, Array $options = []);
  public function processVideoStatusUpload($file, User $user, Array $options = []);
  public function detectUserLike(User $user, Likes $like);
}