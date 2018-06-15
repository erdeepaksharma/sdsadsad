<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Widget_VideoContentController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    //GET VIDEO ID AND OBJECT
    $video_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('video_id', $this->_getParam('video_id', null));
    $sitereview_video = Engine_Api::_()->getItem('sitereview_video', $video_id);

    if (empty($sitereview_video)) {
      return $this->setNoRender();
    }

    //GET TAB ID
    $this->view->tab_selected_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('content_id');

    //GET VIEWER INFO
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();

    //IF THIS IS SENDING A MESSAGE ID, THE USER IS BEING DIRECTED FROM A CONVERSATION
    //CHECK IF MEMBER IS PART OF THE CONVERSATION
    $message_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('message');
    $message_view = false;
    if ($message_id) {
      $conversation = Engine_Api::_()->getItem('messages_conversation', $message_id);
      if ($conversation->hasRecipient(Engine_Api::_()->user()->getViewer()))
        $message_view = true;
    }
    $this->view->message_view = $message_view;

    //SET SITEREVIEW SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $sitereview_video->listing_id);

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);

    $this->view->allowView = $sitereview_video->authorization()->isAllowed($viewer, "view");

    $can_edit = $this->view->can_edit = $sitereview->authorization()->isAllowed($viewer, "edit_listtype_$sitereview->listingtype_id");
    if (empty($can_edit) && $viewer_id == $sitereview_video->owner_id) {
      $this->view->can_edit = $can_edit = 1;
    }

    if ($viewer_id != $sitereview_video->owner_id && $can_edit != 1 && ($sitereview_video->status != 1 || $sitereview_video->search != 1)) {
      return $this->setNoRender();
    }

    //GET VIDEO TAGS
    $this->view->videoTags = $sitereview_video->tags()->getTagMaps();

    //CHECK IF EMBEDDING IS ALLOWED
    $can_embed = true;
    if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.video.embeds', 1)) {
      $can_embed = false;
    } else if (isset($sitereview_video->allow_embed) && !$sitereview_video->allow_embed) {
      $can_embed = false;
    }
    $this->view->can_embed = $can_embed;

    $this->view->videoEmbedded = $embedded = "";

    //INCREMENT IN NUMBER OF VIEWS
    $owner = $sitereview_video->getOwner();
    if (!$owner->isSelf($viewer)) {
      $sitereview_video->view_count++;
    }
    $sitereview_video->save();

    if ($sitereview_video->type != 3) {
      $this->view->videoEmbedded = $embedded = $sitereview_video->getRichContent(true);
    }

    //SET LISTING-VIDEO SUBJECT
    if (Engine_Api::_()->core()->hasSubject()) {
      Engine_Api::_()->core()->clearSubject();
    }
    Engine_Api::_()->core()->setSubject($sitereview_video);

    //VIDEO FROM MY COMPUTER WORK
    if ($sitereview_video->type == 3 && $sitereview_video->status != 0) {
      $sitereview_video->save();

      if (!empty($sitereview_video->file_id)) {
        $storage_file = Engine_Api::_()->getItem('storage_file', $sitereview_video->file_id);
        if ($storage_file) {
          $this->view->video_location = $storage_file->map();
          $this->view->video_extension = $storage_file->extension;
        }
      }
    }

    $this->view->rating_count = Engine_Api::_()->getDbTable('videoratings', 'sitereview')->ratingCount($sitereview_video->getIdentity());
    $this->view->video = $sitereview_video;
    $this->view->rated = Engine_Api::_()->getDbTable('videoratings', 'sitereview')->checkRated($sitereview_video->getIdentity(), $viewer->getIdentity());

    //TAG WORK
    $this->view->limit_sitereview_video = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereviewvideo.tag.limit', 3);

    //VIDEO TABLE
    $videoTable = Engine_Api::_()->getDbtable('videos', 'sitereview');

    //TOTAL VIDEO COUNT FOR THIS LISTING
    $this->view->count_video = $counter = $videoTable->getListingVideoCount($sitereview_video->listing_id);
    
    $this->view->can_create = Engine_Api::_()->sitereview()->allowVideo($sitereview, $viewer, $counter, $uploadVideo = 1);
  }

}