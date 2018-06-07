<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Widget_WelcomeMessageController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    $this->view->isWelcomePageCorrect = Engine_Api::_()->advancedactivity()->isWelcomePageCorrect();
    $this->view->viewer = Engine_Api::_()->user()->getViewer();
    $logo_photo = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity_icon', 'application/modules/Advancedactivity/externals/images/web.png');
    if (!empty($logo_photo)) {
      $baseurl = Zend_Controller_Front::getInstance()->getBaseUrl();
      $photoName = $baseurl . '/' . $logo_photo;
      $this->view->logo = "<img src='$photoName' width='20' height='20'/>";
    }

    $this->view->title = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.site.title');
  }

}