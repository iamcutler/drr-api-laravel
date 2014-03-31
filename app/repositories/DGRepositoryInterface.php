<?php

interface DGRepositoryInterface {
  public function newSubmission(User $user, $params);
}