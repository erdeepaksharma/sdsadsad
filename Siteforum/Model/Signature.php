<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Signature.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Model_Signature extends Core_Model_Item_Abstract {

    protected $_type = 'forum_signature';

    public function getHref($params = array()) {
        $params = array_merge(array(
            'route' => 'default',
            'reset' => true,
            'module' => 'siteforum',
            'controller' => 'profile',
            'action' => 'index',
            'user_id' => $this->user_id,
                ), $params);
        $route = $params['route'];
        $reset = $params['reset'];
        unset($params['route']);
        unset($params['reset']);
        return Zend_Controller_Front::getInstance()->getRouter()
                        ->assemble($params, $route, $reset);
    }

}
