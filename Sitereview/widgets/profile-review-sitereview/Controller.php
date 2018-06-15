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
class Sitereview_Widget_ProfileReviewSitereviewController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF NOT AUTHORIZED
    if (!Engine_Api::_()->core()->hasSubject('sitereview_review')) {
      return $this->setNoRender();
    }

    //UNSET THE CAPTECHA WORD
    $session = new Zend_Session_Namespace();
    if (isset($session->setword)) {
      unset($session->setword);
    }

    //SET PARAMS
    $this->view->params = $params = $this->_getAllParams();

    //GET VIEWER DETAIL
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();

    //GET USER LEVEL ID
    if (!empty($viewer_id)) {
      $this->view->level_id = $level_id = $viewer->level_id;
    } else {
      $this->view->level_id = $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
    }

    //GET REVIEW TABLE
    $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');

    //GET RATING TABLE
    $ratingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');

    //GET SITEREVIEW
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject()->getParent();
    $sitereviewProfileReview = Zend_Registry::isRegistered('sitereviewProfileReview') ?  Zend_Registry::get('sitereviewProfileReview') : null;

    $this->view->listing_id = $listing_id = $sitereview->listing_id;
    $resource_type = $sitereview->getType();
    $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $this->view->listing_singular_uc = ucfirst($listingtypeArray->title_singular);
    $this->view->listing_singular_lc = strtolower($listingtypeArray->title_singular);
    if (empty($listingtypeArray->reviews) || $listingtypeArray->reviews == 1 || empty($sitereviewProfileReview)) {
      return $this->setNoRender();
    }

    //SET HAS POSTED
    if (empty($viewer_id)) {
      $hasPosted = $this->view->hasPosted = 0;
    } else {
      $params = array();
      $params['resource_id'] = $sitereview->listing_id;
      $params['resource_type'] = $resource_type;
      $params['viewer_id'] = $viewer_id;
      $params['type'] = 'user';
      $hasPosted = $this->view->hasPosted = $reviewTable->canPostReview($params);
    }

    //GET WIDGET PARAMETERS
    $coreApi = Engine_Api::_()->getApi('settings', 'core');
    $this->view->getListType = true;
    $this->view->sitereview_proscons = $sitereview_proscons = $coreApi->getSetting('sitereview.proscons', 1);
    $this->view->sitereview_limit_proscons = $sitereview_limit_proscons = $coreApi->getSetting('sitereview.limit.proscons', 500);
    $this->view->sitereview_recommend = $sitereview_recommend = $coreApi->getSetting('sitereview.recommend', 1);
    $this->view->sitereview_report = $coreApi->getSetting('sitereview.report', 1);
    $this->view->sitereview_email = $coreApi->getSetting('sitereview.email', 1);
    $this->view->sitereview_share = $coreApi->getSetting('sitereview.share', 1);

    $this->view->create_level_allow = $create_level_allow = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "review_create_listtype_$listingtype_id");

    $create_review = ($sitereview->owner_id == $viewer_id) ? $listingtypeArray->allow_owner_review : 1;

    if (!$create_review || empty($create_level_allow)) {
      $this->view->can_create = 0;
    } else {
      $this->view->can_create = 1;
    }

    $this->view->can_delete = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "review_delete_listtype_$listingtype_id");

    $this->view->can_reply = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "review_reply_listtype_$listingtype_id");

    $this->view->can_update = $can_update = Engine_Api::_()->authorization()->getPermission($level_id, 'sitereview_listing', "review_update_listtype_$listingtype_id");

    //MAKE CREATE FORM
    if ($this->view->can_create && !$hasPosted) {

      //FATCH REVIEW CATEGORIES
      $categoryIdsArray = array();
      $categoryIdsArray[] = $sitereview->category_id;
      $categoryIdsArray[] = $sitereview->subcategory_id;
      $categoryIdsArray[] = $sitereview->subsubcategory_id;
      $profileTypeReview = Engine_Api::_()->getDbtable('categories', 'sitereview')->getProfileType($categoryIdsArray, 0, 'profile_type_review');

      $this->view->form = $form = new Sitereview_Form_Review_Create(array("settingsReview" => array('sitereview_proscons' => $this->view->sitereview_proscons, 'sitereview_limit_proscons' => $this->view->sitereview_limit_proscons, 'sitereview_recommend' => $this->view->sitereview_recommend), 'item' => $sitereview, 'profileTypeReview' => $profileTypeReview));
    }

    $this->view->review_id = $review_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('review_id');

    //UPDATE FORM
    if ($can_update && $hasPosted) {
      $this->view->update_form = new Sitereview_Form_Review_Update(array('item' => $sitereview));
    }

    //GET REVIEW ITEM
    $this->view->reviews = Engine_Api::_()->getItem('sitereview_review', $review_id);
    $this->view->tab_id = Engine_Api::_()->sitereview()->getTabId($listingtype_id, 'sitereview.user-sitereview');
    $params = array();
    $params['resource_id'] = $listing_id;
    $params['resource_type'] = $resource_type;
    $params['type'] = 'user';
    $this->view->totalReviews = $reviewTable->totalReviews($params);

    if ($this->view->reviews->profile_type_review) {
      //CUSTOM FIELDS
      $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Sitereview/View/Helper', 'Sitereview_View_Helper');
      $this->view->fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($this->view->reviews);
    }

    //START TOP SECTION FOR OVERALL RATING AND IT'S PARAMETER
    $noReviewCheck = $reviewTable->getAvgRecommendation($params);

		if (!empty($noReviewCheck)) {
			$this->view->noReviewCheck = $noReviewCheck->toArray();
			if($this->view->noReviewCheck)
			$this->view->recommend_percentage = round($noReviewCheck[0]['avg_recommend'] * 100, 3);
			$this->view->ratingDataTopbox = $ratingTable->ratingbyCategory($listing_id, 'user', $resource_type);
		}

    //FATCH REVIEW CATEGORIES
    $categoryIdsArray = array();
    $categoryIdsArray[] = $sitereview->category_id;
    $categoryIdsArray[] = $sitereview->subcategory_id;
    $categoryIdsArray[] = $sitereview->subsubcategory_id;
    $this->view->reviewCategory = Engine_Api::_()->getDbtable('ratingparams', 'sitereview')->reviewParams($categoryIdsArray, $resource_type);
    $this->view->total_reviewcats = Count($this->view->reviewCategory);
    $this->view->reviewRateData = $ratingTable->ratingsData($review_id);
    $this->view->reviewRateMyData = $ratingTable->ratingsData($hasPosted);
    $this->view->checkPage = "reviewProfile";
    $this->view->is_ajax = $is_ajax = $this->_getParam('is_ajax', '');

    if (!empty($listingtypeArray->price)) {
      $this->view->price = $sitereview->price;
    }
  }

}