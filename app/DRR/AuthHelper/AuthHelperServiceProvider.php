<?php

namespace DRR\AuthHelper;

use \Illuminate\Support\ServiceProvider;

class AuthHelperServiceProvider extends ServiceProvider {
  public function register()
  {
    $this->app->bind('authhelper', function()
    {
      return new AuthHelper;
    });
  }
} 