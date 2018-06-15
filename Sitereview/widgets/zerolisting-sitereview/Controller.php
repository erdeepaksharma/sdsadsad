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
class Sitereview_Widget_ZerolistingSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    //GET VIEWER
    $viewer = Engine_Api::_()->user()->getViewer();

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    
    if(empty($listingtype_id)) {
        return $this->setNoRender();
    }

    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);

    $this->view->listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);

    //CAN CREATE LISTINGS OR NOT
    $this->view->can_create = Engine_Api::_()->authorization()->isAllowed('sitereview_listing', $viewer, "create_listtype_$listingtype_id");

    //GET LISTS
    $listingCount = Engine_Api::_()->getDbTable('listings', 'sitereview')->hasListings($listingtype_id);

    if ($listingCount > 0) {
      return $this->setNoRender();
    }
  }

}
