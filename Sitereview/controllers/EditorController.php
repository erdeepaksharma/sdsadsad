<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: EditorController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_EditorController extends Seaocore_Controller_Action_Standard {

  //COMMON ACTION WHICH CALL BEFORE EVERY ACTION OF THIS CONTROLLER
  protected $_GETLISTINGTYPE;

  public function init() {

    //CHECK SUBJECT
    if (Engine_Api::_()->core()->hasSubject())
      return;

    $listingtype_id = $this->_getParam('listingtype_id', null);    

    //SET LISTING SUBJECT
    if (0 != ($listing_id = (int) $this->_getParam('listing_id')) &&
            null != ($sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id))) {
      Engine_Api::_()->core()->setSubject($sitereview);
      $listingtype_id = $sitereview->listingtype_id;
    }
  }

  public function homeAction() {

    //GET PAGE OBJECT
    $pageTable = Engine_Api::_()->getDbtable('pages', 'core');
    $pageSelect = $pageTable->select()->where('name = ?', "sitereview_editor_home");
    $pageObject = $pageTable->fetchRow($pageSelect);

    $this->_helper->content
            ->setContentName($pageObject->page_id)
            ->setNoRender()
            ->setEnabled();
  }

  //NONE USER SPECIFIC METHODS
  public function profileAction() {

    //GET USER ID
    $user_id = $this->_getParam('user_id');

    //IF EDITOR
    $isEditor = Engine_Api::_()->getDbTable('editors', 'sitereview')->isEditor($user_id, 0);

    //IF USER IS NOT FOUND
    if (empty($user_id) || empty($isEditor)) {
      return $this->_forward('notfound', 'error', 'core');
    }

    //SET USER SUBJECT
    $user = Engine_Api::_()->getItem('user', $user_id);
    Engine_Api::_()->core()->setSubject($user);
    $params = array('displayname' => $user->getTitle());

    //GET EDITOR TABLE
    $editorTable = Engine_Api::_()->getDbTable('editors', 'sitereview');
    $editor = Engine_Api::_()->getItem('sitereview_editor', $user_id);

    //GET EDITOR DETAILS
    $values = array(); $values['visible'] = 1; $values['editorReviewAllow'] = 1;
    $getDetails = $editorTable->getEditorDetails($user_id, 0, $values);
    $params['listingTypes'] = '';
    foreach ($getDetails as $listingtypes) {
      $params['listingTypes'] .= $listingtypes['title_plural'] . ', ';
    }
    $params['listingTypes'] = rtrim($params['listingTypes'], ', ');

    //SET META KEYWORDS
    Engine_Api::_()->sitereview()->setMetaKeywords($params);

    // Render
    $this->_helper->content
            //->setNoRender()
            ->setEnabled()
    ;
  }

  //ACTION FOR POSTING A REVIEW
  public function createAction() {

    if (!$this->_helper->requireUser()->isValid())
      return;

    if (!$this->_helper->requireSubject('sitereview_listing')->isValid())
      return;

    //GET VIEWER INFORMATION
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //GET LISITING
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject();
    $this->view->listing_id = $listing_id = $sitereview->listing_id;
    $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;

    //CHECK EDITOR REVIEW IS ALLOWED OR NOT
    $allow_editor_review = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingtype_id, 'reviews');
    if (empty($allow_editor_review) || $allow_editor_review == 2) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    //SHOW THIS LINK ONLY EDITOR FOR THIS LISTING TYPE
    $isEditor = Engine_Api::_()->getDbTable('editors', 'sitereview')->isEditor($viewer_id, $listingtype_id);
    if (empty($isEditor)) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    //EDITOR REVIEW HAS BEEN POSTED OR NOT
    $params = array();
    $params['resource_id'] = $sitereview->listing_id;
    $params['resource_type'] = $sitereview->getType();
    $params['viewer_id'] = $viewer_id;
    $params['type'] = 'editor';
    $params['notIncludeStatusCheck'] = 1;
    $isEditorReviewed = Engine_Api::_()->getDbTable('reviews', 'sitereview')->canPostReview($params);
    if (!empty($isEditorReviewed)) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation("sitereview_main_listtype_$listingtype_id");

    //FATCH REVIEW CATEGORIES
    $categoryIdsArray = array();
    $categoryIdsArray[] = $sitereview->category_id;
    $categoryIdsArray[] = $sitereview->subcategory_id;
    $categoryIdsArray[] = $sitereview->subsubcategory_id;
    $this->view->reviewCategory = Engine_Api::_()->getDbtable('ratingparams', 'sitereview')->reviewParams($categoryIdsArray, 'sitereview_listing');
    $profileTypeReview = Engine_Api::_()->getDbtable('categories', 'sitereview')->getProfileType($categoryIdsArray, 0, 'profile_type_review');

    //GET FORM
    $this->view->form = $form = new Sitereview_Form_Editor_Create(array('profileTypeReview' => $profileTypeReview));

    if (isset($_POST['submit'])) {
      $this->view->first_time_load = 0;
    } else {
      $this->view->first_time_load = 1;
    }

    //GET TINYMCE SETTINGS
    $this->view->upload_url = "";
    if (Engine_Api::_()->authorization()->isAllowed($sitereview, $viewer, "photo_listtype_$listingtype_id")) {
      $this->view->upload_url = $this->view->url(array('controller' => 'editor', 'action' => 'upload-photo', 'listing_id' => $sitereview->listing_id), "sitereview_extended_listtype_$listingtype_id", true);
    }

    $orientation = $this->view->layout()->orientation;
    if ($orientation == 'right-to-left') {
      $this->view->directionality = 'rtl';
    } else {
      $this->view->directionality = 'ltr';
    }

    $local_language = $this->view->locale()->getLocale()->__toString();
    $local_language = explode('_', $local_language);
    $this->view->language = $local_language[0];

    $this->view->total_reviewcats = Count($this->view->reviewCategory);

    $this->view->tab_selected_id = $this->_getParam('tab');

    //SHOW PRE-FIELD THE RATINGS IF OVERALL RATING IS EMPTY
    $this->view->reviewRateData = Engine_Api::_()->sitereview()->prefieldRatingData($_POST);

    //GET CATEGORIES ARRAY
    $this->view->bodyElementValue = $bodyElementValue = array();
    foreach ($_POST as $key => $value) {
      $bodyElement = strstr($key, 'body_');

      if (!empty($bodyElement) && !empty($value)) {
        $this->view->bodyElementValue[] = $bodyElementValue[] = $value;
      }
    }

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      if (empty($_POST['review_rate_0'])) {

        $error = $this->view->translate('Please choose Overall Rating field - it is required.');
        $error = Zend_Registry::get('Zend_Translate')->_($error);

        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
        return;
      }
      
      $db = Engine_Db_Table::getDefaultAdapter();
      $this->_GETLISTINGTYPE = Engine_Api::_()->getApi('listingType', 'sitereview')->getListingTypeInfo();
      $db->beginTransaction();

      try {

        $values = $_POST;
        $values['owner_id'] = $viewer_id;
        $values['resource_id'] = $listing_id;
        $values['resource_type'] = $sitereview->getType();
        $values['type'] = 'editor';
        $values['body_pages'] = Zend_Json_Encoder::encode($bodyElementValue);
        $values['recommend'] = 1;
        $values['profile_type_review'] = $profileTypeReview;

        //CREATE REVIEW
        $reviewTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
        $review = $reviewTable->createRow();
        $review->setFromArray($values);
        $review->save();

        if (!empty($profileTypeReview)) {
          //SAVE CUSTOM VALUES AND PROFILE TYPE VALUE
          $customfieldform = $form->getSubForm('fields');
          $customfieldform->setItem($review);
          $customfieldform->saveValues();
        }

        $_POST['user_id'] = $viewer_id;
        $_POST['review_id'] = $review->review_id;
        $_POST['category_id'] = $sitereview->category_id;
        $_POST['resource_id'] = $review->resource_id;
        $_POST['resource_type'] = $review->resource_type;

        //CREATE RATING DATA
        $reviewRatingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');
        $reviewRatingTable->createRatingData($_POST, 'editor');

        //IF PUBLISHED 
        if ($review->status == 1) {

          //INCREASE REVIEW COUNT
          $sitereview->review_count++;
          $sitereview->save();

          //RATING UPDATE
		      $exist_review = $reviewRatingTable->getReviewIdExist($viewer_id,$sitereview->getType(),$sitereview->listing_id);
		      if(!empty($exist_review)) {
		        $reviewRatingTable->listRatingUpdate($review->resource_id, $review->resource_type);
		      }
		      else {
		        $rating_only = 1;
						$reviewRatingTable->listRatingUpdate($review->resource_id, $review->resource_type,$rating_only);
          }
        }

        //COMMENT PRIVACY
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        $commentMax = array_search("everyone", $roles);
        foreach ($roles as $i => $role) {
          $auth->setAllowed($review, $role, 'comment', ($i <= $commentMax));
        }

        if ($sitereview->owner_id != $viewer_id) {

          $host = $_SERVER['HTTP_HOST'];
          $viewer_page_url = (_ENGINE_SSL ? 'https://' : 'http://') . $host . $viewer->getHref();
          $viewer_fullhref = '<a href="' . $viewer_page_url . '">' . $viewer->getTitle() . '</a>';
          $object_link = (_ENGINE_SSL ? 'https://' : 'http://') . $host . $review->getHref();
          $object_parent_with_link = '<a href="' . 'http://' . $_SERVER['HTTP_HOST'] . '/' . $sitereview->getHref() . '">' . $sitereview->getTitle() . '</a>';

          Engine_Api::_()->getApi('mail', 'core')->sendSystem($sitereview->getOwner()->email, 'SITEREVIEW_EDITORREVIEW_CREATION', array(
              'editor' => $viewer_fullhref,
              'editor_name' => $viewer->getTitle(),
              'object_parent_with_link' => $object_parent_with_link,
              'object_title' => $review->title,
              'object_description' => $review->body,
              'object_parent_title' => $sitereview->getTitle(),
              'object_link' => $object_link,
              'queue' => true
          ));
        }

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      return $this->_redirectCustom($sitereview->getHref(), array('prependBase' => false));
    }
  }

  //ACTION FOR POSTING A REVIEW
  public function editAction() {

    if (!$this->_helper->requireUser()->isValid())
      return;

    if (!$this->_helper->requireSubject('sitereview_listing')->isValid())
      return;

    //GET VIEWER INFORMATION
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    //GET LISITING
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject();
    $this->view->listing_id = $listing_id = $sitereview->listing_id;
    $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;

    //SHOW THIS LINK ONLY EDITOR FOR THIS LISTING TYPE
    $isEditor = Engine_Api::_()->getDbTable('editors', 'sitereview')->isEditor($viewer_id, $listingtype_id);
    if (empty($isEditor)) {
      return $this->_forward('requireauth', 'error', 'core');
    }

    $review_id = $this->_getParam('review_id', null);
    $review = Engine_Api::_()->getItem('sitereview_review', $review_id);

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation("sitereview_main_listtype_$listingtype_id");

    //FATCH REVIEW CATEGORIES
    $categoryIdsArray = array();
    $categoryIdsArray[] = $sitereview->category_id;
    $categoryIdsArray[] = $sitereview->subcategory_id;
    $categoryIdsArray[] = $sitereview->subsubcategory_id;
    $this->view->reviewCategory = Engine_Api::_()->getDbtable('ratingparams', 'sitereview')->reviewParams($categoryIdsArray, 'sitereview_listing');
    $profileTypeReview = Engine_Api::_()->getDbtable('categories', 'sitereview')->getProfileType($categoryIdsArray, 0, 'profile_type_review');

    //GET FORM
    $this->view->form = $form = new Sitereview_Form_Editor_Edit(array('item' => $review, 'profileTypeReview' => $profileTypeReview));

    //REMOVE DRAFT ELEMENT IF ALREADY PUBLISHED
    if ($review->status == "1") {
      $form->removeElement('status');
    }

    //GET TINYMCE SETTINGS
    $this->view->upload_url = "";
    if (Engine_Api::_()->authorization()->isAllowed($sitereview, $viewer, "photo_listtype_$listingtype_id")) {
      $this->view->upload_url = $this->view->url(array('controller' => 'editor', 'action' => 'upload-photo', 'listing_id' => $sitereview->listing_id), "sitereview_extended_listtype_$listingtype_id", true);
    }

    $orientation = $this->view->layout()->orientation;
    if ($orientation == 'right-to-left') {
      $this->view->directionality = 'rtl';
    } else {
      $this->view->directionality = 'ltr';
    }

    $local_language = $this->view->locale()->getLocale()->__toString();
    $local_language = explode('_', $local_language);
    $this->view->language = $local_language[0];

    $this->view->total_reviewcats = Count($this->view->reviewCategory);
    $this->view->tab_selected_id = $this->_getParam('tab');

    //GET CATEGORIES ARRAY
    $this->view->bodyElementValue = $bodyElementValue = array();
    if ($this->getRequest()->isPost()) {
      foreach ($_POST as $key => $value) {
        $bodyElement = strstr($key, 'body_');

        if (!empty($bodyElement) && !empty($value)) {
          $this->view->bodyElementValue[] = $bodyElementValue[] = $value;
        }
      }
    } else {
      $encoded_value = $review->body_pages;
      $decoded_value = Zend_Json_Decoder::decode($encoded_value);
      foreach ($decoded_value as $key => $value) {

        if (!empty($value)) {
          $this->view->bodyElementValue[] = $bodyElementValue[] = $value;
        }
      }
    }

    //CHECK POST
    if (!$this->getRequest()->isPost()) {
      $this->view->reviewRateData = Engine_Api::_()->getDbtable('ratings', 'sitereview')->ratingsData($review_id);
      $form->populate($review->toArray());
      return;
    } else {
      $this->view->reviewRateData = Engine_Api::_()->sitereview()->prefieldRatingData($_POST);
    }

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      //SHOW PRE-FIELD THE RATINGS IF OVERALL RATING IS EMPTY
      $this->view->reviewRateData = Engine_Api::_()->sitereview()->prefieldRatingData($_POST);
      $this->_GETLISTINGTYPE = Engine_Api::_()->getApi('listingType', 'sitereview')->getListingTypeInfo();

      if (empty($_POST['review_rate_0'])) {

        $error = $this->view->translate('Please choose Overall Rating field - it is required.');
        $error = Zend_Registry::get('Zend_Translate')->_($error);

        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
        return;
      }

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();

      try {

        $values = $_POST;
        $values['profile_type_review'] = $profileTypeReview;
        $reviewTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
        $review->setFromArray($values);
        $review->body_pages = Zend_Json_Encoder::encode($bodyElementValue);
        $review->save();

        if (!empty($profileTypeReview)) {
          //SAVE CUSTOM VALUES AND PROFILE TYPE VALUE
          $customfieldform = $form->getSubForm('fields');
          $customfieldform->setItem($review);
          $customfieldform->saveValues();
        }

        $reviewRatingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');
        $reviewRatingTable->delete(array('review_id = ?' => $review->review_id));

        $_POST['user_id'] = $viewer_id;
        $_POST['review_id'] = $review->review_id;
        $_POST['category_id'] = $sitereview->category_id;
        $_POST['resource_id'] = $review->resource_id;
        $_POST['resource_type'] = $review->resource_type;

        //CREATE RATING DATA
        $reviewRatingTable->createRatingData($_POST, 'editor');

        //IF PUBLISHED 
        if ($review->status == 1) {

          //INCREASE REVIEW COUNT
          $sitereview->review_count++;
          $sitereview->save();

          //RATING UPDATE
		      $exist_review = $reviewRatingTable->getReviewIdExist($viewer_id,$sitereview->getType(),$sitereview->listing_id);
		      if(!empty($exist_review)) {
		        $reviewRatingTable->listRatingUpdate($review->resource_id, $review->resource_type);
		      }
		      else {
		        $rating_only = 1;
						$reviewRatingTable->listRatingUpdate($review->resource_id, $review->resource_type,$rating_only);
          }
        }

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      return $this->_redirectCustom($sitereview->getHref(), array('prependBase' => false));
    }
  }

  //ACTION FOR UPLOADING THE OVERVIEWS PHOTOS FROM THE EDITOR
  public function uploadPhotoAction() {

    //CHECK USER VALIDATION
    if (!$this->_helper->requireUser()->isValid())
      return;

    //LAYOUT
    $this->_helper->layout->disableLayout();
    if (!$this->_helper->requireUser()->checkRequire()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
      return;
    }

    //PAGE ID
    $listing_id = $this->_getParam('listing_id');
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

    //IF NOT POST OR FORM NOT VALID, RETURN
    if (!$this->getRequest()->isPost()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }
     $fileName = Engine_Api::_()->seaocore()->tinymceEditorPhotoUploadedFileName();
    //IF NOT POST OR FORM NOT VALID, RETURN
    if (!isset($_FILES[$fileName]) || !is_uploaded_file($_FILES[$fileName]['tmp_name'])) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
      return;
    }

    //PROCESS
    $db = Engine_Api::_()->getDbtable('photos', 'sitereview')->getAdapter();
    $db->beginTransaction();
    try {
      //CREATE PHOTO
      $tablePhoto = Engine_Api::_()->getDbtable('photos', 'sitereview');
      $photo = $tablePhoto->createRow();
      $album = $sitereview->getSingletonAlbum();
      $photo->setFromArray(array(
          'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
          'listing_id' => $listing_id,
          'album_id' => $album->getIdentity(),
          'collection_id' => $album->getIdentity()
      ));
      $photo->save();
      $photo->setPhoto($_FILES[$fileName]);

      $this->view->status = true;
      $this->view->name = $_FILES[$fileName]['name'];
      $this->view->photo_id = $photo->photo_id;
      $this->view->photo_url = $photo->getPhotoUrl();

      if (!$album->photo_id) {
        $album->photo_id = $photo->file_id;
        $album->save();
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.');
      return;
    }
  }

  public function editorMailAction() {

    //ONLY LOGGED IN USER CAN VIEW THIS PAGE
    if (!$this->_helper->requireUser()->isValid())
      return;

    //SET LAYOUT
    $this->_helper->layout->setLayout('default-simple');

    //GET VIEWER
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewr_id = $viewer->getIdentity();
    $user_id = $this->_getParam('user_id', null);
    $editor = Engine_Api::_()->getItem('user', $user_id);
    //GET FORM
    $this->view->form = $form = new Sitereview_Form_Editor_EditorMail(array('editor' => $editor));

    $form->setTitle(sprintf(Zend_Registry::get('Zend_Translate')->_('Email %s'), $editor->getTitle()))
            ->setDescription(sprintf(Zend_Registry::get('Zend_Translate')->_("Have a question or comments for %s? Ask from below and our editor will get back to you."), $editor->getTitle()));
    $value['reciver_email'] = $editor->email;
    if (!empty($viewr_id)) {
      $value['sender_email'] = $viewer->email;
      $value['sender_name'] = $viewer->getTitle();
      $form->populate($value);
    }

    //FORM VALIDATION
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      //GET FORM VALUES
      $values = $form->getValues();
      $reciver_ids[] = $values['reciver_email'];
      //CHECK VALID EMAIL ID FORMAT
      $validator = new Zend_Validate_EmailAddress();
      $validator->getHostnameValidator()->setValidateTld(false);
      $sender_email = $values['sender_email'];
      if (!$validator->isValid($sender_email)) {
        $form->addError(Zend_Registry::get('Zend_Translate')->_('Invalid sender email address value'));
        return;
      }

      $sender = $values['sender_name'];
      $message = $values['message'];
      Engine_Api::_()->getApi('mail', 'core')->sendSystem($reciver_ids, 'SITEREVIEW_EDITOR_EMAIL', array(
          'host' => $_SERVER['HTTP_HOST'],
          'sender' => $sender,
          'message' => '<div>' . $message . '</div>',
          'email' => $sender_email,
          'queue' => false
      ));

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => true,
          'parentRefreshTime' => '15',
          'format' => 'smoothbox',
          'messages' => Zend_Registry::get('Zend_Translate')->_('Email to the editor has been sent successfully.')
      ));
    }
  }

  public function similarItemsAction() {

    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET VIEWER INFORMATION
    $viewer = Engine_Api::_()->user()->getViewer();

    //Get LISTING ID AND OBJECT
    $this->view->listing_id = $listing_id = $this->_getParam('listing_id', 0);
    $this->view->listing = $listing = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
    
    $select_alternatives = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listing->listingtype_id, 'select_alternatives');    

    //ONLY EDITOR AND ADMIN CAN ADD SIMILAR ITEMS
    $isEditor = Engine_Api::_()->getDbTable('editors', 'sitereview')->isEditor($viewer->getIdentity(), $listing->listingtype_id);
    if ((empty($isEditor) && $viewer->level_id != 1) && ($listing->owner_id != $viewer->getIdentity() || !$select_alternatives)) {
      return $this->_forward('requireauth', 'error', 'core');
    }
    
    //IF WIDGET IS NOT PLACED
    $this->view->existWidget = Engine_Api::_()->sitereview()->existWidget('similar_items', 0, $listing->listingtype_id);

    //GET CATEGORIES
    $this->view->categories = Engine_Api::_()->getDbTable('categories', 'sitereview')->getCategories(null, 0, $listing->listingtype_id, 0, 1, 0, 'cat_order', 0, array('category_id', 'category_name'));

    $this->view->similarItems = array();

    $similar_items = Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->getColumnValue($listing_id, 'similar_items');
    if (!empty($similar_items)) {
      $this->view->similarItems = Zend_Json_Decoder::decode($similar_items);
    }

    $this->view->page = $page = $this->_getParam('page', 1);
    $this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax', '');
    $this->view->textSearch = $textSearch = $this->_getParam('textSearch', '');

    if (empty($is_ajax)) {
      $this->view->category_id = $category_id = $listing->category_id;
      $this->view->subcategory_id = $subcategory_id = 0;
      if (!empty($listing->subcategory_id)) {
        $this->view->subcategory_id = $subcategory_id = $listing->subcategory_id;
      }
    } else {
      $this->view->category_id = $category_id = $this->_getParam('category_id', '');
      $this->view->subcategory_id = $subcategory_id = $this->_getParam('subcategory_id', '');
    }

    $params = array();
    $params['listingIds'] = array();
    $this->view->similarListings = array();
    if (Count($this->view->similarItems) > 0) {
      $params['listingIds'] = $this->view->similarItems;
      $this->view->similarListings = Engine_Api::_()->getDbTable('listings', 'sitereview')->getSimilarItems($params);
    }

    $params = array();
    $params['listing_id'] = $listing_id;
    $params['listingtype_id'] = $listing->listingtype_id;
    $params['category_id'] = $category_id;
    $params['subcategory_id'] = $subcategory_id;

    if (Count($this->view->similarItems) > 0) {
      $params['notListingIds'] = $this->view->similarItems;
    }

    $params['textSearch'] = $textSearch;

    //FETCH DATA
    $this->view->paginator = Engine_Api::_()->getDbTable('listings', 'sitereview')->getSimilarItems($params);
    $this->view->paginator->setItemCountPerPage(18);
    $this->view->paginator->setCurrentPageNumber($page);
  }

  public function addItemsAction() {

    //ONLY LOGGED IN USER CAN VIEW THIS PAGE
    if (!$this->_helper->requireUser()->isValid())
      return;

    $listing_id = $this->_getParam('listing_id');
    $itemIds = $_POST['selected_resources'];
    $itemIds = explode(',', $itemIds);
    $listing = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
    $tableOtherinfo = Engine_Api::_()->getDbTable('otherinfo', 'sitereview');
    if (!empty($itemIds)) {
      $itemIds = Zend_Json_Encoder::encode($itemIds);
      if ($itemIds != '[""]') {
        $tableOtherinfo->update(array('similar_items' => $itemIds), array('listing_id = ?' => $listing_id));
      } else {
        $tableOtherinfo->update(array('similar_items' => ''), array('listing_id = ?' => $listing_id));
      }
    }

    return $this->_helper->redirector->gotoRoute(array('listing_id' => $listing->listing_id, 'slug' => $listing->getSlug()), "sitereview_entry_view_listtype_$listing->listingtype_id", true);
  }

  public function categoriesAction() {

    $element_value = $this->_getParam('element_value', 1);
    $element_type = $this->_getParam('element_type', 'listingtype_id');

    $this->view->categories = Engine_Api::_()->getDbTable('categories', 'sitereview')->similarItemsCategories($element_value, $element_type);
  }

}
