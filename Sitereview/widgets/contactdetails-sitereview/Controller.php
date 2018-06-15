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
class Sitereview_Widget_ContactdetailsSitereviewController extends Engine_Content_Widget_Abstract {

  //ACTION FOR SHOWING THE RANDOM ALBUMS AND PHOTOS BY OTHERS 
  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    //GET SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
    $this->view->listingType = $listingType = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
    $viewer = Engine_Api::_()->user()->getViewer();

    $this->view->can_edit = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'sitereview_listing', "contact_listtype_$sitereview->listingtype_id"); //Engine_Api::_()

    if (empty($sitereview->phone) && empty($sitereview->email) && empty($sitereview->website) && !$this->view->can_edit) {
      return $this->setNoRender();
    }

    //GET SETTINGS
    $pre_field = array("0" => "1", "1" => "2", "2" => "3");
    $contacts = $this->_getParam('contacts', $pre_field);

    if (empty($contacts)) {
      $this->setNoRender();
    } else {
      //INITIALIZATION
      $this->view->show_phone = $this->view->show_email = $this->view->show_website = 0;
      if (in_array(1, $contacts)) {
        $this->view->show_phone = 1;
      }
      if (in_array(2, $contacts)) {
        $this->view->show_email = 1;
      }
      if (in_array(3, $contacts)) {
        $this->view->show_website = 1;
      }
    }
    $user = Engine_Api::_()->user()->getUser($sitereview->owner_id);
    $view_options = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_listing', $user, "contact_detail_listtype_$sitereview->listingtype_id");
    $availableLabels = array('phone' => 'Phone', 'website' => 'Website', 'email' => 'Email');
    $this->view->options_create = array_intersect_key($availableLabels, array_flip($view_options));
  }

}