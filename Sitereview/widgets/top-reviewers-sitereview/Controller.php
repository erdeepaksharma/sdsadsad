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
class Sitereview_Widget_TopReviewersSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    
    //GET SETTINGS
    $params = array();
    $params['limit'] = $this->_getParam('itemCount', 3);
    $this->view->type = $params['type'] = $this->_getParam('type', 'user');

    //GET LISTING TYPE ID
    $params['listingtype_id'] = $this->_getParam('listingtype_id', null);
    if (empty($listingtype_id)) {
      $params['listingtype_id'] = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    }
    $params['resource_type'] = 'sitereview_listing';

    //GET RESULTS
    $this->view->reviewers = Engine_Api::_()->getDbtable('reviews', 'sitereview')->topReviewers($params);

    //DON'T RENDER IF NO DATA
    if (Count($this->view->reviewers) <= 0) {
      return $this->setNoRender();
    }
  }

}
