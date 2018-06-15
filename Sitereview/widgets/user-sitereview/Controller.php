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
class Sitereview_Widget_UserSitereviewController extends Seaocore_Content_Widget_Abstract {

  protected $_childCount;

  public function indexAction() {

    //CHECK SUBJECT
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    //GET LISTING SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');


    $this->view->listing_id = $listing_id = $sitereview->getIdentity();
    $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $this->view->listingtypeArray = $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $sitereviewGetAttemptType = Zend_Registry::isRegistered('sitereviewGetAttemptType') ? Zend_Registry::get('sitereviewGetAttemptType') : null;
    $sitereviewLsettings = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.lsettings', false);
    $sitereviewListingTypeOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.listingtype.order', false);
    $sitereviewProfileOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.profile.order', false);
    $sitereviewViewAttempt = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.view.attempt', false);
    $sitereviewViewType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.viewtype', false);
    $sitereviewViewAttempt = !empty($sitereviewGetAttemptType) ? $sitereviewGetAttemptType : @convert_uudecode($sitereviewViewAttempt);
    $sitereviewUserReview = Zend_Registry::isRegistered('sitereviewUserReview') ? Zend_Registry::get('sitereviewUserReview') : null;
    $this->view->listing_singular_uc = ucfirst($listingtypeArray->title_singular);
    $this->view->listing_singular_lc = strtolower($listingtypeArray->title_singular);
    if (empty($listingtypeArray->reviews) || $listingtypeArray->reviews == 1 || empty($sitereviewUserReview)) {
      return $this->setNoRender();
    }

    //GET REVIEW TABLE
    $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');

    //SET PARAMS
    $this->view->params = $this->_getAllParams();

    //UNSET CAPTCHA WORD
    $session = new Zend_Session_Namespace();
    if (isset($session->setword)) {
      unset($session->setword);
    }

    //LOADED BY AJAX
    if ($this->_getParam('loaded_by_ajax', false)) {
      $this->view->loaded_by_ajax = true;
      if ($this->_getParam('is_ajax_load', false)) {
        $this->view->is_ajax_load = true;
        $this->view->loaded_by_ajax = false;
        if (!$this->_getParam('onloadAdd', false))
          $this->getElement()->removeDecorator('Title');
        $this->getElement()->removeDecorator('Container');
      } else {
        $params['resource_id'] = $listing_id;
        $params['resource_type'] = $sitereview->getType();
        $params['type'] = 'user';
        $paginator = $reviewTable->listReviews($params);
        $this->_childCount = $paginator->getTotalItemCount();
        return;
      }
    } else {
      if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
        if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "user_review"))
          return $this->setNoRender();
      }
    }
    $this->view->showContent = true;

    //GET VIEWER ID
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();

    $tempGetFinalNumber = $sitereviewSponsoredOrder = $sitereviewFeaturedOrder = 0;
    for ($tempFlag = 0; $tempFlag < strlen($sitereviewLsettings); $tempFlag++) {
      $sitereviewFeaturedOrder += @ord($sitereviewLsettings[$tempFlag]);
    }

    for ($tempFlag = 0; $tempFlag < strlen($sitereviewViewAttempt); $tempFlag++) {
      $sitereviewSponsoredOrder += @ord($sitereviewViewAttempt[$tempFlag]);
    }

    $sitereviewListingTypeOrder += $sitereviewFeaturedOrder + $sitereviewSponsoredOrder;

    //GET USER LEVEL ID
    if (!empty($viewer_id)) {
      $this->view->level_id = $level_id = $viewer->level_id;
    } else {
      $this->view->level_id = $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
    }

    $autorizationApi = Engine_Api::_()->authorization();
    $this->view->create_level_allow = $create_level_allow = $autorizationApi->getPermission($level_id, 'sitereview_listing', "review_create_listtype_$listingtype_id");

    if (!empty($sitereviewViewType) || (!empty($sitereviewProfileOrder) && !empty($sitereviewListingTypeOrder) && ($sitereviewListingTypeOrder == $sitereviewProfileOrder))) {
      $this->view->isEnabledListingType = true;
    }

    $this->view->can_update = $can_update = $autorizationApi->getPermission($level_id, 'sitereview_listing', "review_update_listtype_$listingtype_id");

    $create_review = ($sitereview->owner_id == $viewer_id) ? $listingtypeArray->allow_owner_review : 1;

    if (!$create_review || empty($create_level_allow)) {
      $this->view->can_create = 0;
    } else {
      $this->view->can_create = 1;
    }

    //GET RATING TABLE
    $ratingTable = Engine_Api::_()->getDbTable('ratings', 'sitereview');
    $coreApi = Engine_Api::_()->getApi('settings', 'core');

    //GET WIDGET PARAMETERS
    $this->view->sitereview_proscons = $sitereview_proscons = $coreApi->getSetting('sitereview.proscons', 1);
    $sitereview_limit_proscons = $coreApi->getSetting('sitereview.limit.proscons', 500);
    $sitereview_recommend = $coreApi->getSetting('sitereview.recommend', 1);
    $this->view->sitereview_report = $coreApi->getSetting('sitereview.report', 1);
    $this->view->sitereview_email = $coreApi->getSetting('sitereview.email', 1);
    $this->view->sitereview_share = $coreApi->getSetting('sitereview.share', 1);

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

    //CREATE FORM
    if ($this->view->can_create && !$review_id) {

      //FATCH REVIEW CATEGORIES
      $categoryIdsArray = array();
      $categoryIdsArray[] = $sitereview->category_id;
      $categoryIdsArray[] = $sitereview->subcategory_id;
      $categoryIdsArray[] = $sitereview->subsubcategory_id;
      $profileTypeReview = Engine_Api::_()->getDbtable('categories', 'sitereview')->getProfileType($categoryIdsArray, 0, 'profile_type_review');

      $this->view->form = new Sitereview_Form_Review_Create(array("settingsReview" => array('sitereview_proscons' => $sitereview_proscons, 'sitereview_limit_proscons' => $sitereview_limit_proscons, 'sitereview_recommend' => $sitereview_recommend), 'item' => $sitereview, 'profileTypeReview' => $profileTypeReview));
    }

    //UPDATE FORM
    if ($can_update && $review_id) {
      $this->view->update_form = $update_form = new Sitereview_Form_Review_Update(array('item' => $sitereview));
    }

    //START TOP SECTION FOR OVERALL RATING AND IT'S PARAMETER
    $params = array();
    $params['resource_id'] = $listing_id;
    $params['resource_type'] = $sitereview->getType();
    $params['type'] = 'user';
    $noReviewCheck = $reviewTable->getAvgRecommendation($params);
    if (!empty($noReviewCheck)) {
      $this->view->noReviewCheck = $noReviewCheck->toArray();
      if ($this->view->noReviewCheck)
        $this->view->recommend_percentage = round($noReviewCheck[0]['avg_recommend'] * 100, 3);
    }
    $this->view->ratingDataTopbox = $ratingTable->ratingbyCategory($listing_id, 'user', $sitereview->getType());

    $this->view->isajax = $this->_getParam('isajax', 0);

    //GET FILTER
    $option = $this->_getParam('option', 'fullreviews');
    $this->view->reviewOption = $params['option'] = $option;

    //SET ITEM PER PAGE
    if ($option == 'prosonly' || $option == 'consonly') {
      $this->view->itemProsConsCount = $setItemCountPerPage = $this->_getParam('itemProsConsCount', 20);
    } else {
      $this->view->itemReviewsCount = $setItemCountPerPage = $this->_getParam('itemReviewsCount', 5);
    }

    //GET SORTING ORDER
    $this->view->reviewOrder = $params['order'] = $this->_getParam('order', 'creationDate');
    $this->view->rating_value = $this->_getParam('rating_value', 0);

    $params['rating'] = 'rating';
    $params['rating_value'] = $this->view->rating_value;
    $params['resource_id'] = $listing_id;
    $params['resource_type'] = $sitereview->getType();
    $params['type'] = 'user';
    $this->view->params = $params;
    $paginator = $reviewTable->listReviews($params);
    $this->view->paginator = $paginator->setItemCountPerPage($setItemCountPerPage);
    $this->view->current_page = $current_page = $this->_getParam('page', 1);
    $this->view->paginator = $paginator->setCurrentPageNumber($current_page);

    //GET TOTAL REVIEWS
    $this->_childCount = $this->view->totalReviews = $paginator->getTotalItemCount();

    //FATCH REVIEW CATEGORIES
    $categoryIdsArray = array();
    $categoryIdsArray[] = $sitereview->category_id;
    $categoryIdsArray[] = $sitereview->subcategory_id;
    $categoryIdsArray[] = $sitereview->subsubcategory_id;
    $this->view->reviewCategory = Engine_Api::_()->getDbtable('ratingparams', 'sitereview')->reviewParams($categoryIdsArray, $sitereview->getType());

    //COUNT REVIEW CATEGORY
    $this->view->total_reviewcats = Count($this->view->reviewCategory);

    //GET REVIEW RATE DATA
    $this->view->reviewRateMyData = $this->view->reviewRateData = $ratingTable->ratingsData($review_id, $viewer_id, $listing_id);

    //CAN DELETE
    $this->view->can_delete = $autorizationApi->getPermission($level_id, 'sitereview_listing', "review_delete_listtype_$listingtype_id");

    //CAN REPLY
    $this->view->can_reply = $autorizationApi->getPermission($level_id, 'sitereview_listing', "review_reply_listtype_$listingtype_id");

    //CHECK PAGE
    $this->view->checkPage = "listingProfile";

    //CUSTOM FIELDS
    $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Sitereview/View/Helper', 'Sitereview_View_Helper');
  }

  public function getChildCount() {
    return $this->_childCount;
  }

}