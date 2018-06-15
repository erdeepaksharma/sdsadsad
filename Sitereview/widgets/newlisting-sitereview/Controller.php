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
class Sitereview_Widget_NewlistingSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    //GET VIEWER
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    //DONT SHOW ADD LINK TO VISITOR
    if (empty($viewer_id)) {
      return $this->setNoRender();
    }

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', null);
    if (empty($listingtype_id)) {
      $this->view->listingtype_id = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    }

    //CHECK LISTING CREATION PRIVACY
    if (!Engine_Api::_()->authorization()->isAllowed('sitereview_listing', $viewer, "create_listtype_$listingtype_id")) {
      return $this->setNoRender();
    }

    //GET LISTING TYPE TITLE
    $this->view->title = ucfirst(Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingtype_id, 'title_singular'));
  }

}