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
class Sitereview_Widget_ReviewOfTheDayController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $review_id = $this->_getParam('review_id');

    if (empty($review_id)) {
      return $this->setNoRender();
    }

    //GET REVIEW OF THE DAY
    $this->view->review = $review = Engine_Api::_()->getItem('sitereview_review', $review_id);

    if (empty($review) || $review->status != 1) {
      return $this->setNoRender();
    }
    
    //GET OVERALL RATING VALUE
    $this->view->overallRating = Engine_Api::_()->getDbTable('ratings', 'sitereview')->getOverallRating('sitereview_listing', $review_id);

    $starttime = $this->_getParam('starttime');
    $endtime = $this->_getParam('endtime');
    $currenttime = date('Y-m-d H:i:s');

    if (!empty($starttime) && $currenttime < $starttime) {
      return $this->setNoRender();
    }

    if (!empty($endtime) && $currenttime > $endtime) {
      return $this->setNoRender();
    } 
  }

}
