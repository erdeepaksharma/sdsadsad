<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminGeneralController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_AdminGeneralController extends Core_Controller_Action_Admin {

  //ACTION FOR MAKING THE SITEREVIEW FEATURED/UNFEATURED
  public function featuredAction() {

    $listing_id = $this->_getParam('listing_id');
    if (!empty($listing_id)) {
      $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
      $sitereview->featured = !$sitereview->featured;
      $sitereview->save();
    }
    $this->_redirect('admin/sitereview/manage');
  }

  //ACTION FOR MAKING THE SPONSORED /UNSPONSORED
  public function sponsoredAction() {

    $listing_id = $this->_getParam('listing_id');
    if (!empty($listing_id)) {
      $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
      $sitereview->sponsored = !$sitereview->sponsored;
      $sitereview->save();
    }
    $this->_redirect('admin/sitereview/manage');
  }

  //ACTION FOR MAKING THE SITEREVIEW FEATURED/UNFEATURED
  public function newlabelAction() {

    $listing_id = $this->_getParam('listing_id');
    if (!empty($listing_id)) {
      $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
      $sitereview->newlabel = !$sitereview->newlabel;
      $sitereview->save();
    }
    $this->_redirect('admin/sitereview/manage');
  }

  //ACTION FOR MAKING THE SITEREVIEW OPEN/CLOSE
  public function openCloseAction() {

    $listing_id = $this->_getParam('listing_id');
    if (!empty($listing_id)) {
      $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
      $sitereview->closed = !$sitereview->closed;
      $sitereview->save();
    }
    $this->_redirect('admin/sitereview/manage');
  }

  //ACTION FOR MAKING THE SPONSORED /UNSPONSORED
  public function sponsoredCategoryAction() {

    $category_id = $this->_getParam('category_id');
    if (!empty($category_id)) {
      $category = Engine_Api::_()->getItem('sitereview_category', $category_id);
      $category->sponsored = !$category->sponsored;
      $category->save();
    }
    $this->_redirect('admin/sitereview/settings/categories/listingtype_id/' . $category->listingtype_id);
  }

  //ACTION FOR MAKING THE SITEREVIEW APPROVE/DIS-APPROVE
  public function approvedAction() {

    $listing_id = $this->_getParam('listing_id');
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    $email = array();
    try {

      $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
      $email['subject'] = 'Approved/ Disapproved notification';
      $email['title'] = $sitereview->title;
      $owner = Engine_Api::_()->user()->getUser($sitereview->owner_id);
      $email['mail_id'] = $owner->email;
      $sitereview->approved = !$sitereview->approved;

      if (!empty($sitereview->approved)) {

        if (Engine_Api::_()->sitereview()->hasPackageEnable($sitereview->listingtype_id)) {
          if (!empty($sitereview->pending)) {
            $sitereview->pending = 0;
          }
          $diff_days = 0;
          $package = $sitereview->getPackage();
          if (($sitereview->expiration_date !== '2250-01-01 00:00:00' && !empty($sitereview->expiration_date) && $sitereview->expiration_date !== '0000-00-00 00:00:00') && date('Y-m-d', strtotime($sitereview->expiration_date)) > date('Y-m-d')) {
            $diff_days = round((strtotime($sitereview->expiration_date) - strtotime(date('Y-m-d H:i:s'))) / 86400);
          }
          if (($diff_days <= 0) || empty($sitereview->expiration_date) || $sitereview->expiration_date == '0000-00-00 00:00:00') {
            if (!$package->isFree()) {
              if ($sitereview->status != "active") {
                $relDate = new Zend_Date(time());
                $relDate->add((int) 1, Zend_Date::DAY);
                $sitereview->expiration_date = date('Y-m-d H:i:s', $relDate->toValue());
              } else {
                $expirationDate = $package->getExpirationDate();
                if (!empty($expirationDate))
                  $sitereview->expiration_date = date('Y-m-d H:i:s', $expirationDate);
                else
                  $sitereview->expiration_date = '2250-01-01 00:00:00';
              }
            }
            else {
              $expirationDate = $package->getExpirationDate();
              if (!empty($expirationDate))
                $sitereview->expiration_date = date('Y-m-d H:i:s', $expirationDate);
              else
                $sitereview->expiration_date = '2250-01-01 00:00:00';
            }
          }
        }

        if (empty($sitereview->approved_date))
          $sitereview->approved_date = date('Y-m-d H:i:s');
        $email['message'] = "Your listing  \"" . $email['title'] . " \" approved ";
        Engine_Api::_()->sitereview()->aprovedEmailNotification($sitereview, $email);
      } else {
        $email['message'] = "Your listing " . $email['title'] . "  disapproved ";
        Engine_Api::_()->sitereview()->aprovedEmailNotification($sitereview, $email);
      }
      $sitereview->save();
      
        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting'))
          $sitereview_pending = $sitereview->pending;
        else
          $sitereview_pending = 0;

        if ($sitereview->draft == 0 && $sitereview->search && time() >= strtotime($sitereview->creation_date) && empty($sitereview_pending)) {
          $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($sitereview->getOwner(), $sitereview, 'sitereview_new_listtype_' . $sitereview->listingtype_id);

          if ($action != null) {
            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $sitereview);
          }
        }      
      
      
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->_redirect('admin/sitereview/manage');
  }

  //ACTION FOR MAKING THE SITEREVIEW APPROVE/DIS-APPROVE
  public function renewAction() {

    $listing_id = $this->_getParam('id');
    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
        if (!empty($sitereview->approved)) {
          $package = $sitereview->getPackage();
          if ($sitereview->expiration_date !== '2250-01-01 00:00:00') {

            $expirationDate = $package->getExpirationDate();
            $expiration = $package->getExpirationDate();

            $diff_days = 0;
            if (!empty($sitereview->expiration_date) && $sitereview->expiration_date !== '0000-00-00 00:00:00') {
              $diff_days = round((strtotime($sitereview->expiration_date) - strtotime(date('Y-m-d H:i:s'))) / 86400);
            }
            if ($expiration) {
              $date = date('Y-m-d H:i:s', $expiration);

              if ($diff_days >= 1) {

                $diff_days_expiry = round((strtotime($date) - strtotime(date('Y-m-d H:i:s'))) / 86400);
                $incrmnt_date = date('d', time()) + $diff_days_expiry + $diff_days;
                $incrmnt_date = date('Y-m-d H:i:s', mktime(date("H"), date("i"), date("s"), date("m"), $incrmnt_date));
              } else {
                $incrmnt_date = $date;
              }

              $sitereview->expiration_date = $incrmnt_date;
            } else {
              $sitereview->expiration_date = '2250-01-01 00:00:00';
            }
          }
          if ($package->isFree())
            $sitereview->status = "initial";
          else
            $sitereview->status = "active";
        }
        $sitereview->save();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_(''))
      ));
    }
    $this->renderScript('admin-general/renew.tpl');
  }

  public function categoriesAction() {

    $element_value = $this->_getParam('element_value', 1);
    $element_type = $this->_getParam('element_type', 'listingtype_id');

    $categoriesTable = Engine_Api::_()->getDbTable('categories', 'sitereview');
    $select = $categoriesTable->select()
            ->from($categoriesTable->info('name'), array('category_id', 'category_name'))
            ->where("$element_type = ?", $element_value);

    if ($element_type == 'listingtype_id') {
      $select->where('cat_dependency = ?', 0)->where('subcat_dependency = ?', 0);
    } elseif ($element_type == 'cat_dependency') {
      $select->where('subcat_dependency = ?', 0);
    } elseif ($element_type == 'subcat_dependency') {
      $select->where('cat_dependency = ?', $element_value);
    }

    $categoriesData = $categoriesTable->fetchAll($select);

    $categories = array();
    if (Count($categoriesData) > 0) {
      foreach ($categoriesData as $category) {
        $data = array();
        $data['category_name'] = $category->category_name;
        $data['category_id'] = $category->category_id;
        $categories[] = $data;
      }
    }

    $this->view->categories = $categories;
  }

  //ACTION FOR DELETE THE LISTING
  public function deleteAction() {

    $this->_helper->layout->setLayout('admin-simple');
    $listing_id = $this->_getParam('listing_id');
    $this->view->listing_id = $listing_id;

    if ($this->getRequest()->isPost()) {
      Engine_Api::_()->getItem('sitereview_listing', $listing_id)->delete();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Deleted Succesfully.')
      ));
    }
    $this->renderScript('admin-general/delete.tpl');
  }

  //ACTION FOR SHOWING LISTING TYPE MESSAGE
  public function listingTypesAction() {

    //INCREASE THE MEMORY ALLOCATION SIZE AND INFINITE SET TIME OUT
    ini_set('memory_limit', '1024M');
    set_time_limit(0);

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_listingtypes');

    $coreModuleTable = Engine_Api::_()->getDbtable('modules', 'core');
    $this->view->sitereviewlistingtypeInsalled = $coreModuleTable->hasModule('sitereviewlistingtype');
    $this->view->sitereviewlistingtypeEnabled = $coreModuleTable->isModuleEnabled('sitereviewlistingtype');

    //CREATE FORM
    $this->view->form = $form = new Sitereview_Form_Admin_Listingsettings();
    $form->removeElement('member_level');

    //GET LISTING TYPE ID AND SET OBJECT
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 1);
    $this->view->listingType = $listingType = Engine_Api::_()->getItem('sitereview_listingtype', $listingtype_id);
    $previousLocationValue = $listingType->location;

    //SITEREVIEWLISTINGTYPE IS INSTALLED OR NOT
    $this->view->sitereviewlistingtypeEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype');

    $this->view->defaultListingTypeTitle = strtolower(Engine_Api::_()->getItemTable('sitereview_listingtype')->getListingTypeColumn(1, 'title_plural'));

    $this->view->isModsSupport = Engine_Api::_()->sitereview()->isModulesSupport();
    $this->view->tab_type = 'othersettings';

    $form->populate($listingType->toArray());
    if ($listingType->language_phrases)
      $form->populate($listingType->language_phrases);
    $previous_slug_singular = $listingType->slug_singular;
    $previous_slug_plural = $listingType->slug_plural;
    $this->view->claimlink = $listingType->claimlink;

    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting'))
      $this->view->package = $listingType->package;
    else
      $this->view->package = 0;


    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      $slug_singular = $_POST['slug_singular'];
      $slug_plural = $_POST['slug_plural'];
      $title_singular = $_POST['title_singular'];
      $title_plural = $_POST['title_plural'];

      $titleSingular = $listingType->title_singular;
      $titlePlural = $listingType->title_plural;
      $titleSinUc = ucfirst($listingType->title_singular);
      $titleSinUpper = strtoupper($listingType->title_singular);
      $titleSinLc = strtolower($listingType->title_singular);
      $packageValue = 0;
      $isPackageModuleEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting');
      if ($isPackageModuleEnabled)
        $packageValue = $listingType->package;

      if ($slug_singular == $slug_plural) {
        $error = $this->view->translate("Singular Slug and Plural Slug can't be same.");
        $error = Zend_Registry::get('Zend_Translate')->_($error);

        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
        return;
      }

      if ($previous_slug_singular != $slug_singular) {
        $listingtype_id = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->checkListingSlug($slug_singular);
        if (!empty($listingtype_id)) {
          $error = $this->view->translate("Please choose the different 'Singular Slug', you have already created the same slug.");
          $error = Zend_Registry::get('Zend_Translate')->_($error);

          $form->getDecorator('errors')->setOption('escape', false);
          $form->addError($error);
          return;
        }
      }

      if ($previous_slug_plural != $slug_plural) {
        $listingtype_id = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->checkListingSlug($slug_plural);
        if (!empty($listingtype_id)) {
          $error = $this->view->translate("Please choose the different 'Plural Slug', you have already created the same slug.");
          $error = Zend_Registry::get('Zend_Translate')->_($error);

          $form->getDecorator('errors')->setOption('escape', false);
          $form->addError($error);
          return;
        }
      }
      $values = $form->getValues();
      
      if(isset($_POST['package'])) {
        if(!empty($_POST['package']))
          $values['expiry'] = '0';
      }
      
      if ($values['translation_file'] || ((isset($_POST['package']) && !empty($_POST['package'])) && ($_POST['package'] != $packageValue))) {
        $hasLanguageDirectoryPermissions = Engine_Api::_()->getApi('language', 'sitereview')->hasDirectoryPermissions();
        if (!$hasLanguageDirectoryPermissions) {
          $error = $this->view->translate("Language file for this listing type could not be overwritten. because you do not have write permission chmod -R 777 recursively to the directory '/application/languages/'. Please login in over your Cpanel or FTP and give the recursively write permission to this directory and try again.");
          $error = Zend_Registry::get('Zend_Translate')->_($error);
          $form->getDecorator('errors')->setOption('escape', false);
          $form->addError($error);
          return;
        }
      }

      $db = Zend_Db_Table_Abstract::getDefaultAdapter();
      $listingTypeApi = Engine_Api::_()->getApi('listingType', 'sitereview');

      //START CLAIM WORK
      if (isset($_POST['claimlink'])) {
        $menuItemsTable = Engine_Api::_()->getDbTable('MenuItems', 'core');
        $menuItemsTableName = $menuItemsTable->info('name');
        $menuItemsId = $menuItemsTable->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_main_claim_listtype_$listingtype_id")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
          $menuItemsTable->insert(array(
              'name' => "sitereview_main_claim_listtype_$listingtype_id",
              'module' => 'sitereview',
              'label' => "Claim a $titleSinUc",
              'plugin' => 'Sitereview_Plugin_Menus::canViewClaims',
              'params' => '{"route":"sitereview_claim_listtype_' . $listingtype_id . '","listingtype_id":"' . $listingtype_id . '"}',
              'menu' => "sitereview_main_listtype_$listingtype_id",
              'submenu' => '',
              'order' => 6,
          ));
        }

        $menuItemsId = $menuItemsTable->select()
                ->from($menuItemsTableName, array('id'))
                ->where('name = ? ', "sitereview_gutter_claim_listtype_$listingtype_id")
                ->query()
                ->fetchColumn();

        if (empty($menuItemsId)) {
          $menuItemsTable->insert(array(
              'name' => "sitereview_gutter_claim_listtype_$listingtype_id",
              'module' => 'sitereviewpaidlisting',
              'label' => "Claim this $titleSinUc",
              'plugin' => 'Sitereview_Plugin_Menus::sitereviewGutterClaim',
              'params' => '{"listingtype_id":"' . $listingtype_id . '"}',
              'menu' => "sitereview_gutter_listtype_$listingtype_id",
              'submenu' => '',
              'order' => 16,
          ));
        }

        if ($_POST['claim_show_menu'] == 1) {
          $menuItemsTable->update(array('menu' => 'core_footer', 'params' => '{"route":"sitereview_claim_listtype_' . $listingtype_id . '","listingtype_id":"' . $listingtype_id . '"}'), array('name =?' => "sitereview_main_claim_listtype_$listingtype_id"));
        } else if ($_POST['claim_show_menu'] == 2) {
          $menuItemsTable->update(array('menu' => "sitereview_main_listtype_$listingtype_id", 'params' => '{"route":"sitereview_claim_listtype_' . $listingtype_id . '","listingtype_id":"' . $listingtype_id . '"}'), array('name =?' => "sitereview_main_claim_listtype_$listingtype_id"));
        } else if (empty($_POST['claim_show_menu'])) {
          $menuItemsTable->update(array('menu' => '', 'params' => ''), array('name =?' => "sitereview_main_claim_listtype_$listingtype_id"));
        }
        $listingType->claimlink = 1;
        $listingTypeApi->createClaimPage($listingType);
      }

      //END CLAIM WORK
      //START PACKAGE WORK
      if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting') && !empty($_POST['package'])) {
        $listingType->package = 1;
        $listingTypeApi->freePackageCreate($listingType);
        $listingTypeApi->createPackageNavigation($listingType->listingtype_id);
      }
      //END PACKAGE WORK

      $db->beginTransaction();

      try {
        include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license2.php';
        if(isset($values['location']) && $values['location'] != $previousLocationValue) {
            Engine_Api::_()->getApi('listingType', 'sitereview')->locationMenuUpdate($listingType);
        }
        
        if ($values['pinboard_layout']) {
          $listingTypeApi->setPinBoardLayoutHomePage($listingType);
        }

        //EDIT IF SINGULAR/PLURAL TITLE HAS BEEN CHANGED
        if ($titleSingular != $listingType->title_singular || $titlePlural != $listingType->title_plural) {

          //if (!empty($_POST['pages_navigation']) && in_array('pages', $_POST['pages_navigation'])) {

          $listingTypeApi->widgetizedPagesEdit($listingType, 'home', $titleSingular, $titlePlural);
          $listingTypeApi->widgetizedPagesEdit($listingType, 'index', $titleSingular, $titlePlural);
          $listingTypeApi->widgetizedPagesEdit($listingType, 'view', $titleSingular, $titlePlural);
          $listingTypeApi->widgetizedPagesEdit($listingType, 'map', $titleSingular, $titlePlural);
          //}
          //if (!empty($_POST['pages_navigation']) && in_array('navigations', $_POST['pages_navigation'])) {

          $listingTypeApi->mainNavigationEdit($listingType);
          $listingTypeApi->gutterNavigationEdit($listingType);
          //}

          $listingTypeApi->activityFeedQueryEdit($listingType, $titleSingular, $titlePlural);
          $listingTypeApi->searchFormSettingEdit($listingType, $titleSingular, $titlePlural);

          //START INTERGRATION EXTENSION WORK
          //START FOR PAGE INRAGRATION.
          $sitepageintegrationEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitepageintegration');
          if (!empty($sitepageintegrationEnabled)) {
            Engine_Api::_()->sitepageintegration()->pageintergrationTitleEdit($values['title_singular'], $this->_getParam('listingtype_id'));
          }
          //END FOR PAGE INRAGRATION.
          //START FOR BUSINESS INRAGRATION.
          $sitebusinessintegrationEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitebusinessintegration');
          if (!empty($sitebusinessintegrationEnabled)) {
            Engine_Api::_()->sitebusinessintegration()->businessintergrationTitleEdit($values['title_singular'], $this->_getParam('listingtype_id'));
          }
          //END FOR BUSINESS INRAGRATION.
          //START FOR GROUP INRAGRATION.
          $sitegroupintegrationEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitegroupintegration');
          if (!empty($sitegroupintegrationEnabled)) {
            Engine_Api::_()->sitegroupintegration()->groupintergrationTitleEdit($values['title_singular'], $this->_getParam('listingtype_id'));
          }
          //END FOR GROUP INRAGRATION.
          //START FOR STORE INRAGRATION.
          $sitestoreintegrationEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitestoreintegration');
          if (!empty($sitestoreintegrationEnabled)) {
            Engine_Api::_()->sitestoreintegration()->storeintergrationTitleEdit($values['title_singular'], $this->_getParam('listingtype_id'));
          }
          //END FOR STORE INRAGRATION.
          //END INTERGRATION EXTENSION WORK
        }
        if ($values['translation_file'] || ((isset($_POST['package']) && !empty($_POST['package'])) && ($_POST['package'] != $packageValue))) {
          Engine_Api::_()->getApi('language', 'sitereview')->setTranslateForListType($listingType);
        }
        $this->view->form = $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved successfully.'));

        $listingTypeApi->mainMenuEdit($listingType);
        
        //BANNED PAGE URL WORK.
        $listingTypeApi->addBannedUrls($listingType);

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      $this->_redirect("admin/sitereview/general/listing-types");
    }
  }

  //ACTION FOR CHANGE THE OWNER OF THE LISTING
  public function changeOwnerAction() {

    //LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    //GET LISTING ID
    $this->view->listing_id = $listing_id = $this->_getParam('listing_id');

    //FORM
    $form = $this->view->form = new Sitereview_Form_Admin_Changeowner();

    //SET ACTION
    $form->setAction($this->getFrontController()->getRouter()->assemble(array()));

    //GET SITEREVIEW ITEM
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

    //OLD OWNER ID
    $oldownerid = $sitereview->owner_id;

    $listing_type = Engine_Api::_()->getDbtable('listingtypes', 'sitereview')->getListingTypeColumn($sitereview->listingtype_id, 'title_singular');

    //CHECK FORM VALIDATION
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      //GET FORM VALUES
      $values = $form->getValues();

      //GET USER ID WHICH IS NOW NEW USER
      $changeuserid = $values['user_id'];

      //CHANGE USER TABLE
      $changed_user = Engine_Api::_()->getItem('user', $changeuserid);

      //OWNER USER TABLE
      $user = Engine_Api::_()->getItem('user', $sitereview->owner_id);

      //GET DB
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');
        $activityTableName = $activityTable->info('name');

        $select = $activityTable->select()
                ->from($activityTableName)
                ->where('subject_id = ?', $oldownerid)
                ->where('subject_type = ?', 'user')
                ->where('object_id = ?', $listing_id)
                ->where('object_type = ?', 'sitereview_listing')
                ->where('type = ?', 'sitereview_new_listtype_' . $sitereview->listingtype_id)
        ;
        $activityData = $activityTable->fetchRow($select);
        if (!empty($activityData)) {
          $activityData->subject_id = $changeuserid;
          $activityData->save();
          $activityTable->resetActivityBindings($activityData);
        }


        //UPDATE LISTING TABLE
        Engine_Api::_()->getDbtable('listings', 'sitereview')->update(array('owner_id' => $changeuserid), array('listing_id = ?' => $listing_id));

        //UPDATE PHOTO TABLE
        $photoTable = Engine_Api::_()->getDbtable('photos', 'sitereview');
        $photoTableName = $photoTable->info('name');
        $selectPhotos = $photoTable->select()
                ->from($photoTableName)
                ->where('user_id = ?', $oldownerid)
                ->where('listing_id = ?', $listing_id);
        $photoDatas = $photoTable->fetchAll($selectPhotos);
        foreach ($photoDatas as $photoData) {
          $photoData->user_id = $changeuserid;
          $photoData->save();

          $select = $activityTable->select()
                  ->from($activityTableName)
                  ->where('subject_id = ?', $oldownerid)
                  ->where('subject_type = ?', 'user')
                  ->where('object_id = ?', $photoData->photo_id)
                  ->where('object_type = ?', 'sitereview_listing')
                  ->where('type = ?', 'sitereview_photo_upload_listtype_' . $sitereview->listingtype_id)
          ;
          $activityDatas = $activityTable->fetchAll($select);
          foreach ($activityDatas as $activityData) {
            $activityData->subject_id = $changeuserid;
            $activityData->save();
            $activityTable->resetActivityBindings($activityData);
          }
        }

        Engine_Api::_()->getDbtable('photos', 'sitereview')->update(array('user_id' => $changeuserid), array('user_id = ?' => $oldownerid, 'listing_id = ?' => $listing_id));

        //UPDATE VIDEO TABLE
        $videoTable = Engine_Api::_()->getDbtable('videos', 'sitereview');
        $videoTableName = $videoTable->info('name');
        $selectVideos = $videoTable->select()
                ->from($videoTableName)
                ->where('owner_id = ?', $oldownerid)
                ->where('listing_id = ?', $listing_id);
        $videoDatas = $videoTable->fetchAll($selectVideos);
        foreach ($videoDatas as $videoData) {
          $videoData->owner_id = $changeuserid;
          $videoData->save();

          $select = $activityTable->select()
                  ->from($activityTableName)
                  ->where('subject_id = ?', $oldownerid)
                  ->where('subject_type = ?', 'user')
                  ->where('object_id = ?', $videoData->video_id)
                  ->where('object_type = ?', 'sitereview_listing')
                  ->where('type = ?', 'sitereview_video_new_listtype_' . $sitereview->listingtype_id)
          ;
          $activityDatas = $activityTable->fetchAll($select);
          foreach ($activityDatas as $activityData) {
            $activityData->subject_id = $changeuserid;
            $activityData->save();
            $activityTable->resetActivityBindings($activityData);
          }
        }

        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('video')) {

          $videoTable = Engine_Api::_()->getDbtable('videos', 'video');
          $videoTableName = $videoTable->info('name');

          $clasfVideoTable = Engine_Api::_()->getDbtable('clasfvideos', 'sitereview');
          $clasfVideoTableName = $clasfVideoTable->info('name');

          $videoDatas = $clasfVideoTable->select()
                  ->setIntegrityCheck()
                  ->from($clasfVideoTableName, array('video_id'))
                  ->joinLeft($videoTableName, "$clasfVideoTableName.video_id = $clasfVideoTableName.video_id", array(''))
                  ->where("$clasfVideoTableName.listing_id = ?", $listing_id)
                  ->where("$videoTableName.owner_id = ?", $oldownerid)
                  ->query()
                  ->fetchAll(Zend_Db::FETCH_COLUMN);

          if (!empty($videoDatas)) {

            $db->update('engine4_video_videos', array('owner_id' => $changeuserid), array('video_id IN (?)' => (array) $videoDatas));

            $select = $activityTable->select()
                    ->from($activityTableName)
                    ->where('subject_id = ?', $oldownerid)
                    ->where('subject_type = ?', 'user')
                    ->where('object_id IN (?)', $videoDatas)
                    ->where("type = 'video_new' OR type = 'video_sitereview_listtype_$sitereview->listingtype_id'")
            ;
            $activityDatas = $activityTable->fetchAll($select);
            foreach ($activityDatas as $activityData) {
              $activityData->subject_id = $changeuserid;
              $activityData->save();
              $activityTable->resetActivityBindings($activityData);
            }
          }
        }

        //UPDATE REVIEW TABLE
        $reviewTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
        $previousOwnerReviewed = $reviewTable->canPostReview(array('resource_id' => $listing_id, 'resource_type' => 'sitereview_listing', 'viewer_id' => $oldownerid));
        $newOwnerReviewed = $reviewTable->canPostReview(array('resource_id' => $listing_id, 'resource_type' => 'sitereview_listing', 'viewer_id' => $changeuserid));
        if (!empty($previousOwnerReviewed) && empty($newOwnerReviewed)) {
          $reviewTable->update(array('owner_id' => $changeuserid), array('review_id = ?' => $previousOwnerReviewed));
          $db->update('engine4_sitereview_reviewdescriptions', array('user_id' => $changeuserid), array('review_id = ?' => $previousOwnerReviewed));

          $select = $activityTable->select()
                  ->from($activityTableName)
                  ->where('subject_id = ?', $oldownerid)
                  ->where('subject_type = ?', 'user')
                  ->where('object_type = ?', 'sitereview_listing')
                  ->where('object_id = ?', $previousOwnerReviewed)
                  ->where('type = ?', 'sitereview_review_add_listtype_' . $sitereview->listingtype_id)
          ;
          $activityDatas = $activityTable->fetchAll($select);
          foreach ($activityDatas as $activityData) {
            $activityData->subject_id = $changeuserid;
            $activityData->save();
            $activityTable->resetActivityBindings($activityData);
          }
        }

        //UPDATE DISCUSSION/TOPIC WORK
        $topicTable = Engine_Api::_()->getDbtable('topics', 'sitereview');
        $topicTableName = $topicTable->info('name');
        $selectTopic = $topicTable->select()
                ->from($topicTableName)
                ->where('user_id = ?', $oldownerid)
                ->where('listing_id = ?', $listing_id);
        $topicDatas = $topicTable->fetchAll($selectTopic);
        foreach ($topicDatas as $topicData) {
          $topicData->user_id = $changeuserid;
          $topicData->lastposter_id = $changeuserid;
          $topicData->save();

          $select = $activityTable->select()
                  ->from($activityTableName)
                  ->where('subject_id = ?', $oldownerid)
                  ->where('subject_type = ?', 'user')
                  ->where('object_id = ?', $topicData->topic_id)
                  ->where('type = ?', 'sitereview_topic_create_listtype_' . $sitereview->listingtype_id)
          ;
          $activityDatas = $activityTable->fetchAll($select);
          foreach ($activityDatas as $activityData) {
            $activityData->subject_id = $changeuserid;
            $activityData->save();
            $activityTable->resetActivityBindings($activityData);
          }
        }

        $postTable = Engine_Api::_()->getDbtable('posts', 'sitereview');
        $postTableName = $postTable->info('name');
        $selectPost = $postTable->select()
                ->from($postTableName)
                ->where('user_id = ?', $oldownerid)
                ->where('listing_id = ?', $listing_id);
        $postDatas = $postTable->fetchAll($selectPost);
        foreach ($postDatas as $postData) {
          $postData->user_id = $changeuserid;
          $postData->save();

          $select = $activityTable->select()
                  ->from($activityTableName)
                  ->where('subject_id = ?', $oldownerid)
                  ->where('subject_type = ?', 'user')
                  ->where('object_id = ?', $postData->post_id)
                  ->where('type = ?', 'sitereview_topic_reply_listtype_' . $sitereview->listingtype_id)
          ;
          $activityDatas = $activityTable->fetchAll($select);
          foreach ($activityDatas as $activityData) {
            $activityData->subject_id = $changeuserid;
            $activityData->save();
            $activityTable->resetActivityBindings($activityData);
          }
        }

        //UPDATE THE POST
        $attachementTable = Engine_Api::_()->getDbtable('attachments', 'activity');
        $attachementTableName = $attachementTable->info('name');

        $select = $activityTable->select()
                ->from($activityTableName)
                ->where('subject_id = ?', $oldownerid)
                ->where('subject_type = ?', 'user')
                ->where('object_id = ?', $listing_id)
                ->where('object_type = ?', 'sitereview_listing')
                ->where('type = ?', 'post')
        ;
        $activityDatas = $activityTable->fetchAll($select);
        foreach ($activityDatas as $activityData) {

          $select = $attachementTable->select()
                  ->from($attachementTableName, array('type', 'id'))
                  ->where('action_id = ?', $activityData->action_id);
          $attachmentData = $attachementTable->fetchRow($select);

          if ($attachmentData->type == 'video') {
            $db->update('engine4_video_videos', array('owner_id' => $changeuserid), array('video_id = ?' => $attachmentData->id));
          } elseif ($attachmentData->type == 'album_photo') {
            //UNABLE TO DO THIS CHANGE BECAUSE FOR WALL POST THERE IS ONLY ONE ALBUM PER USER SO WE CAN NOT SAY THAT THIS IS ONLY THE WALL POST POSTED BY SITEREVIEW PROFILE PAGE.
          } elseif ($attachmentData->type == 'music_playlist_song') {
            $db->update('engine4_music_playlists', array('owner_id' => $changeuserid), array('playlist_id = ?' => $attachmentData->id));
          } elseif ($attachmentData->type == 'core_link') {
            $db->update('engine4_core_links', array('owner_id' => $changeuserid), array('link_id = ?' => $attachmentData->id));
          }

          if ($attachmentData->type != 'album_photo') {
            $activityData->subject_id = $changeuserid;
            $activityData->save();
            $activityTable->resetActivityBindings($activityData);
          }
        }

        //EMAIL TO NEW AND PREVIOUS OWNER        
        //GET LISTING URL
        $httpVar = _ENGINE_SSL ? 'https://' : 'http://';
        $list_baseurl = $httpVar . $_SERVER['HTTP_HOST'] .
                Zend_Controller_Front::getInstance()->getRouter()->assemble(array('listing_id' => $listing_id, 'slug' => $sitereview->getSlug()), "sitereview_entry_view_listtype_$sitereview->listingtype_id", true);

        //MAKING LISTING TITLE LINK
        $list_title_link = '<a href="' . $list_baseurl . '"  >' . $sitereview->title . ' </a>';

        //GET ADMIN EMAIL
        $email = Engine_Api::_()->getApi('settings', 'core')->core_mail_from;

        //EMAIL THAT GOES TO OLD OWNER
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($user->email, 'SITEREVIEW_CHANGEOWNER_EMAIL', array(
            'list_title' => $sitereview->title,
            'listing_type' => strtolower($listing_type),
            'list_title_with_link' => $list_title_link,
            'object_link' => $list_baseurl,
            'site_contact_us_link' => $httpVar . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getBaseUrl() . '/help/contact',
            'email' => $email,
            'queue' => true
        ));

        //EMAIL THAT GOES TO NEW OWNER
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($changed_user->email, 'SITEREVIEW_BECOMEOWNER_EMAIL', array(
            'list_title' => $sitereview->title,
            'listing_type' => strtolower($listing_type),
            'list_title_with_link' => $list_title_link,
            'object_link' => $list_baseurl,
            'site_contact_us_link' => $httpVar . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getBaseUrl() . '/help/contact',
            'email' => $email,
            'queue' => true
        ));

        //COMMIT
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      //SUCCESS
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 300,
          'parentRefresh' => 300,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('The listing owner has been changed succesfully.'))
      ));
    }
  }
  
  //ACTION FOR CHANGE THE OWNER OF THE LISTING
  public function changeListingtypeAction() {

    //LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    //GET LISTING ID
    $this->view->listing_id = $listing_id = $this->_getParam('listing_id');

    //FORM
    $this->view->form = $form = new Sitereview_Form_Admin_Changelistingtype();

    //SET ACTION
    $form->setAction($this->getFrontController()->getRouter()->assemble(array()));

    //GET SITEREVIEW ITEM
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

    $previousListingTypeId = $sitereview->listingtype_id;
    
    $previousProfileType = $sitereview->profile_type;
    
    $previousCategoryId = $sitereview->category_id;
    $previoussubCategoryId = $sitereview->subcategory_id;
    $previoussubsubCategoryId = $sitereview->subsubcategory_id;
    
    //CHECK FORM VALIDATION
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
        
      //GET FORM VALUES
      $values = $form->getValues();

      $newListingTypeId = $values['listingtype_id'];
      
      //GET DB
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
          
        $sitereview->category_id = $sitereview->subcategory_id = $sitereview->subsubcategory_id = 0;  

        $categoryTable = Engine_Api::_()->getDbTable('categories', 'sitereview');
        $category = Engine_Api::_()->getItem('sitereview_category', $previousCategoryId);

        $categoryId = $categoryTable->select()->from($categoryTable->info('name'), 'category_id')->where('listingtype_id = ?', $newListingTypeId)->where('category_name = ?', $category->category_name)->query()->fetchColumn();   
        
        if(!empty($categoryId)) {
            $newCategory = Engine_Api::_()->getItem('sitereview_category', $categoryId);
            $sitereview->category_id = $newCategory->category_id;
            
            if(!empty($previoussubCategoryId)) {
                $subcategory = Engine_Api::_()->getItem('sitereview_category', $previoussubCategoryId);
                $subcategoryId = $categoryTable->select()->from($categoryTable->info('name'), 'category_id')->where('cat_dependency = ?', $categoryId)->where('subcat_dependency = ?', 0)->where('category_name = ?', $subcategory->category_name)->query()->fetchColumn();
                
                if($subcategoryId) {
                    $newsubCategory = Engine_Api::_()->getItem('sitereview_category', $subcategoryId);
                    $sitereview->subcategory_id = $newsubCategory->category_id;                    
                    if(!empty($previoussubsubCategoryId)) {
                        $subsubcategory = Engine_Api::_()->getItem('sitereview_category', $previoussubsubCategoryId);
                        $subsubcategoryId = $categoryTable->select()->from($categoryTable->info('name'), 'category_id')->where('cat_dependency = ?', $subcategoryId)->where('subcat_dependency = ?', $subcategoryId)->where('category_name = ?', $subsubcategory->category_name)->query()->fetchColumn();

                        if($subsubcategoryId) {
                            $newsubsubCategory = Engine_Api::_()->getItem('sitereview_category', $subsubcategoryId);
                            $sitereview->subsubcategory_id = $newsubsubCategory->category_id;
                        }
                    }                       
                }
            }
        }
        else {
            
            //CREATE CATEGORY AND WORK ACCORDINGLY
            $categoryArray = $category->toArray();
            unset($categoryArray['category_id']);
            $categoryArray['listingtype_id'] = $newListingTypeId;
            $db->insert($categoryTable->info('name'), $categoryArray);
            $sitereview->category_id = $db->lastInsertId($categoryTable);
            
            if($previoussubCategoryId) {
                
                $subcategory = Engine_Api::_()->getItem('sitereview_category', $sitereview->subcategory_id);
                $subcategoryArray = $subcategory->toArray();
                unset($subcategoryArray['category_id']);
                $subcategoryArray['listingtype_id'] = $newListingTypeId;
                $subcategoryArray['cat_dependency'] = $sitereview->category_id;
                $db->insert($categoryTable->info('name'), $subcategoryArray);
                $sitereview->subcategory_id = $db->lastInsertId($categoryTable);  

                if($previoussubsubCategoryId) {
                    $subsubcategory = Engine_Api::_()->getItem('sitereview_category', $sitereview->subsubcategory_id);
                    $subsubcategoryArray = $subsubcategory->toArray();
                    unset($subsubcategoryArray['category_id']);
                    $subsubcategoryArray['listingtype_id'] = $newListingTypeId;
                    $subsubcategoryArray['cat_dependency'] = $sitereview->subcategory_id;
                    $subsubcategoryArray['subcat_dependency'] = $sitereview->subcategory_id;
                    $db->insert($categoryTable->info('name'), $subsubcategoryArray);
                    $sitereview->subsubcategory_id = $db->lastInsertId($categoryTable);
                }
            }
        }        
        
        //UPDATE PROFILE TYPE ACCORDING TO CATEGORY
        $categoryIds = array();
        $categoryIds[] = $sitereview->category_id;
        $categoryIds[] = $sitereview->subcategory_id;
        $categoryIds[] = $sitereview->subsubcategory_id;
        $sitereview->profile_type = Engine_Api::_()->getDbTable('categories', 'sitereview')->getProfileType($categoryIds, 0, 'profile_type');        
        
        //IF PREVIOUS PROFILE TYPE AND NEW PROFILE TYPE IS NOT SAME THAN DELETE CUSTOM FIELDS
        if($previousProfileType != $sitereview->profile_type) {
            
          $fieldvalueTable = Engine_Api::_()->fields()->getTable('sitereview_listing', 'values');
          $fieldvalueTable->delete(array('item_id = ?' => $sitereview->listing_id));

          Engine_Api::_()->fields()->getTable('sitereview_listing', 'search')->delete(array(
              'item_id = ?' => $sitereview->listing_id,
          ));

          if (!empty($sitereview->profile_type) && !empty($previousProfileType)) {
              
            $defaultProfileId = Engine_Api::_()->getDbTable('metas', 'sitereview')->defaultProfileId();  
              
            //PUT NEW PROFILE TYPE
            $fieldvalueTable->insert(array(
                'item_id' => $sitereview->listing_id,
                'field_id' => $defaultProfileId,
                'index' => 0,
                'value' => $sitereview->profile_type,
            ));
          }               
        }
        
        //UPDATE NEW LISTING TYPE ID
        $sitereview->listingtype_id = $newListingTypeId;
        
        //SAVE CHANGES
        $sitereview->save();        
        
        //SEND EMAIL TO LISTING OWNER
        $httpVar = _ENGINE_SSL ? 'https://' : 'http://';
        $list_baseurl = $httpVar . $_SERVER['HTTP_HOST'] .
                Zend_Controller_Front::getInstance()->getRouter()->assemble(array('listing_id' => $listing_id, 'slug' => $sitereview->getSlug()), "sitereview_entry_view_listtype_$sitereview->listingtype_id", true);
        $listing_type = Engine_Api::_()->getDbtable('listingtypes', 'sitereview')->getListingTypeColumn($previousListingTypeId, 'title_singular');
        $new_listing_type = Engine_Api::_()->getDbtable('listingtypes', 'sitereview')->getListingTypeColumn($sitereview->listingtype_id, 'title_singular');

        //MAKING LISTING TITLE LINK
        $list_title_link = '<a href="' . $list_baseurl . '"  >' . $sitereview->title . ' </a>';

        Engine_Api::_()->getApi('mail', 'core')->sendSystem($sitereview->getOwner()->email, 'SITEREVIEW_CHANGELISTINGTYPE_EMAIL', array(
            'list_title' => $sitereview->title,
            'listing_type' => strtolower($listing_type),
            'new_listing_type' => strtolower($new_listing_type),
            'list_title_with_link' => $list_title_link,
            'object_link' => $list_baseurl,
            'site_contact_us_link' => $httpVar . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getBaseUrl() . '/help/contact',
            'email' => Engine_Api::_()->getApi('settings', 'core')->core_mail_from,
            'queue' => false
        ));

        //COMMIT
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      //SUCCESS
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 300,
          'parentRefresh' => 300,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('Listing type has been changed succesfully.'))
      ));
    }
  }  

  //ACTION FOR GETTING THE LIST OF USERS
  public function getOwnerAction() {

    //GET SITEREVIEW ITEM
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $this->_getParam('listing_id'));

    //USER TABLE
    $tableUser = Engine_Api::_()->getDbtable('users', 'user');
    $userTableName = $tableUser->info('name');
    $noncreate_owner_level = array();
    $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
    foreach (Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll() as $level) {
      $can_create = 0;
      if ($level->type != "public") {
        $can_create = Engine_Api::_()->authorization()->getPermission($level->level_id, 'sitereview_listing', "edit_listtype_$sitereview->listingtype_id");
        if (empty($can_create)) {
          $noncreate_owner_level[] = $level->level_id;
        }
      }
    }

    //SELECT
    $select = $tableUser->select()
            ->where('displayname  LIKE ? ', '%' . $this->_getParam('text') . '%')
            ->where('user_id !=?', $sitereview->owner_id)
            ->order('displayname ASC')
            ->limit($this->_getParam('limit', 40));

    if (!empty($noncreate_owner_level)) {
      $str = (string) ( is_array($noncreate_owner_level) ? "'" . join("', '", $noncreate_owner_level) . "'" : $noncreate_owner_level );
      $select->where($userTableName . '.level_id not in (?)', new Zend_Db_Expr($str));
    }

    //FETCH
    $userlists = $tableUser->fetchAll($select);

    //MAKING DATA
    $data = array();
    $mode = $this->_getParam('struct');

    if ($mode == 'text') {
      foreach ($userlists as $userlist) {
        $content_photo = $this->view->itemPhoto($userlist, 'thumb.icon');
        $data[] = array(
            'id' => $userlist->user_id,
            'label' => $userlist->displayname,
            'photo' => $content_photo
        );
      }
    } else {
      foreach ($userlists as $userlist) {
        $content_photo = $this->view->itemPhoto($userlist, 'thumb.icon');
        $data[] = array(
            'id' => $userlist->user_id,
            'label' => $userlist->displayname,
            'photo' => $content_photo
        );
      }
    }

    return $this->_helper->json($data);
  }

}
