<?php

namespace Takuya\Laravel\Backlog\Services;

use ReflectionClass;
use ReflectionProperty;
use Illuminate\Support\Str;
use Takuya\BacklogApiClient\Models\Comment;
use Takuya\BacklogApiClient\Models\Traits\HasID;
use Takuya\BacklogApiClient\Models\Traits\HasIssueId;
use Takuya\BacklogApiClient\Models\Interfaces\HasIcon;
use Takuya\BacklogApiClient\Models\Traits\HasProjectId;
use Takuya\BacklogApiClient\Models\Traits\RelateToIssue;
use Takuya\BacklogApiClient\Models\Traits\RelateToProject;
use Takuya\BacklogApiClient\Models\Interfaces\ProjectAttrs;
use Takuya\BacklogApiClient\Models\Interfaces\HasFileContent;
use Takuya\BacklogApiClient\Models\Licence;
use Takuya\BacklogApiClient\Models\DiskUsage;
use Takuya\BacklogApiClient\Models\WikiHistory;
use Takuya\BacklogApiClient\Backup\Traits\HasClassCheck;

class BacklogModelToBluePrint {
  
  use HasClassCheck;
  
  public function __construct() { }
  
  public function bluePrint( $class ) {
    $tblName = $this->tableize($class);
    $cols = $this->columns($class);
    $blueprint = $this->_bluePrint($tblName, $cols);
    
    return $blueprint;
  }
  
  public function tableize( $class ) {
    $className = ( new ReflectionClass($class) )->getShortName();
    $tblName = Str::plural(Str::snake($className));
    
    return $tblName;
  }
  
  public function columns( $class ) {
    $attr = $this->getPublicProperties( $class );
    $inter = $this->getInterfaceProperties( $class );
    $rela = $this->getRelatedProperties( $class );
    $props = array_merge( $attr, $inter, $rela );
    $props = $this->filterColumn( $class, $props );
    $props = array_unique( $props, SORT_REGULAR );
    foreach ( $props as $prop ) {
      $col = $this->setConstraint( $class, ...$prop );
      $col = $this->mapToColumnType( ...$col );
      $cols[] = $col;
    }
    $cols = $this->filterPrimaryKey( $cols, $class );
    usort( $cols, function( $a, $b ) { return strcmp( $a['name'], $b['name'] ); } );
  
    return $cols;
  }
  
  protected function  filterPrimaryKey( $arr, $class ) {
    if ( in_array($class ,[ Licence::class,Comment::class,DiskUsage::class,WikiHistory::class] )) {
      // Commnent はid がPrimaryキーになれない。代わりのIDを挿入する。
      //  同一IDで、コメント本文と差分情報がそれぞれ同一コメントIDで別要素に入っている。
      // プライマリ・キーが無いものに擬似的に足しておく。
      foreach ( $arr as $idx=> ['name' => $name, 'type' => $type, 'constraint' => $constraint] ) {
        if(!empty($constraint) &&(!empty($constraint['primary'])|| in_array('primary',$constraint))){
          $arr[$idx]['constraint'] = [];
        }
        if($name == 'project_id'){
          $arr[$idx]['constraint'] = ['nullable'];
        }
      }
      $arr[] = [
        'name' => '_id',
        'type' => 'integer',
        'constraint' => ['primary'],
      ];
    }
    return $arr;
  }
  
  protected function getPublicProperties ( $class ) {
    $ref = new ReflectionClass( $class );
    $props = $ref->getProperties( ReflectionProperty::IS_PUBLIC );
    $arr = [];
    foreach ( $props as $prop ) {
      $arr[] = [
        'name' => $prop->getName(),
        'type' => $prop->getType()?->getName() ?? 'unknown',
        'nullable' => $prop->getType()?->allowsNull() ?? 'true',
      ];
    }
    
    return $arr;
  }
  
