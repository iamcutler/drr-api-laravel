<?php

use \DRR\Transformers\DirtyGirlTransformer;

class DirtyGirlController extends \BaseController {

  /**
   * @var DRR\Transformers\DirtyGirlTransformer
   */
  protected $dirtyGirlTransformer;

  public function __construct(DirtyGirl $DirtyGirl, DirtyGirlTransformer $dirtyGirlTransformer)
  {
    $this->DirtyGirl = $DirtyGirl;
    $this->dirtyGirlTransformer = $dirtyGirlTransformer;
  }

  // Get all dirty girls
  public function index()
  {
    $results = $this->DirtyGirl->Get_all_girls();

    $girls = $this->dirtyGirlTransformer->transformCollection($results->toArray());

    return Response::json($girls);
  }
}