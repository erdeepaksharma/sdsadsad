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
class Sitereview_Plugin_Core extends Zend_Controller_Plugin_Abstract {

  public function onRenderLayoutDefault($event) {
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $view->headTranslate(array("now", 'in a few seconds', 'a few seconds ago', '%s minute ago', 'in %s minute', '%s hour ago', 'in %s hour', '%s at %s', 'Compare All', 'Remove All', 'Compare', 'Show Compare Bar', 'Please select more than one entry for the comparison.', 'Hide Compare Bar'));
  }

  public function onStatistics($event) {

    $table = Engine_Api::_()->getDbTable('listings', 'sitereview');
    $select = new Zend_Db_Select($table->getAdapter());
    $select->from($table->info('name'), 'COUNT(*) AS count');
    $event->addResponse($select->query()->fetchColumn(0), 'listing');
  }

  public function onRenderLayoutMobileSMDefault($event) {
    $view = $event->getPayload();
    if (!($view instanceof Zend_View_Interface)) {
      return;
    }
    $view->headScriptSM()
            ->appendFile($view->layout()->staticBaseUrl . 'application/modules/Sitereview/externals/scripts/sitemobile/core.js');
  }
 //DELETE USERS BELONGINGS BEFORE THAT USER DELETION
  public function onItemDeleteBefore($event) {

    $item = $event->getPayload();
    if ($item instanceof Video_Model_Video) {
      Engine_Api::_()->getDbtable('clasfvideos', 'sitereview')->delete(array('video_id = ?' => $item->getIdentity()));
    }
  }

