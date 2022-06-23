<?php

namespace Dunkul\ReturnData;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Dunkul\ReturnData\ReturnDataClass;

class ReturnDataServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   *
   * @return void
   */
  public function register()
  {
    $this->app->bind('return-data', function () {
      return new ReturnDataClass(new Request);
    });
  }

  /**
   * Bootstrap services.
   *
   * @return void
   */
  public function boot()
  {
    //
  }
}
