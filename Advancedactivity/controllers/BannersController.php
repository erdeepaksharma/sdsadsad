<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: FeelingController.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_BannersController extends Core_Controller_Action_Standard
{
  public function loadBannersAction()
  {
    $banners = Engine_Api::_()->getDbTable('banners', 'advancedactivity')->getBanners();
    return $this->_helper->json($banners);
  }

}
