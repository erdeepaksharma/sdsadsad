<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: FriendsController.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_FriendsController extends Core_Controller_Action_User {

  public function init() {
    // Try to set subject
    $user_id = $this->_getParam('user_id', null);
    if ($user_id && !Engine_Api::_()->core()->hasSubject()) {
      $user = Engine_Api::_()->getItem('user', $user_id);
      if ($user) {
        Engine_Api::_()->core()->setSubject($user);
      }
    }
  }

  public function suggestAction() {
    $subject_guid = $this->_getParam('subject', null);
    $viewer = Engine_Api::_()->user()->getViewer();
    if ($subject_guid && (stripos($subject_guid, 'event') !== false || stripos($subject_guid, 'group') !== false)) {
      $subject = Engine_Api::_()->getItemByGuid($subject_guid);
    } else {
      $subject = $viewer;
    }
    if (!$viewer->getIdentity()) {
      $data = null;
    } else {
      $data = array();
      $table = Engine_Api::_()->getItemTable('user');
      $select = $subject->membership()->getMembersObjectSelect();

      if (0 < ($limit = (int) $this->_getParam('limit', 10))) {
        $select->limit($limit);
      }

      if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
        $select->where('`' . $table->info('name') . '`.`displayname` LIKE ?', '%' . $text . '%');
      }
      $select->where('`' . $table->info('name') . '`.`user_id` <> ?', $viewer->getIdentity());
      $select->order("{$table->info('name')}.displayname ASC");
      $ids = array();
      foreach ($select->getTable()->fetchAll($select) as $friend) {
        $data[] = array(
            'type' => 'user',
            'id' => $friend->getIdentity(),
            'guid' => $friend->getGuid(),
            'label' => $friend->getTitle(),
            'photo' => $this->view->itemPhoto($friend, 'thumb.icon'),
            'url' => $friend->getHref(),
        );
      }
    }

    if ($this->_getParam('sendNow', true)) {
      return $this->_helper->json($data);
    } else {
      $this->_helper->viewRenderer->setNoRender(true);
      $data = Zend_Json::encode($data);
      $this->getResponse()->setBody($data);
    }
  }

  public function suggestTagAction() {
    $subject_guid = $this->_getParam('subject', null);
    $viewer = Engine_Api::_()->user()->getViewer();
    if ($subject_guid && (stripos($subject_guid, 'event') !== false || stripos($subject_guid, 'group') !== false)) {
      $subject = Engine_Api::_()->getItemByGuid($subject_guid);
    } else {
      $subject = $viewer;
    }
    if (!$viewer->getIdentity()) {
      $data = null;
    } else {
      $data = array();
      $enableContent = Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.tagging.module', array('friends', 'sitepage', 'sitebusiness', 'sitegroup', 'sitestore', 'list', 'group', 'event'));
      if (in_array('friends', $enableContent)) {
        $table = Engine_Api::_()->getItemTable('user');
        $select = $subject->membership()->getMembersObjectSelect();

        if ($this->_getParam('includeSelf', false) && stripos($viewer->getTitle(), $this->_getParam('search', $this->_getParam('value'))) !== false) {
          $data[] = array(
              'type' => 'user',
              'id' => $viewer->getIdentity(),
              'guid' => $viewer->getGuid(),
              'label' => $viewer->getTitle() . ' ' . $this->view->translate('(you)'),
              'photo' => $this->view->itemPhoto($viewer, 'thumb.icon'),
              'url' => $viewer->getHref(),
          );
        }

        if (0 < ($limit = (int) $this->_getParam('limit', 10))) {
          $select->limit($limit);
        }

        if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
          $select->where('`' . $table->info('name') . '`.`displayname` LIKE ?', '%' . $text . '%');
        }
        $select->where('`' . $table->info('name') . '`.`user_id` <> ?', $viewer->getIdentity());
        $select->order("{$table->info('name')}.displayname ASC");
        $ids = array();
        foreach ($select->getTable()->fetchAll($select) as $friend) {
          $data[] = array(
              'type' => 'user',
              'id' => $friend->getIdentity(),
              'guid' => $friend->getGuid(),
              'label' => $friend->getTitle(),
              'photo' => $this->view->itemPhoto($friend, 'thumb_icon', "", array('nolazy' => true)),
              'url' => $friend->getHref(),
          );
          $ids[] = $friend->getIdentity();
          $friend_data[$friend->getIdentity()] = $friend->getTitle();
        }
      }
      /*
        // first get friend lists created by the user
        $listTable = Engine_Api::_()->getItemTable('user_list');
        $lists = $listTable->fetchAll($listTable->select()->where('owner_id = ?', $viewer->getIdentity()));
        $listIds = array();
        foreach ($lists as $list) {
        $listIds[] = $list->list_id;
        $listArray[$list->list_id] = $list->title;
        }

        // check if user has friend lists
        if ($listIds) {
        // get list of friend list + friends in the list
        $listItemTable = Engine_Api::_()->getItemTable('user_list_item');
        $uName = Engine_Api::_()->getDbtable('users', 'user')->info('name');
        $iName = $listItemTable->info('name');

        $listItemSelect = $listItemTable->select()
        ->setIntegrityCheck(false)
        ->from($iName, array($iName . '.listitem_id', $iName . '.list_id', $iName . '.child_id', $uName . '.displayname'))
        ->joinLeft($uName, "$iName.child_id = $uName.user_id")
        //->group("$iName.child_id")
        ->where('list_id IN(?)', $listIds);

        $listItems = $listItemTable->fetchAll($listItemSelect);

        $listsByUser = array();
        foreach ($listItems as $listItem) {
        $listsByUser[$listItem->list_id][$listItem->user_id] = $listItem->displayname;
        }

        foreach ($listArray as $key => $value) {
        if (!empty($listsByUser[$key])) {
        $data[] = array(
        'type' => 'list',
        'friends' => $listsByUser[$key],
        'label' => $value,
        );
        }
        }
        } */
      if (in_array('sitepage', $enableContent) && Engine_Api::_()->hasItemType('sitepage_page')) {
        $remaningLimit = $limit - @count($data);
        if ($remaningLimit > 0) {
          $table = Engine_Api::_()->getItemTable('sitepage_page');
          $tableName = $table->info('name');
          $select = $table->getPagesSelectSql(array('limit' => $remaningLimit));
          // $select = $table->getPagesSelectSql();
          if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
            $select->where('`' . $table->info('name') . '`.`title` LIKE ?', '%' . $text . '%');
          }
          $select->order("{$tableName}.title ASC");
          foreach ($select->getTable()->fetchAll($select) as $page) {
            $data[] = array(
                'type' => ucfirst($this->view->translate('sitepage_page')),
                'id' => $page->getIdentity(),
                'guid' => $page->getGuid(),
                'label' => $page->getTitle(),
                'photo' => $this->view->itemPhoto($page, 'thumb.icon'),
                'url' => $page->getHref(),
            );
            $ids[] = $page->getIdentity();
          }
        }
      }

      if (in_array('sitebusiness', $enableContent) && Engine_Api::_()->hasItemType('sitebusiness_business')) {
        $remaningLimit = $limit - @count($data);
        if ($remaningLimit > 0) {
          $table = Engine_Api::_()->getItemTable('sitebusiness_business');
          $tableName = $table->info('name');
          $select = $table->getBusinessesSelectSql(array('limit' => $remaningLimit));
          if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
            $select->where('`' . $table->info('name') . '`.`title` LIKE ?', '%' . $text . '%');
          }
          $select->order("{$tableName}.title ASC");
          foreach ($select->getTable()->fetchAll($select) as $business) {
            $data[] = array(
                'type' => ucfirst($this->view->translate('sitebusiness_business')),
                'id' => $business->getIdentity(),
                'guid' => $business->getGuid(),
                'label' => $business->getTitle(),
                'photo' => $this->view->itemPhoto($business, 'thumb.icon'),
                'url' => $business->getHref(),
            );
            $ids[] = $business->getIdentity();
          }
        }
      }

      if (in_array('sitegroup', $enableContent) && Engine_Api::_()->hasItemType('sitegroup_group')) {
        $remaningLimit = $limit - @count($data);
        if ($remaningLimit > 0) {
          $table = Engine_Api::_()->getItemTable('sitegroup_group');
          $tableName = $table->info('name');
          $select = $table->getGroupsSelectSql(array('limit' => $remaningLimit));
          // $select = $table->getPagesSelectSql();
          if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
            $select->where('`' . $table->info('name') . '`.`title` LIKE ?', '%' . $text . '%');
          }
          $select->order("{$tableName}.title ASC");
          foreach ($select->getTable()->fetchAll($select) as $group) {
            $data[] = array(
                'type' => ucfirst($this->view->translate('sitegroup_group')),
                'id' => $group->getIdentity(),
                'guid' => $group->getGuid(),
                'label' => $group->getTitle(),
                'photo' => $this->view->itemPhoto($group, 'thumb.icon'),
                'url' => $group->getHref(),
            );
            $ids[] = $group->getIdentity();
          }
        }
      }
      if (in_array('sitestore', $enableContent) && Engine_Api::_()->hasItemType('sitestore_store')) {
        $remaningLimit = $limit - @count($data);
        if ($remaningLimit > 0) {
          $table = Engine_Api::_()->getItemTable('sitestore_store');
          $tableName = $table->info('name');
          $select = $table->getStoresSelectSql(array('limit' => $remaningLimit));
          // $select = $table->getPagesSelectSql();
          if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
            $select->where('`' . $table->info('name') . '`.`title` LIKE ?', '%' . $text . '%');
          }
          $select->order("{$tableName}.title ASC");
          foreach ($select->getTable()->fetchAll($select) as $store) {
            $data[] = array(
                'type' => ucfirst($this->view->translate('sitestore_store')),
                'id' => $store->getIdentity(),
                'guid' => $store->getGuid(),
                'label' => $store->getTitle(),
                'photo' => $this->view->itemPhoto($store, 'thumb.icon'),
                'url' => $store->getHref(),
            );
            $ids[] = $store->getIdentity();
          }
        }
      }


      if (in_array('list', $enableContent) && Engine_Api::_()->hasItemType('list_listing')) {
        $remaningLimit = $limit - @count($data);
        if ($remaningLimit > 0) {
          $table = Engine_Api::_()->getItemTable('list_listing');
          $tableName = $table->info('name');
          $select = $table->getListingSelectSql(array('limit' => $remaningLimit));
          if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
            $select->where('`' . $table->info('name') . '`.`title` LIKE ?', '%' . $text . '%');
          }
          $select->order("{$tableName}.title ASC");
          foreach ($select->getTable()->fetchAll($select) as $list) {
            $data[] = array(
                'type' => ucfirst($this->view->translate('list_listing')),
                'id' => $list->getIdentity(),
                'guid' => $list->getGuid(),
                'label' => $list->getTitle(),
                'photo' => $this->view->itemPhoto($list, 'thumb.icon'),
                'url' => $list->getHref(),
            );
            $ids[] = $list->getIdentity();
          }
        }
      }
      if (in_array('group', $enableContent) && Engine_Api::_()->hasItemType('group')) {
        $remaningLimit = $limit - @count($data);
        if ($remaningLimit > 0) {
          $table = Engine_Api::_()->getItemTable('group');
          $tableName = $table->info('name');
          $select = $table->select();
          $select->where('search = ?', (bool) 1);
          if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
            $select->where('`' . $table->info('name') . '`.`title` LIKE ?', '%' . $text . '%');
          }
          $select->order("{$tableName}.title ASC");
          foreach ($select->getTable()->fetchAll($select) as $group) {
            $data[] = array(
                'type' => $group->getShortType(true),
                'id' => $group->getIdentity(),
                'guid' => $group->getGuid(),
                'label' => $group->getTitle(),
                'photo' => $this->view->itemPhoto($group, 'thumb.icon'),
                'url' => $group->getHref(),
            );
            $ids[] = $group->getIdentity();
          }
        }
      }
      if (in_array('event', $enableContent) && Engine_Api::_()->hasItemType('event')) {
        $remaningLimit = $limit - @count($data);
        if ($remaningLimit > 0) {
          $table = Engine_Api::_()->getItemTable('event');
          $tableName = $table->info('name');
          $select = $table->select();
          $select->where('search = ?', (bool) 1);
          $select->where("endtime > FROM_UNIXTIME(?)", time());
          if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
            $select->where('`' . $table->info('name') . '`.`title` LIKE ?', '%' . $text . '%');
          }
          $select->order("{$tableName}.title ASC");
          foreach ($select->getTable()->fetchAll($select) as $event) {
            $data[] = array(
                'type' => $event->getShortType(true),
                'id' => $event->getIdentity(),
                'guid' => $event->getGuid(),
                'label' => $event->getTitle(),
                'photo' => $this->view->itemPhoto($event, 'thumb.icon'),
                'url' => $event->getHref(),
            );
            $ids[] = $event->getIdentity();
          }
        }
      }
    }
    if ($this->_getParam('sendNow', true)) {
      echo Zend_Json::encode($data);
      exit(0);
    } else {
      $this->_helper->viewRenderer->setNoRender(true);
      $data = Zend_Json::encode($data);
      $this->getResponse()->setBody($data);
    }
  }

  public function suggestMobileAction() {
    $error_message = '';
    $subject_guid = $this->_getParam('subject', null);
    $viewer = Engine_Api::_()->user()->getViewer();
    if ($subject_guid && (stripos($subject_guid, 'event') !== false || stripos($subject_guid, 'group') !== false)) {
      $subject = Engine_Api::_()->getItemByGuid($subject_guid);
    } else {
      $subject = $viewer;
    }
    if (!$viewer->getIdentity()) {
      $error_message = 'non login user';
    } else {
      $data = array();
      $table = Engine_Api::_()->getItemTable('user');
      $select = $subject->membership()->getMembersObjectSelect();

      if (0 < ($limit = (int) $this->_getParam('limit', 10))) {
        $select->limit($limit);
      }

      if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
        $select->where('`' . $table->info('name') . '`.`displayname` LIKE ?', '%' . $text . '%');
      }
      $select->where('`' . $table->info('name') . '`.`user_id` <> ?', $viewer->getIdentity());
      $select->order("{$table->info('name')}.displayname ASC");
      // $ids = array();
      $this->view->paginator = $paginator = Zend_Paginator::factory($select);
      // Set item count per page and current page number
      $this->view->itemCountPerPage = $this->_getParam('itemCountPerPage', 5);
      $paginator->setItemCountPerPage($this->view->itemCountPerPage);
      $this->view->currectPage = $this->_getParam('page', 1);
      $paginator->setCurrentPageNumber($this->view->currectPage);

      // Hide if nothing to show
      if ($paginator->getTotalItemCount() <= 0) {
        $error_message = 'not found any friends';
      }
    }
  }

}
