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
class Sitereview_Widget_OptionsSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);

    //GET NAVIGATION
    $this->view->gutterNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation("sitereview_gutter_listtype_$listingtype_id");

    if(Count($this->view->gutterNavigation) <= 0) {
			return $this->setNoRender();
		}	
  }

}
