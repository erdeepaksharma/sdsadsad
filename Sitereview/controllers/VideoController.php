<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: VideoController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_VideoController extends Seaocore_Controller_Action_Standard {

  protected $_listingType;

  //COMMON ACTION WHICH CALL BEFORE EVERY ACTION OF THIS CONTROLLER
  public function init() {

    if ($this->_getParam('action', null) == 'browse')
      return;

    $listing_id = $this->_getParam('listing_id');
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', null);

    $type_video = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video', 1);
    $video_id = $this->_getParam('video_id', $this->_getParam('id', null));
    if (!empty($listing_id)) {
      $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
      if (empty($listingtype_id))
        $listingtype_id = $sitereview->listingtype_id;
    }
    if ($type_video) {
      if ($video_id) {
        $video = Engine_Api::_()->getItem('video', $video_id);
        if ($video) {
          Engine_Api::_()->core()->setSubject($video);
        }
      }
    } else {
      if ($video_id) {
        $reviewVideo = Engine_Api::_()->getItem('sitereview_video', $video_id);
        $listing_id = $reviewVideo->listing_id;
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        if (empty($listingtype_id))
          $listingtype_id = $sitereview->listingtype_id;
        if ($reviewVideo) {
          Engine_Api::_()->core()->setSubject($reviewVideo);
        }
      }
    }

    if (!empty($listingtype_id)) {
      Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
      $this->_listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);
      //AUTHORIZATION CHECK
      if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "view_listtype_$listingtype_id")->isValid())
        return;
    }
  }

  //ACTION FOR SHOWING THE VIDEO LISTING
  public function indexAction() {

    //ONLY LOGGED IN USER CAN VIEW THIS PAGE
    if (!$this->_helper->requireUser->isValid())
      return;

    $this->view->listingtype_id = $listingtype_id = $this->_listingType->listingtype_id;
    $this->view->listing_singular_uc = ucfirst($this->_listingType->title_singular);

    //VIDEO CREATION SHOULD BE ALLOWED
    if (!$this->_helper->requireAuth()->setAuthParams('video', null, "create")->isValid())
      return;

    //GET VIEWER
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //GET LISTING
    $this->view->listing_id = $listing_id = $this->_getParam('listing_id');
    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

    //GET CONTENT ID
    $this->view->content_id = $content_id = $this->_getParam('tab');

    //WHO CAN EDIT THE LISTING
    $this->view->canEdit = $canEdit = Engine_Api::_()->authorization()->isAllowed($sitereview, $viewer, "edit_listtype_$listingtype_id");

    //ACTIVE TAB
    $this->view->TabActive = "video";

    //VIDEO UPLOAD IS ALLOWED OR NOT
    $this->view->allowed_upload_video = Engine_Api::_()->sitereview()->allowVideo($sitereview, $viewer);
    if (empty($this->view->allowed_upload_video)) {
      return $this->_forwardCustom('requireauth', 'error', 'core');
    }

    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
      //AUTHORIZATION CHECK
      $allowed_upload_photo = Engine_Api::_()->authorization()->isAllowed($sitereview, $viewer, "auth_photo_listtype_$listingtype_id");
      if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "photo")) {
        $this->view->allowed_upload_photo = $allowed_upload_photo;
      }
      else
        $this->view->allowed_upload_photo = 0;
    }
    else
      $this->view->allowed_upload_photo = Engine_Api::_()->authorization()->isAllowed($sitereview, $viewer, "photo_listtype_$listingtype_id");

    $video = null;
    $values['user_id'] = $viewer_id;

    //COUNT TOTAL VIDEO
    $this->view->videoCount = Engine_Api::_()->sitereview()->getTotalVideo($viewer_id);

    $this->view->video = $video = Engine_Api::_()->getItemTable('sitereview_clasfvideo', 'sitereview')->getListingVideos($listing_id, 0);

    $this->view->message = $message = null;
    $session = new Zend_Session_Namespace();
    if (isset($session->video_message)) {
      $message = $session->video_message;
      unset($session->video_message);
    }

    //UPLOAD VIDEO
    if (isset($_GET['ul']) || isset($_FILES['Filedata'])) {
      return $this->_forwardCustom('upload-video', null, null, array('format' => 'json'));
    }

    //GET VIDEO PAGINATOR
    $values['user_id'] = $viewer_id;
    $paginator = Engine_Api::_()->getApi('core', 'video')->getVideosPaginator($values);
    $this->view->current_count = $paginator->getTotalItemCount();

    //GET TOTAL ALLOWED VIDEO
    $this->view->quota = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'video', 'max');

    //MAKE FORM
    $this->view->form = $form = new Sitereview_Form_Video_Video();

    if ($this->_getParam('type', false)) {
      $form->getElement('type')->setValue($this->_getParam('type'));
    }

    $this->view->display = 0;

    //CHECK POST
    if (!$this->getRequest()->isPost()) {
      return;
    }

    $this->view->display = 1;

    if (!$form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues('url');
      return;
    }

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      //PROCESS
      $values = $form->getValues();
      $values['owner_id'] = $viewer->getIdentity();
      $insert_action = false;
      $db = Engine_Api::_()->getDbtable('videos', 'video')->getAdapter();
      $db->beginTransaction();
      try {

        //CREATE VIDEO
        $table = Engine_Api::_()->getDbtable('videos', 'video');
        if ($values['type'] == 3) {
          $video = Engine_Api::_()->getItem('video', $this->_getParam('id'));
        } else {
          $video = $table->createRow();
        }

        $video->setFromArray($values);
        $video->save();

        $params = $sitereview->main_video;

        //CREATE THUMBNAIL
        $thumbnail = $this->handleThumbnail($video->type, $video->code);
        $ext = ltrim(strrchr($thumbnail, '.'), '.');
        $thumbnail_parsed = @parse_url($thumbnail);

        if (@GetImageSize($thumbnail)) {
          $valid_thumb = true;
        } else {
          $valid_thumb = false;
        }

        if ($valid_thumb && $thumbnail && $ext && $thumbnail_parsed && in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {

          $tmp_file = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
          $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;
          $src_fh = fopen($thumbnail, 'r');
          $tmp_fh = fopen($tmp_file, 'w');
          stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
          $image = Engine_Image::factory();
          $image->open($tmp_file)
                  ->resize(120, 240)
                  ->write($thumb_file)
                  ->destroy();
          try {
            $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
                'parent_type' => $video->getType(),
                'parent_id' => $video->getIdentity()
            ));

            //REMOVE TEMP FILES
            @unlink($thumb_file);
            @unlink($tmp_file);
          } catch (Exception $e) {
            
          }
          $information = $this->handleInformation($video->type, $video->code);

          $video->duration = $information['duration'];
          if (!$video->description)
            $video->description = $information['description'];
          $video->photo_id = $thumbFileRow->file_id;
          $video->status = 1;
          $video->save();

          //INSERT NEW ACTION ITEM
          $insert_action = true;
        }

        if ($values['ignore'] == true) {

          $video->status = 1;
          $video->save();

          //INSERT NEW ACTION ITEM
          $insert_action = true;
          $owner = $video->getOwner();

          //INSERT NEW ACTION ITEM
          $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $video, 'video_new');
          if ($action != null) {
            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
          }
        }

        //CREATE AUTH STUFF HERE
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'everyone');
        if (isset($values['auth_view']))
          $auth_view = $values['auth_view'];
        else
          $auth_view = "everyone";
        $viewMax = array_search($auth_view, $roles);

        foreach ($roles as $i => $role) {
          $auth->setAllowed($video, $role, 'view', ($i <= $viewMax));
        }

        if (isset($values['auth_comment']))
          $auth_comment = $values['auth_comment'];
        else
          $auth_comment = "everyone";
        $commentMax = array_search($auth_comment, $roles);
        foreach ($roles as $i => $role) {
          $auth->setAllowed($video, $role, 'comment', ($i <= $commentMax));
        }

        //ADD TAGS
        $tags = preg_split('/[,]+/', $values['tags']);
        $video->tags()->addTagMaps($viewer, $tags);
        $db->commit();
        $db->beginTransaction();
        try {
          if ($insert_action) {
            $owner = $video->getOwner();
            $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($owner, $video, 'video_new');
            if ($action != null) {
              Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
            }
          }

          //REBUILD PRIVACY
          $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
          foreach ($actionTable->getActionsByObject($video) as $action) {
            $actionTable->resetActivityBindings($action);
          }
          $db->commit();
        } catch (Exception $e) {
          $db->rollBack();
          throw $e;
        }

        $video_id = $video->getIdentity();
        $table = Engine_Api::_()->getItemTable('sitereview_clasfvideo', 'sitereview');
        $select = $table->select();
        $rName = $table->info('name');
        $select->where($rName . '.listing_id = ?', $listing_id);
        $row = $table->fetchAll($select);
        if ($video_id != NULL) {
          try {

            $db = Engine_Db_Table::getDefaultAdapter();
            $db->beginTransaction();

            $row = $table->createRow();
            $row->listing_id = $listing_id;
            $row->created = date('Y-m-d H:i:s');
            $row->video_id = $video_id;
            $row->save();

            $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
            $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
            $subject = $sitereview;
            $subjectOwner = $subject->getOwner('user');

            if (time() >= strtotime($sitereview->creation_date)) {
              $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $subject, 'video_sitereview_listtype_' . $listingtype_id, '', array(
                  'owner' => $subjectOwner->getGuid(),
                  'title' => $subject->getTitle()
              ));

              if ($action != null) {
                Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
              }
            }
            $db->commit();
            unset($_POST);
            if ($canEdit) {
              return $this->_gotoRouteCustom(array('action' => 'edit', 'listing_id' => $listing_id), "sitereview_videospecific_listtype_$listingtype_id", true);
            } else {
              $content_id = $this->_getParam('content_id');
              return $this->_gotoRouteCustom(array('listing_id' => $listing_id, 'slug' => $sitereview->getSlug(), 'tab' => $content_id), "sitereview_entry_view_listtype_$listingtype_id", true);
            }
          } catch (Exception $e) {
            $db->rollBack();
            throw $e;
          }
        }
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
  }

  //ACTION FOR CREATE VIDEO
  public function createAction() {

    //CHECK USER VALIDATION
    if (!$this->_helper->requireUser()->isValid())
      return;
    if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
      // Upload video
      if (isset($_GET['ul']) || (isset($_FILES['Filedata']) && !empty($_FILES['Filedata']['name']))) {
        return $this->_forwardCustom('upload-review-video', null, null, array('format' => 'json'));
      }

      //GET NAVIGATION
      $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sitereview_main');
    }
    //GET LISTING ID
    $this->view->listing_id = $listing_id = $this->_getParam('listing_id');
    $this->view->listing_singular_uc = ucfirst($this->_listingType->title_singular);

    $this->view->listingtype_id = $listingtype_id = $this->_listingType->listingtype_id;

    //ACTIVE TAB
    $this->view->TabActive = "video";

    //GET CONTENT ID
    $this->view->content_id = $content_id = $this->_getParam('content_id');

    $sitereviewModHostName = str_replace('www.', '', strtolower($_SERVER['HTTP_HOST']));

    //GET SITEREVIEW OBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

    //GET VIEWER INFO
    $viewer = Engine_Api::_()->user()->getViewer();

    $videos = array();
    $type_video = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video');
    if (Engine_Api::_()->sitereview()->enableVideoPlugin() && !empty($type_video)) {
      $videos = Engine_Api::_()->getItemTable('sitereview_clasfvideo', 'sitereview')->getListingVideos($sitereview->listing_id, 0, 1);
    } elseif (empty($type_video)) {
      $videos = Engine_Api::_()->getItemTable('sitereview_clasfvideo', 'sitereview')->getListingVideos($sitereview->listing_id, 0, 0);
    }

    $this->view->allowed_upload_video = Engine_Api::_()->sitereview()->allowVideo($sitereview, $viewer, count($videos));
    if (empty($this->view->allowed_upload_video)) {
      return $this->_forwardCustom('requireauth', 'error', 'core');
    }

    $viewer_id = $viewer->getIdentity();

    //VIDEO UPLOAD PROCESS
    $this->view->imageUpload = Engine_Api::_()->sitereview()->isUpload();

    $this->view->can_edit = $canEdit = $sitereview->authorization()->isAllowed($viewer, "edit_listtype_$sitereview->listingtype_id");

    //FORM GENERATON
    $this->view->form = $form = new Sitereview_Form_Video();
    if ($this->_getParam('type', false))
      $form->getElement('type')->setValue($this->_getParam('type'));
    if (!$this->getRequest()->isPost()) {
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues('url');
      return;
    }
    $insert_action = false;
    Engine_Api::_()->getApi('settings', 'core')->setSetting('sitereview.video.utility.type', convert_uuencode($sitereviewModHostName));

    //GET FORM VALUES
    $values = $form->getValues();
