<?php

namespace Takuya\Laravel\Backlog\Console\Commands\initialize;

use Illuminate\Console\Command;
use Takuya\Laravel\Backlog\Services\BacklogModelToBluePrint;

class MakeModelTable extends Command {
  protected $signature = 'backlog:make:migration {model}';
  
  protected $description = 'make from backlog model';
  
  public function __construct () {
    parent::__construct();
  }
  
  public function handle (): void {
    $model = $this->argument('model');
    $printer = (new BacklogModelToBluePrint());
    $class = $printer->findClass($model);
    $blueprint = $printer->bluePrint($class);
    $tblName = $printer->tableize($class);
    $mName = $this->migration_name($tblName);
    $ret = file_put_contents($mName,$this->migration($blueprint));
    if($ret){
      $this->info( sprintf('Migration [%s] created successfully.',basename($mName)));
    }
  }
  protected function migration_name($tblName){
    $mg_path = database_path('migrations');
    $date  = (new \DateTime)->format('Y_m_d_His');
    $name = sprintf('%s_create_%s_autogen.php',$date,$tblName);
    return $mg_path.DIRECTORY_SEPARATOR.$name;
  }
  protected function migration($blueprint){
    $body=<<<'EOD'
    <?php
    
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    
    return new class extends Migration {
      /**
       * Run the migrations.
       */
      public function up (): void {
        %s
      }
      
      /**
       * Reverse the migrations.
       */
      public function down (): void {
        //
      }
    };
    EOD;
    return sprintf($body,$blueprint);
  }
  
  
  
}

