<?php

namespace Takuya\Laravel\Backlog\Console\Commands;

use Illuminate\Console\Command;
use Takuya\BacklogApiClient\Backup\BacklogArchiver;
use Takuya\BacklogApiClient\Backup\ArchiveService\ArchiveEloquent;
use Takuya\BacklogApiClient\Backlog;

class BackupNulabBacklogProject extends Command {
  protected $signature = 'backlog:save:project {project_id_or_key} {--with-issue} {--with-wiki}';
  
  protected $description = 'dump Backlog/Project into DB';
  
  public function __construct () {
    parent::__construct();
  }
  
  public function handle (): void {
    $token = env('BACKLOG_API_KEY');
    $spaceKey = env('BACKLOG_SPACE');
    //
    $id = $this->argument('project_id_or_key');
    $include_issue = $this->option('with-issue');
    $include_wiki = $this->option('with-wiki');
    //
    $archiver = new BacklogArchiver( new ArchiveEloquent() );
    $project = (new Backlog($spaceKey,$token))->project($id);
    //
    $archiver->saveProject($project,$include_issue, $include_wiki);
    
  }
  
}

