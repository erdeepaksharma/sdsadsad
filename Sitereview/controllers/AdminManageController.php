<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminManageController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_AdminManageController extends Core_Controller_Action_Admin {

  //ACTION FOR MANAGE LISTINGS
  public function indexAction() {

    $listingtype_id = $this->_getParam('listingtype_id', 0);
    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting')) {
      //FOR UPDATE EXPIRATION
      if ((Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereviewpaidlisting.task.updateexpiredlistings') + 900) <= time()) {
        Engine_Api::_()->sitereviewpaidlisting()->updateExpiredListings($listingtype_id);
      }
    }

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_manage');

    //MAKE FORM
    $this->view->formFilter = $formFilter = new Sitereview_Form_Admin_Manage_Filter();

    //GET PAGE NUMBER
    $page = $this->_getParam('page', 1);
    $tempManageListingFlag = false;
    //GET USER TABLE NAME
    $tableUserName = Engine_Api::_()->getItemTable('user')->info('name');

    //GET CATEGORY TABLE
    $this->view->tableCategory = $tableCategory = Engine_Api::_()->getDbtable('categories', 'sitereview');
    include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license2.php';
    //GET LISTING TABLE
    $tableListing = Engine_Api::_()->getDbtable('listings', 'sitereview');
    $listingTableName = $tableListing->info('name');

    //MAKE QUERY
    $select = $tableListing->select()
            ->setIntegrityCheck(false)
            ->from($listingTableName)
            ->joinLeft($tableUserName, "$listingTableName.owner_id = $tableUserName.user_id", 'username')
            ->group("$listingTableName.listing_id");

    $values = array();

    if ($formFilter->isValid($this->_getAllParams())) {
      $values = $formFilter->getValues();
    }
    foreach ($values as $key => $value) {

      if (null == $value) {
        unset($values[$key]);
      }
    }

    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting')) {
      $packageTable = Engine_Api::_()->getDbtable('packages', 'sitereviewpaidlisting');
      $packageselect = $packageTable->select()->from($packageTable->info("name"), array("package_id", "title"))->order("package_id DESC");
      $this->view->packageList = $packageTable->fetchAll($packageselect);
    }

    $listingTypeCount = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount();

    if ($listingTypeCount > 1) {
      $this->view->listingtype_id = $this->_getParam('listingtype_id', 0);
    } else {
      $this->view->listingtype_id = 1;
    }

    // searching
    $this->view->owner = '';
    $this->view->title = '';
    $this->view->sponsored = '';
    $this->view->newlabel = '';
    $this->view->approved = '';
    $this->view->featured = '';
    $this->view->status = '';
    $this->view->listingbrowse = '';
    $this->view->category_id = '';
    $this->view->subcategory_id = '';
    $this->view->subsubcategory_id = '';
    $this->view->package_id = '';
    $this->view->package = 1;
    if (isset($_POST['search'])) {

      if (!empty($_POST['owner'])) {
        $this->view->owner = $_POST['owner'];
        $select->where($tableUserName . '.username  LIKE ?', '%' . $_POST['owner'] . '%');
      }

      if (!empty($_POST['review_status'])) {
        $this->view->review_status = $review_status = $_POST['review_status'];
        $_POST['review_status']--;

        if ($review_status == 'rating_editor') {
          $select->where($listingTableName . '.rating_editor > ? ', 0);
        } elseif ($review_status == 'rating_users') {
          $select->where($listingTableName . '.rating_users > ? ', 0);
        } elseif ($review_status == 'rating_avg') {
          $select->where($listingTableName . '.rating_avg > ? ', 0);
        } elseif ($review_status == 'both') {
          $select->where($listingTableName . '.rating_editor > ? ', 0);
          $select->where($listingTableName . '.rating_users > ? ', 0);
        }
      }

      if (!empty($_POST['title'])) {
        $this->view->title = $_POST['title'];
        $select->where($listingTableName . '.title  LIKE ?', '%' . $_POST['title'] . '%');
      }

      if (!empty($_POST['sponsored'])) {
        $this->view->sponsored = $_POST['sponsored'];
        $_POST['sponsored']--;

        $select->where($listingTableName . '.sponsored = ? ', $_POST['sponsored']);
      }

      if (!empty($_POST['approved'])) {
        $this->view->approved = $_POST['approved'];
        $_POST['approved']--;
        $select->where($listingTableName . '.approved = ? ', $_POST['approved']);
      }

      if (!empty($_POST['featured'])) {
        $this->view->featured = $_POST['featured'];
        $_POST['featured']--;
        $select->where($listingTableName . '.featured = ? ', $_POST['featured']);
      }

      if (!empty($_POST['newlabel'])) {
        $this->view->newlabel = $_POST['newlabel'];
        $_POST['newlabel']--;
        $select->where($listingTableName . '.newlabel = ? ', $_POST['newlabel']);
      }

      if (!empty($_POST['status'])) {
        $this->view->status = $_POST['status'];
        $_POST['status']--;
        $select->where($listingTableName . '.closed = ? ', $_POST['status']);
      }

      if (!empty($_POST['package_id'])) {
        $this->view->package_id = $_POST['package_id'];
        $select->where($listingTableName . '.package_id = ? ', $_POST['package_id']);
      }

      if (!empty($_POST['listingbrowse'])) {
        $this->view->listingbrowse = $_POST['listingbrowse'];
        $_POST['listingbrowse']--;
        if ($_POST['listingbrowse'] == 0) {
          $select->order($listingTableName . '.view_count DESC');
        } else {
          $select->order($listingTableName . '.listing_id DESC');
        }
      }

      if (!empty($_POST['listingtype_id'])) {
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting'))
          $this->view->package = Engine_Api::_()->getItem('sitereview_listingtype', $_POST['listingtype_id'])->package;
        $this->view->listingtype_id = $_POST['listingtype_id'];
        $select->where($listingTableName . '.listingtype_id = ? ', $_POST['listingtype_id']);
      }
      else
        $this->view->package = 1;

      if (!empty($_POST['category_id']) && empty($_POST['subcategory_id']) && empty($_POST['subsubcategory_id'])) {
        $this->view->category_id = $_POST['category_id'];
        $select->where($listingTableName . '.category_id = ? ', $_POST['category_id']);
      } elseif (!empty($_POST['category_id']) && !empty($_POST['subcategory_id']) && empty($_POST['subsubcategory_id'])) {
        $this->view->category_id = $_POST['category_id'];
        $this->view->subcategory_id = $_POST['subcategory_id'];
        $this->view->subcategory_name = $tableCategory->getCategory($this->view->subcategory_id)->category_name;

        $select->where($listingTableName . '.category_id = ? ', $_POST['category_id'])
                ->where($listingTableName . '.subcategory_id = ? ', $_POST['subcategory_id']);
      } elseif (!empty($_POST['category_id']) && !empty($_POST['subcategory_id']) && !empty($_POST['subsubcategory_id'])) {
        $this->view->category_id = $_POST['category_id'];
        $this->view->subcategory_id = $_POST['subcategory_id'];
        $this->view->subsubcategory_id = $_POST['subsubcategory_id'];
        $this->view->subcategory_name = $tableCategory->getCategory($this->view->subcategory_id)->category_name;
        $this->view->subsubcategory_name = $tableCategory->getCategory($this->view->subsubcategory_id)->category_name;

        $select->where($listingTableName . '.category_id = ? ', $_POST['category_id'])
                ->where($listingTableName . '.subcategory_id = ? ', $_POST['subcategory_id'])
                ->where($listingTableName . '.subsubcategory_id = ? ', $_POST['subsubcategory_id']);
      }
    }

    if (empty($tempManageListingFlag)) {
      return;
    }

    $values = array_merge(array(
        'order' => 'listing_id',
        'order_direction' => 'DESC',
            ), $values);

    $this->view->assign($values);

    $select->order((!empty($values['order']) ? $values['order'] : 'listing_id' ) . ' ' . (!empty($values['order_direction']) ? $values['order_direction'] : 'DESC' ));

    //MAKE PAGINATOR
    $this->view->paginator = Zend_Paginator::factory($select);
    $this->view->paginator->setItemCountPerPage(50);
    $this->view->paginator = $this->view->paginator->setCurrentPageNumber($page);
  }

  //ACTION FOR VIEWING SITEREVIEW DETAILS
  public function detailAction() {

    //GET THE SITEREVIEW ITEM
    $this->view->sitereviewDetail = Engine_Api::_()->getItem('sitereview_listing', (int) $this->_getParam('id'));
  }

  //ACTION FOR MULTI-DELETE LISTINGS
  public function multiDeleteAction() {

    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();

      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          Engine_Api::_()->getItem('sitereview_listing', (int) $value)->delete();
        }
      }
    }
    return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
  }

}