<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Bootstrap.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Bootstrap extends Engine_Application_Bootstrap_Abstract {
    protected function _initFrontController() {
        include APPLICATION_PATH . '/application/modules/Siteforum/controllers/license/license.php';
    }
}
