<?php

namespace Takuya\Laravel\Backlog\Console\Commands\initialize;

use Illuminate\Console\Command;
use Takuya\Laravel\Backlog\Services\BackLogToEloquent;
use Takuya\BacklogApiClient\Models\BaseModel;
use Takuya\BacklogApiClient\BacklogAPIClient;

class MakeCopyModelAll extends Command {
  protected $signature = 'backlog:make:models:all';
  
  protected $description = 'copy backlog model to laravel ORM';
  
  public function __construct () {
    parent::__construct();
  
  }
  
  public function handle (): void {
    $list = BaseModel::listModelClass();
    $printer = new BackLogToEloquent();
    foreach ( $list as $item ) {
      $name = $printer->shortName($item);
      $path = $this->models_path($name);
      $casts = $printer->propCasts($item);
      $body = $printer->body($name,$casts);
      file_put_contents($path,$body);
      $this->info( sprintf('Model [%s] created successfully.','app/Models/'.basename($path)));
    }
  }
  protected function models_path($name){
    return base_path(sprintf('app/Models/%s.php',$name));
  }
}

