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
class Sitereview_Widget_WishlistBrowseSearchController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    //DO NOT RENDER IF FAVOURITE FUNCTIONALITY IS ENABLED  
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0)) {
      return $this->setNoRender();
    }         
      
    //GENERATE SEARCH FORM
    $this->view->form = $form = new Sitereview_Form_Wishlist_Search();
    $this->view->viewType = $this->_getParam('viewType', 'horizontal');

    //GET FORM VALUES
    $requestParams = Zend_Controller_Front::getInstance()->getRequest()->getParams();

    //POPULATE SEARCH FORM
    $form->populate($requestParams);
  }

}
