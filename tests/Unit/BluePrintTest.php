<?php

namespace Tests\Unit;

use tests\TestCase;
use Takuya\Laravel\Backlog\Services\BacklogModelToBluePrint;
use Takuya\BacklogApiClient\Models\BaseModel;
use Takuya\BacklogApiClient\Models\Notification;

class BluePrintTest extends TestCase {
  public function test_blue_print_create_schema(){
    $printer = new BacklogModelToBluePrint();
    $printer->bluePrint( Notification::class );
    $list = BaseModel::listModelClass();
    foreach ( $list as $class ) {
  
      $this->assertMatchesRegularExpression('/[a-z_]+/',$printer->tableize($class));
      foreach ( $printer->columns( $class ) as ['name'=>$name,'type'=>$type] ) {
        $this->assertContains($type,['string','text','json','integer','boolean','binary']);
        $this->assertMatchesRegularExpression('/[a-z_]+/',$name);
      }
      $src = $printer->bluePrint($class);
      $this->assertStringContainsString(sprintf("'%s'", $printer->tableize($class)),$src);
      $this->assertStringContainsString("primary",$src);
    }
  }
  
}