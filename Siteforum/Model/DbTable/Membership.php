<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Membership.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Model_DbTable_Membership extends Core_Model_DbTable_Membership {

    protected $_name = 'forum_membership';

    /**
     * Does membership require approval of the resource?
     *
     * @param Core_Model_Item_Abstract $resource
     * @return bool
     */
    public function isResourceApprovalRequired(Core_Model_Item_Abstract $resource) {
        return true;
    }

}
