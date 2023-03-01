<?php

namespace Tests\Unit\BackUp;

use Tests\TestCase;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\Unit\Traits\SearchIssue;
use App\Models\IssueAttachment;
use App\Models\Star;
use Takuya\BacklogApiClient\Models\Comment;
use Illuminate\Support\Str;
use App\Models\Notification;
use App\Models\CustomFieldSelectedValue;

class BackupIssueTest extends TestCase {
  
  use DatabaseMigrations;
  use DatabaseTransactions;
  use SearchIssue;
  
  public function test_backup_comment_has_notification(){
    $archiver = $this->archiver_client();
    $issue = $this->find_issue_has_comment_notify();
    /** @var Comment $comment */
    $comment = array_values( array_filter( $issue->comments(), fn( $c ) => sizeof( $c->notifications ) > 0 ) )[0];
    $notif = $comment->notifications[0];
    //
    $archiver->saveIssue( $issue );
    $saved = Notification::where( ['id' => $notif->id,] )->first();
    foreach ( ['alreadyRead', 'resourceAlreadyRead', 'id', 'reason',] as $key ) {
      $this->assertEquals( $notif->{$key}, $saved->{Str::snake( $key )} );
    }
    //
    $this->assertEquals( $notif->user->userId, $saved->user );
  }
  
  public function test_backup_comment_has_star(){
    $archiver = $this->archiver_client();
    $issue = $this->find_issue_has_star_comment();
    /** @var Comment $comment */
    $comment = array_values(array_filter($issue->comments(),fn($c)=>$c->hasStar()))[0];
    $star = $comment->stars[0];
    $archiver->saveIssue( $issue );
    $savedStar = Star::where( ['id' => $star->id,] )->first();
    //
    foreach ( ['url', 'title', 'id','comment'] as $key ) {
      $this->assertEquals( $star->{$key}, $savedStar->{$key} );
    }
    $this->assertEquals( $star->presenter->userId, $savedStar->presenter );
    
  }
  
  public function test_backup_issue_has_custom_fields(){
    $archiver = $this->archiver_client();
    $issue = $this->find_issue_uses_custom_field();
    $customField = $issue->customFields[0];
    //
    $archiver->saveIssue( $issue );
    $saved = CustomFieldSelectedValue::where( ['id' => $customField->id,] )->first();
    $this->assertEquals( $customField->id, $saved->id );
  }
  
  public function test_backup_issue_has_star () {
    $archiver = $this->archiver_client();
    $issue = $this->find_issue_has_stars();
    $star = $issue->stars[0];
    $archiver->saveStar( $star );
    $ret = Star::where( ['id' => $star->id,] )->first();
    foreach ( ['url', 'title', 'id','comment'] as $key ) {
      $this->assertEquals( $star->{$key}, $ret->{$key} );
    }
    $this->assertEquals( $star->presenter->userId, $ret->presenter );
  }
  
  public function test_backup_issue_attachment () {
    $archiver = $this->archiver_client();
    $issue = $this->find_issue_has_attachment();
    $attachment = $issue->attachments[0];
    $archiver->saveIssueAttachment( $attachment );
    //
    $this->assertGreaterThan( 0, IssueAttachment::all()->count() );
    $ret = IssueAttachment::where( [
      'issue_id' => $issue->id,
      'id' => $issue->attachments[0]->id,
    ] )->first();
    
    
    foreach ( ['name', 'size', 'id'] as $key ) {
      $this->assertEquals( $attachment->{$key}, $ret->{$key} );
    }
    
    $this->assertNotEmpty( $ret->content );
    $this->assertNotNull( $ret->id );
    $this->assertIsInt( $ret->id );
  }
}