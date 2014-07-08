<?php

namespace DRR\AuthHelper\Facades;

use Illuminate\Support\Facades\Facade;

class AuthHelper extends Facade {
  protected static function getFacadeAccessor() { return 'authhelper'; }
} 