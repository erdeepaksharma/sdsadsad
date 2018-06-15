<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: ReviewController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_ReviewController extends Seaocore_Controller_Action_Standard {

  protected $_listingType;

  //COMMON ACTION WHICH CALL BEFORE EVERY ACTION OF THIS CONTROLLER
  public function init() {

    //CHECK SUBJECT
    if (Engine_Api::_()->core()->hasSubject())
      return;

    if (!in_array($this->_getParam('action', null), array('browse', 'categories'))) {

      //SET REVIEW SUBJECT
      if (0 != ($review_id = (int) $this->_getParam('review_id')) &&
              null != ($review = Engine_Api::_()->getItem('sitereview_review', $review_id))) {
        Engine_Api::_()->core()->setSubject($review);
      } else if (0 != ($listing_id = (int) $this->_getParam('listing_id')) &&
              null != ($sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id))) {
        Engine_Api::_()->core()->setSubject($sitereview);
      }

      //SET LISTING TYPE ID AND OBJECT
      if ($this->_getParam('listingtype_id', null)) {
        $listingtype_id = $this->_getParam('listingtype_id', null);
      } else {
        if (!empty($review)) {
          $sitereview = $review->getParent();
        }
        $listingtype_id = $sitereview->listingtype_id;
      }

      Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
      $this->_listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);

      if (empty($this->_listingType->reviews) || $this->_listingType->reviews == 1) {
        return;
      }

      //AUTHORIZATION CHECK
      if (!$this->_helper->requireAuth()->setAuthParams('sitereview_listing', null, "view_listtype_$listingtype_id")->isValid())
        return;
    }
  }

  public function browseAction() {

    //GET VIEWER INFO
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();
    $this->view->autoContentLoad = $isappajax = $this->_getParam('isappajax', false);
    //GET PARAMS
    $params['type'] = '';

    $params = $this->_getAllParams();
    if (!isset($params['order']) || empty($params['order']))
      $params['order'] = 'recent';
    if (isset($params['show'])) {

      switch ($params['show']) {
        case 'friends_reviews':
          $params['user_ids'] = $viewer->membership()->getMembershipsOfIds();
          if (empty($params['user_ids']))
            $params['user_ids'] = -1;
          break;
        case 'self_reviews':
          $params['user_id'] = $viewer_id;
          break;
        case 'featured':
          $params['featured'] = 1;
          break;
      }
    }

    $params['resource_type'] = 'sitereview_listing';

    $searchForm = $this->view->searchForm = new Sitereview_Form_Review_Search(array('type' => 'sitereview_review'));
    $searchForm->populate($this->_getAllParams());
    $searchParams = $searchForm->getValues();

    //GET REVIEW TABLE
    $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');

    //CUSTOM FIELD WORK
    $customFieldValues = array_intersect_key($searchParams, $searchForm->getFieldElements());

    //GET PAGINATOR
    $paginator = $reviewTable->getReviewsPaginator($params, $customFieldValues);
    $this->view->paginator = $paginator->setItemCountPerPage(10);
    $this->view->paginator = $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    if (isset($params['subcategory_id']) && $params['subcategory_id'])
      $searchParams['subcategory_id'] = $params['subcategory_id'];
    if (isset($params['subsubcategory_id']) && $params['subsubcategory_id'])
      $searchParams['subsubcategory_id'] = $params['subsubcategory_id'];
    $this->view->searchParams = $searchParams;

    //GET TOTAL REVIEWS
    $this->view->totalReviews = $paginator->getTotalItemCount();
    $this->view->page = $this->_getParam('page', 1);
    $this->view->totalPages = ceil(($this->view->totalReviews) / 10);
    $metaParams = array();

    //GET LISTING CATEGORY TABLE
    $tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');
    $request = Zend_Controller_Front::getInstance()->getRequest();

    $category_id = $request->getParam('category_id', null);

    if (!empty($category_id)) {

      $metaParams['categoryname'] = Engine_Api::_()->getItem('sitereview_category', $category_id)->getCategorySlug();

      $subcategory_id = $request->getParam('subcategory_id', null);

      if (!empty($subcategory_id)) {

        $metaParams['subcategoryname'] = Engine_Api::_()->getItem('sitereview_category', $subcategory_id)->getCategorySlug();

        $subsubcategory_id = $request->getParam('subsubcategory_id', null);

        if (!empty($subsubcategory_id)) {

          $metaParams['subsubcategoryname'] = Engine_Api::_()->getItem('sitereview_category', $subsubcategory_id)->getCategorySlug();
        }
      }
    }

    //SET META TITLES
    Engine_Api::_()->sitereview()->setMetaTitles($metaParams);

    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', null);
    if (!empty($listingtype_id)) {
      $allow_review = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getAllowReview($listingtype_id);

      if (empty($allow_review)) {
        return $this->_forwardCustom('requireauth', 'error', 'core');
      }
    }

    //GET LISTING TITLE
    if ($listingtype_id) {
      $metaParams['listing_type_title'] = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingtype_id, 'title_plural');
    }

    //GET TAG
    if ($this->_getParam('search', null)) {
      $metaParams['search'] = $this->_getParam('search', null);
    }

    //SET META KEYWORDS
    Engine_Api::_()->sitereview()->setMetaKeywords($metaParams);

    //RENDER
    if (!$isappajax)
      $this->_helper->content
              //->setNoRender()
              ->setEnabled()
      ;
  }

  //GET CATEGORIES ACTION
  public function categoriesAction() {

    $element_value = $this->_getParam('element_value', 1);
    $element_type = $this->_getParam('element_type', 'listingtype_id');

    $categoriesTable = Engine_Api::_()->getDbTable('categories', 'sitereview');
    $select = $categoriesTable->select()
            ->from($categoriesTable->info('name'), array('category_id', 'category_name'))
            ->where("$element_type = ?", $element_value);

    if ($element_type == 'listingtype_id') {
      $select->where('cat_dependency = ?', 0)->where('subcat_dependency = ?', 0);
    } elseif ($element_type == 'cat_dependency') {
      $select->where('subcat_dependency = ?', 0);
    } elseif ($element_type == 'subcat_dependency') {
      $select->where('cat_dependency = ?', $element_value);
    }

    $categoriesData = $categoriesTable->fetchAll($select);

    $categories = array();
    if (Count($categoriesData) > 0) {
      foreach ($categoriesData as $category) {
        $data = array();
        $data['category_name'] = $this->view->translate($category->category_name);
        $data['category_id'] = $category->category_id;
        $data['category_slug'] = $category->getCategorySlug();
        $categories[] = $data;
      }
    }

    $this->view->categories = $categories;
  }

  //ACTION FOR WRITE A REVIEW
  public function createAction() {

    //LISTING SUBJECT SHOULD BE SET
    if (!$this->_helper->requireSubject('sitereview_listing')->isValid())
      return;


    if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
      //GET VIEWER INFO
      $viewer = Engine_Api::_()->user()->getViewer();
      $viewer_id = $viewer->getIdentity();

      //GET LISTING SUBJECT
      $sitereview = Engine_Api::_()->core()->getSubject();
      $listingtype_id = $this->_listingType->listingtype_id;

      //FATCH REVIEW CATEGORIES
      $categoryIdsArray = array();
      $categoryIdsArray[] = $sitereview->category_id;
      $categoryIdsArray[] = $sitereview->subcategory_id;
      $categoryIdsArray[] = $sitereview->subsubcategory_id;
      $profileTypeReview = Engine_Api::_()->getDbtable('categories', 'sitereview')->getProfileType($categoryIdsArray, 0, 'profile_type_review');

      //GET USER LEVEL ID
      if (!empty($viewer_id)) {
        $level_id = $viewer->level_id;
      } else {
        $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
      }

      $can_create = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "review_create_listtype_$listingtype_id");

      if (empty($can_create)) {
        return $this->_forwardCustom('requireauth', 'error', 'core');
      }

      $postData = $this->getRequest()->getPost();

      if ($this->getRequest()->isPost() && $postData) {
        $isvalid = 1;
        if (empty($viewer_id) && Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.captcha', 1)) {
          $captchas = $postData['captcha'];
          $isvalid = $this->validateCaptcha($captchas);
        }
        if (!$isvalid) {
          echo Zend_Json::encode(array('captchaError' => 1));
          exit();
        } else {
          $db = Engine_Db_Table::getDefaultAdapter();
          $db->beginTransaction();

          try {

            $coreApi = Engine_Api::_()->getApi('settings', 'core');
            $this->view->sitereview_proscons = $sitereview_proscons = $coreApi->getSetting('sitereview.proscons', 1);
            $this->view->sitereview_limit_proscons = $sitereview_limit_proscons = $coreApi->getSetting('sitereview.limit.proscons', 500);
            $this->view->sitereview_recommend = $sitereview_recommend = $coreApi->getSetting('sitereview.recommend', 1);
            $getListingRevType = Engine_Api::_()->getApi('listingType', 'sitereview')->getListingReviewType();
            $form = new Sitereview_Form_Review_Create(array("settingsReview" => array('sitereview_proscons' => $this->view->sitereview_proscons, 'sitereview_limit_proscons' => $this->view->sitereview_limit_proscons, 'sitereview_recommend' => $this->view->sitereview_recommend), 'item' => $sitereview, 'profileTypeReview' => $profileTypeReview));
            $form->populate($postData);
            $otherValues = $form->getValues();

            $values = array_merge($postData, $otherValues);
            $values['owner_id'] = $viewer_id;
            $values['resource_id'] = $sitereview->listing_id;
            $values['resource_type'] = $sitereview->getType();
            $values['profile_type_review'] = $profileTypeReview;
            $values['type'] = $viewer_id ? 'user' : 'visitor';
            if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.recommend', 1)) {
              $values['recommend'] = 0;
            }
            $reviewTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
            $review = $reviewTable->createRow();
            $review->setFromArray($values);
            $review->view_count = 1;
            $review->save();

            if (!empty($profileTypeReview)) {
              //SAVE CUSTOM VALUES AND PROFILE TYPE VALUE
              $form = new Sitereview_Form_Review_Create(array('item' => $sitereview, 'profileTypeReview' => $profileTypeReview));
              $form->populate($postData);
              $customfieldform = $form->getSubForm('fields');
              $customfieldform->setItem($review);
              $customfieldform->saveValues();
            }

            //INCREASE REVIEW COUNT IN LISTING TABLE
            if (!empty($viewer_id))
              $sitereview->review_count++;

            $sitereview->save();

            $reviewRatingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');
            $reviewRatingTable->delete(array('review_id = ?' => 0, 'resource_id = ?' => $review->resource_id, 'type = ?' => 'user', 'resource_type = ?' => $review->resource_type));

            $postData['user_id'] = $viewer_id;
            $postData['review_id'] = $review->review_id;
            $postData['category_id'] = $sitereview->category_id;
            $postData['resource_id'] = $review->resource_id;
            $postData['resource_type'] = $review->resource_type;

            $review_count = Engine_Api::_()->getDbtable('ratings', 'sitereview')->getReviewId($viewer_id, $sitereview->getType(), $review->resource_id);

            if (count($review_count) == 0) {
              //CREATE RATING DATA
              $reviewRatingTable->createRatingData($postData, $values['type']);
            } else {
              $reviewRatingTable->update(array('review_id' => $review->review_id, 'rating' => $postData['rating']), array('resource_type = ?' => $review->resource_type, 'user_id = ?' => $viewer_id, 'resource_id = ?' => $review->resource_id, 'type = ?' => 'user'));
            }

            //UPDATE RATING IN RATING TABLE
            if (!empty($viewer_id)) {
              $reviewRatingTable->listRatingUpdate($review->resource_id, $review->resource_type);
            }

            if (empty($review_id) && !empty($viewer_id) && time() >= strtotime($sitereview->creation_date)) {
              $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');

              //ACTIVITY FEED
              $action = $activityApi->addActivity($viewer, $sitereview, 'sitereview_review_add_listtype_' . $listingtype_id);

              if ($action != null) {
                $activityApi->attachActivity($action, $review);
              }
            }

            if (empty($viewer_id)) {
              $review->status = 0;
              $review->save();
              $email = Engine_Api::_()->getApi('settings', 'core')->core_mail_from;
              $admin_emails = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.contact', Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.from', 'email@domain.com'));
              $explodeEmails = explode(",", $admin_emails);
              foreach ($explodeEmails as $value) {
                Engine_Api::_()->getApi('mail', 'core')->sendSystem($value, 'SITEREVIEW_REVIEW_WRITE', array(
                    'listing_Name' => $sitereview->title,
                    'listing_Name_With_link' => '<a href="' . 'http://' . $_SERVER['HTTP_HOST'] .
                    Zend_Controller_Front::getInstance()->getRouter()->assemble(array('listing_id' => $sitereview->listing_id), "sitereview_entry_view_listtype_$listingtype_id", true) . '"  >' . $sitereview->title . '</a>',
                    'user_name' => $review->anonymous_name,
                    'review_title' => $review->title,
                    'review_description' => $review->body,
                    'review_link' => '<a href="' . 'http://' . $_SERVER['HTTP_HOST'] .
                    Zend_Controller_Front::getInstance()->getRouter()->assemble(array('review_id' => $review->review_id, 'listing_id' => $review->resource_id), "sitereview_view_review_listtype_$listingtype_id", true) . '"  >' . 'http://' . $_SERVER['HTTP_HOST'] .
                    Zend_Controller_Front::getInstance()->getRouter()->assemble(array('review_id' => $review->review_id, 'listing_id' => $review->resource_id), "sitereview_view_review_listtype_$listingtype_id", true) . '</a>',
                    'email' => $email,
                    'queue' => false
                ));
              }
            }

            if ($sitereview->owner_id != $viewer_id && !empty($review->owner_id)) {
              $object_parent_with_link = '<a href="' . 'http://' . $_SERVER['HTTP_HOST'] . '/' . $sitereview->getHref() . '">' . $sitereview->getTitle() . '</a>';
              $subjectOwner = $sitereview->getOwner('user');
              $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
              $notifyApi->addNotification($subjectOwner, $viewer, $review, 'sitereview_write_review', array("object_parent_with_link" => $object_parent_with_link));
            }

            $db->commit();
          } catch (Exception $e) {
            $db->rollBack();
            throw $e;
          }
          echo Zend_Json::encode(array('captchaError' => 0, 'review_href' => $review->getHref()));
          exit();
        }
      }
    } else {

      //GET LISTING SUBJECT
      $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');


      $listing_id = $sitereview->getIdentity();
      $listingtype_id = $sitereview->listingtype_id;
      Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
      $this->view->listingtypeArray = $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
      $this->view->listing_singular_uc = ucfirst($listingtypeArray->title_singular);
      $this->view->listing_singular_lc = strtolower($listingtypeArray->title_singular);

      //GET REVIEW TABLE
      $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');


      //GET VIEWER ID
      $viewer = Engine_Api::_()->user()->getViewer();
      $this->view->viewer_id = $viewer_id = $viewer->getIdentity();

      //GET USER LEVEL ID
      if (!empty($viewer_id)) {
        $this->view->level_id = $level_id = $viewer->level_id;
      } else {
        $this->view->level_id = $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
      }

      $autorizationApi = Engine_Api::_()->authorization();
      $this->view->create_level_allow = $create_level_allow = $autorizationApi->getPermission($level_id, 'sitereview_listing', "review_create_listtype_$listingtype_id");

      $create_review = ($sitereview->owner_id == $viewer_id) ? $listingtypeArray->allow_owner_review : 1;

      if (!$create_review || empty($create_level_allow)) {
        $this->view->can_create = $can_create = 0;
      } else {
        $this->view->can_create = $can_create = 1;
      }

      //GET RATING TABLE
      $ratingTable = Engine_Api::_()->getDbTable('ratings', 'sitereview');

      //GET REVIEW ID
      if (!empty($viewer_id)) {
        $params = array();
        $params['resource_id'] = $sitereview->listing_id;
        $params['resource_type'] = $sitereview->getType();
        $params['viewer_id'] = $viewer_id;
        $params['type'] = 'user';
        $review_id = $this->view->hasPosted = $reviewTable->canPostReview($params);
      } else {
        $review_id = $this->view->hasPosted = 0;
      }

      if (empty($can_create)) {
        return $this->_forwardCustom('requireauth', 'error', 'core');
      }

      //CREATE FORM
      if ($this->view->can_create && !$review_id) {

        //FATCH REVIEW CATEGORIES
        $categoryIdsArray = array();
        $categoryIdsArray[] = $sitereview->category_id;
        $categoryIdsArray[] = $sitereview->subcategory_id;
        $categoryIdsArray[] = $sitereview->subsubcategory_id;
        $profileTypeReview = Engine_Api::_()->getDbtable('categories', 'sitereview')->getProfileType($categoryIdsArray, 0, 'profile_type_review');

        $this->view->form = $form = new Sitereview_Form_Review_SitemobileCreate(array('item' => $sitereview, 'profileTypeReview' => $profileTypeReview));
        if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
          Zend_Registry::set('setFixedCreationForm', true);
          Zend_Registry::set('setFixedCreationFormBack', 'Back');
          Zend_Registry::set('setFixedCreationHeaderTitle', Zend_Registry::get('Zend_Translate')->_('Write a Review'));
          Zend_Registry::set('setFixedCreationHeaderSubmit', Zend_Registry::get('Zend_Translate')->_('Submit'));
          $this->view->form->setAttrib('id', 'sitereview_create');
          Zend_Registry::set('setFixedCreationFormId', '#sitereview_create');
          $this->view->form->removeElement('submit');
          $form->setTitle(sprintf(Zend_Registry::get('Zend_Translate')->_('For %s'), $sitereview->getTitle()));
        }
      }

      //START TOP SECTION FOR OVERALL RATING AND IT'S PARAMETER
      $params = array();
      $params['resource_id'] = $listing_id;
      $params['resource_type'] = $sitereview->getType();
      $params['type'] = 'user';
      $noReviewCheck = $reviewTable->getAvgRecommendation($params);
      if (!empty($noReviewCheck)) {
        $this->view->recommend_percentage = round($noReviewCheck->avg_recommend * 100, 3);
      }
      $this->view->ratingDataTopbox = $ratingTable->ratingbyCategory($listing_id, 'user', $sitereview->getType());

      $this->view->isajax = $this->_getParam('isajax', 0);

      //FATCH REVIEW CATEGORIES
      $categoryIdsArray = array();
      $categoryIdsArray[] = $sitereview->category_id;
      $categoryIdsArray[] = $sitereview->subcategory_id;
      $categoryIdsArray[] = $sitereview->subsubcategory_id;
      $this->view->reviewCategory = Engine_Api::_()->getDbtable('ratingparams', 'sitereview')->reviewParams($categoryIdsArray, $sitereview->getType());

      //COUNT REVIEW CATEGORY
      $this->view->total_reviewcats = Count($this->view->reviewCategory);

      //GET REVIEW RATE DATA
      $this->view->reviewRateMyData = $this->view->reviewRateData = $ratingTable->ratingsData($review_id);

      //CUSTOM FIELDS
      $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Sitereview/View/Helper', 'Sitereview_View_Helper');

      $postData = $this->getRequest()->getPost();

      if ($this->getRequest()->isPost() && $postData) {
        if (!$form->isValid($this->getRequest()->getPost())) {
          return;
        }
        if (isset($postData['review_rate_0']) && !$postData['review_rate_0']) {
          $error = $this->view->translate('* Please complete Overall Rating field - it is required.');
          $error = Zend_Registry::get('Zend_Translate')->_($error);

          $form->getDecorator('errors')->setOption('escape', false);
          $form->addError($error);
          return;
        }
        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try {

          $otherValues = $_POST;

          $values = array_merge($postData, $otherValues);

          $values['owner_id'] = $viewer_id;
          $values['resource_id'] = $sitereview->listing_id;
          $values['resource_type'] = $sitereview->getType();
          $values['profile_type_review'] = $profileTypeReview;
          $values['type'] = $viewer_id ? 'user' : 'visitor';
          if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.recommend', 1)) {
            $values['recommend'] = 1;
          } else {
            $values['recommend'] = 0;
          }
          $reviewTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
          $review = $reviewTable->createRow();
          $review->setFromArray($values);
          $review->view_count = 1;
          $review->save();

          if (!empty($profileTypeReview)) {
            //SAVE CUSTOM VALUES AND PROFILE TYPE VALUE
            $form = new Sitereview_Form_Review_Create(array('item' => $sitereview, 'profileTypeReview' => $profileTypeReview));
            $customfieldform = $form->getSubForm('fields');
            $customfieldform->setItem($review);
            $customfieldform->saveValues();
          }

          //INCREASE REVIEW COUNT IN LISTING TABLE
          if (!empty($viewer_id))
            $sitereview->review_count++;

          $sitereview->save();

          $reviewRatingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');
          $reviewRatingTable->delete(array('review_id = ?' => 0, 'resource_id = ?' => $review->resource_id, 'type = ?' => 'user', 'resource_type = ?' => $review->resource_type));

          $postData['user_id'] = $viewer_id;
          $postData['review_id'] = $review->review_id;
          $postData['category_id'] = $sitereview->category_id;
          $postData['resource_id'] = $review->resource_id;
          $postData['resource_type'] = $review->resource_type;

          $review_count = Engine_Api::_()->getDbtable('ratings', 'sitereview')->getReviewId($viewer_id, $sitereview->getType(), $review->resource_id);

          if (count($review_count) == 0 || empty($viewer_id)) {
            //CREATE RATING DATA
            $reviewRatingTable->createRatingData($postData, $values['type']);
          } else {
            $reviewRatingTable->update(array('review_id' => $review->review_id), array('resource_type = ?' => $review->resource_type, 'user_id = ?' => $viewer_id, 'resource_id = ?' => $review->resource_id));
          }

          //UPDATE RATING IN RATING TABLE
          if (!empty($viewer_id)) {
            $reviewRatingTable->listRatingUpdate($review->resource_id, $review->resource_type);
          }

          if (empty($review_id) && !empty($viewer_id) && time() >= strtotime($sitereview->creation_date)) {
            $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');

            //ACTIVITY FEED
            $action = $activityApi->addActivity($viewer, $sitereview, 'sitereview_review_add_listtype_' . $listingtype_id);

            if ($action != null) {
              $activityApi->attachActivity($action, $review);
            }
          }

          if (empty($viewer_id)) {
            $review->status = 0;
            $review->save();
            $email = Engine_Api::_()->getApi('settings', 'core')->core_mail_from;
            $admin_emails = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.contact', Engine_Api::_()->getApi('settings', 'core')->getSetting('core.mail.from', 'email@domain.com'));
            $explodeEmails = explode(",", $admin_emails);
            foreach ($explodeEmails as $value) {
              Engine_Api::_()->getApi('mail', 'core')->sendSystem($value, 'SITEREVIEW_REVIEW_WRITE', array(
                  'listing_Name' => $sitereview->title,
                  'listing_Name_With_link' => '<a href="' . 'http://' . $_SERVER['HTTP_HOST'] .
                  Zend_Controller_Front::getInstance()->getRouter()->assemble(array('listing_id' => $sitereview->listing_id), "sitereview_entry_view_listtype_$listingtype_id", true) . '"  >' . $sitereview->title . '</a>',
                  'user_name' => $review->anonymous_name,
                  'review_title' => $review->title,
                  'review_description' => $review->body,
                  'review_link' => '<a href="' . 'http://' . $_SERVER['HTTP_HOST'] .
                  Zend_Controller_Front::getInstance()->getRouter()->assemble(array('review_id' => $review->review_id, 'listing_id' => $review->resource_id), "sitereview_view_review_listtype_$listingtype_id", true) . '"  >' . 'http://' . $_SERVER['HTTP_HOST'] .
                  Zend_Controller_Front::getInstance()->getRouter()->assemble(array('review_id' => $review->review_id, 'listing_id' => $review->resource_id), "sitereview_view_review_listtype_$listingtype_id", true) . '</a>',
                  'email' => $email,
                  'queue' => false
              ));
            }
          }

          if ($sitereview->owner_id != $viewer_id && !empty($review->owner_id)) {
            $object_parent_with_link = '<a href="' . 'http://' . $_SERVER['HTTP_HOST'] . '/' . $sitereview->getHref() . '">' . $sitereview->getTitle() . '</a>';
            $subjectOwner = $sitereview->getOwner('user');
            $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
            $notifyApi->addNotification($subjectOwner, $viewer, $review, 'sitereview_write_review', array("object_parent_with_link" => $object_parent_with_link));
          }

          $db->commit();
        } catch (Exception $e) {
          $db->rollBack();
          throw $e;
        }

        return $this->_forwardCustom('success', 'utility', 'core', array(
                    'parentRedirect' =>  $sitereview->getHref(array('tab' => Zend_Controller_Front::getInstance()->getRequest()->getParam('tab'))),
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your review has been successfully posted.'))
        ));

        //      return $this->_redirectCustom($sitereview->getHref(array('tab' => Zend_Controller_Front::getInstance()->getRequest()->getParam('tab'))), array('prependBase' => false));
      }
    }
  }

  //ACTION FOR UPDATE THE REVIEW
  public function updateAction() {


    if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
      //REVIEW SUBJECT SHOULD BE SET
      if (!$this->_helper->requireSubject('sitereview_review')->isValid())
        return;

      //GET VIEWER INFO
      $viewer = Engine_Api::_()->user()->getViewer();
      $viewer_id = $viewer->getIdentity();

      $sitereview = Engine_Api::_()->core()->getSubject()->getParent();
      $listingtype_id = $this->_listingType->listingtype_id;

      //FATCH REVIEW CATEGORIES
      $categoryIdsArray = array();
      $categoryIdsArray[] = $sitereview->category_id;
      $categoryIdsArray[] = $sitereview->subcategory_id;
      $categoryIdsArray[] = $sitereview->subsubcategory_id;
      $profileTypeReview = Engine_Api::_()->getDbtable('categories', 'sitereview')->getProfileType($categoryIdsArray, 0, 'profile_type_review');

      //GET USER LEVEL ID
      if (!empty($viewer_id)) {
        $level_id = $viewer->level_id;
      } else {
        $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
      }

      $can_update = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "review_update_listtype_$listingtype_id");

      if (empty($can_update)) {
        return $this->_forwardCustom('requireauth', 'error', 'core');
      }

      $postData = $this->getRequest()->getPost();
      if ($this->getRequest()->isPost() && $postData) {
        $review_id = (int) $this->_getParam('review_id');
        $review = Engine_Api::_()->core()->getSubject();

        $form = new Sitereview_Form_Review_Update(array('item' => $sitereview));
        $form->populate($postData);
        $otherValues = $form->getValues();
        $postData = array_merge($postData, $otherValues);

        $postData['user_id'] = $viewer_id;
        $postData['category_id'] = $sitereview->category_id;
        $postData['resource_id'] = $review->resource_id;
        $postData['resource_type'] = $sitereview->getType();
        $postData['review_id'] = $review_id;
        $postData['profile_type_review'] = $profileTypeReview;
        $reviewDescription = Engine_Api::_()->getDbtable('reviewDescriptions', 'sitereview');
        $reviewDescription->insert(array('review_id' => $review_id, 'body' => $postData['body'], 'modified_date' => date('Y-m-d H:i:s'), 'user_id' => $viewer_id));
        $reviewRatingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');
        $reviewRatingTable->delete(array('review_id = ?' => $review_id));
        //CREATE RATING DATA
        $reviewRatingTable->createRatingData($postData, 'user');
        $getListingRevType = Engine_Api::_()->getApi('listingType', 'sitereview')->getListingReviewType();
        Engine_Api::_()->getDbtable('ratings', 'sitereview')->listRatingUpdate($review->resource_id, $review->resource_type);
        echo Zend_Json::encode(array('captchaError' => 0, 'review_href' => $review->getHref()));

        if (!empty($profileTypeReview)) {
          //SAVE CUSTOM VALUES AND PROFILE TYPE VALUE
          $form = new Sitereview_Form_Review_Create(array('item' => $sitereview, 'profileTypeReview' => $profileTypeReview));
          $form->populate($postData);
          $customfieldform = $form->getSubForm('fields');
          $customfieldform->setItem($review);
          $customfieldform->saveValues();
        }

        exit();
      }
    } else {
      //REVIEW SUBJECT SHOULD BE SET
      if (!$this->_helper->requireSubject('sitereview_review')->isValid())
        return;

      //GET VIEWER INFO
      $viewer = Engine_Api::_()->user()->getViewer();
      $viewer_id = $viewer->getIdentity();

      $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject()->getParent();
      $listingtype_id = $this->_listingType->listingtype_id;
      $this->view->tab = $this->_getParam('tab');
      //FATCH REVIEW CATEGORIES
      $categoryIdsArray = array();
      $categoryIdsArray[] = $sitereview->category_id;
      $categoryIdsArray[] = $sitereview->subcategory_id;
      $categoryIdsArray[] = $sitereview->subsubcategory_id;
      $profileTypeReview = Engine_Api::_()->getDbtable('categories', 'sitereview')->getProfileType($categoryIdsArray, 0, 'profile_type_review');

      $this->view->reviewCategory = Engine_Api::_()->getDbtable('ratingparams', 'sitereview')->reviewParams($categoryIdsArray, $sitereview->getType());

      //COUNT REVIEW CATEGORY
      $this->view->total_reviewcats = Count($this->view->reviewCategory);

      //GET USER LEVEL ID
      if (!empty($viewer_id)) {
        $level_id = $viewer->level_id;
      } else {
        $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
      }

      $this->view->can_update = $can_update = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "review_update_listtype_$listingtype_id");

      if (empty($can_update)) {
        return $this->_forwardCustom('requireauth', 'error', 'core');
      }
      $review_id = (int) $this->_getParam('review_id');

      $review = Engine_Api::_()->core()->getSubject();
      $this->view->reviewRateMyData = Engine_Api::_()->getDbtable('ratings', 'sitereview')->ratingsData($review_id);


      $this->view->form = $form = new Sitereview_Form_Review_Update(array('item' => $sitereview));
      $form->submit->setAttrib('onclick', '');
      if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
        Zend_Registry::set('setFixedCreationForm', true);
        Zend_Registry::set('setFixedCreationFormBack', 'Back');
        Zend_Registry::set('setFixedCreationHeaderTitle', Zend_Registry::get('Zend_Translate')->_('Update your Review'));

        $this->view->form->setAttrib('id', 'sitereview_create');
        Zend_Registry::set('setFixedCreationFormId', '#sitereview_create');
        $form->setTitle('');
      }
      if ($this->getRequest()->isPost() && $this->getRequest()->getPost()) {
        if (!$form->isValid($this->getRequest()->getPost())) {
          return;
        }
        $postData = $this->getRequest()->getPost();
        $otherValues = $form->getValues();
        $postData = array_merge($postData, $otherValues);
        $postData['user_id'] = $viewer_id;
        $postData['category_id'] = $sitereview->category_id;
        $postData['resource_id'] = $review->resource_id;
        $postData['resource_type'] = $sitereview->getType();
        $postData['review_id'] = $review_id;
        $postData['profile_type_review'] = $profileTypeReview;
        $reviewDescription = Engine_Api::_()->getDbtable('reviewDescriptions', 'sitereview');
        $reviewDescription->insert(array('review_id' => $review_id, 'body' => $postData['body'], 'modified_date' => date('Y-m-d H:i:s'), 'user_id' => $viewer_id));
        $reviewRatingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');
        $reviewRatingTable->delete(array('review_id = ?' => $review_id));
        //CREATE RATING DATA
        $reviewRatingTable->createRatingData($postData, 'user');
        $getListingRevType = Engine_Api::_()->getApi('listingType', 'sitereview')->getListingReviewType();
        Engine_Api::_()->getDbtable('ratings', 'sitereview')->listRatingUpdate($review->resource_id, $review->resource_type);

        if (!empty($profileTypeReview)) {
          //SAVE CUSTOM VALUES AND PROFILE TYPE VALUE
          $form = new Sitereview_Form_Review_Create(array('item' => $sitereview, 'profileTypeReview' => $profileTypeReview));
          $form->populate($postData);
          $customfieldform = $form->getSubForm('fields');
          $customfieldform->setItem($review);
          $customfieldform->saveValues();
        }


        return $this->_forwardCustom('success', 'utility', 'core', array(
                    'redirect' => $review->getHref(),
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your review has been updated successfully.'))
        ));

        return $this->_redirectCustom($sitereview->getHref(array('tab' => $this->view->tab)), array('prependBase' => false));
      }
    }
  }

  public function rateAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    $rating = $this->_getParam('rating');
    $listing_id = $this->_getParam('listing_id');

    $postData = array();

    //GET LISTING SUBJECT
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
    $listingtype_id = $sitereview->listingtype_id;

    $reviewRatingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');
    $review = $reviewRatingTable->getReviewId($viewer_id, $sitereview->getType(), $sitereview->listing_id);

    if (count($review) == 0) {
      //CREATE RATING DATA
      $postData['user_id'] = $viewer_id;
      $postData['review_id'] = 0;
      $postData['category_id'] = $sitereview->category_id;
      $postData['resource_id'] = $sitereview->listing_id;
      $postData['resource_type'] = $sitereview->getType();
      $postData['review_rate_0'] = $rating;
      $values['type'] = $viewer_id ? 'user' : 'visitor';
      $reviewRatingTable->createRatingData($postData, $values['type']);
    } else {
      $reviewRatingTable->update(array('rating' => $rating), array('resource_type = ?' => $review->resource_type, 'user_id = ?' => $viewer_id, 'resource_id = ?' => $sitereview->listing_id));
    }

    //UPDATE RATING IN RATING TABLE
    if (!empty($viewer_id) && (count($review) == 0)) {
      $rating_only = 1;
      $user_rating = $reviewRatingTable->listRatingUpdate($sitereview->listing_id, $sitereview->getType(), $rating_only);
    } else {
      $rating_only = 1;
      $user_rating = $reviewRatingTable->listRatingUpdate($review->resource_id, $review->resource_type, $rating_only);
    }

    $totalUsers = $reviewRatingTable->select()
                    ->from($reviewRatingTable->info('name'), 'COUNT(*) AS count')
                    ->where('user_id != ?', 0)
                    ->where('type = ?', 'user')
                    ->where('resource_id = ?', $sitereview->listing_id)
                    ->query()->fetchColumn();

    $data = array();
    $data[] = array(
        'rating' => $rating,
        'rating_users' => $user_rating,
        'users' => $totalUsers,
    );
    return $this->_helper->json($data);
    $data = Zend_Json::encode($data);
    $this->getResponse()->setBody($data);
  }

  //ACTION FOR MARKING HELPFUL REVIEWS
  public function helpfulAction() {

    //NOT VALID USER THEN RETURN
    if (!$this->_helper->requireUser()->isValid())
      return;

    //GET VIEWER DETAIL
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    //GET RATING
    $helpful = $this->_getParam('helpful');

    //GET REVIEW ID
    $review_id = $this->_getParam('review_id');
    $review = Engine_Api::_()->core()->getSubject();
    $sitereview = Engine_Api::_()->core()->getSubject()->getParent();
    $listingtype_id = $sitereview->listingtype_id;
    $anonymous = $this->_getParam('anonymous', 0);
    if (!empty($anonymous)) {
      return $this->_helper->redirector->gotoRoute(array('review_id' => $review_id, 'listing_id' => $review->resource_id), "sitereview_view_review_listtype_$listingtype_id", true);
    }

    //GET HELPFUL TABLE
    $helpfulTable = Engine_Api::_()->getDbtable('helpful', 'sitereview');

    $this->view->already_entry = $helpfulTable->getHelpful($review_id, $viewer_id, $helpful);

    if (empty($this->view->already_entry)) {
      $this->view->already_entry = 0;
    }

    //MAKE ENTRY FOR HELPFUL
    $helpfulTable->setHelful($review_id, $viewer_id, $helpful);

    echo Zend_Json::encode(array('already_entry' => $this->view->already_entry));
    exit();
  }

  //ACTION FOR VIEW REVIEWS
  public function viewAction() {

    //IF ANONYMOUS USER THEN SEND HIM TO SIGN IN PAGE
    $check_anonymous_help = $this->_getParam('anonymous');
    if ($check_anonymous_help) {
      if (!$this->_helper->requireUser()->isValid())
        return;
    }

    //GET LOGGED IN USER INFORMATION
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    if (!Engine_Api::_()->core()->hasSubject()) {
      return $this->_forwardCustom('notfound', 'error', 'core');
    }

    //GET LISTING ID AND OBJECT
    $sitereview = Engine_Api::_()->core()->getSubject()->getParent();

    $listingtype_id = $this->_listingType->listingtype_id;

    //WHO CAN VIEW THE LISTINGS
    if (!$this->_helper->requireAuth()->setAuthParams($sitereview, null, "view_listtype_$listingtype_id")->isValid() || empty($this->_listingType->allow_review)) {
      return $this->_forwardCustom('requireauth', 'error', 'core');
    }

    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
      if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "user_review"))
        return $this->_forward('requireauth', 'error', 'core');
    }

    $review = Engine_Api::_()->core()->getSubject();
    if (empty($review)) {
      return $this->_forwardCustom('requireauth', 'error', 'core');
    }

    //GET USER LEVEL ID
    if (!empty($viewer_id)) {
      $level_id = $viewer->level_id;
    } else {
      $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
    }

    //GET LEVEL SETTING
    $can_view = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "view_listtype_$listingtype_id");

    if ($can_view != 2 && $viewer_id != $sitereview->owner_id && ($sitereview->draft == 1 || $sitereview->search == 0 || $sitereview->approved != 1)) {
      return $this->_forwardCustom('requireauth', 'error', 'core');
    }

    if ($can_view != 2 && ($review->status != 1 && empty($review->owner_id))) {
      return $this->_forwardCustom('requireauth', 'error', 'core');
    }

    $params = array();
    $params['listing_type_title'] = ucfirst($this->_listingType->title_plural);

    //GET LOCATION
    if (!empty($sitereview->location) && $this->_listingType->location) {
      $params['location'] = $sitereview->location;
    }

    $params['tag'] = $sitereview->getKeywords(', ');

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

    //SET META KEYWORDS
    Engine_Api::_()->sitereview()->setMetaKeywords($params);

    //IF MODE IS APP  THEN FIXED THE HEADER.
    if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
      Zend_Registry::set('setFixedCreationFormBack', 'Back');
    }

    //GET PAGE OBJECT
    $pageTable = Engine_Api::_()->getDbtable('pages', 'core');
    $pageSelect = $pageTable->select()->where('name = ?', "sitereview_review_view");
    $pageObject = $pageTable->fetchRow($pageSelect);

    $this->_helper->content
            ->setContentName('sitereview_review_view')
            ->setNoRender()
            ->setEnabled();
  }

  //ACTION FOR DELETING REVIEW
  public function deleteAction() {

    //ONLY LOGGED IN USER CAN DELETE REVIEW
    if (!$this->_helper->requireUser()->isValid())
      return;

    //SUBJECT SHOULD BE SET
    if (!$this->_helper->requireSubject('sitereview_review')->isValid())
      return;

    //GET VIEWER ID
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->review = $review = Engine_Api::_()->core()->getSubject();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();
    $this->view->sitereview = $sitereview = $review->getParent();

    //GET REVIEW ID AND REVIEW OBJECT
    $review_id = $this->_getParam('review_id');
    $listingtype_id = $this->_listingType->listingtype_id;

    //AUTHORIZATION CHECK
    $can_delete = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "review_delete_listtype_$listingtype_id");

    //WHO CAN DELETE THE REVIEW
    if (empty($can_delete) || ($can_delete == 1 && $viewer_id != $review->owner_id)) {
      return $this->_forwardCustom('requireauth', 'error', 'core');
    }

    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        //DELETE REVIEW FROM DATABASE
        Engine_Api::_()->getItem('sitereview_review', (int) $review_id)->delete();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      //REDIRECT
      $url = $this->_helper->url->url(array('listing_id' => $sitereview->getIdentity(), 'slug' => $sitereview->getSlug(), 'tab' => $this->_getParam('tab')), "sitereview_entry_view_listtype_$listingtype_id", true);
      
      return $this->_forwardCustom('success', 'utility', 'core', array(
                  'parentRedirect' => $url,
                  'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your review has been deleted successfully.'))
      ));
    } else {
      $this->renderScript('review/delete.tpl');
    }
  }

  //VALIDATES CAPTCHA RESPONSE
  function validateCaptcha($captcha) {

    $captchaId = $captcha['id'];
    $captchaInput = $captcha['input'];
    $session = new Zend_Session_Namespace();
    if (!isset($session->setword)) {
      $captchaSession = new Zend_Session_Namespace('Zend_Form_Captcha_' . $captchaId);
      $captchaIterator = $captchaSession->getIterator();
      if (isset($captchaIterator['word']))
        $captchaWord = $captchaIterator['word'];
      $session->setword = $captchaWord;
    }
    else {
      $captchaWord = $session->setword;
    }

    if ($captchaWord) {
      if ($captchaInput != $captchaWord) {
        return 0;
      } else {
        return 1;
      }
    } else {
      return 0;
    }
  }

  //ACTION FOR EMAIL THE REVIEW
  public function emailAction() {

    if (!$this->_helper->requireUser()->isValid())
      return;
    $sitemobile = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemobile');
    //SUBJECT SHOULD BE SET
    if (!$this->_helper->requireSubject('sitereview_review')->isValid())
      return;

    //SET LAYOUT
    $this->_helper->layout->setLayout('default-simple');

    $review = Engine_Api::_()->core()->getSubject();
    $sitereview = $review->getParent();
    $listingtype_id = $sitereview->listingtype_id;

    //GET FORM
    $this->view->form = $form = new Sitereview_Form_Review_Email();
    if (Engine_Api::_()->seaocore()->isSitemobileApp()) {
      Zend_Registry::set('setFixedCreationForm', true);
      Zend_Registry::set('setFixedCreationFormBack', 'Back');
      Zend_Registry::set('setFixedCreationHeaderTitle', Zend_Registry::get('Zend_Translate')->_('Email Review'));
      Zend_Registry::set('setFixedCreationHeaderSubmit', Zend_Registry::get('Zend_Translate')->_('Send'));
      $this->view->form->setAttrib('id', 'emailReviewForm');
      Zend_Registry::set('setFixedCreationFormId', '#emailReviewForm');
      $this->view->form->removeElement('send');
      $this->view->form->removeElement('cancel');
      $form->setTitle('');
    }
    //NOT VALID FORM POST THEN RETURN
    if (!$this->getRequest()->isPost())
      return;

    //FORM VALIDATION
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      //GET VIEWER ID
      $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
      $postData = $this->getRequest()->getPost();
      $emailTo = $postData['emailTo'];
      $userComment = $postData['userComment'];

      //EDPLODES EMAIL IDS
      $reciver_ids = explode(',', $postData['emailTo']);

      //CHECK VALID EMAIL ID FORMITE
      $validator = new Zend_Validate_EmailAddress();
      $validator->getHostnameValidator()->setValidateTld(false);

      foreach ($reciver_ids as $reciver_id) {
        $reciver_id = trim($reciver_id, ' ');
        if (!$validator->isValid($reciver_id)) {
          $form->addError(Zend_Registry::get('Zend_Translate')->_('Please enter correct email address of the receiver(s).'));
          return;
        }
      }

      //SEND EMAIL
      Engine_Api::_()->getApi('mail', 'core')->sendSystem($reciver_ids, 'SITEREVIEW_EMAIL_FRIEND', array(
          'user_email' => Engine_Api::_()->getItem('user', $viewer_id)->email,
          'userComment' => $userComment,
          'site_title' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.site.title', 1),
          'review_title' => $review->title,
          'review_title_with_link' => '<a href="' . 'http://' . $_SERVER['HTTP_HOST'] .
          Zend_Controller_Front::getInstance()->getRouter()->assemble(array('review_id' => $review->review_id, 'listing_id' => $sitereview->listing_id), "sitereview_view_review_listtype_$listingtype_id", true) . '">' . $review->title . '</a>',
          'email' => Engine_Api::_()->getApi('settings', 'core')->core_mail_from,
          'queue' => false
      ));

      if ($sitemobile && Engine_Api::_()->sitemobile()->checkMode('mobile-mode'))
        $this->_forwardCustom('success', 'utility', 'core', array(
            'parentRedirect' => $review->getHref(),
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your message has been sent successfully.'))
        ));
      else
        $this->_forwardCustom('success', 'utility', 'core', array(
            'smoothboxClose' => true,
            //'parentRefreshTime' => '15',
            //'format' => 'smoothbox',
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your message has been sent successfully.'))
        ));
    }
  }

}