  protected function getInterfaceProperties( $class ) {
    $props = [];
    if( $this->hasInterface($class, HasIcon::class) ) {
      $props[] = ['name' => 'icon', 'type' => 'blob', 'nullable' => true,];
    }
    if( $this->hasInterface($class, ProjectAttrs::class) ) {
      $props[] = ['name' => 'users', 'type' => 'text', 'nullable' => false];
      $props[] = ['name' => 'teams', 'type' => 'text', 'nullable' => false];
    }
    if( $this->hasInterface($class, HasFileContent::class) ) {
      $props[] = ['name' => 'content', 'type' => 'blob', 'nullable' => true,];
    }
    
    return $props;
  }
  
  protected function getRelatedProperties( $class ) {
    $traits = $this->findRelationTraits($class);
    $props = [];
    foreach ($traits as $trait) {
      $filter = \ReflectionProperty::IS_PROTECTED|\ReflectionProperty::IS_PUBLIC;
      foreach ($trait->getProperties($filter) as $property) {
        $props[] = [
          'name'     => $property->getName(),
          'type'     => $property->getType()->getName(),
          'nullable' => $property->getType()?->allowsNull() ?? 'true',
        ];
      }
    }
    
    return $props;
  }
  
  protected function filterColumn( $class, $cols ) {
    $map = $class::attribute_mapping_list();
    $userAttrNames = array_map(fn( $e ) => $e[0], array_filter($map, fn( $e ) => str_ends_with($e[1], 'User')));
    foreach ($cols as $idx => ['name' => $name, 'type' => $type, 'nullable' => $nullable]) {
      if( in_array($name, $userAttrNames) ) {
        $cols[$idx]['type'] = 'string';
      }
      if( $name == 'nulabAccount' ) {
        $cols[$idx]['type'] = 'string';
      }
    }
    
    return $cols;
  }
  
  protected function setConstraint( $class, $name, $type, $nullable ) {
    $constraint = null;
    $name = Str::snake( $name );
    if ( in_array( $name, ['id', 'wiki_id', 'space_key', 'nulab_id'] ) ) {
      $constraint['primary'] = [$name];
      // id がいつでも primary とは限らない。
      if ( $this->hasTrait( $class, HasID::class ) ) {
        $constraint = ['primary' => ['id']];
      }
      if ( $this->hasTrait( $class, RelateToProject::class )
        || $this->hasTrait( $class, HasProjectId::class ) ) {
        $constraint['primary'][] = 'project_id';
      }
      if ( $this->hasTrait( $class, RelateToIssue::class )
        || $this->hasTrait( $class, HasIssueId::class ) ) {
        $constraint['primary'][] = 'issue_id';
      }
    } else {
      if ( $nullable ) {
        $constraint = ['nullable'];
      }
    }
    return [
      'name'       => $name,
      'type'       => $type,
      'constraint' => $constraint,
    ];
  }
  
  protected function mapToColumnType( $name, $type, $constraint ) {
    if( in_array($name, ['content']) ) {
      $type = 'text';
    }
    return [
      'name'       => Str::snake($name),
      'type'       => match ( $type ) {
        // php types => sqlite types.
        "text", "unknown" => 'text',
        'string' => 'string',
        "array", "object" => 'json',
        'bool' => 'boolean',
        'int' => 'integer',
        'blob' => 'binary',
      },
      'constraint' => $constraint,
    ];
  }
  
  protected function _bluePrint( $tblName, array $cols ) {
    $lines = [];
    $lines[] = sprintf('Schema::create( \'%s\', function( Blueprint $table ) {', $tblName);
    foreach ($cols as ['name' => $name, 'type' => $type, 'constraint' => $constraints]) {
      $line = sprintf('      $table->%s("%s")', $type, $name);
  
      if( ! empty($constraints) ) {
        if ( array_key_exists( 'primary', $constraints ) ) {
          $c = array_map( fn( $e ) => "'${e}'", $constraints['primary'] );
          $lines[] = sprintf( '      $table->primary([%s]);', join( ',', $c ) );
          unset( $constraints['primary'] );
        }
        foreach ( $constraints as $constraint ) {
          $line .= sprintf( '->%s()', $constraint );
        }
      }
      $lines[] = $line.';';
    }
    $lines[] = '    } );';
    
    return join(PHP_EOL, $lines);
  }
}