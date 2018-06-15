<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Core.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Plugin_Sitemobile extends Zend_Controller_Plugin_Abstract {

  protected $_pagesTable;
  protected $_contentTable;

  public function onIntegrated($pageTable, $contentTable) {
    $this->_pagesTable = $pageTable;
    $this->_contentTable = $contentTable;
    $db = Engine_Db_Table::getDefaultAdapter();
    //CHECK FOR THE LISTING TYPES THIS PLUGIN ALREADY HAVE
    $table = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');
    $tableName = $table->info('name');
    $db->beginTransaction();
    try {
      $this->addBrowseReviewPage();
      $this->addCategroiesPage();
      $this->addBrowseWishlistPage();
      $this->addWishlistProfilePage();
      $this->addDiscussionTopicViewPage();
      $this->addEditorProfilePage();
      $this->addVideoViewPage();
      $this->addmemberProfilePageWidgets();
      $this->addReviewProfilePage();
      $data = $table->select()->from($tableName, 'listingtype_id')
              ->query()
              ->fetchAll(Zend_Db::FETCH_COLUMN);
      $listingTypeApi = Engine_Api::_()->getApi('listingTypeSM', 'sitereview');
      foreach ($data as $listingtype_id) {
        $listingTypeApi->defaultCreation($listingtype_id, 1, 0, $pageTable, $contentTable);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      throw $e;
    }
  }

  protected function addBrowseReviewPage() {
    $db = Engine_Db_Table::getDefaultAdapter();

    $page_id = Engine_Api::_()->getApi('modules', 'sitemobile')->getPageId('sitereview_review_browse');
    // insert if it doesn't exist yet
    if (!$page_id) {
      // Insert page
      $db->insert($this->_pagesTable, array(
          'name' => 'sitereview_review_browse',
          'displayname' => 'Multiple Listing Types - Browse Reviews',
          'title' => 'Browse Reviews',
          'description' => 'This is the review browse page.',
          'custom' => 0,
      ));
      $page_id = $db->lastInsertId();

      // Insert main
      $db->insert($this->_contentTable, array(
          'type' => 'container',
          'name' => 'main',
          'page_id' => $page_id,
          'order' => 1,
      ));
      $main_id = $db->lastInsertId();

      // Insert main-middle
      $db->insert($this->_contentTable, array(
          'type' => 'container',
          'name' => 'middle',
          'page_id' => $page_id,
          'parent_content_id' => $main_id,
      ));
      $main_middle_id = $db->lastInsertId();

      $db->insert($this->_contentTable, array(
          'type' => 'widget',
          'name' => 'sitereview.navigation-sitereview',
          'page_id' => $page_id,
          'parent_content_id' => $main_middle_id,
          'order' => 1,
      ));
//      // Insert Advance search
      $db->insert($this->_contentTable, array(
          'type' => 'widget',
          'name' => 'sitemobile.sitemobile-advancedsearch',
          'page_id' => $page_id,
          'parent_content_id' => $main_middle_id,
          'params' => '{"search":"2","title":"","nomobile":"0","name":"sitemobile.sitemobile-advancedsearch"}',
          'order' => 2,
      ));
      // Insert content
      $db->insert($this->_contentTable, array(
          'type' => 'widget',
          'name' => 'core.content',
          'page_id' => $page_id,
          'parent_content_id' => $main_middle_id,
          'order' => 3,
      ));
    }
  }

  protected function addCategroiesPage() {
    $db = Engine_Db_Table::getDefaultAdapter();

    $page_id = Engine_Api::_()->getApi('modules', 'sitemobile')->getPageId('sitereview_index_categories');
    // insert if it doesn't exist yet
    if (!$page_id) {
      // Insert page
      $db->insert($this->_pagesTable, array(
          'name' => 'sitereview_index_categories',
          'displayname' => 'Multiple Listing Types - Categories Home',
          'title' => 'Categories Home',
          'description' => 'This is the categories home page.',
          'custom' => 0,
      ));
      $page_id = $db->lastInsertId();

      // Insert main
      $db->insert($this->_contentTable, array(
          'type' => 'container',
          'name' => 'main',
          'page_id' => $page_id,
          'order' => 1,
      ));
      $main_id = $db->lastInsertId();

      // Insert main-middle
      $db->insert($this->_contentTable, array(
          'type' => 'container',
          'name' => 'middle',
          'page_id' => $page_id,
          'parent_content_id' => $main_id,
      ));
      $main_middle_id = $db->lastInsertId();

      $db->insert($this->_contentTable, array(
          'type' => 'widget',
          'name' => 'sitereview.navigation-sitereview',
          'page_id' => $page_id,
          'parent_content_id' => $main_middle_id,
          'order' => 1,
      ));

      // Insert content
      $db->insert($this->_contentTable, array(
          'type' => 'widget',
          'name' => 'sitereview.categories-home',
          'page_id' => $page_id,
          'parent_content_id' => $main_middle_id,
          'order' => 3,
          'params' => '{"listingtype_id":"-1"}',
      ));
    }
  }

  protected function addBrowseWishlistPage() {
    $db = Engine_Db_Table::getDefaultAdapter();

    $page_id = Engine_Api::_()->getApi('modules', 'sitemobile')->getPageId('sitereview_wishlist_browse');
    // insert if it doesn't exist yet
    if (!$page_id) {
      // Insert page
      $db->insert($this->_pagesTable, array(
          'name' => 'sitereview_wishlist_browse',
          'displayname' => 'Multiple Listing Types - Browse Wishlists',
          'title' => 'Browse Wishlists',
          'description' => 'This is the wishlist browse page.',
          'custom' => 0,
      ));
      $page_id = $db->lastInsertId();

      // Insert main
      $db->insert($this->_contentTable, array(
          'type' => 'container',
          'name' => 'main',
          'page_id' => $page_id,
          'order' => 1,
      ));
      $main_id = $db->lastInsertId();

      // Insert main-middle
      $db->insert($this->_contentTable, array(
          'type' => 'container',
          'name' => 'middle',
          'page_id' => $page_id,
          'parent_content_id' => $main_id,
      ));
      $main_middle_id = $db->lastInsertId();

      //PLACE CATEGORY WIDGET FOR MOBILE APP AND TABLET APP.
      if($this->_pagesTable != 'engine4_sitemobileapp_pages' && $this->_pagesTable != 'engine4_sitemobileapp_tablet_pages'){ 
      $db->insert($this->_contentTable, array(
          'type' => 'widget',
          'name' => 'sitereview.navigation-sitereview',
          'page_id' => $page_id,
          'parent_content_id' => $main_middle_id,
          'order' => 1,
      ));}
//      // Insert Advance search
      $db->insert($this->_contentTable, array(
          'type' => 'widget',
          'name' => 'sitemobile.sitemobile-advancedsearch',
          'page_id' => $page_id,
          'parent_content_id' => $main_middle_id,
          'params' => '{"search":"2","title":"","nomobile":"0","name":"sitemobile.sitemobile-advancedsearch"}',
          'order' => 2,
      ));
      // Insert content
      $db->insert($this->_contentTable, array(
          'type' => 'widget',
          'name' => 'sitereview.wishlist-browse',
          'page_id' => $page_id,
          'parent_content_id' => $main_middle_id,
          'order' => 3,
          'params' => '{"statisticsWishlist":["entryCount","likeCount","viewCount","followCount"],"itemCount":"10"}',
      ));
    }
  }
  
  
  protected function addWishlistProfilePage() {
    $db = Engine_Db_Table::getDefaultAdapter();

    $page_id = Engine_Api::_()->getApi('modules', 'sitemobile')->getPageId('sitereview_wishlist_profile');
    // insert if it doesn't exist yet
    if (!$page_id) {
      // Insert page
      $db->insert($this->_pagesTable, array(
          'name' => 'sitereview_wishlist_profile',
          'displayname' => 'Multiple Listing Types - Wishlist Profile',
          'title' => 'Wishlist Profile',
          'description' => 'This is the wishlist profile page.',
          'custom' => 0,
      ));
      $page_id = $db->lastInsertId();

      // Insert main
      $db->insert($this->_contentTable, array(
          'type' => 'container',
          'name' => 'main',
          'page_id' => $page_id,
          'order' => 1,
      ));
      $main_id = $db->lastInsertId();

      // Insert main-middle
      $db->insert($this->_contentTable, array(
          'type' => 'container',
          'name' => 'middle',
          'page_id' => $page_id,
          'parent_content_id' => $main_id,
      ));
      $main_middle_id = $db->lastInsertId();

      // Insert content
      $db->insert($this->_contentTable, array(
          'type' => 'widget',
          'name' => 'sitereview.wishlist-profile-items',
          'page_id' => $page_id,
          'parent_content_id' => $main_middle_id,
          'order' => 3,
          'params' => '{"followLike":["follow","like"],"shareOptions":["siteShare","friend","report"],"statistics":["likeCount","reviewCount"],"statisticsWishlist":["entryCount","likeCount","viewCount","followCount"]}',
      ));
    }
  }

  public function addDiscussionTopicViewPage() {
    $db = Engine_Db_Table::getDefaultAdapter();

    $page_id = Engine_Api::_()->getApi('modules', 'sitemobile')->getPageId('sitereview_topic_view');
    // insert if it doesn't exist yet
    if (!$page_id) {
      // Insert page
      $db->insert($this->_pagesTable, array(
          'name' => 'sitereview_topic_view',
          'displayname' => 'Multiple Listing Types - Topic Discussion View Page',
          'title' => 'Reviews Discussion Topic View Page',
          'description' => 'This is the review topic view page.',
          'custom' => 0,
      ));
      $page_id = $db->lastInsertId();

      // Insert main
      $db->insert($this->_contentTable, array(
          'type' => 'container',
          'name' => 'main',
          'page_id' => $page_id,
          'order' => 1,
      ));
      $main_id = $db->lastInsertId();

      // Insert main-middle
      $db->insert($this->_contentTable, array(
          'type' => 'container',
          'name' => 'middle',
          'page_id' => $page_id,
          'parent_content_id' => $main_id,
      ));
      $main_middle_id = $db->lastInsertId();

      // Insert content
      $db->insert($this->_contentTable, array(
          'type' => 'widget',
          'name' => 'sitereview.discussion-content',
          'page_id' => $page_id,
          'parent_content_id' => $main_middle_id,
          'order' => 1,
      ));
    }
  }

  public function addEditorProfilePage() {
    $db = Engine_Db_Table::getDefaultAdapter();
		//EDITOR PROFILE PAGE
		$page_id = $db->select()
						->from($this->_pagesTable, 'page_id')
						->where('name = ?', "sitereview_editor_profile")
						->limit(1)
						->query()
						->fetchColumn();

		if (!$page_id) {

			$containerCount = 0;
			$widgetCount = 0;

			$db->insert($this->_pagesTable, array(
					'name' => "sitereview_editor_profile",
					'displayname' => 'Multiple Listing Types - Editor Profile',
					'title' => 'Editor Profile',
					'description' => 'This is the editor profile page.',
					'custom' => 0,
			));
			$page_id = $db->lastInsertId();

			//MAIN CONTAINER
			$db->insert($this->_contentTable, array(
					'page_id' => $page_id,
					'type' => 'container',
					'name' => 'main',
					'order' => $containerCount++,
					'params' => '',
			));
			$main_container_id = $db->lastInsertId();

			//MIDDLE CONTAINER  
			$db->insert($this->_contentTable, array(
					'page_id' => $page_id,
					'type' => 'container',
					'name' => 'middle',
					'parent_content_id' => $main_container_id,
					'order' => $containerCount++,
					'params' => '',
			));
			$main_middle_id = $db->lastInsertId();

			$db->insert($this->_contentTable, array(
					'page_id' => $page_id,
					'type' => 'widget',
					'name' => 'sitereview.editor-photo-sitereview',
					'parent_content_id' => $main_middle_id,
					'order' => $widgetCount++,
					'params' => '',
			));

			$db->insert($this->_contentTable, array(
					'page_id' => $page_id,
					'type' => 'widget',
					'name' => 'sitemobile.container-tabs-columns',
					'parent_content_id' => $main_middle_id,
					'order' => $widgetCount++,
					'params' => '{"layoutContainer":"tab","title":""}',
			));
			$tab_id = $db->lastInsertId();

			$db->insert($this->_contentTable, array(
					'page_id' => $page_id,
					'type' => 'widget',
					'name' => 'sitereview.editor-profile-reviews-sitereview',
					'parent_content_id' => $tab_id,
					'order' => $widgetCount++,
					'params' => '{"title":"Reviews As Editor","type":"editor"}',
			));

			$db->insert($this->_contentTable, array(
					'page_id' => $page_id,
					'type' => 'widget',
					'name' => 'sitereview.editor-profile-reviews-sitereview',
					'parent_content_id' => $tab_id,
					'order' => $widgetCount++,
					'params' => '{"title":"Reviews As User","type":"user", "onlyListingtypeEditorReviews":"0"}',
			));

			$db->insert($this->_contentTable, array(
					'page_id' => $page_id,
					'type' => 'widget',
					'name' => 'sitereview.editor-replies-sitereview',
					'parent_content_id' => $tab_id,
					'order' => $widgetCount++,
					'params' => '{"title":"Comments", "onlyListingtypeEditor":"0"}',
			));

			$db->insert($this->_contentTable, array(
					'page_id' => $page_id,
					'type' => 'widget',
					'name' => 'sitereview.editors-sitereview',
					'parent_content_id' => $tab_id,
					'order' => $widgetCount++,
					'params' => '{"title":"Site Editors","listingtype_id":"-1","nomobile":"1"}',
			));
		}
  }

  public function addVideoViewPage() {
    $db = Engine_Db_Table::getDefaultAdapter();

    $page_id = Engine_Api::_()->getApi('modules', 'sitemobile')->getPageId('sitereview_video_view');
    // insert if it doesn't exist yet
    if (!$page_id) {
      // Insert page
      $db->insert($this->_pagesTable, array(
          'name' => 'sitereview_video_view',
          'displayname' => 'Multiple Listing Types - Video View Page',
          'title' => 'Reviews Video View Page',
          'description' => 'This is the review video view page.',
          'custom' => 0,
      ));
      $page_id = $db->lastInsertId();

      // Insert main
      $db->insert($this->_contentTable, array(
          'type' => 'container',
          'name' => 'main',
          'page_id' => $page_id,
          'order' => 1,
      ));
      $main_id = $db->lastInsertId();

      // Insert main-middle
      $db->insert($this->_contentTable, array(
          'type' => 'container',
          'name' => 'middle',
          'page_id' => $page_id,
          'parent_content_id' => $main_id,
      ));
      $main_middle_id = $db->lastInsertId();

      // Insert content
      $db->insert($this->_contentTable, array(
          'type' => 'widget',
          'name' => 'sitereview.video-content',
          'page_id' => $page_id,
          'parent_content_id' => $main_middle_id,
          'order' => 1,
      ));
    }
  }
  
  //Create Review Profile Page
  
  public function addReviewProfilePage(){
    //REVIEW PROFILE PAGE
    $pageTable = $this->_pagesTable;
    $contentTable = $this->_contentTable;
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
$page_id = $db->select()
        ->from($pageTable, 'page_id')
        ->where('name = ?', "sitereview_review_view")
        ->limit(1)
        ->query()
        ->fetchColumn();

    //CREATE PAGE IF NOT EXIST
    if (!$page_id) {

      $containerCount = 0;
      $widgetCount = 0;

      $db->insert($pageTable, array(
          'name' => "sitereview_review_view",
          'displayname' => 'Multiple Listing Types - Review Profile',
          'title' => 'Review Profile',
          'description' => 'This is the review profile page.',
          'custom' => 0,
      ));
      $page_id = $db->lastInsertId();

      //TOP CONTAINER
      $db->insert($contentTable, array(
          'type' => 'container',
          'name' => 'top',
          'page_id' => $page_id,
          'order' => $containerCount++,
      ));
      $top_container_id = $db->lastInsertId();

      //MAIN CONTAINER
      $db->insert($contentTable, array(
          'type' => 'container',
          'name' => 'main',
          'page_id' => $page_id,
          'order' => $containerCount++,
      ));
      $main_container_id = $db->lastInsertId();

      //INSERT TOP-MIDDLE
      $db->insert($contentTable, array(
          'type' => 'container',
          'name' => 'middle',
          'page_id' => $page_id,
          'parent_content_id' => $top_container_id,
          'order' => $containerCount++,
      ));
      $top_middle_id = $db->lastInsertId();


      //MAIN-MIDDLE CONTAINER
      $db->insert($contentTable, array(
          'type' => 'container',
          'name' => 'middle',
          'page_id' => $page_id,
          'parent_content_id' => $main_container_id,
          'order' => $containerCount++,
      ));
      $main_middle_id = $db->lastInsertId();

  //PLACE CATEGORY WIDGET FOR MOBILE APP AND TABLET APP.
      if($pageTable != 'engine4_sitemobileapp_pages' && $pageTable != 'engine4_sitemobileapp_tablet_pages') 
      $db->insert($contentTable, array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.profile-review-breadcrumb-sitereview',
          'parent_content_id' => $top_middle_id,
          'order' => $widgetCount++,
          'params' => '{"nomobile":"1"}',
      ));


      $db->insert($contentTable, array(
          'page_id' => $page_id,
          'type' => 'widget',
          'name' => 'sitereview.profile-review-sitereview',
          'parent_content_id' => $main_middle_id,
          'order' => $widgetCount++,
          'params' => '{"title":"","titleCount":true,"loaded_by_ajax":"1","name":"sitereview.profile-review-sitereview"}',
      )); 
    }
    
  }
  
   //MEMBER PROFILE PAGE WIDGETS
  public function addmemberProfilePageWidgets() {
    $pageTable = $this->_pagesTable;
    $contentTable = $this->_contentTable;
    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    //MEMBER PROFILE PAGE WIDGETS
    $page_id = $db->select()
            ->from($pageTable, array('page_id'))
            ->where('name =?', 'user_profile_index')
            ->limit(1)
            ->query()
            ->fetchColumn();

    if (!empty($page_id)) {

      $tab_id = $db->select()
              ->from($contentTable, array('content_id'))
              ->where('page_id =?', $page_id)
              ->where('type = ?', 'widget')
              ->where('name = ?', 'sitemobile.container-tabs-columns')
              ->limit(1)
              ->query()
              ->fetchColumn();

      if (!empty($tab_id)) {

        $content_id = $db->select()
                ->from($contentTable, array('content_id'))
                ->where('page_id =?', $page_id)
                ->where('type = ?', 'widget')
                ->where('name = ?', 'sitereview.profile-sitereview')
                ->limit(1)
                ->query()
                ->fetchColumn();

        if (empty($content_id)) {
          $db->insert($contentTable, array(
              'page_id' => $page_id,
              'type' => 'widget',
              'name' => 'sitereview.profile-sitereview',
              'parent_content_id' => $tab_id,
              'order' => 999,
              'params' => '{"title":"Listings","titleCount":"true","statistics":["viewCount","likeCount","commentCount","reviewCount"]}',
          ));
        }
//::- NOT ADD Widget on USER Profile Page at installation
//        $content_id = $db->select()
//                ->from($contentTable, array('content_id'))
//                ->where('page_id =?', $page_id)
//                ->where('type = ?', 'widget')
//                ->where('name = ?', 'sitereview.editor-profile-reviews-sitereview')
//                ->limit(1)
//                ->query()
//                ->fetchColumn();
//
//        if (empty($content_id)) {
//
//          $db->insert($contentTable, array(
//              'page_id' => $page_id,
//              'type' => 'widget',
//              'name' => 'sitereview.editor-profile-reviews-sitereview',
//              'parent_content_id' => $tab_id,
//              'order' => 999,
//              'params' => '{"title":"Reviews As Editor","type":"editor"}',
//          ));
//
//          $db->insert($contentTable, array(
//              'page_id' => $page_id,
//              'type' => 'widget',
//              'name' => 'sitereview.editor-profile-reviews-sitereview',
//              'parent_content_id' => $tab_id,
//              'order' => 999,
//              'params' => '{"title":"Reviews As User","type":"user", "onlyListingtypeEditorReviews":"1"}',
//          ));
//        }
      }
    }
  }
}