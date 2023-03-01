<?php

namespace Tests\Unit\Traits;

use Takuya\BacklogApiClient\Backlog;
use Takuya\BacklogApiClient\Models\Project;

trait SearchProject {
  public function find_project_has_wiki_tag () {
    return $this->find_project( fn( Project $e ) => $e->useWiki && sizeof( $e->wiki_tags() ) > 0 );
  }
  public function find_project_has_wiki_page () {
    return $this->find_project( fn( Project $e ) => $e->useWiki && sizeof( $e->wiki_page_ids() ) > 0 );
  }
  public function find_project_has_wiki_page_attachment_and_history () {
    $func  = function( Project $e ) {
      if ($e->useWiki!=true){
        return false;
      }
      foreach ( $e->wiki_pages() as $wiki_page ) {
        if (sizeof($wiki_page->attachments)>0 && sizeof($wiki_page->histories())>0 ){
          return true;
        }
      }
      return false;
    };
    return $this->find_project( $func );
  }
  
  
  public function find_project_has_webhook () {
    return $this->find_project( fn( Project $e ) => sizeof( $e->webhooks() ) > 0 );
  }
  
  protected function find_project ( callable $condition ) {
    $pids = $this->model_client()->space()->project_ids( Backlog::PROJECTS_ONLY_MINE );
    shuffle( $pids );
    foreach ( $pids as $id ) {
      $project = $this->model_client()->project( $id );
      if ( $condition( $project ) ) {
        return $project;
      }
    }
    return null;
  }
  public function find_project_has_custom_field () {
    return $this->find_project( fn( Project $e ) => sizeof( $e->custom_fields() ) > 0 );
  }
  
  public function find_project_has_shared_file () {
    return $this->find_project( fn( Project $e ) => sizeof( $e->shared_files() ) > 0 );
  }
  public function find_project_has_teams () {
    return $this->find_project( fn( Project $e ) => sizeof( $e->teams() ) > 0 );
  }
  
  public function find_project_has_milestone () {
    return $this->find_project( fn( Project $e ) => sizeof( $e->versions() ) > 0 );
  }
  
}