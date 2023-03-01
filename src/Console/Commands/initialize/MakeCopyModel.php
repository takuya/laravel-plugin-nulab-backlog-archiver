<?php

namespace Takuya\Laravel\Backlog\Console\Commands\initialize;

use Illuminate\Console\Command;
use Takuya\Laravel\Backlog\Services\BackLogToEloquent;
use Takuya\BacklogApiClient\Models\BaseModel;

class MakeCopyModel extends Command {
  protected $signature = 'backlog:make:models {model}';
  
  protected $description = 'copy backlog model to laravel ORM';
  
  public function __construct () {
    parent::__construct();
  }
  
  public function handle (): void {
    $model = $this->argument( 'model' );
    $list = BaseModel::listModelClass();
    $list = array_values(array_filter($list,fn($e)=>str_ends_with($e,$model)));
    if (sizeof($list)!==1){
      $this->error(sprintf('class "%s" is not found.',$model));
      exit(1);
    }
    $class = '\\'.$list[0];
    $printer = new BackLogToEloquent();
    $name = $printer->shortName( $class );
    $path = $this->models_path( $name );
    $casts = $printer->propCasts( $class );
    $body = $printer->body( $name, $casts );
    file_put_contents( $path, $body );
    $this->info( sprintf( 'Model [%s] created successfully.', 'app/Models/'.basename( $path ) ) );
  }
  protected function models_path($name){
    return base_path(sprintf('app/Models/%s.php',$name));
  }
}

