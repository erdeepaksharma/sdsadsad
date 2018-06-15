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
class Sitereview_Widget_OwnerreviewsSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_review')) {
      return $this->setNoRender();
    }

    $this->view->review = $review = Engine_Api::_()->core()->getSubject('sitereview_review');

    if (empty($review->owner_id)) {
      return $this->setNoRender();
    }

    //FETCH REVIEW DATA
    $params = array();
    $this->view->statistics = $this->_getParam('statistics', array("likeCount", "replyCount", "commentCount"));
    $params['limit'] = $this->_getParam('itemCount', 3);
    $params['resource_type'] = $review->getParent()->getType();
    $params['order'] = $params['rating'] = 'rating';
    $params['review_id'] = $review->review_id;
    $params['owner_id'] = $review->owner_id;
    $this->view->reviews = $reviews = Engine_Api::_()->getDbtable('reviews', 'sitereview')->listReviews($params);

    if ($reviews->getTotalItemCount() <= 0) {
      return $this->setNoRender();
    }
  }

}