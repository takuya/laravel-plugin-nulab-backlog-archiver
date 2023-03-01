<?php

namespace Tests\Unit\BackUp;

use Tests\TestCase;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Status;
use App\Models\IssueType;
use App\Models\Category;
use App\Models\Version;
use App\Models\CustomField;
use App\Models\Webhook;
use App\Models\DiskUsage;
use App\Models\SharedFile;
use Tests\Unit\Traits\SearchProject;

class BackupProjectTest extends TestCase {
  use DatabaseMigrations;
  use DatabaseTransactions;
  use SearchProject;
  
  public function test_backup_project_attr () {
    $archiver = $this->archiver_client();
    $project = $this->find_project_has_custom_field();
    $archiver->saveProject( $project );
    //
    $this->assertGreaterThan( 0, Status::all()->count() );
    $this->assertGreaterThan( 0, IssueType::all()->count() );
    $this->assertGreaterThan( 0, Category::all()->count() );
    $this->assertGreaterThan( 0, DiskUsage::all()->count() );
  }
  
  public function test_backup_project_custom_field () {
    $archiver = $this->archiver_client();
    $project = $this->find_project_has_custom_field();
    $archiver->saveProject( $project );
    //
    $this->assertGreaterThan( 0, CustomField::all()->count() );
  }
  
  public function test_backup_project_has_shared_file () {
    $archiver = $this->archiver_client();
    $project = $this->find_project_has_shared_file();
    $archiver->saveProject( $project );
    //
    $this->assertGreaterThan( 0, SharedFile::all()->count() );
  }
  
  public function test_backup_project_has_milestone () {
    $archiver = $this->archiver_client();
    $project = $this->find_project_has_milestone();
    $archiver->saveProject( $project );
    //
    $this->assertGreaterThan( 0, Version::all()->count() );
  }
  
  public function test_backup_project_with_webhook () {
    $archiver = $this->archiver_client();
    $project = $this->find_project_has_webhook();
    $archiver->saveProject( $project );
    //
    $this->assertGreaterThan( 0, Webhook::all()->count() );
  }
}