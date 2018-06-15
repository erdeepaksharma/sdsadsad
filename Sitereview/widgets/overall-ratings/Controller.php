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
class Sitereview_Widget_OverallRatingsController extends Seaocore_Content_Widget_Abstract {

  public function indexAction() {

    //SET NO RENDER IF NO SUBJECT
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    //GET SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');

    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
      $editorReview = $sitereview->getEditorReview();  
      if (!Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($sitereview->package_id, "user_review") && empty($editorReview))
        return $this->setNoRender();
    }

    $this->view->listingtype_id = $listingtype_id = $sitereview->listingtype_id;
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $this->view->allow_review = $listingtypeArray->allow_review;
    $this->view->listing_singular_uc = ucfirst($listingtypeArray->title_singular);

    //GET SETTING
    $this->view->show_rating = $show_rating = $this->_getParam('show_rating', 'both');
    $this->view->ratingParameter = $ratingParameter = $this->_getParam('ratingParameter', 1);
    //DO NOT RENDER THIS WIDGET IF BOTH TYPE OF REVIEWS ARE NOT ALLOWED
    $this->view->reviewsAllowed = $listingtypeArray->reviews;
    if (empty($listingtypeArray->reviews)) {
      return $this->setNoRender();
    } elseif ($listingtypeArray->reviews == 1) {
      $this->view->show_rating = $show_rating = 'editor';
    } elseif ($listingtypeArray->reviews == 2) {
      $this->view->show_rating = $show_rating = 'avg';
    }

    $this->view->listing_id = $listing_id = $sitereview->getIdentity();

    //GET REVIEW TABLE
    $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
    //GET RATING TABLE
    $ratingTable = Engine_Api::_()->getDbTable('ratings', 'sitereview');

    if ($show_rating == 'both' || $show_rating == 'avg') {
      //START TOP SECTION FOR OVERALL RATING AND IT'S PARAMETER
      $params = array();
      $params['resource_id'] = $listing_id;
      $params['resource_type'] = $sitereview->getType();
      $noReviewCheck = $reviewTable->getAvgRecommendation($params);
      if (!empty($noReviewCheck)) {
        $this->view->noReviewCheck = $noReviewCheck->toArray();
        if ($this->view->noReviewCheck)
          $this->view->recommend_percentage = round($noReviewCheck[0]['avg_recommend'] * 100, 3);
      }
      $type = null;
      if ($show_rating == 'both') {
        $type = 'user';
      }
      $this->view->type = $type;
      $this->view->ratingData = $ratingTable->ratingbyCategory($listing_id, $type, $sitereview->getType());
    }

    if ($show_rating == 'both' || $show_rating == 'editor') {
      $this->view->ratingEditorData = $ratingTable->ratingbyCategory($listing_id, 'editor', $sitereview->getType());
      $this->view->editorReview = $sitereview->getEditorReview();
    }
  }

}