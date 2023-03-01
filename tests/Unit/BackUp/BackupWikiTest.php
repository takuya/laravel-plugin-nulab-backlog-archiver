<?php

namespace Tests\Unit\BackUp;

use tests\TestCase;
use Tests\Unit\Traits\SearchProject;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Models\WikiTag;
use Tests\Unit\Traits\SearchIssue;
use App\Models\WikiHistory;
use App\Models\WikiPageAttachment;
use App\Models\WikiPage;

class BackupWikiTest extends TestCase {
  
  use SearchProject;
  use DatabaseMigrations;
  use DatabaseTransactions;
  use SearchIssue;
  
  public function test_backup_wiki_tag () {
    $project = $this->find_project_has_wiki_tag();
    if ( empty( $project ) ) {
      throw new \RuntimeException( 'Wikiがあるプロジェクトがありません。作成してください。' );
    }
    $archiver = $this->archiver_client();
    $archiver->saveProjectWiki( $project );
    //
    $this->assertGreaterThan(0,WikiTag::where(['project_id'=>$project->id])->count());
  }
  public function test_backup_wiki_page() {
    $project = $this->find_project_has_wiki_page_attachment_and_history();
    if ( empty( $project ) ) {
      throw new \RuntimeException( 'Wiki:添付ファイル・タグ・履歴があるプロジェクトウィキがありません。作成してください。' );
    }
    $archiver = $this->archiver_client();
    $wiki = $project->wiki_pages()[0];
    $attachment = $wiki->attachments[0];
  
    $history = $wiki->histories()[0];
    $archiver->saveWiki( $wiki );
  
    $this->assertGreaterThan( 0, WikiPage::where( ['id' => $wiki->id] )->count() );
    $this->assertGreaterThan( 0, WikiPageAttachment::where( ['wiki_id' => $wiki->id] )->count() );
    $this->assertGreaterThan( 0, WikiHistory::where( ['wiki_id' => $wiki->id] )->count() );
    $this->assertEquals( 1, WikiPageAttachment::where( ['id' => $attachment->id] )->count() );
    $this->assertEquals( 1, WikiHistory::where( ['wiki_id' => $wiki->id, 'version' => $history->version] )->count() );
  }
}