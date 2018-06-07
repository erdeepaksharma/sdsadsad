<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: ActionTypes.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_DbTable_SaveFeeds extends Engine_Db_Table {

  protected $_name = 'advancedactivity_savefeeds';

  public function getSaveFeed(User_Model_User $user, $action_id) {
    return $this->select()
                    ->from($this, 'action_id')
                    ->where('user_id = ?', $user->getIdentity())
                    ->where('action_id = ?', $action_id)
                    ->query()
                    ->fetchColumn();
  }

  public function getSaveFeeds(User_Model_User $user, $types, $params = array()) {
    $limit = (!empty($params['limit']) ? $params['limit'] : 15) * 2;
    $max_id = $params['max_id'];
    $select = $this->select()
            //->from($this)
            ->where('user_id = ?', $user->getIdentity())
            ->where('action_type IN(?)', (array) $types)
            ->limit($limit);
    if (null !== $max_id) {
      $select->where('action_id <= ?', $max_id);
    }
    $data = $select
            ->query()
            ->fetchAll();


    $settings = array();
    foreach ($data as $row) {
      $settings[] = $row['action_id'];
    }

    return $settings;
  }

  public function setSaveFeeds(User_Model_User $user, $action_id, $action_type) {
    if (null === ($prev = $this->getSaveFeed($user, $action_id)) ||
            false === $prev) {
      $this->insert(array(
          'user_id' => $user->getIdentity(),
          'action_type' => $action_type,
          'action_id' => $action_id
      ));
    } else {
      $this->delete(array(
          'user_id = ?' => $user->getIdentity(),
          'action_id = ?' => $action_id,
      ));
    }

    return $this;
  }

}