$this->view->clear_cache = true;
    $values['owner_id'] = $viewer_id;

    //VIDEO CREATION PROCESS
    $videoTable = Engine_Api::_()->getDbtable('videos', 'sitereview');

    $db = $videoTable->getAdapter();
    $db->beginTransaction();
    try {
      
      if ($values['type'] == 3) {
        if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
          $sitereview_video = Engine_Api::_()->getItem('sitereview_video', $this->_getParam('id'));
        } else {
//          if (empty($values['Filename'])) {
//            $this->view->status = false;
//            $this->view->error = Zend_Registry::get('Zend_Translate')->_('No file');
//            return;
//          }
          if ((!isset($_FILES['Filedata']) && !empty($_FILES['Filedata']['name']))) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload') . print_r($_FILES, true);
            return;
          }

          if (!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload') . print_r($_FILES, true);
            return;
          }

          $illegal_extensions = array('php', 'pl', 'cgi', 'html', 'htm', 'txt');
          if (in_array(pathinfo($_FILES['Filedata']['name'], PATHINFO_EXTENSION), $illegal_extensions)) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
            return;
          }
          $viewer = Engine_Api::_()->user()->getViewer();
          $values['owner_id'] = $viewer->getIdentity();

          $params = array(
              'owner_type' => 'user',
              'owner_id' => $viewer->getIdentity()
          );
          $sitereview_video = Engine_Api::_()->sitereview()->createSitereviewvideo($params, $_FILES['Filedata'], $values);
          $sitereview_video->owner_id = $viewer->getIdentity();
          $sitereview_video->save();
          $this->view->status = true;
          $this->view->name = $_FILES['Filedata']['name'];
          $this->view->code = $sitereview_video->code;
          $this->view->video_id = $sitereview_video->getIdentity();

           $sitereview_video->save();
        }
      } else {
        $sitereview_video = $videoTable->createRow();
      }

      $sitereview_video->setFromArray($values);
      $sitereview_video->listing_id = $this->_getParam('listing_id');
      $sitereview_video->save();

      $params = $sitereview->main_video;

      //THUMBNAIL CREATION
      $thumbnail = $this->handleThumbnail($sitereview_video->type, $sitereview_video->code);
      $ext = ltrim(strrchr($thumbnail, '.'), '.');
      $thumbnail_parsed = @parse_url($thumbnail);

      if (@GetImageSize($thumbnail)) {
        $valid_thumb = true;
      } else {
        $valid_thumb = false;
      }

      if ($valid_thumb && $thumbnail && $ext && $thumbnail_parsed && in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {
        $tmp_file = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
        $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;
        $src_fh = fopen($thumbnail, 'r');
        $tmp_fh = fopen($tmp_file, 'w');
        stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
        $image = Engine_Image::factory();
        $image->open($tmp_file)
                ->resize(120, 240)
                ->write($thumb_file)
                ->destroy();

        try {
          $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
              'parent_type' => $sitereview_video->getType(),
              'parent_id' => $sitereview_video->getIdentity()
          ));

          //REMOVE TEMP FILES
          @unlink($thumb_file);
          @unlink($tmp_file);
        } catch (Exception $e) {
          
        }
        $information = $this->handleInformation($sitereview_video->type, $sitereview_video->code);
        $sitereview_video->duration = $information['duration'];
        $sitereview_video->photo_id = $thumbFileRow->file_id;
        $sitereview_video->status = 1;
        $sitereview_video->save();

        //INSERT NEW ACTION ITEM
        $insert_action = true;
      }

      if ($values['ignore'] == true) {
        $sitereview_video->status = 1;
        $sitereview_video->save();
        $insert_action = true;
      }

      //COMMENT PRIVACY
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'everyone');
      $auth_comment = "everyone";
      $commentMax = array_search($auth_comment, $roles);
      foreach ($roles as $i => $role) {
        $auth->setAllowed($sitereview_video, $role, 'comment', ($i <= $commentMax));
      }

      //TAG WORK
      if (!empty($values['tags'])) {
        $tags = preg_split('/[,]+/', $values['tags']);
        $sitereview_video->tags()->addTagMaps($viewer, $tags);
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $db->beginTransaction();
    try {
      if ($insert_action && $sitereview_video->search == 1 && time() >= strtotime($sitereview->creation_date)) {
        $owner = $sitereview_video->getOwner();
        $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
        $action = $actionTable->addActivity($owner, $sitereview, 'sitereview_video_new_listtype_' . $sitereview->listingtype_id);
        if ($action != null) {
          $actionTable->attachActivity($action, $sitereview_video);
        }
      }

      $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
      foreach ($actionTable->getActionsByObject($sitereview_video) as $action) {
        $actionTable->resetActivityBindings($action);
      }

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
      if ($canEdit) {
        return $this->_gotoRouteCustom(array('action' => 'edit', 'listing_id' => $sitereview->listing_id), "sitereview_videospecific_listtype_$listingtype_id", true);
      } else {
        return $this->_gotoRouteCustom(array('listing_id' => $sitereview->listing_id, 'slug' => $sitereview->getSlug(), 'tab' => $content_id), "sitereview_entry_view_listtype_$listingtype_id", true);
      }
    } else {
      return $this->_gotoRouteCustom(array('listing_id' => $sitereview->listing_id, 'slug' => $sitereview->getSlug(), 'tab' => $content_id), "sitereview_entry_view_listtype_$listingtype_id", true);
    }
  }

  //ACTION FOR EDIT VIDEO
  public function editAction() {

    //CHECK USER VALIDATION
    if (!$this->_helper->requireUser()->isValid()) {
      return;
    }

    //GET LISTING ID
    $listing_id = $this->_getParam('listing_id', 0);

    $listingtype_id = $this->_listingType->listingtype_id;

    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

    //GET VIDEO OBJECT
    $sitereview_video = Engine_Api::_()->getItem('sitereview_video', $this->_getParam('video_id'));

    //GET TAB ID
    $this->view->tab_selected_id = $this->_getParam('content_id');

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    $this->view->can_edit = $canEdit = $sitereview->authorization()->isAllowed($viewer, "edit_listtype_$sitereview->listingtype_id");

    //SUPERADMIN, VIDEO OWNER AND LISTING OWNER CAN EDIT VIDEO
    if ($viewer_id != $sitereview_video->owner_id && $canEdit != 1) {
      return $this->_forwardCustom('requireauth', 'error', 'core');
    }

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sitereview_main');

    //FORM GENERATION
    $this->view->form = $form = new Sitereview_Form_Editvideo();

    //PREPARE TAGS
    $sitereviewTags = $sitereview_video->tags()->getTagMaps();
    $tagString = '';
    foreach ($sitereviewTags as $tagmap) {
      if ($tagString !== '') {
        $tagString .= ', ';
      }
      $tagString .= $tagmap->getTag()->getTitle();
    }
    $this->view->tagNamePrepared = $tagString;
    $form->tags->setValue($tagString);

    //IF NOT POST OR FORM NOT VALID THAN RETURN
    if (!$this->getRequest()->isPost()) {
      $form->populate($sitereview_video->toArray());
      return;
    }

    //IF NOT POST OR FORM NOT VALID THAN RETURN
    if (!$form->isValid($this->getRequest()->getPost())) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data');
      return;
    }

    //GET FORM VALUES
    $values = $form->getValues();

    //PROCESS
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try {
      $sitereview_video->setFromArray($values);

      // Add tags
      $tags = preg_split('/[,]+/', $values['tags']);
      $sitereview_video->tags()->setTagMaps($viewer, $tags);
      $sitereview_video->save();

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    return $this->_gotoRouteCustom(array('listing_id' => $sitereview->listing_id, 'slug' => $sitereview_video->getSlug(), 'video_id' => $sitereview_video->getIdentity(), 'user_id' => $sitereview_video->owner_id, 'content_id' => $this->view->tab_selected_id), "sitereview_video_view_listtype_$listingtype_id", true);
  }

  //ACTION FOR DELETE VIDEO
  public function deleteAction() {

    //CHECK USER VALIDATION
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET VIEWER INFO
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    $listingtype_id = $this->_listingType->listingtype_id;
    $this->view->listingType = $this->_listingType;

    //GET TAB ID
    $this->view->tab_selected_id = $tab_selected_id = $this->_getParam('content_id');
    $this->view->format_form = $this->_getParam('format', null);

    //GET VIDEO OBJECT
    $this->view->sitereview_video = $sitereview_video = Engine_Api::_()->getItem('sitereview_video', $this->getRequest()->getParam('video_id'));

    //GET VIDEO TITLE
    $this->view->title = $sitereview_video->title;

    //GET LISTING ID
    $listing_id = $sitereview_video->listing_id;

    //GET NAVIGATION 
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sitereview_main');

    //GET SITEREVIEW SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
    $can_edit = $sitereview->authorization()->isAllowed($viewer, 'edit_listtype_' . $sitereview->listingtype_id);

    if (!$sitereview_video) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Video doesn't exists or not authorized to delete");
      return;
    }

    //VIDEO OWNER AND LISTING OWNER CAN DELETE VIDEO
    if ($viewer_id != $sitereview_video->owner_id && $can_edit != 1) {
      return $this->_forwardCustom('requireauth', 'error', 'core');
    }

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    $db = $sitereview_video->getTable()->getAdapter();
    $db->beginTransaction();

    try {

      Engine_Api::_()->getDbtable('videoratings', 'sitereview')->delete(array('videorating_id =?' => $this->getRequest()->getParam('video_id')));

      $sitereview_video->delete();

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }





    $this->view->status = true;
    if ($this->view->format_form == 'smoothbox') {
      $this->_forwardCustom('success', 'utility', 'core', array(
          'smoothboxClose' => true,
          'parentRefresh' => '500',
          'parentRefreshTime' => '500',
          'format' => 'smoothbox',
          'messages' => Zend_Registry::get('Zend_Translate')->_('You have successfully deleted this video.')
      ));
    } else {
      if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
        if ($can_edit) {
          return $this->_gotoRouteCustom(array('action' => 'edit', 'listing_id' => $sitereview->listing_id), "sitereview_videospecific_listtype_$listingtype_id", true);
        } else {
          return $this->_gotoRouteCustom(array('listing_id' => $sitereview->listing_id, 'slug' => $sitereview->getSlug(), 'tab' => $tab_selected_id), "sitereview_entry_view_listtype_$listingtype_id", true);
        }
      } else {
        return $this->_gotoRouteCustom(array('listing_id' => $sitereview->listing_id, 'slug' => $sitereview->getSlug(), 'tab' => $tab_selected_id), "sitereview_entry_view_listtype_$listingtype_id", true);
      }
    }
  }

  //ACTION FOR VIEW VIDEO
  public function viewAction() {

    //IF SITEREVIEWVIDEO SUBJECT IS NOT THEN RETURN
    if (!$this->_helper->requireSubject('sitereview_video')->isValid())
      return;

    //GET LOGGED IN USER INFORMATION
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //GET VIDEO ITEM
    $sitereview_video = Engine_Api::_()->getItem('sitereview_video', $this->getRequest()->getParam('video_id'));

    //GET SITEREVIEW ITEM
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $sitereview_video->listing_id);
    $this->view->can_create = 1;
    $can_edit = 1;

    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
      if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "video"))
        return $this->_forwardCustom('requireauth', 'error', 'core');
    }

    //CHECKING THE USER HAVE THE PERMISSION TO VIEW THE VIDEO OR NOT
    if ($viewer_id != $sitereview_video->owner_id && $can_edit != 1 && ($sitereview_video->search != 1 || $sitereview_video->status != 1)) {
      return $this->_forwardCustom('requireauth', 'error', 'core');
    }

    $params = array();

    //GET LISTING CATEGORY TABLE
    $tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');

    $category_id = $sitereview->category_id;
    if (!empty($category_id)) {

      $params['categoryname'] = Engine_Api::_()->getItem('sitereview_category', $category_id)->getCategorySlug();

      $subcategory_id = $sitereview->subcategory_id;

      if (!empty($subcategory_id)) {

        $params['subcategoryname'] = Engine_Api::_()->getItem('sitereview_category', $subcategory_id)->getCategorySlug();

        $subsubcategory_id = $sitereview->subsubcategory_id;

        if (!empty($subsubcategory_id)) {

          $params['subsubcategoryname'] = Engine_Api::_()->getItem('sitereview_category', $subsubcategory_id)->getCategorySlug();
        }
      }
    }

    $params['location'] = $sitereview->location;

    $params['tag'] = $sitereview->getKeywords(', ');

    $params['listing_type_title'] = ucfirst($this->_listingType->title_plural);

    $params['listing_title'] = $sitereview->getTitle();

    //SET META KEYWORDS
    Engine_Api::_()->sitereview()->setMetaKeywords($params);

    //CHECK THE VERSION OF THE CORE MODULE
    $coremodule = Engine_Api::_()->getDbtable('modules', 'core')->getModule('core');
    $coreversion = $coremodule->version;
    $checkVersion = Engine_Api::_()->sitereview()->checkVersion($coreversion, '4.1.0');
    if ($checkVersion == 0) {
      $this->_helper->content->render();
    } else {
      $this->_helper->content
              ->setNoRender()
              ->setEnabled()
      ;
    }
  }

  //ACTION FOR DO RATING
  public function rateAction() {

    $user_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    $rating = $this->_getParam('rating');
    $video_id = $this->_getParam('video_id');

    $ratingTable = Engine_Api::_()->getDbtable('videoratings', 'sitereview');
    $db = $ratingTable->getAdapter();
    $db->beginTransaction();

    try {

      $ratingTable->setRating($video_id, $user_id, $rating);

      $total = $ratingTable->ratingCount($video_id);

      $sitereview_video = Engine_Api::_()->getItem('sitereview_video', $video_id);

      //UPDATE CURRENT AVERAGE RATING IN VIDEO TABLE
      $rating = $ratingTable->rateVideo($video_id);

      $sitereview_video->rating = $rating;
      $sitereview_video->save();

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    $data = array();
    $data[] = array(
        'total' => $total,
        'rating' => $rating,
    );
    return $this->_helper->json($data);
    $data = Zend_Json::encode($data);
    $this->getResponse()->setBody($data);
  }

  public function browseAction() {

    // Get navigation
    $this->view->navigation = Engine_Api::_()
            ->getApi('menus', 'core')
            ->getNavigation('sitereview_video_main', array(), 'sitereview_video_main_browse');

    //GET LOGGED IN USER INFORMATION
    $this->view->viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    // Make form
    $this->view->form = $form = new Sitereview_Form_Searchvideo();

    if (!empty($this->_listingType))
      $this->view->listingtype_id = $this->_listingType->listingtype_id;

    // Process form
    if ($form->isValid($this->_getAllParams())) {
      $values = $form->getValues();
    } else {
      $values = array();
    }
    $this->view->formValues = $values;
    $values['search'] = 1;
    $values['status'] = 1;
    $this->view->category = @$values['category'];
    $this->view->text = @$values['text'];

    if (!empty($values['tag'])) {
      $this->view->tag = Engine_Api::_()->getItem('core_tag', $values['tag'])->text;
    }

    // check to see if request is for specific user's listings
    $user_id = $this->_getParam('user');
    if ($user_id) {
      $values['user_id'] = $user_id;
    }
    //VIDEO CREATION PROCESS
    $videoTable = Engine_Api::_()->getDbtable('videos', 'sitereview');
    $this->view->paginator = $paginator = $videoTable->getVideosPaginator($values);
    $items_count = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('video.page', 20);
    $paginator->setItemCountPerPage($items_count);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }

  //ACTION FOR AUTO SUGGEST SEARCH BASED ON VIDEO TITLE
  public function suggestAction() {

    //GET DETAILS
    $params = array();
    $params['viewer_id'] = Engine_Api::_()->user()->getViewer()->getIdentity();
    $params['text'] = $this->_getParam('text');
    $params['limit'] = $this->_getParam('limit', 40);

    //FETCH RESULTS
    $videoSitereviews = Engine_Api::_()->sitereview()->getAutoSuggestedVideo($params);

    $data = array();
    $mode = $this->_getParam('struct');

    if ($mode == 'text') {
      foreach ($videoSitereviews as $videositereview) {
        $content_photo = $this->view->itemPhoto($videositereview, 'thumb.icon');
        $data[] = array(
            'id' => $videositereview->video_id,
            'label' => $videositereview->title,
            'photo' => $content_photo
        );
      }
    } else {
      foreach ($videoSitereviews as $videositereview) {
        $content_photo = $this->view->itemPhoto($videositereview, 'thumb.icon');
        $data[] = array(
            'id' => $videositereview->video_id,
            'label' => $videositereview->title,
            'photo' => $content_photo
        );
      }
    }

    if ($this->_getParam('sendNow', true)) {
      return $this->_helper->json($data);
    } else {
      $this->_helper->viewRenderer->setNoRender(true);
      $data = Zend_Json::encode($data);
      $this->getResponse()->setBody($data);
    }
  }

  //stored the selected search  video title into clasfvideo
  public function loadAction() {

    $url = '';
    $this->view->listing_id = $listing_id = $this->_getParam('listing_id');
    $listingtype_id = $this->_getParam('listingtype_id', null);
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

    //GET LOGGED IN USER INFORMATION
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //PROCESS
    $coreVideoTable = Engine_Api::_()->getDbtable('videos', 'video');
    $select = $coreVideoTable->select()
            ->order('video_id DESC');
    $rName = $coreVideoTable->info('name');
    $select->where($rName . '.owner_id = ?', $viewer_id);

    if ($this->getRequest()->isPost()) {
      $values = $_POST;

      if (!empty($values['video_id'])) {
        $select->where($rName . '.video_id = ?', $values['video_id']);
      } else {
        $select->where($rName . '.video_id = ?', 0);
      }
    }
    $this->view->uploadvideositereview = $uploadvideositereview = $coreVideoTable->fetchAll($select);

    if (empty($uploadvideositereview) || empty($values['video_id'])) {
      $session = new Zend_Session_Namespace();
      $session->video_message = 'No matching videos were found.';
      $this->_gotoRouteCustom(array('action' => 'index', 'listing_id' => $listing_id), "sitereview_video_upload_listtype_$listingtype_id");
    }

    foreach ($uploadvideositereview as $sitereview) {
      $id = $sitereview->video_id;
    }

    $table = Engine_Api::_()->getItemTable('sitereview_clasfvideo', 'sitereview');

    if ($id != NULL) {
      try {
        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        $row = $table->createRow();
        $row->listing_id = $listing_id;
        $row->created = date('Y-m-d H:i:s');
        $row->video_id = $id;
        $row->save();

        $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');

        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
          Engine_Api::_()->core()->clearSubject();
          Engine_Api::_()->core()->setSubject($sitereview);
        }
        $subject = Engine_Api::_()->core()->getSubject();
        $subjectOwner = $subject->getOwner('user');

        //ACTIVITY
        $video = Engine_Api::_()->getItem('video', $id);
        if (time() >= strtotime($sitereview->creation_date)) {
          $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($viewer, $subject, 'video_sitereview_listtype_' . $listingtype_id, '', array(
              'owner' => $subjectOwner->getGuid(),
              'title' => $subject->getTitle()
          ));
          if ($action != null) {
            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $video);
          }
        }
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }

    if ($sitereview->owner_id == $viewer_id) {
      return $this->_gotoRouteCustom(array('action' => 'edit', 'listing_id' => $listing_id), "sitereview_videospecific_listtype_$listingtype_id", true);
    } else {
      $content_id = $this->_getParam('content_id');
      return $this->_gotoRouteCustom(array('listing_id' => $listing_id, 'slug' => $sitereview->getSlug(), 'tab' => $content_id), "sitereview_entry_view_listtype_$listingtype_id", true);
    }
  }

  public function uploadVideoAction() {

    if (!$this->_helper->requireUser()->checkRequire()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
      return;
    }

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    $values = $this->getRequest()->getPost();

    if (empty($values['Filename'])) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No file');
      return;
    }

    if (!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload') . print_r($_FILES, true);
      return;
    }

    $db = Engine_Api::_()->getDbtable('videos', 'video')->getAdapter();
    $db->beginTransaction();

    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      $values['owner_id'] = $viewer->getIdentity();

      $params = array(
          'owner_type' => 'user',
          'owner_id' => $viewer->getIdentity()
      );
      $video = Engine_Api::_()->video()->createVideo($params, $_FILES['Filedata'], $values);
      $video->owner_id = $viewer->getIdentity();
      $video->save();
      $this->view->status = true;
      $this->view->name = $_FILES['Filedata']['name'];
      $this->view->code = $video->code;
      $this->view->video_id = $video->getIdentity();

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.') . $e;
      return;
    }
  }

  public function uploadReviewVideoAction() {

    if (!$this->_helper->requireUser()->checkRequire()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
      return;
    }

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    $values = $this->getRequest()->getPost();

    if (empty($values['Filename'])) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No file');
      return;
    }

    if (!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload') . print_r($_FILES, true);
      return;
    }

    $db = Engine_Api::_()->getDbtable('videos', 'sitereview')->getAdapter();
    $db->beginTransaction();

    try {
      $viewer = Engine_Api::_()->user()->getViewer();
      $values['owner_id'] = $viewer->getIdentity();

      $params = array(
          'owner_type' => 'user',
          'owner_id' => $viewer->getIdentity()
      );
      $video = Engine_Api::_()->sitereview()->createSitereviewvideo($params, $_FILES['Filedata'], $values);
      $video->owner_id = $viewer->getIdentity();
      $video->save();
      $this->view->status = true;
      $this->view->name = $_FILES['Filedata']['name'];
      $this->view->code = $video->code;
      $this->view->video_id = $video->getIdentity();

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.') . $e;
      return;
    }
  }

  //ACTION FOR UPLOAD VIDEO
  public function uploadAction() {
    if (isset($_GET['ul']) || isset($_FILES['Filedata']))
      return $this->_forwardCustom('upload-video', null, null, array('format' => 'json'));

    if (!$this->_helper->requireUser()->isValid())
      return;

    $this->view->form = $form = new Sitereview_Form_Reviewvideo();

    if (!$this->getRequest()->isPost()) {
      if (null !== ($video_id = $this->_getParam('video_id'))) {
        $form->populate(array(
            'video' => $video_id
        ));
      }
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    $video = $form->saveValues();
  }

  //ACTION FOR VALIDATING FOR VIDEO UPLOAD
  public function validationAction() {

    $video_type = $this->_getParam('type');
    $code = $this->_getParam('code');
    $ajax = $this->_getParam('ajax', false);
    $valid = false;

    //CHECK WHICH API SHOULD BE USED
    if ($video_type == "youtube") {
      $valid = $this->checkYouTube($code);
    }

    if ($video_type == "vimeo") {
      $valid = $this->checkVimeo($code);
    }

    $this->view->code = $code;
    $this->view->ajax = $ajax;
    $this->view->valid = $valid;
  }

  //HELPER FUNCTIONS
  public function extractCode($url, $type) {

    switch ($type) {

      //YOUTUBE
      case "1":
        // change new youtube URL to old one
        $new_code = @pathinfo($url);
        $url = preg_replace("/#!/", "?", $url);

        // get v variable from the url
        $arr = array();
        $arr = @parse_url($url);
        if ($arr['host'] === 'youtu.be') {
          $data = explode("?", $new_code['basename']);
          $code = $data[0];
        } else {
          $parameters = $arr["query"];
          parse_str($parameters, $data);
          $code = $data['v'];
          if ($code == "") {
            $code = $new_code['basename'];
          }
        }
        return $code;

      //VIMEO
      case "2":
        //GET THE FIRST VARIABLE AFTER SLASH
        $code = @pathinfo($url);
        return $code['basename'];
    }
  }

  // YouTube Functions
  public function checkYouTube($code){
    $key = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey');
    if (!$data = @file_get_contents('https://www.googleapis.com/youtube/v3/videos?part=id&id=' . $code . '&key=' . $key)) return false;

    $data = Zend_Json::decode($data);
    if (empty($data['items'])) return false;
    return true;
  }

  //FUNCTION VIMEO
  public function checkVimeo($code) {

    //http://www.vimeo.com/api/docs/simple-api
    //http://vimeo.com/api/v2/video
    $data = @simplexml_load_file("http://vimeo.com/api/v2/video/" . $code . ".xml");
    $id = count($data->video->id);
    if ($id == 0)
      return false;
    return true;
  }

  //FUNCTION FOR HANDLING THUMBNAILS
  public function handleThumbnail($type, $code = null) {
    switch ($type) {

      //YOUTUBE
      case "1":
        //https://i.ytimg.com/vi/Y75eFjjgAEc/default.jpg
        return "https://i.ytimg.com/vi/$code/default.jpg";

      //VIMEO
      case "2":
        //MEDIUM THUMBNAIL
        $data = simplexml_load_file("http://vimeo.com/api/v2/video/" . $code . ".xml");
        $thumbnail = $data->video->thumbnail_medium;
        return $thumbnail;
    }
  }

  //FUNCTION FOR RETREVES INFORMATION AND RETURES TITLE AND DESCRIPTION
  public function handleInformation($type, $code) {
    switch ($type) {

      //YOUTUBE
      case "1":
        $key = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey');
        $data = file_get_contents('https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=' . $code . '&key=' . $key);
        if (empty($data)) {
          return;
        }
        $data = Zend_Json::decode($data);
        $information = array();
        $youtube_video = $data['items'][0];
        $information['title'] = $youtube_video['snippet']['title'];
        $information['description'] = $youtube_video['snippet']['description'];
        $information['duration'] = Engine_Date::convertISO8601IntoSeconds($youtube_video['contentDetails']['duration']);
        return $information;

      //VIMEO
      case "2":
        //MEDIUM THUMBNAIL
        $data = simplexml_load_file("http://vimeo.com/api/v2/video/" . $code . ".xml");
        $thumbnail = $data->video->thumbnail_medium;
        $information = array();
        $information['title'] = $data->video->title;
        $information['description'] = $data->video->description;
        $information['duration'] = $data->video->duration;
        //http://img.youtube.com/vi/Y75eFjjgAEc/default.jpg
        return $information;
    }
  }

  public function videoPlayerAction() {
    $video_guid = $this->_getParam('video_guid', null);
    $this->view->video = $video = Engine_Api::_()->getItemByGuid($video_guid);
    $embedded = $video->getRichContent(true);
    if ($video->type == 1) {
      $this->view->videoEmbedded = $embedded = str_replace('feature=player_embedded&fs=1', 'feature=player_embedded&fs=1&autoplay=1', $embedded);
    } else if ($video->type == 2) {
      $this->view->videoEmbedded = $embedded = str_replace('color=&amp;fullscreen=1', 'color=&amp;fullscreen=1&autoplay=1', $embedded);
    } else if ($video->type == 4) {
      $this->view->videoEmbedded = $embedded = str_replace('&foreground=E8D9AC&highlight=FFFFF0', '&foreground=E8D9AC&highlight=FFFFF0&autoplay=1', $embedded);
    }
  }

  //ACTION FOR EMBEDING THE VIDEO
  public function embedAction() {

    //GET SUBJECT (EITHER VIDEO TYPE OR SITEREVIEW_VIDEO TYPE)
    $this->view->video = $video = Engine_Api::_()->core()->getSubject();

    //CHECK THAT EMBEDDING IS ALLOWED OR NOT
    if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview_video.embeds', 1)) {
      $this->view->error = 1;
      return;
    } else if (isset($video->allow_embed) && !$video->allow_embed) {
      $this->view->error = 2;
      return;
    }

    //GET EMBED CODE
    $this->view->embedCode = $video->getEmbedCode();
  }

  //ACTION FOR FETCHING THE VIDEO INFORMATION
  public function externalAction() {

    //GET SUBJECT
    $this->view->video = $video = Engine_Api::_()->core()->getSubject('sitereview_video');

    //CHECK THAT EMBEDDING IS ALLOWED OR NOT
    if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview_video.embeds', 1)) {
      $this->view->error = 1;
      return;
    } else if (isset($video->allow_embed) && !$video->allow_embed) {
      $this->view->error = 2;
      return;
    }

    //GET EMBED CODE
    $this->view->videoEmbedded = "";
    if ($video->status == 1) {
      $video->view_count++;
      $video->save();
      $this->view->videoEmbedded = $video->getRichContent(true);
    }

    //TRACK VIEWS FROM EXTERNAL SOURCES
    Engine_Api::_()->getDbtable('statistics', 'core')
            ->increment('video.embedviews');

    //GET FILE LOCATION
    if ($video->type == 3 && $video->status == 1) {
      if (!empty($video->file_id)) {
        $storage_file = Engine_Api::_()->getItem('storage_file', $video->file_id);
        if ($storage_file) {
          $this->view->video_location = $storage_file->map();
        }
      }
    }

    //GET RATING DATA
    $this->view->rating_count = Engine_Api::_()->getDbTable('videoratings', 'sitereview')->ratingCount($video->getIdentity());
  }

  //ACTION FOR VIDEO COMPOSE UPLOAD
  public function composeUploadAction() {

    //GET VIEWER INFO
    $viewer = Engine_Api::_()->user()->getViewer();

    if (!$viewer->getIdentity()) {
      $this->_redirect('login');
      return;
    }

    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid method');
      return;
    }

    $video_title = $this->_getParam('title');
    $video_url = $this->_getParam('uri');
    $video_type = $this->_getParam('type');
    $composer_type = $this->_getParam('c_type', 'wall');

    $code = $this->extractCode($video_url, $video_type);
    // check if code is valid
    // check which API should be used
    if ($video_type == 1) {
      $valid = $this->checkYouTube($code);
    }
    if ($video_type == 2) {
      $valid = $this->checkVimeo($code);
    }

    if ($valid) {
      $db = Engine_Api::_()->getDbtable('videos', 'sitereview')->getAdapter();
      $db->beginTransaction();

      try {
        $information = $this->handleInformation($video_type, $code);
        // create video
        $table = Engine_Api::_()->getDbtable('videos', 'sitereview');
        $video = $table->createRow();
        $video->title = $information['title'];
        $video->description = $information['description'];
        $video->duration = $information['duration'];
        $video->owner_id = $viewer->getIdentity();
        $video->code = $code;
        $video->type = $video_type;
        $video->save();

        // Now try to create thumbnail
        $thumbnail = $this->handleThumbnail($video->type, $video->code);
        $ext = ltrim(strrchr($thumbnail, '.'), '.');
        $thumbnail_parsed = @parse_url($thumbnail);

        $tmp_file = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
        $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;

        $src_fh = fopen($thumbnail, 'r');
        $tmp_fh = fopen($tmp_file, 'w');
        stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);

        $image = Engine_Image::factory();
        $image->open($tmp_file)
                ->resize(120, 240)
                ->write($thumb_file)
                ->destroy();

        $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
            'parent_type' => $video->getType(),
            'parent_id' => $video->getIdentity()
        ));

        // If video is from the composer, keep it hidden until the post is complete
        if ($composer_type)
          $video->search = 0;

        $video->photo_id = $thumbFileRow->file_id;
        $video->status = 1;
        $video->save();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      // make the video public
      if ($composer_type === 'wall') {
        // CREATE AUTH STUFF HERE
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        foreach ($roles as $i => $role) {
          $auth->setAllowed($video, $role, 'view', ($i <= $roles));
          $auth->setAllowed($video, $role, 'comment', ($i <= $roles));
        }
      }

      $this->view->status = true;
      $this->view->video_id = $video->video_id;
      $this->view->photo_id = $video->photo_id;
      $this->view->title = $video->title;
      $this->view->description = $video->description;
      $this->view->src = $video->getPhotoUrl();
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Video posted successfully');
    } else {
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('We could not find a video there - please check the URL and try again.');
    }
  }

}