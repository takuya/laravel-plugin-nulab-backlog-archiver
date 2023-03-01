<?php

namespace Takuya\Laravel\Backlog\Services;

use Illuminate\Support\Str;
use Takuya\BacklogApiClient\Models\Interfaces\HasIcon;
use Takuya\BacklogApiClient\Models\Interfaces\ProjectAttrs;
use Takuya\BacklogApiClient\Models\Interfaces\HasFileContent;
use Takuya\BacklogApiClient\Backup\Traits\HasClassCheck;

class BackLogToEloquent {
  
  use HasClassCheck;
  
  public function __construct() { }
  
  public function shortName( $class ) {
    return ( new \ReflectionClass($class) )->getShortName();
  }
  
  public function propCasts( $class ) {
    $props = array_merge(
      $this->getProperties($class),
      $this->getInterfaceProperties($class),
    );
    $props = $this->propFilterByName($class, $props);
    $props = $this->propFilterByType($props);
    $props = $this->propFilterSnake( $props );
    $casts = array_reduce( $props, function( $c, $e ) {
      $c[$e['name']] = $e['type'];
      return $c;
    }, [] );
    //dd($casts);
    return $casts;
  }
  
  protected function getProperties( $class ) {
    $ref = new \ReflectionClass($class);
    $props = [];
    foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $idx => $property) {
      $props[$idx] = [
        'name'     => $property->getName(),
        'type'     => $property->getType()?->getName(),
        'nullable' => $property->getType()?->allowsNull() ?? 'true',
      ];
    }
    
    return $props;
  }
  
  public function getInterfaceProperties( $class ) {
    $list = [];
    if( $this->hasInterface($class, ProjectAttrs::class) ) {
      $list[] = ['name' => 'teams', 'type' => 'array', 'nullable' => true];
      $list[] = ['name' => 'users', 'type' => 'array', 'nullable' => true];
    }
    if( $this->hasInterface($class, HasIcon::class) ) {
      $list[] = ['name' => 'icon', 'type' => 'blob', 'nullable' => true];
    }
    if( $this->hasInterface($class, HasFileContent::class) ) {
      $list[] = ['name' => 'content', 'type' => 'blob', 'nullable' => true];
    }
    
    return $list;
  }
  
  public function propFilterByName( $class, $props ) {
    $map = $class::attribute_mapping_list();
    $userAttrNames = array_map(fn( $e ) => $e[0], array_filter($map, fn( $e ) => str_ends_with($e[1], 'User')));
    $userAttrNames = array_filter($userAttrNames, fn( $str ) => $str == Str::singular($str));
    foreach ($props as $idx => ['name' => $name, 'type' => $type, 'nullable' => $nullable]) {
      /** @var \ReflectionProperty $prop */
      if( in_array($name, $userAttrNames) ) {
        $props[$idx]['type'] = 'string';
      }
      if( $name == 'nulabAccount' ) {
        $props[$idx]['type'] = 'string';
      }
      if( in_array($name, ['created','updated']) ) {
        $props[$idx]['type'] = 'datetime';
      }
      if( str_ends_with($name, 'time') ) {
        $props[$idx]['type'] = 'datetime';
      }
  
    }
    
    return $props;
  }
  
  public function propFilterByType( $props ) {
    $list = [];
    foreach ($props as $idx => ['name' => $name, 'type' => $type, 'nullable' => $nullable]) {
      if ( in_array( $type, ['object'] ) ) {
        $list[] = [
          'name' => $name,
          'type' => 'json',
          'nullable' => $nullable,
        ];
      }
      if ( in_array( $type, ['array'] ) ) {// array は array のママで良いかも知んれない。
        $list[] = [
          'name' => $name,
          'type' => 'array',
          'nullable' => $nullable,
        ];
      }
      if ( in_array( $type, ['blob'] ) ) {
        $list[] = [
          'name' => $name,
          'type' => 'string',
          'nullable' => $nullable,
        ];
      }
      if ( in_array( $type, ['datetime'] ) ) {
        $list[] = [
          'name' => $name,
          'type'     => 'datetime',
          'nullable' => $nullable,
        ];
      }
      if( is_null($type)){
        $list[] = [
          'name'     => $name,
          'type'     => 'json',
          'nullable' => true,
        ];
      }
    }
    
    return $list;
  }
  
  protected function propFilterSnake( $props ) {
    $list = [];
    foreach ($props as $idx => ['name' => $name, 'type' => $type, 'nullable' => $nullable]) {
      $list[$idx]['name'] = Str::snake($name);
      $list[$idx]['type'] = $type;
    }
    
    return $list;
  }
  
  public function body( $name, $casts ) {
    $body = <<<'EOD'
    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class %s extends Model {
      //protected $fillable = [];
      protected $guarded = [];
      public $timestamps = false;
      protected $casts = %s;
    }
    EOD;
    
    return sprintf($body, $name, $this->array_to_source($casts));
  }
  
  protected function array_to_source( $arr ) {
    if( sizeof($arr) == 0 ) {
      return '[]';
    }
    $lines = [];
    $lines[] = '[';
    foreach ($arr as $k => $v) {
      $lines[] = sprintf('    "%s" => "%s",', $k, $v);
    }
    $lines[] = '  ]';
    
    return join(PHP_EOL, $lines);
  }
}