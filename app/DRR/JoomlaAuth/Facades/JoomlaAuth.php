<?php

namespace DRR\JoomlaAuth\Facades;

use Illuminate\Support\Facades\Facade;

class JoomlaAuth extends Facade {
  protected static function getFacadeAccessor() { return 'joomlaauth'; }
} 