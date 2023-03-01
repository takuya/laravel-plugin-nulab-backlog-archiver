<?php

namespace Takuya\Laravel\Backlog\Console\Commands\initialize;

use Illuminate\Console\Command;
use Takuya\BacklogApiClient\Models\BaseModel;

class MakeBacklogModelTableAll extends Command {
  protected $signature = 'backlog:make:migration:all';
  
  protected $description = 'make migration from all of backlog model.';
  
  public function __construct () {
    parent::__construct();
  }
  
  public function handle (): void {
    $cmd  = $this->getCommandName(MakeModelTable::class);
    $names = $this->getModelClassShortNames();
    foreach ( $names as $model ) {
      $command = trim(str_replace("{model}",'',$cmd));
      $this->call($command,['model'=>$model]);
    }
  }
  public function getModelClassShortNames(){
    $list = BaseModel::listModelClass();
    foreach ( $list as $idx=>$class ) {
      $ref = new \ReflectionClass($class );
      $list[$idx] = $ref->getShortName();
    }
    return $list;
  }
  public function getCommandName($class){
    $ref = new \ReflectionClass($class );
    $props = $ref->getProperties(\ReflectionProperty::IS_PROTECTED);
    $prop = array_filter($props,fn($p)=>$p->name=='signature')[0];
    $signature = $prop->getDefaultValue();
    return $signature;
  }
  
}

