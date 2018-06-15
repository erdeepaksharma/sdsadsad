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
class Sitereview_Widget_PopularReviewsSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    //FETCH REVIEW DATA
    $params = array();
    $this->view->popularity = $params['popularity'] = $this->_getParam('popularity', 'view_count');
    $params['limit'] = $this->_getParam('itemCount', 3);
    $this->view->type = $params['type'] = $this->_getParam('type', 'user');
    $this->view->status = $params['status'] = $this->_getParam('status', 0);
    $params['interval'] = $interval = $this->_getParam('interval', 'overall');
    $params['groupby'] = $this->_getParam('groupby', 1);
    $this->view->title_truncation = $this->_getParam('truncation', 16);
    $params['resource_type'] = 'sitereview_listing';
    $this->view->statistics = $this->_getParam('statistics', array("likeCount", "commentCount"));

    //GET LISTING TYPE ID
    $params['listingtype_id'] = $listingtype_id = $this->_getParam('listingtype_id', null);
    if (empty($listingtype_id)) {
      $params['listingtype_id'] = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    }

    //IF SOME REVIEW TYPE IS NOT ALLOWED AND LISTING TYPE ID IS NOT EMPTY
    if ($listingtype_id > 0) {
      Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
      $listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);

      if (empty($listingType->reviews) || ($listingType->reviews == 2 && $this->view->type == 'editor') || ($listingType->reviews == 1 && $this->view->type == 'user')) {
        return $this->setNoRender();
      }
    }
    else {
      Engine_Api::_()->sitereview()->setListingTypeInRegistry(-1);
    }

    //GET REVIEWS
    $this->view->reviews = Engine_Api::_()->getDbtable('reviews', 'sitereview')->getReviews($params);

    //DON'T RENDER IF NO DATA FOUND
    if ((Count($this->view->reviews) <= 0)) {
      return $this->setNoRender();
    }
  }

}
