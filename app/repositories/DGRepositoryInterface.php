<?php

interface DGRepositoryInterface {
  public function newSubmission(User $user, $params);
  public function updateSubmissionImagePath(Submission $submission, $num, $path);
  public function uploadSubmissionImage($file);
}