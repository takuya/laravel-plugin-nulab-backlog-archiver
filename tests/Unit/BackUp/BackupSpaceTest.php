<?php

namespace Tests\Unit\BackUp;

use Tests\TestCase;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\Licence;
use App\Models\Priority;
use App\Models\Resolution;
use App\Models\Space;
use App\Models\User;
use App\Models\Team;

class BackupSpaceTest extends TestCase {
  use DatabaseMigrations;
  use DatabaseTransactions;
  public function test_backup_space(){
    $space = $this->model_client()->space();
    $archiver = $this->archiver_client();
    $archiver->saveSpace($space);
    //
    $this->assertGreaterThan(0,Space::all()->count());
    $this->assertGreaterThan(0,Licence::all()->count());
    $this->assertGreaterThan(0,Priority::all()->count());
    $this->assertGreaterThan(0,Resolution::all()->count());
    $this->assertGreaterThan(0,User::all()->count());
    $this->assertGreaterThan(0,Team::all()->count());
  }
}