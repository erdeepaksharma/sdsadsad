<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Listmemberclaims.php 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Listmemberclaims extends Engine_Db_Table {

  protected $_rowClass = 'Sitereview_Model_Listmemberclaim';

  /**
   * Return claim member
   *
   * @param int $userid
   * @return Zend_Db_Table_Select
   */
  public function getClaimListMember($userid, $listingtype_id) {

    $select = $this->select();
    if (isset($userid)) {
      $select
              ->where('user_id =?', $userid)
              ->where('listingtype_id =?', $listingtype_id);
    }

    return $select;
  }

  /**
   * Return users lists whose listings can be claimed
   *
   * @param int $text
   * @param int $limit
   * @return user lists
   */
  public function getMembers($text, $limit, $listingtype_id) {

    if (empty($listingtype_id))
      return;

    //SELECT

    $user_idarray = $this->fetchAll($this->select()->from($this->info('name'), 'user_id')->where('listingtype_id = ?', $listingtype_id));

    //MAKING USER ID ARRAY
    $user_id_array = '';
    if (!empty($user_idarray)) {
      foreach ($user_idarray as $user_ids) {
        $user_id_array = $user_ids->user_id . ',' . $user_id_array;
      }
    }
    $user_id_array = $user_id_array . '0';

    //GET USER TABLE
    $tableUser = Engine_Api::_()->getDbtable('users', 'user');

    //SELECT
    $select = $tableUser->select()
            ->where('displayname  LIKE ? ', '%' . $text . '%')
            ->where($tableUser->info('name') . '.user_id NOT IN (' . $user_id_array . ')')
            ->order('displayname ASC')
            ->limit($limit);

    //FETCH
    return$tableUser->fetchAll($select);
  }

}