<?php

namespace Takuya\Laravel\Backlog\Providers;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Takuya\Laravel\Backlog\Console\Commands\initialize\MakeModelTable;
use Takuya\Laravel\Backlog\Console\Commands\initialize\MakeBacklogModelTableAll;
use Takuya\Laravel\Backlog\Console\Commands\initialize\MakeCopyModel;
use Takuya\Laravel\Backlog\Console\Commands\initialize\MakeCopyModelAll;
use Takuya\Laravel\Backlog\Console\Commands\BackupNulabBacklogProject;
use Takuya\Laravel\Backlog\Console\Commands\BackupNulabBacklogSpace;

class AppServiceProvider extends ServiceProvider {
  
  /**
   * Register any application services.
   * @return void
   */
  public function register () {
    Str::macro( 'isSingular', function( string $str ) {
      return $str == Str::singular( $str );
    } );
    Str::macro( 'isPlural', function( string $str ) {
      return $str == Str::plural( $str );
    } );
  }
  
  public function boot () {
    if ( $this->app->runningInConsole() ) {
      $this->commands( [
        MakeModelTable::class,
        MakeBacklogModelTableAll::class,
        MakeCopyModel::class,
        MakeCopyModelAll::class,
        BackupNulabBacklogProject::class,
        BackupNulabBacklogSpace::class,
      ] );
    }
  }
  
}
