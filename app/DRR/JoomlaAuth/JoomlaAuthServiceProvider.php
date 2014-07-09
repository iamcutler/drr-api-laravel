<?php

namespace DRR\JoomlaAuth;

use \Illuminate\Support\ServiceProvider;

class JoomlaAuthServiceProvider extends ServiceProvider {
  public function register()
  {
    $this->app->bind('joomlaauth', function()
    {
      return new JoomlaAuth;
    });
  }
} 