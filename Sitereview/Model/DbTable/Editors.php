<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Editors.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Editors extends Engine_Db_Table {

  protected $_rowClass = 'Sitereview_Model_Editor';

  public function getSimilarEditors($params = array()) {

    //GET EDITOR TABLE NAME
    $editorTableName = $this->info('name');

    //GET USER TABLE NAME
    $userTable = Engine_Api::_()->getItemtable('user');
    $userTableName = $userTable->info('name');

    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($userTableName, array('user_id', 'username', 'displayname', 'photo_id'))
            ->join($editorTableName, "$userTableName.user_id = $editorTableName.user_id", array('editor_id', 'designation', 'listingtype_id'))
            ->group("$editorTableName.user_id");

    if (isset($params['user_id']) && !empty($params['user_id']) && (empty($params['listingtype_id']) || $params['listingtype_id'] == -1)) {
      $listingtypeIdsArray = array();
      $selectListingtypeIds = $this->select()
              ->from($this->info('name'), array('listingtype_id'))
              ->where('user_id = ?', $params['user_id'])
              ->group('listingtype_id');

      $listingtypeIds = $this->fetchAll($selectListingtypeIds);
      if (!empty($listingtypeIds)) {
        foreach ($listingtypeIds as $ids) {
          $listingtypeIdsArray[] = $ids->listingtype_id;
        }
      }

      $listingIds = join(',', $listingtypeIdsArray);
      $select->where("listingtype_id IN ($listingIds)");
    }

    if (isset($params['listingtype_id']) && (!empty($params['listingtype_id']) && $params['listingtype_id'] != -1)) {
      $select->where('listingtype_id = ?', $params['listingtype_id']);
    }

    if (isset($params['user_id']) && !empty($params['user_id'])) {
      $select->where($editorTableName . ".user_id != ?", $params['user_id']);
    }
    
    if (isset($params['super_editor_user_id']) && !empty($params['super_editor_user_id'])) {
      $select->where($editorTableName . ".user_id != ?", $params['super_editor_user_id']);
    }    

    $select->order("RAND()");

    if (isset($params['limit']) && !empty($params['limit'])) {
      $select->limit($params['limit']);
    }

    if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
			return Zend_Paginator::factory($select);
    }


    return $this->fetchAll($select);
  }

  public function getEditorsCount($listingtype_id = 0) {

    //MAKE QUERY
    $select = $this->select()->from($this->info('name'), array('COUNT(DISTINCT user_id) as total_editors'));

    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }

    $editorsCount = $select->query()->fetchColumn();

    return $editorsCount;
  }

  public function getEditorsListing($params = null) {

    //GET EDITOR TABLE NAME
    $editorTableName = $this->info('name');

    //GET USER TABLE NAME
    $userTable = Engine_Api::_()->getItemtable('user');
    $userTableName = $userTable->info('name');

    //GET REVIEW TABLE NAME
    $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
    $reviewTableName = $reviewTable->info('name');

    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($userTableName, array('user_id', 'email', 'username', 'displayname', 'photo_id'))
            ->join($editorTableName, "$userTableName.user_id = $editorTableName.user_id", array('editor_id', 'designation', 'listingtype_id'))
            ->joinLeft($reviewTableName, "($reviewTableName.owner_id = $editorTableName.user_id and $reviewTableName.type = 'editor')", array("COUNT(review_id) as total_reviews"))
            ->group("$editorTableName.user_id")
            ->order("total_reviews DESC")
    ;

    if (isset($params['user_id']) && !empty($params['user_id'])) {
      $select->where("$editorTableName.user_id != ?", $params['user_id']);
    }

    if (isset($params['limit']) && !empty($params['limit'])) {
      $select->limit($params['limit']);
    }

    return $this->fetchAll($select);
  }

  public function isEditor($user_id = 0, $listingtype_id = 0) {

    $select = $this->select()
            ->from($this->info('name'), "editor_id");

    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }

    $isEditor = $select
            ->where('user_id = ?', $user_id)
            ->query()
            ->fetchColumn();

    return $isEditor;
  }

  public function getColumnValue($user_id = 0, $column_name = 'designation', $listingtype_id = 0) {

    $select = $this->select()
            ->from($this->info('name'), array("$column_name"));
    if (!empty($user_id)) {
      $select->where('user_id = ?', $user_id);
    }

    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }

    return $select->limit(1)->query()->fetchColumn();
  }

  /**
   * Return users lists whose pages can be claimed
   *
   * @param int $text
   * @param int $limit
   * @return user lists
   */
  public function getMembers($text, $limit = 40, $listingtype_id = 0, $featured_editor = 0) {

    //MAKE QUERY
    $select = $this->select()->from($this->info('name'), array('user_id'));

    if (!empty($listingtype_id) && $listingtype_id != -1) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }

    $select->group('user_id');

    $userDatas = $this->fetchAll($select);

    //MAKING USER ID ARRAY
    $userIds = '0,';
    if (!empty($userDatas)) {
      foreach ($userDatas as $user) {
        $userIds .= "$user->user_id,";
      }
    }

    $userIds = trim($userIds, ',');

    //GET USER TABLE
    $tableUser = Engine_Api::_()->getDbtable('users', 'user');

    //SELECT
    $selectUsers = $tableUser->select()
            ->from($tableUser->info('name'), array('user_id', 'username', 'displayname', 'photo_id'))
            ->where('displayname  LIKE ? OR username LIKE ?', '%' . $text . '%');

    if (!empty($featured_editor)) {
      $selectUsers->where($tableUser->info('name') . '.user_id IN (' . $userIds . ')');
    } else {
      $selectUsers->where($tableUser->info('name') . '.user_id NOT IN (' . $userIds . ')');
    }

    $selectUsers->where('approved = ?', 1)
            ->where('verified = ?', 1)
            ->where('enabled = ?', 1)
            ->order('displayname ASC')
            ->limit($limit);

    //FETCH
    return $tableUser->fetchAll($selectUsers);
  }

  public function getEditor($user_id, $listingtype_id) {

    $select = $this->select()
            ->where('user_id = ?', $user_id)
            ->where('listingtype_id = ?', $listingtype_id);

    return $this->fetchRow($select);
  }

  public function getEditorDetails($user_id = 0, $listingtype_id = 0, $params = array()) {

    //GET LISTING TYPE TABLE
    $listingTypeTable = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');
    $listingTypeTableName = $listingTypeTable->info('name');

    //GET EDITOR TABLE NAME
    $editorTableName = $this->info('name');

    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($editorTableName, array('details'))
            ->joinLeft($listingTypeTableName, "$listingTypeTableName.listingtype_id = $editorTableName.listingtype_id", array('title_plural', 'listingtype_id'))
            ->where("$editorTableName.user_id = ?", $user_id);

    if (!empty($listingtype_id) && $listingtype_id != -1) {
      $select->where($listingTypeTableName . '.listingtype_id = ?', $listingtype_id);
    }

    if (isset($params['visible']) && !empty($params['visible'])) {
      $select->where($listingTypeTableName . '.visible = ?', $params['visible']);
    }
    
    if (isset($params['editorReviewAllow']) && !empty($params['editorReviewAllow'])) {
      $select->where("$listingTypeTableName.reviews = 1 OR $listingTypeTableName.reviews = 3");
    }    

    return $this->fetchAll($select);
  }

  public function getListingtypeEditorCount() {

    //GET LISTING TYPE TABLE
    $listingTypeTable = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');
    $listingTypeTableName = $listingTypeTable->info('name');

    //GET EDITOR TABLE NAME
    $editorTableName = $this->info('name');

    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($editorTableName, array('COUNT(user_id) AS total_editors'))
            ->join($listingTypeTableName, "$listingTypeTableName.listingtype_id = $editorTableName.listingtype_id", array('title_plural'))
            ->group("$listingTypeTableName.listingtype_id")
            ->where("$listingTypeTableName.visible = ?", 1)
    ;

    return $this->fetchAll($select);
  }

  public function getListingTypeIds($user_id) {

    $select = $this->select()
            ->from($this->info('name'), 'listingtype_id')
            ->where('user_id = ?', $user_id);

    $listingtypeIds = $this->fetchAll($select);

    $ids = array();
    foreach ($listingtypeIds as $listingtypeId) {
      $ids[] = $listingtypeId->listingtype_id;
    }

    return $ids;
  }

  /**
   * Return Editors Ids
   *
   * @param int $listingtype_id
   * @param int $user_id
   * @return Editors Ids
   */
  public function getAllEditors($listingtype_id = 0, $user_id = 0, $email_notify = 0) {

    //MAKE QUERY
    $select = $this->select()->from($this->info('name'), array('user_id'));

    if (!empty($user_id)) {
      $select->where('user_id != ?', $user_id);
    }

    if (!empty($listingtype_id) && $listingtype_id != -1) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }
    
    if(!empty($email_notify)) {
      $select->where('email_notify = ?', 1);
    }

    //FETCH
    return $this->fetchAll($select);
  }

  public function moveListingtypeEditors($old_listingtype_id, $new_listingtype_id) {

    $select = $this->select()
            ->from($this->info('name'), array('editor_id'))
            ->where('listingtype_id = ?', $old_listingtype_id);
    $old_editors = $this->fetchAll($select);

    foreach ($old_editors as $old_editor) {
      $editor = Engine_Api::_()->getItem('sitereview_editor', $old_editor->editor_id);
      $isExist = $this->isEditor($editor->user_id, $new_listingtype_id);
      if (empty($isExist)) {
        $editor->listingtype_id = $new_listingtype_id;
        $editor->save();
      } elseif (!empty($isExist)) {
        $editor->delete();
      }
    }
  }

  public function getSuperEditor($column_name = 'editor_id') {

    $column_value = $this->select()
            ->from($this->info('name'), $column_name)
            ->where('super_editor = ?', 1)
            ->limit(1)
            ->query()
            ->fetchColumn();

    return $column_value;
  }

  public function getHighestLevelEditorId() {

    $userTable = Engine_Api::_()->getItemTable('user');
    $userTableName = $userTable->info('name');

    $editorTableName = $this->info('name');

    $editor_id = $this->select()
            ->setIntegrityCheck(false)
            ->from($editorTableName, 'editor_id')
            ->joinInner($userTableName, "$userTableName.user_id = $editorTableName.user_id", array(''))
            ->group($editorTableName . '.user_id')
            ->order('level_id ASC')
            ->limit(1)
            ->query()
            ->fetchColumn();

    return $editor_id;
  }

}
