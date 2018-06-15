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
class Sitereview_Widget_TopPostersController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    
    //GET SETTINGS
    $params = array();
    $params['limit'] = $this->_getParam('itemCount', 3);

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $params['listingtype_id'] = $this->_getParam('listingtype_id', null);
    if (empty($this->view->listingtype_id)) {
      $this->view->listingtype_id = $params['listingtype_id'] = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    }
    
    if(!empty($this->view->listingtype_id)) {
      Engine_Api::_()->sitereview()->setListingTypeInRegistry($this->view->listingtype_id);
      $this->view->listingtypeArray = Zend_Registry::get('listingtypeArray' . $this->view->listingtype_id);
    }

    //GET RESULTS
    $this->view->posters = Engine_Api::_()->getDbtable('listings', 'sitereview')->topPosters($params);

    //DON'T RENDER IF NO DATA
    if (Count($this->view->posters) <= 0) {
      return $this->setNoRender();
    }
  }

}
