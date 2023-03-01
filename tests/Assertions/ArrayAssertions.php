<?php

namespace Tests\Assertions;

trait ArrayAssertions {
  public static function assertArrayIsSameValues($expected,$arr){
    sort($expected);
    sort($arr);
    self::assertEquals($expected,$arr);
  }
  public static function assertIsArrayOfString ( $array ) {
    array_walk_recursive( $array, function( $v ) {
      self::assertIsString( $v );
    } );
  }
  
  public static function assertIsArrayOfInt ( $array ) {
    array_walk_recursive( $array, function( $v ) {
      self::assertIsInt( $v );
    } );
  }
  
  public static function assertIsArrayOfArray ( $array ) {
    array_map(function( $v ) {
      self::assertIsArray( $v );
    },$array);
  }
  
  public static function assertArrayIsConsitsOfPermitiveType ( $array ) {
    array_walk_recursive( $array, function( $v ) {
      self::assertTrue(
        is_array( $v ) ||
        is_numeric( $v ) ||
        is_string( $v ) ||
        is_bool( $v ) ||
        is_null( $v )
      );
    } );
  }
  
}