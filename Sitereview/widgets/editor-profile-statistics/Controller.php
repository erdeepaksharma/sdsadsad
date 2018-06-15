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
class Sitereview_Widget_EditorProfileStatisticsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('user')) {
      return $this->setNoRender();
    }

    //GET USER SUBJECT
    $this->view->user = $user = Engine_Api::_()->core()->getSubject('user');

    //GET TOTAL REVIEW COUNT
    $reviewTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');

    $params = array();
    $params['owner_id'] = $user->getIdentity();
    $params['type'] = 'user';
    $this->view->totalUserReviews = $reviewTable->totalReviews($params);
    $params['type'] = 'editor';
    $this->view->totalEditorReviews = $reviewTable->totalReviews($params);
    $this->view->totalReviews = $this->view->totalUserReviews + $this->view->totalEditorReviews;

    //GET TOTAL COMMENT COUNT
    $this->view->totalComments = $reviewTable->countReviewComments($user->getIdentity());

    //GET TOTAL CATEGORIES IN WHICH THIS EDITOR HAS GIVEN REVIEWS
    $this->view->totalCategoriesReview = $reviewTable->countReviewCategories($user->getIdentity(), 'sitereview_listing');

    $ratingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');
    $ratingCount = array();
    for ($i = 5; $i > 0; $i--) {
      $ratingCount[$i] = $ratingTable->getNumbersOfUserRating(0, '', 0, $i, $user->getIdentity());
    }
    $this->view->ratingCount = $ratingCount;
  }

}
