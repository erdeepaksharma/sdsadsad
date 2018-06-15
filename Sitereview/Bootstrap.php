<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Bootstrap.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Bootstrap extends Engine_Application_Bootstrap_Abstract {

  public function __construct($application) {
    parent::__construct($application);
    include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license.php';
  }
  
  protected function _initFrontController() {

    $this->initActionHelperPath();
    $this->initViewHelperPath();
    $front = Zend_Controller_Front::getInstance();
    $front->registerPlugin(new Sitereview_Plugin_Core);
  }

}