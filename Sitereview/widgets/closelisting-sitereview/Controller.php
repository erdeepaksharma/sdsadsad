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
class Sitereview_Widget_CloselistingSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    //GET SUBJECT
    $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
    $this->view->listing_singular_lc = strtolower($listingtypeArray->title_singular);
    
    //DONT RENDER IF LISTING IS OPEN
    if (empty($sitereview->closed)) {
      return $this->setNoRender();
    }
  }

}