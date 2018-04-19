<?php

namespace Jiko\Discord\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use DirectoryIterator;
use Jiko\Discord\Commands\ErrantBotAnnounce;

class DiscordServiceProvider extends ServiceProvider
{
  public function boot()
  {
    parent::boot();

    if ($this->app->runningInConsole()) {
      $this->commands([
        ErrantBotAnnounce::class
      ]);
    }
  }

  public function register()
  {
  }

  protected function loadRoutesFromDir($routes_path, $recursive = false)
  {
    foreach (new DirectoryIterator($routes_path) as $file) {
      if (!$file->isDot() && !$file->isDir() && ($file->getExtension() === 'php')) {
        require_once $routes_path . DIRECTORY_SEPARATOR . $file->getFilename();
      }
    }
  }

  public function map()
  {
  }
}