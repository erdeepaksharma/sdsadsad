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
class Sitereview_Widget_ReviewsStatisticsController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id');
    $reviewTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
    $paginator = $reviewTable->getReviewsPaginator(array('type' => 'user', 'resource_type' => 'sitereview_listing', 'listingtype_id' => $listingtype_id));

    $this->view->totalReviews = $paginator->getTotalItemCount();
    $recommendpaginator = $reviewTable->getReviewsPaginator(array('type' => 'user', 'recommend' => 1, 'resource_type' => 'sitereview_listing', 'listingtype_id' => $listingtype_id));

    $this->view->totalRecommend = $recommendpaginator->getTotalItemCount();
    $ratingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');
    $ratingCount = array();
    
    for ($i = 5; $i > 0; $i--) {
      $ratingCount[$i] = $ratingTable->getNumbersOfUserRating(0, 'user', 0, $i, 0, 'sitereview_listing', array('listingtype_id' => $listingtype_id));
    }
    
    $this->view->ratingCount = $ratingCount;
  }

}
