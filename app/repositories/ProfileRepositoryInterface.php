<?php

interface ProfileRepositoryInterface {
  public function getFeed($id, $offset = 0, $limit = 10);
  public function about($user);
  public function friends($user);
  public function albums($user);
  public function album($slug, $id);
}