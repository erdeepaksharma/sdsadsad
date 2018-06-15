<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminExtensionController.php 2014-05-19 5:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_AdminExtensionController extends Core_Controller_Action_Admin {

  public function upgradeAction() {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_extensions');
  }

  public function informationAction() {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_extensions');
  }
}

?>