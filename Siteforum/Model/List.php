<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: List.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Model_List extends Core_Model_List {

    protected $_owner_type = 'siteforum';
    protected $_child_type = 'user';
    protected $_type = 'forum_list';

    public function getListItemTable() {
        return Engine_Api::_()->getItemTable('forum_list_item');
    }

}
