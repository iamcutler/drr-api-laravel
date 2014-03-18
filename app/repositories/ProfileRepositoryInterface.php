<?php

interface ProfileRepositoryInterface {
  public function getFeed($id, $offset = 0, $limit = 10);
}