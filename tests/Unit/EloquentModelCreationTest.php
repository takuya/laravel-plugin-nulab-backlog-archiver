<?php

namespace Tests\Unit;

use Tests\TestCase;
use Takuya\BacklogApiClient\Models\BaseModel;
use Takuya\Laravel\Backlog\Services\BackLogToEloquent;
use Illuminate\Support\Str;

class EloquentModelCreationTest  extends TestCase {

  public function test_create_eloquent_model(){
    $printer = new BackLogToEloquent();
    $list = BaseModel::listModelClass();
    foreach ( $list as $class ) {
      $casts = $printer->propCasts($class);
      foreach ( $casts as $name=>$type ) {
        if(preg_match("/created$|updated$|date$/",$name)){
          $this->assertEquals('datetime',$type);
        }
        if (Str::isPlural($name)){
          $this->assertEquals('array',$type);
        }
        if (in_array($name,['content','icon'])){
          $this->assertEquals('string',$type);
        }
      }
    }
    
  }
  
}