<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Lists.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_DbTable_Lists extends Engine_Db_Table {

  protected $_rowClass = 'Advancedactivity_Model_List';

  public function getMemberOfList(Core_Model_Item_Abstract $resource, $type = null) {
    $select = $this->select()
            ->where('owner_id 	 = ?', $resource->getIdentity());
    if($type){
      $select->where('type = ?', $type);
    }
    return $this->fetchAll($select);
  }

}