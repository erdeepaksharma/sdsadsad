<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Shares.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_DbTable_Shares extends Engine_Db_Table {

  public function countShareOfItem($params=array()) {
    $actionTable = Engine_Api::_()->getDbtable('actions', 'advancedactivity');
    return $this->select()
                    ->from($this, new Zend_Db_Expr('COUNT(share_id)'))
                    ->where('parent_action_id = ?', $params['parent_action_id'])
                    // ->where('resource_type = ?', $params['type'])
                    // ->where('resource_id  = ?', $params['id'])
                    ->limit(1)
                    ->query()
                    ->fetchColumn();
  }

  public function getShareActionIdsForFeed($params=array()) {
    return $this->select()
                    ->from($this->info('name'), "action_id")
                    ->where('parent_action_id = ?', $params['parent_action_id'])
                    //   ->where('resource_type = ?', $params['type'])
                    //   ->where('resource_id  = ?', $params['id'])
                    ->query()
                    ->fetchAll(Zend_Db::FETCH_COLUMN);
  }

}