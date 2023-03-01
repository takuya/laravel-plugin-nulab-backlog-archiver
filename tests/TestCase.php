<?php

namespace Tests;

use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use Takuya\BacklogApiClient\BacklogAPIClient;
use Takuya\BacklogApiClient\Backlog;
use Takuya\BacklogApiClient\Backup\BacklogArchiver;
use Takuya\BacklogApiClient\Backup\ArchiveService\ArchiveEloquent;
use Takuya\Laravel\Backlog\Providers\AppServiceProvider;

abstract class TestCase extends BaseTestCase {
  public function api_client () {
    $token = env('BACKLOG_API_KEY');
    $spaceKey = env('BACKLOG_SPACE');
    return new BacklogAPIClient( $spaceKey, $token );
  }
  
  public function model_client () {
    $token = env('BACKLOG_API_KEY');
    $spaceKey = env('BACKLOG_SPACE');
    return new Backlog(  $spaceKey, $token );
  }
  public function archiver_client () {
    return new BacklogArchiver( new ArchiveEloquent() );
  }
  
  /**
   * Creates the application.
   *
   * @return \Laravel\Lumen\Application
   */
  public function createApplication () {
    $app = new \Laravel\Lumen\Application(
      dirname(__DIR__)
    );
    $app->register(AppServiceProvider::class);
    return $app;
  }
}
