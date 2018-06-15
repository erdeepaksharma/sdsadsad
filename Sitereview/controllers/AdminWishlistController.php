<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminWishlistController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_AdminWishlistController extends Core_Controller_Action_Admin {

  //ACTION FOR MANAGE PLAYLISTS
  public function manageAction() {

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_wishlist');

    //FORM GENERATION
    $this->view->formFilter = $formFilter = new Sitereview_Form_Admin_Filter();

    //GET CURRENT PAGE NUMBER
    $page = $this->_getParam('page', 1);

    //GET USER TABLE NAME
    $tableUserName = Engine_Api::_()->getItemTable('user')->info('name');

    //GET WISHLIST PAGE TABLE
    $wishlistListingTable = Engine_Api::_()->getDbtable('wishlistmaps', 'sitereview');
    $wishlistListingTableName = $wishlistListingTable->info('name');

    //MAKE QUERY
    $tableWishlist = Engine_Api::_()->getDbtable('wishlists', 'sitereview');
    $tableWishlistName = $tableWishlist->info('name');
    $select = $tableWishlist->select()
            ->setIntegrityCheck(false)
            ->from($tableWishlistName)
            ->joinLeft($wishlistListingTableName, "$wishlistListingTableName.wishlist_id = $tableWishlistName.wishlist_id", array("COUNT($wishlistListingTableName.wishlist_id) AS total_item"))
            ->joinLeft($tableUserName, "$tableWishlistName.owner_id = $tableUserName.user_id", 'username')
            ->group($tableWishlistName . '.wishlist_id');

    //GET VALUES
    if ($formFilter->isValid($this->_getAllParams())) {
      $values = $formFilter->getValues();
    }

    foreach ($values as $key => $value) {
      if (null === $value) {
        unset($values[$key]);
      }
    }

    $values = array_merge(array('order' => 'wishlist_id', 'order_direction' => 'DESC'), $values);

    if (!empty($_POST['user_name'])) {
      $user_name = $_POST['user_name'];
    } elseif (!empty($_GET['user_name']) && !isset($_POST['post_search'])) {
      $user_name = $_GET['user_name'];
    } else {
      $user_name = '';
    }

    if (!empty($_POST['wishlist_name'])) {
      $wishlist_name = $_POST['wishlist_name'];
    } elseif (!empty($_GET['wishlist_name']) && !isset($_POST['post_search'])) {
      $wishlist_name = $_GET['wishlist_name'];
    } else {
      $wishlist_name = '';
    }

    if (!empty($_POST['listing_name'])) {
      $listing_name = $_POST['listing_name'];
    } elseif (!empty($_GET['listing_name']) && !isset($_POST['post_search'])) {
      $listing_name = $_GET['listing_name'];
    } elseif ($this->_getParam('listing_name', '') && !isset($_POST['post_search'])) {
      $listing_name = $this->_getParam('listing_name', '');
    } else {
      $listing_name = '';
    }

    //SEARCHING
    $this->view->user_name = $values['user_name'] = $user_name;
    $this->view->wishlist_name = $values['wishlist_name'] = $wishlist_name;
    $this->view->listing_name = $values['listing_name'] = $listing_name;

    if (!empty($user_name)) {
      $select->where($tableUserName . '.username  LIKE ?', '%' . $user_name . '%');
    }
    if (!empty($wishlist_name)) {
      $select->where($tableWishlistName . '.title  LIKE ?', '%' . $wishlist_name . '%');
    }
    if (!empty($listing_name)) {
      $tablePageName = Engine_Api::_()->getDbTable('listings', 'sitereview')->info('name');
      $select->joinLeft($tablePageName, "$wishlistListingTableName.listing_id = $tablePageName.listing_id", array('title AS page_title'))
              ->where($tablePageName . '.title  LIKE ?', '%' . $listing_name . '%');
    }

    //ASSIGN VALUES TO THE TPL
    $this->view->formValues = array_filter($values);
    $this->view->assign($values);

    $select->order((!empty($values['order']) ? $values['order'] : 'wishlist_id' ) . ' ' . (!empty($values['order_direction']) ? $values['order_direction'] : 'DESC' ));
    
    include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license2.php';
  }

  //ACTION FOR DELETE THE WISHLIST
  public function deleteAction() {

    //SET LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    //GET WISHLIST ID
    $this->view->wishlist_id = $wishlist_id = $this->_getParam('wishlist_id');

    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {

        //DELETE WISHLIST CONTENT
        Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id)->delete();

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('')
      ));
    }
    $this->renderScript('admin-wishlist/delete.tpl');
  }

  //ACTION FOR MULTI DELETE WISHLIST
  public function multiDeleteAction() {

    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {

          //GET WISHLIST ID
          $wishlist_id = (int) $value;

          //DELETE WISHLIST CONTENT
          Engine_Api::_()->getItem('sitereview_wishlist', $wishlist_id)->delete();
        }
      }
    }
    return $this->_helper->redirector->gotoRoute(array('action' => 'manage'));
  }

}