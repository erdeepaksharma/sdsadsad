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
class Sitereview_Widget_SearchboxSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 0);
    if (empty($listingtype_id)) {
      $this->view->listingtype_id = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', 0);
    }

    //PREPARE FORM
    $this->view->form = new Sitereview_Form_Searchbox();
  }

}
