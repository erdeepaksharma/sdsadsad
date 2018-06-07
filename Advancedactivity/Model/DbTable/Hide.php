<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Hide.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_DbTable_Hide extends Engine_Db_Table {

  public function getHideItemByMember($user, $params=array()) {
    $hideItems = array();
    $select = $this->select()
            ->where('user_id  = ?', $user->getIdentity());
    if (isset($params['not_activity_action']) && $params['not_activity_action'])
      $select->where('hide_resource_type  != ?', 'activity_action');
    $results = $select->query()
            ->fetchAll();
    foreach ($results as $result) {
      $hideItems[$result['hide_resource_type']][] = $result['hide_resource_id'];
    }
    return $hideItems;
  }

}