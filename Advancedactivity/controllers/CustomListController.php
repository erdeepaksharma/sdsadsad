<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: CustomListController.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_CustomListController extends Core_Controller_Action_Standard {

  protected $_viewer;
  protected $_viewer_id;

  public function init() {
    //USER VALDIATION
    if (!$this->_helper->requireUser()->isValid())
      return;
    $this->view->viewer = $this->_viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $this->_viewer_id = $this->_viewer->getIdentity();
  }

  public function createAction() {
    $this->view->type = $type = $this->_getParam('type', null);
    if ($type === 'category') {
      if(!Engine_Api::_()->hasModuleBootstrap('advancedactivitypost'))
        return;
      $tableCategories = Engine_Api::_()->getDbtable('categories', 'advancedactivitypost');
      $this->view->categoriesList = $categoriesList = $tableCategories->getCategories();
      $count = count($categoriesList);
      if (empty($count))
        return $this->_forward('notfound', 'error', 'core');
    } else {
      $this->view->customTypeLists = $customTypeLists = Engine_Api::_()->getDbtable('customtypes', 'advancedactivity')->getCustomTypeList(array('enabled' => 1));
      $count = count($customTypeLists);
      if (empty($count))
        return $this->_forward('notfound', 'error', 'core');
    }
    if (!$this->getRequest()->isPost()) {
      return;
    }
    $listTitle = $_POST['title'];
    if (!empty($listTitle)) {
      // create new list
      $table = Engine_Api::_()->getDbtable('lists', 'advancedactivity');
      $list = $table->createRow();
      $list->setFromArray(
              array('title' => $listTitle,
                  'owner_id' => $this->_viewer_id)
      );
      if ($type && isset($list->type)) {
        $list->type = $type;
      }
      $list->save();

      $selected_resources = $_POST['selected_resources'];
      $list->setListItems($selected_resources);
    }

    return $this->_forward('success', 'utility', 'core', array(
                'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your List has been created successfully.')),
                'layout' => 'default-simple',
                'parentRefresh' => true,
    ));
  }

  public function editAction() {
    $list_id = $this->_getParam('list_id', null);
    $this->view->list = $list = Engine_Api::_()->getItem('advancedactivity_list', $list_id);
    $this->view->type = $type = $list->type;
    if ($type === 'category') {
      $tableCategories = Engine_Api::_()->getDbtable('categories', 'advancedactivity');
      $this->view->categoriesList = $categoriesList = $tableCategories->getCategories();
      $count = count($categoriesList);
      if (empty($count))
        return $this->_forward('notfound', 'error', 'core');
    }
    $this->view->customTypeLists = $customTypeLists = Engine_Api::_()->getDbtable('customtypes', 'advancedactivity')->getCustomTypeList(array('enabled' => 1));
    $count = count($customTypeLists);
    if (empty($count))
      return $this->_forward('notfound', 'error', 'core');

    $this->view->listCount = $list->count();
    $this->view->customList = $list->getListItems();
    if (!$this->getRequest()->isPost()) {
      return;
    }
    $listTitle = $_POST['title'];
    if (!empty($listTitle)) {
      $list->setFromArray(
              array('title' => $listTitle)
      );
      $list->save();

      $selected_resources = $_POST['selected_resources'];
      $list->setListItems($selected_resources);
    }
    return $this->_forward('success', 'utility', 'core', array(
                'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.')),
                'layout' => 'default-simple',
                'parentRefresh' => true,
    ));
  }

  public function deleteAction() {
    $list_id = $this->_getParam('list_id', null);

    $this->view->list = $list = Engine_Api::_()->getItem('advancedactivity_list', $list_id);
    if (!empty($list)) {
      $list->getListItemTable()->delete(array("list_id = ? " => $list->list_id));
      $list->delete();
    }
    return $this->_forward('success', 'utility', 'core', array(
                'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your List has been deleted.')),
                'layout' => 'default-simple',
                'parentRefresh' => true,
    ));
  }

  public function getContentItemsAction() {
    $resource_type = $this->_getParam('resource_type', null);

    //FETCH CATEGORY LIST FOR CATEGORY BASED FILTERING
    if ($resource_type == 'advancedactivity_category') {
      $categoryItemsTable = Engine_Api::_()->getDbtable('categories', 'advancedactivity');
      $categoryItemsSelect = $categoryItemsTable->select()
              ->order('order');
      $this->view->paginator = $paginator = Zend_Paginator::factory($categoryItemsSelect);
      $paginator->setItemCountPerPage(40);
      $paginator->setCurrentPageNumber($this->_getParam('page', 1));
      $this->view->count = $paginator->getTotalItemCount();
    } else {

      if ($resource_type && strpos($resource_type, '_listtype_') !== false) {
        $explode_resource_type = explode('_listtype_', $resource_type);
        $resource_type = $explode_resource_type[0];
        $listingtype_id = $explode_resource_type[1];
      }
      if (empty($resource_type) || !Engine_Api::_()->hasItemType($resource_type))
        return;
      $this->_helper->layout->disableLayout();
      $likeTable = Engine_Api::_()->getDbtable('likes', 'core');
      $likeTableName = $likeTable->info('name');

      $table = Engine_Api::_()->getItemTable($resource_type);
      $tableName = $table->info('name');
      $primary_id = current($table->info('primary'));

      $metaDataInfo = $table->info('metadata');

      if (isset($metaDataInfo['user_id'])) {
        $owner_id = 'user_id';
      } else {
        $owner_id = 'owner_id';
      }

      $search = $this->_getParam('search', null);
      $ids = array();

      // For User Friends
      if ($resource_type == 'user') {

        $ids = $this->_viewer->membership()->getMembershipsOfIds();
      }
      // For Group which member have join ||   For Event which member have attend
      if ($resource_type == 'group' || $resource_type == 'event') {

        $membershipTable = Engine_Api::_()->getDbtable('membership', $resource_type);
        $mtName = $membershipTable->info('name');

        $select = $membershipTable->select()
                ->setIntegrityCheck(false)
                ->from($tableName, "$tableName.$primary_id")
                ->join($mtName, "`{$mtName}`.`resource_id` = `{$tableName}`.`{$primary_id}`", null)
                ->where("`{$mtName}`.`active` = ?", (bool) true)
                ->where("`{$mtName}`.`user_id` = ?", $this->_viewer_id);

        if ($resource_type == 'event') {
          $select->where("`{$mtName}`.`rsvp` = ?", 2);
        }

        $ids = $select->query()
                ->fetchAll(Zend_Db::FETCH_COLUMN);
      }

      if (!empty($primary_id)) {
        $select = $likeTable->select()
                ->setIntegrityCheck(false)
                ->from($likeTableName, "$likeTableName.resource_id")
                ->join($tableName, "$likeTableName.resource_id = $tableName.$primary_id", null)
                ->where($likeTableName . '.resource_type = ?', $resource_type)
                ->where($likeTableName . '.poster_type = ?', 'user')
                ->where($likeTableName . '.poster_id = ?', $this->_viewer_id);
        if ($resource_type == 'sitereview_listing' && $listingtype_id) {
          $select->where($tableName . '.listingtype_id = ?', $listingtype_id);
        }
        $likeIds = $select->query()
                ->fetchAll(Zend_Db::FETCH_COLUMN);
        $ids = array_merge($ids, $likeIds);
      }

      $orBaseSql = "$owner_id = $this->_viewer_id";

      $select = $table->select();

      if (!empty($ids)) {
        $ids = array_unique($ids);
        if ($resource_type != 'user') {
          $orBaseSql = "$owner_id = $this->_viewer_id";
          $select->where("( $orBaseSql or $primary_id  IN (?))", (array) $ids);
        } else {
          $select->where("$primary_id  IN (?)", (array) $ids);
        }
      } else {
        $select->where("$owner_id = ?", $this->_viewer_id);
      }

      if (!empty($search) && isset($metaDataInfo['title'])) {
        $select->where("title like ? ", "%" . $search . "%");
      } elseif (($resource_type == 'user') && !empty($search) && isset($metaDataInfo['displayname'])) {
        $select->where("displayname like ? ", "%" . $search . "%");
      }

      if ($resource_type == 'sitereview_listing' && $listingtype_id) {
        $select->where($tableName . '.listingtype_id = ?', $listingtype_id);
      }
      $this->view->paginator = $paginator = Zend_Paginator::factory($select);
      $paginator->setItemCountPerPage(40);
      $paginator->setCurrentPageNumber($this->_getParam('page', 1));
      $this->view->count = $paginator->getTotalItemCount();
    }
  }

}
