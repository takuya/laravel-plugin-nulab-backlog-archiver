<?php

namespace Takuya\Laravel\Backlog\Console\Commands;

use Illuminate\Console\Command;
use Takuya\BacklogApiClient\Backlog;
use Takuya\BacklogApiClient\Backup\BacklogArchiver;
use Takuya\BacklogApiClient\Backup\ArchiveService\ArchiveEloquent;

class BackupNulabBacklogSpace extends Command {
  protected $signature = 'backlog:save:space {--with-project} {--with-issue} {--with-wiki}';
  
  protected $description = 'Dump Backlog/Space into DB';
  
  public function __construct () {
    parent::__construct();
  }
  
  public function handle (): void {
    $token = env( 'BACKLOG_API_KEY' );
    $spaceKey = env( 'BACKLOG_SPACE' );
    //
    $include_project = $this->option( 'with-project' );
    $include_issue = $this->option( 'with-issue' );
    $include_wiki = $this->option( 'with-wiki' );
    //
    $archiver = new BacklogArchiver( new ArchiveEloquent() );
    $space = ( new Backlog( $spaceKey, $token ) )->space();
    //
    $archiver->saveSpace( $space );
    
    if ( $include_project ) {
      foreach ( $space->my_projects() as $project ) {
        $archiver->saveProject( $project, $include_issue, $include_wiki );
      }
    }
  }
  
}

