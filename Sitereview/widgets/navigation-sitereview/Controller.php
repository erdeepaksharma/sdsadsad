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
class Sitereview_Widget_NavigationSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    
    $this->view->package_show = Zend_Controller_Front::getInstance()->getRequest()->getParam('package', 0);
    
    //GET LISTING TYPE COUNT
    $listingTypeCount = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount();
    if ($listingTypeCount == 1) {
      $listingtype_id = 1;
    }

    if ($listingtype_id) {
      //GET LISTING TYPE TITLE
      $this->view->title = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingtype_id, 'title_plural');

      $moduleName = Engine_API::_()->seaocore()->isSiteMobileModeEnabled() ? 'sitemobile' : 'core';
      //GET NAVIGATION
      $this->view->navigation = Engine_Api::_()->getApi('menus', $moduleName)->getNavigation("sitereview_main_listtype_$listingtype_id");
    }
  }

}