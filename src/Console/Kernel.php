<?php

namespace Takuya\Laravel\Backlog\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;
use Takuya\Laravel\Backlog\Console\Commands\BackupNulabBacklogProject;
use Takuya\Laravel\Backlog\Console\Commands\initialize\MakeCopyModel;
use Takuya\Laravel\Backlog\Console\Commands\initialize\MakeModelTable;
use Takuya\Laravel\Backlog\Console\Commands\initialize\MakeBacklogModelTableAll;
use Takuya\Laravel\Backlog\Console\Commands\initialize\MakeCopyModelAll;
use Takuya\Laravel\Backlog\Console\Commands\BackupNulabBacklogSpace;

class Kernel extends ConsoleKernel {
  /**
   * The Artisan commands provided by your application.
   *
   * @var array
   */
  protected $commands = [
    MakeModelTable::class,
    MakeBacklogModelTableAll::class,
    MakeCopyModel::class,
    MakeCopyModelAll::class,
    BackupNulabBacklogProject::class,
    BackupNulabBacklogSpace::class,
  ];
  
  /**
   * Define the application's command schedule.
   *
   * @param \Illuminate\Console\Scheduling\Schedule $schedule
   * @return void
   */
  protected function schedule ( Schedule $schedule ) {
    //
  }
}