  public function onUserDeleteBefore($event) {

    //GET VIEWER ID
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    $payload = $event->getPayload();
    if ($payload instanceof User_Model_User) {

      //VIDEO TABLE
      $sitereviewvideoTable = Engine_Api::_()->getDbtable('videos', 'sitereview');
      $sitereviewvideoSelect = $sitereviewvideoTable->select()->where('owner_id = ?', $payload->getIdentity());

      //RATING TABLE
      $ratingTable = Engine_Api::_()->getDbtable('videoratings', 'sitereview');

      foreach ($sitereviewvideoTable->fetchAll($sitereviewvideoSelect) as $sitereviewvideo) {
        $ratingTable->delete(array('videorating_id = ?' => $sitereviewvideo->video_id));
        $sitereviewvideo->delete();
      }

      $ratingSelect = $ratingTable->select()->where('user_id = ?', $payload->getIdentity());
      $ratingVideoDatas = $ratingTable->fetchAll($ratingSelect)->toArray();

      if (!empty($ratingVideoDatas)) {
        foreach ($ratingVideoDatas as $ratingvideo) {
          $ratingTable->delete(array('user_id = ?' => $ratingvideo['user_id']));
          $video_id = $ratingvideo['videorating_id'];
          $avg_rating = $ratingTable->rateVideo($ratingvideo['videorating_id']);
          $sitereviewvideoTable->update(array('rating' => $avg_rating), array('video_id = ?' => $ratingvideo['videorating_id']));
        }
      }

      //DELETE SITEREVIEWS
      $sitereviewTable = Engine_Api::_()->getDbtable('listings', 'sitereview');
      $sitereviewSelect = $sitereviewTable->select()->where('owner_id = ?', $payload->getIdentity());
      foreach ($sitereviewTable->fetchAll($sitereviewSelect) as $sitereview) {
        $sitereview->delete();
      }

      //DELETE REVIEWS
      $sitereviewTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
      $sitereviewSelect = $sitereviewTable->select()->where('owner_id = ?', $payload->getIdentity())->where('type in (?)', array('user', 'visitor'));
      foreach ($sitereviewTable->fetchAll($sitereviewSelect) as $sitereview) {
        $sitereview->delete();
      }

      //DELETE WISHLISTS
      $wishlistTable = Engine_Api::_()->getDbtable('wishlists', 'sitereview');
      $wishlistSelect = $wishlistTable->select()->where('owner_id = ?', $payload->getIdentity());
      foreach ($wishlistTable->fetchAll($wishlistSelect) as $wishlist) {
        $wishlist->delete();
      }

      //LIKE COUNT DREASE FORM LISTING TABLE.
      $likesTable = Engine_Api::_()->getDbtable('likes', 'core');
      $likesTableSelect = $likesTable->select()->where('poster_id = ?', $payload->getIdentity())->Where('resource_type = ?', 'sitereview_listing');
      $results = $likesTable->fetchAll($likesTableSelect);
      foreach ($results as $user) {
        $resource = Engine_Api::_()->getItem('sitereview_listing', $user->resource_id);
        $resource->like_count--;
        $resource->save();
      }

      //COMMENT COUNT DECREASE FORM LISTING TABLE.
      $commentsTable = Engine_Api::_()->getDbtable('comments', 'core');
      $commentsTableSelect = $commentsTable->select()->where('poster_id = ?', $payload->getIdentity())->Where('resource_type = ?', 'sitereview_listing');
      $results = $commentsTable->fetchAll($commentsTableSelect);
      foreach ($results as $user) {
        $resource = Engine_Api::_()->getItem('sitereview_listing', $user->resource_id);
        $resource->comment_count--;
        $resource->save();
      }

      $commentsTableSelect = $commentsTable->select()->where('poster_id = ?', $payload->getIdentity())->Where('resource_type = ?', 'sitereview_review');
      $results = $commentsTable->fetchAll($commentsTableSelect);
      foreach ($results as $user) {
        $resource = Engine_Api::_()->getItem('sitereview_review', $user->resource_id);
        $resource->comment_count--;
        $resource->save();
      }

      //LIKE COUNT DREASE FORM LISTING TABLE.
      $likesTable = Engine_Api::_()->getDbtable('likes', 'core');
      $likesTableSelect = $likesTable->select()->where('poster_id = ?', $payload->getIdentity())->Where('resource_type = ?', 'sitereview_review');
      $results = $likesTable->fetchAll($likesTableSelect);
      foreach ($results as $user) {
        $resource = Engine_Api::_()->getItem('sitereview_review', $user->resource_id);
        $resource->like_count--;
        $resource->save();
      }

      //GET EDITOR TABLE
      $editorTable = Engine_Api::_()->getDbTable('editors', 'sitereview');
      $isSuperEditor = $editorTable->getColumnValue($payload->getIdentity(), 'super_editor', 0);

      if ($isSuperEditor) {
        $totalEditors = $editorTable->getEditorsCount(0);

        if ($totalEditors == 2) {
          $editorTable->delete(array('user_id = ?' => $payload->getIdentity()));

          $editor_id = $editorTable->getColumnValue(0, 'editor_id', 0);
          $editor = Engine_Api::_()->getItem('sitereview_editor', $editor_id);
          $editorTable->update(array('super_editor' => 1), array('user_id = ?' => $editor->user_id));

          $listingtypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes();

          foreach ($listingtypes as $listingtype) {

            //IF EDITOR IS NOT EXIST
            $isExist = $editorTable->isEditor($editor->user_id, $listingtype->listingtype_id);
            if (empty($isExist)) {
              $editorNew = $editorTable->createRow();
              $editorNew->user_id = $editor->user_id;
              $editorNew->listingtype_id = $listingtype->listingtype_id;
              $editorNew->designation = $editor->designation;
              $editorNew->details = $editor->details;
              $editorNew->about = $editor->about;
              $editorNew->super_editor = 1;
              $editorNew->save();
            }
          }
        } elseif ($totalEditors == 1) {
          $editorTable->delete(array('user_id = ?' => $payload->getIdentity()));

          $listingtypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes();

          foreach ($listingtypes as $listingtype) {

            //IF EDITOR IS NOT EXIST
            $isExist = $editorTable->isEditor($viewer_id, $listingtype->listingtype_id);
            if (empty($isExist)) {
              $editorNew = $editorTable->createRow();
              $editorNew->user_id = $viewer_id;
              $editorNew->listingtype_id = $listingtype->listingtype_id;
              $editorNew->designation = 'Super Editor';
              $editorNew->details = '';
              $editorNew->about = '';
              $editorNew->super_editor = 1;
              $editorNew->save();
            }
          }
        } else {
          $editorTable->delete(array('user_id = ?' => $payload->getIdentity()));
          $editor_id = $editorTable->getHighestLevelEditorId();
          $editor = Engine_Api::_()->getItem('sitereview_editor', $editor_id);

          $listingtypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes();

          foreach ($listingtypes as $listingtype) {

            //IF EDITOR IS NOT EXIST
            $isExist = $editorTable->isEditor($editor->user_id, $listingtype->listingtype_id);
            if (empty($isExist)) {
              $editorNew = $editorTable->createRow();
              $editorNew->user_id = $editor->user_id;
              $editorNew->listingtype_id = $listingtype->listingtype_id;
              $editorNew->designation = $editor->designation;
              $editorNew->details = $editor->details;
              $editorNew->about = $editor->about;
              $editorNew->super_editor = 1;
              $editorNew->save();
            }
          }
        }
      }

      $super_editor_user_id = $editorTable->getSuperEditor('user_id');

      //GET REVIEW TABLE
      $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
      $reviewTable->update(array('owner_id' => $super_editor_user_id), array('type = ?' => 'editor', 'owner_id = ?' => $payload->getIdentity()));
      Engine_Api::_()->getDbTable('ratings', 'sitereview')->update(array('user_id' => $super_editor_user_id), array('user_id = ?' => $payload->getIdentity(), 'type' => 'editor'));
    }
  }

}