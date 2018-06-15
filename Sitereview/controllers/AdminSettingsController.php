<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminSettingsController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_AdminSettingsController extends Core_Controller_Action_Admin {

  //ACTION FOR GLOBAL SETTINGS
  public function indexAction() {
    $getHostTypeArray = array();
    $requestListType = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.request.listtype', false);
    $sitereviewShowViewtype = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.viewtype', null);
    if( empty($sitereviewShowViewtype) && !empty($requestListType) ) {
      $viewAttampt = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.view.attempt', null);
      if( !empty($viewAttampt) ) {
        $viewAttampt = @convert_uudecode($viewAttampt);
      }      
      $this->view->viewAttapt = $viewAttampt;
      $getHostTypeArray = @unserialize($requestListType);
      $getHostTypeArray = @array_unique($getHostTypeArray);      
    }
    $this->view->getHostTypeArray  = $getHostTypeArray;
    
    // TRIM LICENSE KEYS
    if(!empty($_POST)) {
      foreach($_POST as $key => $value) {
        if(@strstr($key, "_lsettings")) {
          $_POST[$key] = @trim($_POST[$key]);
        }
      }
    }
    
    if( !empty($_POST['is_remove_note']) ) {
      Engine_Api::_()->getApi('settings', 'core')->setSetting('sitereview.request.listtype', false);
      $this->view->getHostTypeArray = array();
    }
    
    if( !empty($_POST) && array_key_exists('is_remove_note', $_POST) ) {
      unset($_POST['is_remove_note']);
    }
    
    $this->view->moduleSitereview = Engine_Api::_()->getDbtable('modules', 'core')->getModule('sitereview');
    
    $this->view->moduleSitereviewlistingtype = Engine_Api::_()->getDbtable('modules', 'core')->getModule('sitereviewlistingtype');    
    $sitereview_global_form_content = array('sitereview_proximitysearch', 'sitereview_checkcomment_widgets', 'sitereview_proximity_search_kilometer', 'save', "sitereview_network", "sitereview_default_show", "sitereview_map_sponsored", "sitereview_map_city", "sitereview_map_zoom", "sitereview_code_share", "sitereview_currency", "sitereview_networks_type", "sitereview_expirydate_enabled", "sitereview_fs_markers", "sitereview_tinymceditor", "sitereview_editorprofile", "sitereview_listtype_lsettings", "is_remove_note", "sitereview_favourite","sitereview_create_redirection");

    $oldLocation = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.map.city', "World");
    $oldFavouriteSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0);     
    $this->view->isModsSupport = Engine_Api::_()->sitereview()->isModulesSupport();

    $pluginName = 'sitereview';
    if (!empty($_POST[$pluginName . '_lsettings']))
      $_POST[$pluginName . '_lsettings'] = @trim($_POST[$pluginName . '_lsettings']);
    
    include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license1.php';
    $newLocation = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.map.city', "World");

    $newFavouriteSetting = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0);    
    
    if ($oldLocation != $newLocation) {
      $this->setDefaultMapCenterPoint($oldLocation, $newLocation);
    }
    
    if($oldFavouriteSetting != $newFavouriteSetting) {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $newFavouriteSetting = !empty($newFavouriteSetting) ? 0 : 1;
        $db->query("UPDATE `engine4_activity_actiontypes` SET `enabled` = $newFavouriteSetting WHERE type LIKE '%sitereview_wishlist_add_listing_listtype_%' AND module = 'sitereview'");
        $db->query("UPDATE `engine4_activity_actiontypes` SET `enabled` = $newFavouriteSetting WHERE type = 'follow_sitereview_wishlist' AND module = 'sitereview'");        
    }
    
    $isActivite = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.isActivate', null);
    if( empty($isActivite) ) {
      $isAdvancedActivity = Engine_Api::_()->sitereview()->isModulesSupport(array('modName' => 'advancedactivity', 'version' => '4.2.9'));
      if( !empty($isAdvancedActivity) ) {
        $this->view->supportingModules = $isAdvancedActivity;
      }
    }
  }

  // Added phrase in language file.
  public function addPhraseAction($phrase) {

    if ($phrase) {
      //file path name
      $targetFile = APPLICATION_PATH . '/application/languages/en/custom.csv';
      if (!file_exists($targetFile)) {
        //Sets access of file
        touch($targetFile);
        //changes permissions of the specified file.
        chmod($targetFile, 0777);
      }
      if (file_exists($targetFile)) {
        $writer = new Engine_Translate_Writer_Csv($targetFile);
        $writer->setTranslations($phrase);
        $writer->write();
        //clean the entire cached data manually
        @Zend_Registry::get('Zend_Cache')->clean();
      }
    }
  }

  //ACTION FOR LEVEL SETTINGS
  public function levelTypeAction() {

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_level');

    $this->view->tab_type = 'levelType';

    $this->view->listingTypeCount = $listingTypeCount = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount();

    //GET LEVEL ID
    if (null != ($id = $this->_getParam('id'))) {
      $level = Engine_Api::_()->getItem('authorization_level', $id);
    } else {
      $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
    }

    if (!$level instanceof Authorization_Model_Level) {
      throw new Engine_Exception('missing level');
    }

    $id = $level->level_id;

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 1);

    //MAKE FORM
    $this->view->form = $form = new Sitereview_Form_Admin_Settings_Leveltype(array(
                'public' => ( in_array($level->type, array('public')) ),
                'moderator' => ( in_array($level->type, array('admin', 'moderator')) ),
            ));
    $form->level_id->setValue($id);

    //POPULATE DATA
    $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
    $prefieldValues = $permissionsTable->getAllowed('sitereview_listing', $id, array_keys($form->getValues()));
    $prefieldValues['max_listtype_'.$listingtype_id] = Engine_Api::_()->authorization()->getPermission($id, 'sitereview_listing', 'max_listtype_'.$listingtype_id);
    $form->populate($prefieldValues);

    if ($listingTypeCount > 1) {
      $form->listingtype_id->setValue($listingtype_id);
    } else {
      $wishlistArray = array();
      $wishlistArray['wishlist'] = $permissionsTable->getAllowed('sitereview_wishlist', $id, 'view');
      $wishlistArray['auth_wishlist'] = $permissionsTable->getAllowed('sitereview_wishlist', $id, 'auth_view');
      $form->populate($wishlistArray);
    }

    //CHECK POST
    if (!$this->getRequest()->isPost()) {
      return;
    }

    //CHECK VALIDITY
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    //PROCESS
    $values = $form->getValues();

    if ($listingTypeCount == 1) {
      $values['view'] = $values['view_listtype_1'];
      $values['comment'] = $values['comment_listtype_1'];

      $wishlistSettings = array();
      $otherSettings = array();
      foreach ($values as $key => $value) {
        if ($key == 'wishlist') {
          $wishlistSettings['view'] = $value;
        } elseif ($key == 'auth_wishlist') {
          $wishlistSettings['auth_view'] = $value;
        } else {
          $otherSettings[$key] = $value;
        }
      }
    }

    $db = $permissionsTable->getAdapter();
    $db->beginTransaction();
    try {

      //SET PERMISSION
      if ($listingTypeCount == 1) {
        include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license2.php';
      } else {

        $permissionsTable->setAllowed('sitereview_listing', $id, $values);

        //IF ALL LISTINGTYPE HAS NO FOR VIEW AND COMMENT THEN WE WILL SET NO ELSE SET YES
        $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypesArray(0, 0);
        $fixed_values = array();
        $levelFlag = true;
        foreach ($listingTypes as $listingtype_id => $plural_title) {
          $fixed_values['view'] = $view = $permissionsTable->getAllowed('sitereview_listing', $id, 'view_listtype_' . $listingtype_id);
          if (!empty($view)) {
            break;
          }
        }

        foreach ($listingTypes as $listingtype_id => $plural_title) {
          $fixed_values['comment'] = $comment = $permissionsTable->getAllowed('sitereview_listing', $id, 'comment_listtype_' . $listingtype_id);
          if (!empty($comment)) {
            break;
          }
        }

        include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license2.php';
      }


      //COMMIT
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }

  //ACTION FOR LEVEL SETTINGS
  public function levelAction() {

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_level');

    $this->view->tab_type = 'level';

    //GET LEVEL ID
    if (null != ($id = $this->_getParam('id'))) {
      $level = Engine_Api::_()->getItem('authorization_level', $id);
    } else {
      $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
    }

    if (!$level instanceof Authorization_Model_Level) {
      throw new Engine_Exception('missing level');
    }

    $id = $level->level_id;

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 1);

    //MAKE FORM
    $this->view->form = $form = new Sitereview_Form_Admin_Settings_Level(array(
                'public' => ( in_array($level->type, array('public')) ),
                'moderator' => ( in_array($level->type, array('admin', 'moderator')) ),
            ));
    $form->level_id->setValue($id);

    //POPULATE DATA
    $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');

    $wishlistArray = array();
    $wishlistArray['wishlist'] = $permissionsTable->getAllowed('sitereview_wishlist', $id, 'view');
    $wishlistArray['auth_wishlist'] = $permissionsTable->getAllowed('sitereview_wishlist', $id, 'auth_view');
    $form->populate($wishlistArray);

    //CHECK POST
    if (!$this->getRequest()->isPost()) {
      return;
    }

    //CHECK VALIDITY
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    //PROCESS
    $values = $form->getValues();

    $wishlistSettings = array();
    $otherSettings = array();

    foreach ($values as $key => $value) {
      if ($key == 'wishlist') {
        $wishlistSettings['view'] = $value;
      } elseif ($key == 'auth_wishlist') {
        $wishlistSettings['auth_view'] = $value;
      } else {
        $otherSettings[$key] = $value;
      }
    }

    $db = $permissionsTable->getAdapter();
    $db->beginTransaction();
    try {
      //SET PERMISSION
      $permissionsTable->setAllowed('sitereview_wishlist', $id, $wishlistSettings);

      //COMMIT
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }

  //ACTION FOR GETTING THE CATGEORIES, SUBCATEGORIES AND 3RD LEVEL CATEGORIES
  public function categoriesAction() {
      
    //REMOVE CACHEING OF LISTINGTYPE AND CATEGORIES HIERARCHY
    $cache = Zend_Registry::get('Zend_Cache');
    $cache->remove('listtype_categories');
    $cache->remove('categories_home_sidebar');

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_categories');

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 1);

    $this->view->success_msg = $this->_getParam('success');
    
    $levels = Engine_Api::_()->getDbtable('levels', 'authorization')->getLevelsAssoc();

    foreach ($levels as $level_id => $level_name) {

      $cacheName = 'listtype_categories_all_' . $level_id;
      $cache->remove($cacheName);
      $cacheName = 'listtype_categories_' . $level_id . '_' . $listingtype_id;
      $cache->remove($cacheName);
    }

    $this->view->listingType = Engine_Api::_()->getItem('sitereview_listingtype', $listingtype_id);
    
    //LIGHTBOX FOR OTHER PLUGINS    
    $this->view->template_type = $this->_getParam('template_type');
    
    $integration_plugin_name = array('advancedactivity', 'communityad', 'facebookse', 'facebooksefeed', 'suggestion', 'sitepage', 'sitefaq', 'sitetagcheckin', 'sitevideoview', 'sitelike', 'advancedslideshow');
    
    $this->view->pluginCounts = 0;
    foreach($integration_plugin_name as $plugin) {
      if(Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled($plugin)) {
        $this->view->pluginCounts++;
      }
    }
    
    //GET TASK
    if (isset($_POST['task'])) {
      $task = $_POST['task'];
    } elseif (isset($_GET['task'])) {
      $task = $_GET['task'];
    } else {
      $task = "main";
    }

    $orientation = $this->view->layout()->orientation;
    if ($orientation == 'right-to-left') {
      $this->view->directionality = 'rtl';
    } else {
      $this->view->directionality = 'ltr';
    }

    $local_language = $this->view->locale()->getLocale()->__toString();
    $local_language = explode('_', $local_language);
    $this->view->language = $local_language[0];

    //GET CATEGORIES TABLE
    $tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');
    $tableCategoryName = $tableCategory->info('name');

    //GET STORAGE API
    $this->view->storage = Engine_Api::_()->storage();

    //GET LISTING TABLE
    $tableSitereview = Engine_Api::_()->getDbtable('listings', 'sitereview');

    if ($task == "changeorder") {
      $divId = $_GET['divId'];
      $sitereviewOrder = explode(",", $_GET['siterevieworder']);
      //RESORT CATEGORIES
      if ($divId == "categories") {
        for ($i = 0; $i < count($sitereviewOrder); $i++) {
          $category_id = substr($sitereviewOrder[$i], 4);
          $tableCategory->update(array('cat_order' => $i + 1), array('category_id = ?' => $category_id));
        }
      } elseif (substr($divId, 0, 7) == "subcats") {
        for ($i = 0; $i < count($sitereviewOrder); $i++) {
          $category_id = substr($sitereviewOrder[$i], 4);
          $tableCategory->update(array('cat_order' => $i + 1), array('category_id = ?' => $category_id));
        }
      } elseif (substr($divId, 0, 11) == "treesubcats") {
        for ($i = 0; $i < count($sitereviewOrder); $i++) {
          $category_id = substr($sitereviewOrder[$i], 4);
          $tableCategory->update(array('cat_order' => $i + 1), array('category_id = ?' => $category_id));
        }
      }
    }

    $categories = array();
    $category_info = $tableCategory->getCategories(null, 0, $listingtype_id, 0, 1, 0, 'cat_order', 0, array('category_id', 'category_name', 'cat_order', 'file_id', 'banner_id', 'sponsored', 'apply_compare'));
    foreach ($category_info as $value) {
      $sub_cat_array = array();
      $subcategories = $tableCategory->getSubCategories($value->category_id, array('category_id', 'category_name', 'cat_order', 'file_id', 'banner_id', 'apply_compare', 'sponsored'));
      foreach ($subcategories as $subresults) {
        $subsubcategories = $tableCategory->getSubCategories($subresults->category_id, array('category_id', 'category_name', 'cat_order', 'file_id', 'banner_id', 'apply_compare', 'sponsored'));
        $treesubarrays[$subresults->category_id] = array();

        foreach ($subsubcategories as $subsubcategoriesvalues) {

          //GET TOTAL LISTING COUNT
          $subsubcategory_sitereview_count = $tableSitereview->getListingsCount($subsubcategoriesvalues->category_id, 'subsubcategory_id', $listingtype_id);

          $treesubarrays[$subresults->category_id][] = $treesubarray = array(
              'tree_sub_cat_id' => $subsubcategoriesvalues->category_id,
              'tree_sub_cat_name' => $subsubcategoriesvalues->category_name,
              'count' => $subsubcategory_sitereview_count,
              'file_id' => $subsubcategoriesvalues->file_id,
              'banner_id' => $subsubcategoriesvalues->banner_id,
              'order' => $subsubcategoriesvalues->cat_order,
              'apply_compare' => $subsubcategoriesvalues->apply_compare,
              'sponsored' => $subsubcategoriesvalues->sponsored);
        }

        //GET TOTAL LISTINGS COUNT
        $subcategory_sitereview_count = $tableSitereview->getListingsCount($subresults->category_id, 'subcategory_id', $listingtype_id);

        $sub_cat_array[] = $tmp_array = array(
            'sub_cat_id' => $subresults->category_id,
            'sub_cat_name' => $subresults->category_name,
            'tree_sub_cat' => $treesubarrays[$subresults->category_id],
            'count' => $subcategory_sitereview_count,
            'file_id' => $subresults->file_id,
            'banner_id' => $subresults->banner_id,
            'order' => $subresults->cat_order,
            'apply_compare' => $subresults->apply_compare,
            'sponsored' => $subresults->sponsored);
      }

      //GET TOTAL LISTINGS COUNT
      $category_sitereview_count = $tableSitereview->getListingsCount($value->category_id, 'category_id', $listingtype_id);

      $categories[] = $category_array = array('category_id' => $value->category_id,
          'category_name' => $value->category_name,
          'order' => $value->cat_order,
          'count' => $category_sitereview_count,
          'file_id' => $value->file_id,
          'banner_id' => $value->banner_id,
          'sponsored' => $value->sponsored,
          'apply_compare' => $value->apply_compare,
          'sub_categories' => $sub_cat_array);
    }

    $this->view->categories = $categories;

    //GET CATEGORIES TABLE
    $tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');
    $tableCategoryName = $tableCategory->info('name');
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $this->view->category_id = $category_id = $request->getParam('category_id', 0);
    $perform = $request->getParam('perform', 'add');
    $cat_dependency = 0;
    $subcat_dependency = 0;
    if ($category_id) {
      $category = Engine_Api::_()->getItem('sitereview_category', $category_id);
      if ($category && empty($category->cat_dependency)) {
        $cat_dependency = $category->category_id;
      } elseif ($category && !empty($category->cat_dependency)) {
        $cat_dependency = $category->category_id;
        $subcat_dependency = $category->category_id;
      }
    }

    if ($perform == 'add') {
      $this->view->form = $form = new Sitereview_Form_Admin_Categories_Add();

      //CHECK POST
      if (!$this->getRequest()->isPost()) {
        return;
      }

      //CHECK VALIDITY
      if (!$form->isValid($this->getRequest()->getPost())) {

        if (empty($_POST['category_name'])) {
          $form->addError($this->view->translate("Category Name * Please complete this field - it is required."));
        }
        return;
      }

      //PROCESS
      $values = $form->getValues();

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {

        $row_info = $tableCategory->fetchRow($tableCategory->select()->from($tableCategoryName, 'max(cat_order) AS cat_order'));
        $cat_order = $row_info['cat_order'] + 1;

        //GET CATEGORY TITLE
        $category_name = str_replace("'", "\'", trim($values['category_name']));
        $values['cat_order'] = $cat_order;
        $values['category_name'] = $category_name;
        $values['listingtype_id'] = $listingtype_id;
        $values['cat_dependency'] = $cat_dependency;
        $values['subcat_dependency'] = $subcat_dependency;

        include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license2.php';

        if(empty($row))
          return;
        
        //UPLOAD ICON
        if (isset($_FILES['icon'])) {
          $photoFileIcon = $row->setPhoto($form->icon);
          //UPDATE FILE ID IN CATEGORY TABLE
          if (!empty($photoFileIcon->file_id)) {
            $row->file_id = $photoFileIcon->file_id;
          }
        }

        //UPLOAD BANNER
        if (isset($_FILES['banner'])) {
          $photoFileBanner = $row->setPhoto($form->banner);
          //UPDATE FILE ID IN CATEGORY TABLE
          if (!empty($photoFileBanner->file_id)) {
            $row->banner_id = $photoFileBanner->file_id;
          }
        }

        $banner_url = preg_match('/\s*[a-zA-Z0-9]{2,5}:\/\//', $values['banner_url']);

        if (empty($banner_url)) {
          if ($values['banner_url']) {
            $row->banner_url = "http://" . $values['banner_url'];
          } else {
            $row->banner_url = $values['banner_url'];
          }
        } else {
          $row->banner_url = $values['banner_url'];
        }

        $category_id = $row->save();
        
        $row->afterCreate();

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      return $this->_helper->redirector->gotoRoute(array('module' => 'sitereview', 'action' => 'categories', 'controller' => 'settings', 'category_id' => $category_id, 'listingtype_id' => $listingtype_id, 'perform' => 'edit'), 'admin_default', true);
    } else {
      $this->view->form = $form = new Sitereview_Form_Admin_Categories_Edit();
      $category = Engine_Api::_()->getItem('sitereview_category', $category_id);
      $form->populate($category->toArray());

      //CHECK POST
      if (!$this->getRequest()->isPost()) {
        return;
      }

      //CHECK VALIDITY
      if (!$form->isValid($this->getRequest()->getPost())) {

        if (empty($_POST['category_name'])) {
          $form->addError($this->view->translate("Category Name * Please complete this field - it is required."));
        }
        return;
      }
      $values = $form->getValues();
      
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {

        //GET CATEGORY TITLE
        $category_name = str_replace("'", "\'", trim($values['category_name']));

        $category->category_name = $category_name;
        $category->listingtype_id = $listingtype_id;
        $category->meta_title = $values['meta_title'];
        $category->meta_description = $values['meta_description'];
        $category->meta_keywords = $values['meta_keywords'];
        $category->sponsored = $values['sponsored'];
        $category->banner_title = $values['banner_title'];
        $category->banner_url_window = $values['banner_url_window'];
        $category->category_slug = $values['category_slug'];
        $category->top_content = $values['top_content'];
        $category->bottom_content = $values['bottom_content'];
        $cat_dependency = $category->cat_dependency;
        $subcat_dependency = $category->subcat_dependency;
        if ($category_id && empty($subcat_dependency) && !empty($cat_dependency)) {
          $cat_dependency = $cat_dependency;
          $subcat_dependency = 0;
        } elseif ($category_id && !empty($subcat_dependency) && !empty($cat_dependency)) {
          $cat_dependency = $cat_dependency;
          $subcat_dependency = $subcat_dependency;
        }

        $category->cat_dependency = $cat_dependency;
        $category->subcat_dependency = $subcat_dependency;

        //UPLOAD ICON
        if (isset($_FILES['icon'])) {
          $previous_file_id = $category->file_id;
          $photoFileIcon = $category->setPhoto($form->icon);
          //UPDATE FILE ID IN CATEGORY TABLE
          if (!empty($photoFileIcon->file_id)) {
              
            //DELETE PREVIOUS CATEGORY ICON
            if ($previous_file_id) {
              $file = Engine_Api::_()->getItem('storage_file', $previous_file_id);
              $file->delete();
            }              
              
            $category->file_id = $photoFileIcon->file_id;
            $category->save();
          }
        }

        //UPLOAD BANNER
        if (isset($_FILES['banner'])) {
          $previous_banner_id = $category->banner_id;
          $photoFileBanner = $category->setPhoto($form->banner);
          //UPDATE FILE ID IN CATEGORY TABLE
          if (!empty($photoFileBanner->file_id)) {
              
            //DELETE PREVIOUS CATEGORY BANNER
            if ($previous_banner_id) {
              $file = Engine_Api::_()->getItem('storage_file', $previous_banner_id);
              $file->delete();
            }                 
              
            $category->banner_id = $photoFileBanner->file_id;
            $category->save();
          }
        }

        $banner_url = preg_match('/\s*[a-zA-Z0-9]{2,5}:\/\//', $values['banner_url']);

        if (empty($banner_url)) {
          if ($values['banner_url']) {
            $category->banner_url = "http://" . $values['banner_url'];
          } else {
            $category->banner_url = $values['banner_url'];
          }
        } else {
          $category->banner_url = $values['banner_url'];
        }

        $category->save();

        if (isset($values['removeicon']) && !empty($values['removeicon'])) {
            
          $previous_icon_id = $category->file_id;  
          
          if($previous_icon_id) {
            //UPDATE FILE ID IN CATEGORY TABLE
            $category->file_id = 0;
            $category->save();            

            //DELETE CATEGORY ICON
            $file = Engine_Api::_()->getItem('storage_file', $previous_icon_id);
            $file->delete();
          }
        }

        if (isset($values['removebanner']) && !empty($values['removebanner'])) {
            
          $previous_banner_id = $category->banner_id;  
            
          if($previous_banner_id) {
            //UPDATE FILE ID IN CATEGORY TABLE
            $category->banner_id = 0;
            $category->save();            

            //DELETE CATEGORY ICON
            $file = Engine_Api::_()->getItem('storage_file', $previous_banner_id);
            $file->delete();
          }
        }

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      return $this->_helper->redirector->gotoRoute(array('module' => 'sitereview', 'action' => 'categories', 'controller' => 'settings', 'category_id' => $category_id, 'listingtype_id' => $listingtype_id, 'perform' => 'edit'), 'admin_default', true);
    }
  }

  //ACTION FOR MAPPING OF LISTINGS
  Public function mappingCategoryAction() {

    //SET LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    //GET CATEGORY ID AND OBJECT
    $this->view->catid = $catid = $this->_getParam('category_id');
    $category = Engine_Api::_()->getItem('sitereview_category', $catid);

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id');

    //GET CATEGORY DEPENDANCY
    $this->view->subcat_dependency = $subcat_dependency = $this->_getParam('subcat_dependency');

    //CREATE FORM
    $this->view->form = $form = new Sitereview_Form_Admin_Settings_Mapping();

    $this->view->close_smoothbox = 0;

    if (!$this->getRequest()->isPost()) {
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    if ($this->getRequest()->isPost()) {

      //GET FORM VALUES
      $values = $form->getValues();

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {

        //GET LISTING TABLE
        $tableSitereview = Engine_Api::_()->getDbtable('listings', 'sitereview');
        $tableSitereviewName = $tableSitereview->info('name');

        //GET REVIEW TABLE
        $reviewTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
        $reviewTableName = $reviewTable->info('name');

        //GET CATEGORY TABLE
        $tableCategory = Engine_Api::_()->getDbtable('categories', 'sitereview');

        //ON CATEGORY DELETE
        $rows = $tableCategory->getSubCategories($catid);
        foreach ($rows as $row) {
          $subrows = $tableCategory->getSubCategories($row->category_id);
          foreach ($subrows as $subrow) {
            $subrow->delete();
          }
          $row->delete();
        }

        $previous_cat_profile_type = $tableCategory->getProfileType(null, $catid);
        $new_cat_profile_type = $tableCategory->getProfileType(null, $values['new_category_id']);

        /// LISTINGS WHICH HAVE THIS CATEGORY
        if ($previous_cat_profile_type != $new_cat_profile_type && !empty($values['new_category_id'])) {
          $listings = $tableSitereview->getCategoryList($catid, 'category_id');

          foreach ($listings as $listing) {

            //DELETE ALL MAPPING VALUES FROM FIELD TABLES
            Engine_Api::_()->fields()->getTable('sitereview_listing', 'values')->delete(array('item_id = ?' => $listing->listing_id));
            Engine_Api::_()->fields()->getTable('sitereview_listing', 'search')->delete(array('item_id = ?' => $listing->listing_id));
            //UPDATE THE PROFILE TYPE OF ALREADY CREATED LISTINGS
            $tableSitereview->update(array('profile_type' => $new_cat_profile_type), array('listing_id = ?' => $listing->listing_id));

            //REVIEW PROFILE TYPE UPDATION WORK
            $reviewIds = $reviewTable->select()
                    ->from($reviewTableName, 'review_id')
                    ->where('resource_id = ?', $listing->listing_id)
                    ->where('resource_type = ?', 'sitereview_listing')
                    ->query()
                    ->fetchAll(Zend_Db::FETCH_COLUMN);
            if (!empty($reviewIds)) {
              foreach ($reviewIds as $reviewId) {
                //DELETE ALL MAPPING VALUES FROM FIELD TABLES
                Engine_Api::_()->fields()->getTable('sitereview_review', 'values')->delete(array('item_id = ?' => $reviewId));
                Engine_Api::_()->fields()->getTable('sitereview_review', 'search')->delete(array('item_id = ?' => $reviewId));

                //UPDATE THE PROFILE TYPE OF ALREADY CREATED REVIEWS
                $reviewTable->update(array('profile_type_review' => $new_cat_profile_type), array('resource_id = ?' => $reviewId));
              }
            }
          }
        }

        //LISTING TABLE CATEGORY DELETE WORK
        if (isset($values['new_category_id']) && !empty($values['new_category_id'])) {
          $tableSitereview->update(array('category_id' => $values['new_category_id']), array('category_id = ?' => $catid));
        } else {
          
          $selectListings = $tableSitereview->select()
                      ->from($tableSitereview->info('name'))
                      ->where('category_id = ?', $catid);
          
          foreach($tableSitereview->fetchAll($selectListings) as $listing) {
            $listing->delete();
          }
        }

        $category->delete();

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }

    $this->view->close_smoothbox = 1;
  }

  //ACTION FOR GETTING THE MEMBER WHICH CAN BE CLAIMED THE PAGE
  function getListingsAction() {

    $page_id = $this->_getParam('page_id', 0);

    $pageTable = Engine_Api::_()->getDbTable('pages', 'core');
    $page_name = $pageTable->select()
            ->from($pageTable->info('name'), 'name')
            ->where('page_id = ?', $page_id)
            ->query()
            ->fetchColumn();

    $page_name_array = explode('_listtype_', $page_name);
    $listingtype_id = $page_name_array[1];

    //GET LISTING TABLE
    $sitereviewTable = Engine_Api::_()->getDbtable('listings', 'sitereview');
    $sitereviewTableName = $sitereviewTable->info('name');

    //MAKE QUERY
    $select = $sitereviewTable->select()
            ->where('title  LIKE ? ', '%' . $this->_getParam('text') . '%')
            ->where($sitereviewTableName . '.closed = ?', '0')
            ->where($sitereviewTableName . '.approved = ?', '1')
            ->where($sitereviewTableName . '.draft = ?', '0')
            ->where($sitereviewTableName . '.search = ?', '1')
            ->order('title ASC')
            ->limit($this->_getParam('limit', 40));

    if (!empty($listingtype_id) && is_numeric($listingtype_id)) {
      $select->where($sitereviewTableName . '.listingtype_id = ?', $listingtype_id);
    }

    //FETCH RESULTS
    $usersitereviews = $sitereviewTable->fetchAll($select);
    $data = array();
    $mode = $this->_getParam('struct');

    if ($mode == 'text') {
      foreach ($usersitereviews as $usersitereview) {
        $content_photo = $this->view->itemPhoto($usersitereview, 'thumb.icon');
        $data[] = array(
            'id' => $usersitereview->listing_id,
            'label' => $usersitereview->title,
            'photo' => $content_photo
        );
      }
    } else {
      foreach ($usersitereviews as $usersitereview) {
        $content_photo = $this->view->itemPhoto($usersitereview, 'thumb.icon');
        $data[] = array(
            'id' => $usersitereview->listing_id,
            'label' => $usersitereview->title,
            'photo' => $content_photo
        );
      }
    }
    return $this->_helper->json($data);
  }
  
  //ACTION FOR GETTING THE MEMBER WHICH CAN BE CLAIMED THE PAGE
  function getReviewsAction() {

    //GET LISTING TABLE
    $reviewTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
    $reviewTableName = $reviewTable->info('name');

    //MAKE QUERY
    $select = $reviewTable->select()
            ->where('title  LIKE ? ', '%' . $this->_getParam('text') . '%')
            ->where($reviewTableName . '.type != ?', 'visitor')
            ->where($reviewTableName . '.status = ?', '1')
            ->order('title ASC')
            ->limit($this->_getParam('limit', 40));

    //FETCH RESULTS
    $reviews = $reviewTable->fetchAll($select);
    $data = array();
    $mode = $this->_getParam('struct');

    if ($mode == 'text') {
      foreach ($reviews as $review) {
        $content_photo = $this->view->itemPhoto($review->getOwner(), 'thumb.icon');
        $data[] = array(
            'id' => $review->review_id,
            'label' => $review->title,
            'photo' => $content_photo
        );
      }
    } else {
      foreach ($reviews as $review) {
        $content_photo = $this->view->itemPhoto($review->getOwner(), 'thumb.icon');
        $data[] = array(
            'id' => $review->review_id,
            'label' => $review->title,
            'photo' => $content_photo
        );
      }
    }
    return $this->_helper->json($data);
  }  

  //ACTINO FOR SEARCH FORM TAB
  public function formSearchAction() {

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_formsearch');

    $listingtype_id = $this->view->listingtype_id = $this->_getParam('listingtype_id', 1);

    //GET SEARCH TABLE
    $tableSearchForm = Engine_Api::_()->getDbTable('searchformsetting', 'seaocore');

    //CHECK POST
    if ($this->getRequest()->isPost()) {

      //BEGIN TRANSCATION
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();

      $values = $_POST;
      $rowCategory = $tableSearchForm->getFieldsOptions('sitereview_listtype_' . $listingtype_id, 'category_id');
      $rowLocation = $tableSearchForm->getFieldsOptions('sitereview_listtype_' . $listingtype_id, 'location');
      $defaultCategory = 0;
      $defaultAddition = 0;
      $count = 1;
      try {
        foreach ($values['order'] as $key => $value) {
          $multiplyAddition = $count * 5;
          $tableSearchForm->update(array('order' => $defaultAddition + $defaultCategory + $key + $multiplyAddition + 1), array('searchformsetting_id = ?' => (int) $value));

          if (!empty($rowCategory) && $value == $rowCategory->searchformsetting_id) {
            $defaultCategory = 1;
            $defaultAddition = 10000000;
          }
          $count++;
        }
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }

    //MAKE QUERY
    $select = $tableSearchForm->select()->where('module = ?', 'sitereview_listtype_' . $listingtype_id)->order('order');

    include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license2.php';
  }

  //ACTION FOR DISPLAY/HIDE FIELDS OF SEARCH FORM
  public function diplayFormAction() {

    $field_id = $this->_getParam('id');
    $name = $this->_getParam('name');
    $display = $this->_getParam('display');
    $listingtype_id = $this->_getParam('listingtype_id');
    if (!empty($field_id)) {

      if ($name == 'location' && $display == 0) {
        Engine_Api::_()->getDbTable('searchformsetting', 'seaocore')->update(array('display' => $display), array('module = ?' => 'sitereview_listtype_' . $listingtype_id, 'name = ?' => 'proximity'));
      }

      Engine_Api::_()->getDbTable('searchformsetting', 'seaocore')->update(array('display' => $display), array('module = ?' => 'sitereview_listtype_' . $listingtype_id, 'searchformsetting_id = ?' => (int) $field_id));
    }
    $this->_redirect('admin/sitereview/settings/form-search/listingtype_id/' . $listingtype_id);
  }

  //ACTION FOR SHOW STATISTICS OF LISTING PLUGIN
  public function statisticAction() {

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_statistic');

    //GET LISTING TYPE
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 0);

    if ($listingtype_id) {
      Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
      $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
      $this->view->listing_plural_uc = ucfirst($listingtypeArray->title_plural);
    } else {
      $this->view->listing_plural_uc = 'Listings';
    }

    //GET LISTING TABLE
    $listingTable = Engine_Api::_()->getDbtable('listings', 'sitereview');
    $listingTableName = $listingTable->info('name');

    //GET LISTING DETAILS
    $select = $listingTable->select()->from($listingTableName, 'count(*) AS totallisting');
    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }
    $this->view->totalSitereview = $select->query()->fetchColumn();

    $this->view->totalEditors = Engine_Api::_()->getDbTable('editors', 'sitereview')->getEditorsCount($listingtype_id);

    $select = $listingTable->select()->from($listingTableName, 'count(*) AS totalpublish')->where('draft = ?', 0);
    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }
    $this->view->totalPublish = $select->query()->fetchColumn();

    $select = $listingTable->select()->from($listingTableName, 'count(*) AS totaldrafted')->where('draft = ?', 1);
    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }
    $this->view->totalDrafted = $select->query()->fetchColumn();

    $select = $listingTable->select()->from($listingTableName, 'count(*) AS totalclosed')->where('closed = ?', 1);
    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }
    $this->view->totalClosed = $select->query()->fetchColumn();

    $select = $listingTable->select()->from($listingTableName, 'count(*) AS totalopen')->where('closed = ?', 0);
    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }
    $this->view->totalOpen = $select->query()->fetchColumn();

    $select = $listingTable->select()->from($listingTableName, 'count(*) AS totalapproved')->where('approved = ?', 1);
    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }
    $this->view->totalapproved = $select->query()->fetchColumn();

    $select = $listingTable->select()->from($listingTableName, 'count(*) AS totaldisapproved')->where('approved = ?', 0);
    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }
    $this->view->totaldisapproved = $select->query()->fetchColumn();

    $select = $listingTable->select()->from($listingTableName, 'count(*) AS totalfeatured')->where('featured = ?', 1);
    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }
    $this->view->totalfeatured = $select->query()->fetchColumn();

    $select = $listingTable->select()->from($listingTableName, 'count(*) AS totalsponsored')->where('sponsored = ?', 1);
    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }
    $this->view->totalsponsored = $select->query()->fetchColumn();

    $select = $listingTable->select()->from($listingTableName, 'sum(comment_count) AS totalcomments');
    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }
    $this->view->totalListingComments = $select->query()->fetchColumn();
    if (empty($this->view->totalListingComments))
      $this->view->totalListingComments = 0;

    $select = $listingTable->select()->from($listingTableName, 'sum(like_count) AS totalLikes');
    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }
    $this->view->totalListingLikes = $select->query()->fetchColumn();
    if (empty($this->view->totalListingLikes))
      $this->view->totalListingLikes = 0;

    //GET REVIEW TABLE
    $reviewTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
    $reviewTableName = $reviewTable->info('name');

    //GET REVIEW DETAILS
    $select = $reviewTable->select()->setIntegrityCheck(false)
            ->from($reviewTableName, 'count(*) AS totalreview')
            ->where($reviewTableName . '.resource_type = ?', 'sitereview_listing')
            ->where($reviewTableName . '.type = ?', 'editor');

    if (!empty($listingtype_id)) {
      $select->joinLeft("$listingTableName", "$listingTableName.listing_id = $reviewTableName.resource_id", null);
      $select->where($listingTableName . '.listingtype_id = ?', $listingtype_id);
    }

    $this->view->totalEditorReviews = $select->query()->fetchColumn();

    $select = $reviewTable->select()->setIntegrityCheck(false)
            ->from($reviewTableName, 'count(*) AS totalreview')
            ->where($reviewTableName . '.status = ?', 0)
            ->where($reviewTableName . '.resource_type = ?', 'sitereview_listing')
            ->where($reviewTableName . '.type = ?', 'editor');

    if (!empty($listingtype_id)) {
      $select->joinLeft("$listingTableName", "$listingTableName.listing_id = $reviewTableName.resource_id", null);
      $select->where($listingTableName . '.listingtype_id = ?', $listingtype_id);
    }

    $this->view->totalDraftEditorReviews = $select->query()->fetchColumn();

    $select = $reviewTable->select()->setIntegrityCheck(false)
            ->from($reviewTableName, 'count(*) AS totalreview')
            ->where($reviewTableName . '.resource_type = ?', 'sitereview_listing')
            ->where($reviewTableName . '.type = ?', 'user')
            ->where($reviewTableName . '.owner_id != ?', 0);

    if (!empty($listingtype_id)) {
      $select->joinLeft("$listingTableName", "$listingTableName.listing_id = $reviewTableName.resource_id", null);
      $select->where($listingTableName . '.listingtype_id = ?', $listingtype_id);
    }

    $this->view->totalUserReviews = $select->query()->fetchColumn();

    $select = $reviewTable->select()->setIntegrityCheck(false)
            ->from($reviewTableName, 'count(*) AS totalreview')
            ->where($reviewTableName . '.resource_type = ?', 'sitereview_listing')
            ->where($reviewTableName . '.type = ?', 'user')
            ->where($reviewTableName . '.owner_id = ?', 0)
            ->where($reviewTableName . '.status = ?', 1);

    if (!empty($listingtype_id)) {
      $select->joinLeft("$listingTableName", "$listingTableName.listing_id = $reviewTableName.resource_id", null);
      $select->where($listingTableName . '.listingtype_id = ?', $listingtype_id);
    }

    $this->view->totalApprovedVisitorsReviews = $select->query()->fetchColumn();

    $select = $reviewTable->select()->setIntegrityCheck(false)
            ->from($reviewTableName, 'count(*) AS totalreview')
            ->where($reviewTableName . '.resource_type = ?', 'sitereview_listing')
            ->where($reviewTableName . '.type = ?', 'user')
            ->where($reviewTableName . '.owner_id = ?', 0)
            ->where($reviewTableName . '.status = ?', 0);

    if (!empty($listingtype_id)) {
      $select->joinLeft("$listingTableName", "$listingTableName.listing_id = $reviewTableName.resource_id", null);
      $select->where($listingTableName . '.listingtype_id = ?', $listingtype_id);
    }

    $this->view->totalDisApprovedVisitorsReviews = $select->query()->fetchColumn();

    $select = $reviewTable->select()->setIntegrityCheck(false)
            ->from($reviewTableName, 'count(*) AS totalreview');

    if (!empty($listingtype_id)) {
      $select->joinLeft("$listingTableName", "$listingTableName.listing_id = $reviewTableName.resource_id", null);
      $select->where($listingTableName . '.listingtype_id = ?', $listingtype_id);
    }

    $this->view->totalReviews = $select->query()->fetchColumn();

    //GET THE TOTAL DISCUSSIONES
    $discussionTable = Engine_Api::_()->getDbtable('topics', 'sitereview');
    $discussionTableName = $discussionTable->info('name');
    $select = $discussionTable->select()->setIntegrityCheck(false)
            ->from($discussionTableName, 'count(*) AS totaldiscussion');

    if (!empty($listingtype_id)) {
      $select->joinLeft("$listingTableName", "$listingTableName.listing_id = $discussionTableName.listing_id", null);
      $select->where($listingTableName . '.listingtype_id = ?', $listingtype_id);
    }

    $this->view->totalDiscussionTopics = $select->query()->fetchColumn();

    //GET THE TOTAL POSTS
    $discussionPostTable = Engine_Api::_()->getDbtable('posts', 'sitereview');
    $discussionPostTableName = $discussionPostTable->info('name');
    $select = $discussionPostTable->select()->setIntegrityCheck(false)
            ->from($discussionPostTableName, 'count(*) AS totalpost');

    if (!empty($listingtype_id)) {
      $select->joinLeft("$listingTableName", "$listingTableName.listing_id = $discussionPostTableName.listing_id", null);
      $select->where($listingTableName . '.listingtype_id = ?', $listingtype_id);
    }

    $this->view->totalDiscussionPosts = $select->query()->fetchColumn();

    //GET THE TOTAL PHOTOS
    $photoTable = Engine_Api::_()->getDbtable('photos', 'sitereview');
    $photoTableName = $photoTable->info('name');
    $select = $photoTable->select()->setIntegrityCheck(false)
            ->from($photoTableName, 'count(*) AS totalphoto');

    if (!empty($listingtype_id)) {
      $select->joinLeft("$listingTableName", "$listingTableName.listing_id = $photoTableName.listing_id", null);
      $select->where($listingTableName . '.listingtype_id = ?', $listingtype_id);
    }

    $this->view->totalPhotos = $select->query()->fetchColumn();

    //GET THE TOTAL VIDEOS
    $type_video = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video');
    if (empty($type_video)) {
      $videoTable = Engine_Api::_()->getDbtable('videos', 'sitereview');
    } else {
      $videoTable = Engine_Api::_()->getDbtable('clasfvideos', 'sitereview');
    }
    $videoTableName = $videoTable->info('name');
    $select = $videoTable->select()->setIntegrityCheck(false)
            ->from($videoTableName, 'count(*) AS totalvideo');

    if (!empty($listingtype_id)) {
      $select->joinLeft("$listingTableName", "$listingTableName.listing_id = $videoTableName.listing_id", null);
      $select->where($listingTableName . '.listingtype_id = ?', $listingtype_id);
    }

    $this->view->totalVideos = $select->query()->fetchColumn();

    //GET WISHLITS FOR LISTING TYPE
    $this->view->totalWishlists = Engine_Api::_()->getDbTable('wishlists', 'sitereview')->getWishlistCount($listingtype_id);
  }

  //ACTION FOR SET THE DEFAULT MAP CENTER POINT
  public function setDefaultMapCenterPoint($oldLocation, $newLocation) {

    if ($oldLocation !== $newLocation && $newLocation !== "World" && $newLocation !== "world") {
        $locationResults = Engine_Api::_()->getApi('geoLocation', 'seaocore')->getLatLong(array('location' => $newLocation, 'module' => 'Listing / Catalog Showcase'));
        if(!empty($locationResults['latitude']) && !empty($locationResults['longitude'])) {
            $latitude = $locationResults['latitude'];
            $longitude = $locationResults['longitude'];
        }

      Engine_Api::_()->getApi('settings', 'core')->setSetting('sitereview.map.latitude', $latitude);
      Engine_Api::_()->getApi('settings', 'core')->setSetting('sitereview.map.longitude', $longitude);
    }
  }

  public function compareAction() {

    //GET NAGIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_compare');
    $compare_categories_id = 0;
    $this->view->form = $form = new Sitereview_Form_Admin_Compare_Settings();
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 1);
    $category_id = $this->_getParam('category_id', null);
    $subcategory_id = $this->_getParam('subcategory_id', null);
    $subsubcategory_id = $this->_getParam('subsubcategory_id', null);

    $paramsArray = array(
        'listtype_id' => $listingtype_id,
        'category_id' => $category_id,
        'subcategory_id' => $subcategory_id,
        'subsubcategory_id' => $subsubcategory_id
    );
    $category_ids = array();
    $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypesArray();
    $this->view->listingTypesCount = $listingTypesCount = count($listingTypes);
    $this->view->listingTypesName = $listingTypes[$listingtype_id];
    if (empty($this->view->listingTypesName)) {
      return $this->_forward('notfound', 'error', 'core');
    }
    if ($listingTypesCount > 1) {
      $form->getElement('listtype_id')
              ->setMultiOptions($listingTypes);
    } else {
      $form->getElement('listtype_id')
              ->setMultiOptions($listingTypes);
      // $form->removeElement('listtype_id');
//       $form->addElement('Hidden', 'listtype_id', array(
//           'value' => 1,
//       ));
      $listingtype_id = $paramsArray['listtype_id'] = 1;
    }
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingType = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    if (empty($listingType->compare))
      return;

    if (isset($form->tags))
      $form->tags->setLabel(strtoupper($listingType->title_singular) . '_TAGS');

    $categories = Engine_Api::_()->getDbtable('categories', 'sitereview')->getCategoriesList($listingtype_id, 0, array('category_id', 'category_name'));
    $categoriesFormMultiOptions = array();
    $checkCategoriesFlage = true;
    foreach ($categories as $category) {
      if ($checkCategoriesFlage && !empty($category_id) && $category_id == $category->category_id) {
        $checkCategoriesFlage = false;
        $compare_categories_id = $category_id;
        $category_ids[0] = $category->category_id;
      }
      $categoriesFormMultiOptions[$category->category_id] = $category->category_name;
    }
    $this->view->countCategories = $countCategories = count($categoriesFormMultiOptions);
    if (empty($countCategories))
      return;
    $form->getElement('category_id')
            ->setMultiOptions($categoriesFormMultiOptions);

    if (empty($category_id) || $checkCategoriesFlage) {
      $category_ids[0] = $compare_categories_id = $paramsArray['category_id'] = $category_id = key($categoriesFormMultiOptions);
    }

    $sub_category_id = $paramsArray['category_id'];
    $firstlevelcategory = Engine_Api::_()->getItem('sitereview_category', $paramsArray['category_id']);
    if (empty($firstlevelcategory->apply_compare)) {
      $subcategories = Engine_Api::_()->getDbtable('categories', 'sitereview')->getCategoriesList($listingtype_id, $paramsArray['category_id'], array('category_id', 'category_name'));
    } else {
      $subcategories = array();
    }

    $subcategoriesFormMultiOptions = array();
    $checkCategoriesFlage = true;
    foreach ($subcategories as $category) {
      if ($checkCategoriesFlage && !empty($subcategory_id) && $subcategory_id == $category->category_id) {
        $checkCategoriesFlage = false;
        $compare_categories_id = $category->category_id;
        $category_ids[1] = $category->category_id;
      }
      $subcategoriesFormMultiOptions[$category->category_id] = $category->category_name;
    }
    $countSubcategories = count($subcategoriesFormMultiOptions);

    if ($countSubcategories) {
      $form->getElement('subcategory_id')
              ->setMultiOptions($subcategoriesFormMultiOptions);
      if (empty($subcategory_id) || $checkCategoriesFlage) {
        $category_ids[1] = $compare_categories_id = $paramsArray['subcategory_id'] = $subcategory_id = key($subcategoriesFormMultiOptions);
      }
      $secondlevelcategory = Engine_Api::_()->getItem('sitereview_category', $subcategory_id);
      if (empty($secondlevelcategory->apply_compare)) {
        $subsubcategories = Engine_Api::_()->getDbtable('categories', 'sitereview')->getCategoriesList($listingtype_id, $subcategory_id, array('category_id', 'category_name'));
      } else {
        $subsubcategories = array();
      }
      $subsubcategoriesFormMultiOptions = array();
      $checkCategoriesFlage = true;
      foreach ($subsubcategories as $category) {
        if ($checkCategoriesFlage && !empty($subsubcategory_id) && $subsubcategory_id == $category->category_id) {
          $checkCategoriesFlage = false;
          $compare_categories_id = $category->category_id;
          $category_ids[2] = $category->category_id;
        }
        $subsubcategoriesFormMultiOptions[$category->category_id] = $category->category_name;
      }
      $countSubcategories = count($subsubcategoriesFormMultiOptions);

      if ($countSubcategories) {
        $form->getElement('subsubcategory_id')
                ->setMultiOptions($subsubcategoriesFormMultiOptions);

        if (empty($subsubcategory_id) || $checkCategoriesFlage) {
          $category_ids[2] = $compare_categories_id = $paramsArray['subsubcategory_id'] = $subcategory_id = key($subsubcategoriesFormMultiOptions);
        }
      } else {
        $form->removeElement('subsubcategory_id');
      }
    } else {
      $form->removeElement('subcategory_id');
      $form->removeElement('subsubcategory_id');
    }

    $compareSettingsTable = Engine_Api::_()->getDbtable('compareSettings', 'sitereview');
    $this->view->compareSettingList = $result = $compareSettingsTable->getCompareList(array(
        'listingtype_id' => $listingtype_id,
        'category_id' => $compare_categories_id,
        'fetchRow' => 1
            ));

    if (empty($result)) {
      return $this->_forward('notfound', 'error', 'core');
    }

    $resultArray = $result->toArray();
    unset($resultArray['category_id']);
    $resultArray['custom_fields'] = !empty($result->custom_fields) ? Zend_Json_Decoder::decode($result->custom_fields) : array();
    $resultArray['editor_rating_fields'] = !empty($result->editor_rating_fields) ? Zend_Json_Decoder::decode($result->editor_rating_fields) : array();
    $resultArray['user_rating_fields'] = !empty($result->user_rating_fields) ? Zend_Json_Decoder::decode($result->user_rating_fields) : array();
    $resultArray = array_merge($resultArray, $paramsArray);

    $ratingsParamsArray = array();
    if ($listingType->reviews) {
      $ratingsParams = Engine_Api::_()->getDbtable('ratingparams', 'sitereview')->reviewParams($category_ids, 'sitereview_listing');
      foreach ($ratingsParams as $value) {
        $ratingsParamsArray[$value->ratingparam_id] = $value->ratingparam_name;
      }
    }
    if (count($ratingsParamsArray) > 0 && ($listingType->reviews == 1 || $listingType->reviews == 3)) {
      $form->getElement('editor_rating_fields')
              ->setMultiOptions($ratingsParamsArray);
    } else {
      $form->removeElement('editor_rating_fields');
    }

    if (count($ratingsParamsArray) > 0 && ($listingType->reviews == 2 || $listingType->reviews == 3)) {
      $form->getElement('user_rating_fields')
              ->setMultiOptions($ratingsParamsArray);
    } else {
      $form->removeElement('user_rating_fields');
    }

    $proifle_map_ids = Engine_Api::_()->getDBTable('categories', 'sitereview')->getAllProfileTypes($category_ids, 1);
    $multiOptionsCustomFields = array();
    foreach ($proifle_map_ids as $proifle_map_id) {
      $selectOption = Engine_Api::_()->getDbTable('metas', 'sitereview')->getProfileFields($proifle_map_id);
      if ($selectOption) {
        foreach ($selectOption as $key => $value) {
          $multiOptionsCustomFields[$key] = $value['lable'] . " (" . ucfirst($value['type']) . ")";
        }
      }
    }
    if (count($multiOptionsCustomFields) > 0) {
      $form->getElement('field_dummy_3')
              ->setLabel(Engine_Api::_()->getDBTable('options', 'sitereview')->getFieldLabel($proifle_map_id) . ' Listing Profile Questions')
              ->setDescription('Choose the options from below that you want to display under the "Specifications" section on listings comparison page.)');

      $form->getElement('custom_fields')
              ->setMultiOptions($multiOptionsCustomFields);
    } else {
      $form->removeElement('field_dummy_3');
      $form->removeElement('custom_fields');
    }

    if (!$this->getRequest()->isPost()) {
      $form->populate($resultArray);
    }
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
      unset($values['category_id']);
      unset($values['listtype_id']);
      if (isset($values['subcategory_id']))
        unset($values['subcategory_id']);
      if (isset($values['subsubcategory_id']))
        unset($values['subsubcategory_id']);
      unset($values['field_dummy_1']);
      unset($values['field_dummy_2']);
      unset($values['field_dummy_3']);
      unset($values['save']);
      if (!isset($values['custom_fields']))
        $values['custom_fields'] = array();
      $values['custom_fields'] = Zend_Json::encode($values['custom_fields']);
      if (isset($values['editor_rating_fields']))
        $values['editor_rating_fields'] = Zend_Json::encode($values['editor_rating_fields']);
      else
        $values['editor_rating_fields'] = null;
      if (isset($values['user_rating_fields']))
        $values['user_rating_fields'] = Zend_Json::encode($values['user_rating_fields']);
      else
        $values['user_rating_fields'] = null;
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license2.php';
        $this->view->form = $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved successfully.'));
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
  }

  //ACTION FOR SHOWING THE FAQ
  public function faqAction() {

    //GET NAGIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_faq');

    $this->view->faq = 1;
    $this->view->faq_type = $this->_getParam('faq_type', 'general');
  }

  //ACTION FOR SHOWING THE Video
  public function showVideoAction() {

    //GET NAGIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_video');

    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sitereview_admin_submain', array(), 'sitereview_admin_submain_general_tab');

    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    //MAKE FORM
    $this->view->form = $form = new Sitereview_Form_Admin_Video_General();
    $type_video_value = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video', 1);

    if ($this->getRequest()->isPost()) {
      
      $currentYouTubeApiKey = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey');
      if ( !empty($_POST['video_youtube_apikey']) && $_POST['video_youtube_apikey'] != $currentYouTubeApiKey ) {
        $response = Engine_Api::_()->seaocore()->verifyYotubeApiKey($_POST['video_youtube_apikey']);
        if ( !empty($response['errors']) ) {
          $error_message = array('Invalid API Key');
          foreach ( $response['errors'] as $error ) {
            $error_message[] = "Error Reason (" . $error['reason'] . '): ' . $error['message'];
          }

          return $form->video_youtube_apikey->addErrors($error_message);
        }
      }
      
      $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getAllListingTypes();
      $values = $_POST;

      $reviewVideoTable = Engine_Api::_()->getDbtable('videos', 'sitereview');
      $reviewVideoTableName = $reviewVideoTable->info('name');

      $reviewVideoRatingTable = Engine_Api::_()->getDbTable('videoratings', 'sitereview');
      $reviewVideoRatingName = $reviewVideoRatingTable->info('name');

      $sitereviewVideoTable = Engine_Api::_()->getDbtable('clasfvideos', 'sitereview');
      $sitereviewVideoTableName = $sitereviewVideoTable->info('name');
      if (isset($values['sitereview_show_video']) && ($type_video_value != $values['sitereview_show_video'])) {
      
      $coreVideoTable = Engine_Api::_()->getDbtable('videos', 'video');
      $coreVideoTableName = $coreVideoTable->info('name');

      $videoRating = Engine_Api::_()->getDbTable('ratings', 'video');
      $videoRatingName = $videoRating->info('name');
      
        if (!empty($values['sitereview_show_video'])) {

          $selectListingVideos = $reviewVideoTable->select()
                  ->from($reviewVideoTableName, array('video_id', 'listing_id'))
                  ->where('is_import != ?', 1)
                  ->group('video_id');
          $listingVideoDatas = $reviewVideoTable->fetchAll($selectListingVideos);
          foreach ($listingVideoDatas as $listingVideoData) {
            $listVideo = Engine_Api::_()->getItem('sitereview_video', $listingVideoData->video_id);
            if (!empty($listVideo)) {

              $db = $sitereviewVideoTable->getAdapter();
              $db->beginTransaction();

              try {
                $clasfVideo = $sitereviewVideoTable->createRow();
                $clasfVideo->listing_id = $listingVideoData->listing_id;
                $clasfVideo->video_id = $listingVideoData->video_id;
                $clasfVideo->is_import = 1;
                $clasfVideo->created = $listVideo->creation_date;

                $clasfVideo->save();
                $db->commit();
              } catch (Exception $e) {
                $db->rollBack();
                throw $e;
              }

              $db = $coreVideoTable->getAdapter();
              $db->beginTransaction();

              try {
                $coreVideo = $coreVideoTable->createRow();
                $coreVideo->title = $listVideo->title;
                $coreVideo->description = $listVideo->description;
                $coreVideo->search = $listVideo->search;
                $coreVideo->owner_id = $listVideo->owner_id;
                $coreVideo->creation_date = $listVideo->creation_date;
                $coreVideo->modified_date = $listVideo->modified_date;

                $coreVideo->view_count = 1;
                if ($listVideo->view_count > 0) {
                  $coreVideo->view_count = $listVideo->view_count;
                }

                $coreVideo->comment_count = $listVideo->comment_count;
                $coreVideo->type = $listVideo->type;
                $coreVideo->code = $listVideo->code;
                $coreVideo->rating = $listVideo->rating;
                $coreVideo->status = $listVideo->status;
                $coreVideo->file_id = 0;
                $coreVideo->duration = $listVideo->duration;
                $coreVideo->save();
                $db->commit();
              } catch (Exception $e) {
                $db->rollBack();
                throw $e;
              }

              //START VIDEO THUMB WORK
              if (!empty($coreVideo->code) && !empty($coreVideo->type) && !empty($listVideo->photo_id)) {
                $storageTable = Engine_Api::_()->getDbtable('files', 'storage');
                $storageData = $storageTable->fetchRow(array('file_id = ?' => $listVideo->photo_id));
                if (!empty($storageData)) {
                  $thumbnail = $storageData->storage_path;

                  $ext = ltrim(strrchr($thumbnail, '.'), '.');
                  $thumbnail_parsed = @parse_url($thumbnail);

                  if (@GetImageSize($thumbnail)) {
                    $valid_thumb = true;
                  } else {
                    $valid_thumb = false;
                  }

                  if ($valid_thumb && $thumbnail && $ext && $thumbnail_parsed && in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {
                    $tmp_file = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
                    $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;
                    $src_fh = fopen($thumbnail, 'r');
                    $tmp_fh = fopen($tmp_file, 'w');
                    stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
                    $image = Engine_Image::factory();
                    $image->open($tmp_file)
                            ->resize(120, 240)
                            ->write($thumb_file)
                            ->destroy();

                    try {
                      $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
                          'parent_type' => 'video',
                          'parent_id' => $coreVideo->video_id
                              ));

                      //REMOVE TEMP FILE
                      @unlink($thumb_file);
                      @unlink($tmp_file);
                    } catch (Exception $e) {
                      
                    }

                    $coreVideo->photo_id = $thumbFileRow->file_id;
                    $coreVideo->save();
                  }
                }
              }
              //END VIDEO THUMB WORK
              //START FETCH TAG
              $videoTags = $listVideo->tags()->getTagMaps();
              $tagString = '';

              foreach ($videoTags as $tagmap) {

                if ($tagString != '')
                  $tagString .= ', ';
                $tagString .= $tagmap->getTag()->getTitle();

                $owner = Engine_Api::_()->getItem('user', $listVideo->owner_id);
                $tags = preg_split('/[,]+/', $tagString);
                $tags = array_filter(array_map("trim", $tags));
                $coreVideo->tags()->setTagMaps($owner, $tags);
              }
              //END FETCH TAG

              $likeTable = Engine_Api::_()->getDbtable('likes', 'core');
              $likeTableName = $likeTable->info('name');

              //START FETCH LIKES
              $selectLike = $likeTable->select()
                      ->from($likeTableName, 'like_id')
                      ->where('resource_type = ?', 'sitereview_video')
                      ->where('resource_id = ?', $listingVideoData->video_id);
              $selectLikeDatas = $likeTable->fetchAll($selectLike);
              foreach ($selectLikeDatas as $selectLikeData) {
                $like = Engine_Api::_()->getItem('core_like', $selectLikeData->like_id);

                $newLikeEntry = $likeTable->createRow();
                $newLikeEntry->resource_type = 'video';
                $newLikeEntry->resource_id = $like->resource_id;
                $newLikeEntry->poster_type = 'user';
                $newLikeEntry->poster_id = $like->poster_id;
                $newLikeEntry->creation_date = $like->creation_date;
                $newLikeEntry->save();
              }
              //END FETCH LIKES

              $commentTable = Engine_Api::_()->getDbtable('comments', 'core');
              $commentTableName = $commentTable->info('name');

              //START FETCH COMMENTS
              $selectLike = $commentTable->select()
                      ->from($commentTableName, 'comment_id')
                      ->where('resource_type = ?', 'sitereview_video')
                      ->where('resource_id = ?', $listingVideoData->video_id);
              $selectLikeDatas = $commentTable->fetchAll($selectLike);
              foreach ($selectLikeDatas as $selectLikeData) {
                $comment = Engine_Api::_()->getItem('core_comment', $selectLikeData->comment_id);

                $newLikeEntry = $commentTable->createRow();
                $newLikeEntry->resource_type = 'video';
                $newLikeEntry->resource_id = $comment->resource_id;
                $newLikeEntry->poster_type = 'user';
                $newLikeEntry->poster_id = $comment->poster_id;
                $newLikeEntry->body = $comment->body;
                $newLikeEntry->creation_date = $comment->creation_date;
                $newLikeEntry->like_count = $comment->like_count;
                $newLikeEntry->save();
              }
              //END FETCH COMMENTS
              //START UPDATE TOTAL LIKES IN LISTING-VIDEO TABLE
              $selectLikeCount = $likeTable->select()
                      ->from($likeTableName, array('COUNT(*) AS like_count'))
                      ->where('resource_type = ?', 'sitereview_video')
                      ->where('resource_id = ?', $coreVideo->video_id);
              $selectLikeCounts = $likeTable->fetchAll($selectLikeCount);
// 						if (!empty($selectLikeCounts)) {
//   
//    
// 							$selectLikeCounts = $selectLikeCounts->toArray();
// 							$coreVideo->like_count = $selectLikeCounts[0]['like_count'];
// 							$coreVideo->save();
// 						}
              //END UPDATE TOTAL LIKES IN LISTING-VIDEO TABLE
              //START FETCH RATTING DATA
              $selectVideoRating = $videoRating->select()
                      ->from($videoRatingName)
                      ->where('video_id = ?', $listingVideoData->video_id);

              $videoRatingDatas = $videoRating->fetchAll($selectVideoRating);
              if (!empty($videoRatingDatas)) {
                $videoRatingDatas = $videoRatingDatas->toArray();
              }

              foreach ($videoRatingDatas as $videoRatingData) {

                $reviewVideoRatingTable->insert(array(
                    'videorating_id' => $coreVideo->video_id,
                    'user_id' => $videoRatingData['user_id'],
                    'rating' => $videoRatingData['rating']
                ));
              }
              //END FETCH RATTING DATA
              $reviewVideoTable->update(array('is_import' => 1), array('video_id = ?' => $listingVideoData->video_id));
            }
          }
          //END FETCH VIDEO DATA
        } else {
          //START FETCH VIDEO DATA


          $selectSitereviewVideos = $sitereviewVideoTable->select()
                  ->from($sitereviewVideoTableName, array('video_id', 'listing_id'))
                  ->where('is_import != ?', 1)
                  ->group('video_id');
          $sitereviewVideoDatas = $sitereviewVideoTable->fetchAll($selectSitereviewVideos);
          foreach ($sitereviewVideoDatas as $sitereviewVideoData) {
            $sitereviewVideo = Engine_Api::_()->getItem('video', $sitereviewVideoData->video_id);
            if (!empty($sitereviewVideo)) {
              $db = $reviewVideoTable->getAdapter();
              $db->beginTransaction();

              try {
                $listingVideo = $reviewVideoTable->createRow();
                $listingVideo->listing_id = $sitereviewVideoData->listing_id;
                $listingVideo->title = $sitereviewVideo->title;
                $listingVideo->description = $sitereviewVideo->description;
                $listingVideo->search = $sitereviewVideo->search;
                $listingVideo->owner_id = $sitereviewVideo->owner_id;
                $listingVideo->creation_date = $sitereviewVideo->creation_date;
                $listingVideo->modified_date = $sitereviewVideo->modified_date;

                $listingVideo->view_count = 1;
                if ($sitereviewVideo->view_count > 0) {
                  $listingVideo->view_count = $sitereviewVideo->view_count;
                }

                $listingVideo->comment_count = $sitereviewVideo->comment_count;
                $listingVideo->type = $sitereviewVideo->type;
                $listingVideo->code = $sitereviewVideo->code;
                $listingVideo->rating = $sitereviewVideo->rating;
                $listingVideo->status = $sitereviewVideo->status;
                $listingVideo->file_id = 0;
                $listingVideo->duration = $sitereviewVideo->duration;
                $listingVideo->is_import = 1;
                $listingVideo->save();
                $db->commit();
              } catch (Exception $e) {
                $db->rollBack();
                throw $e;
              }

              //START VIDEO THUMB WORK
              if (!empty($listingVideo->code) && !empty($listingVideo->type) && !empty($sitereviewVideo->photo_id)) {
                $storageTable = Engine_Api::_()->getDbtable('files', 'storage');
                $storageData = $storageTable->fetchRow(array('file_id = ?' => $sitereviewVideo->photo_id));
                if (!empty($storageData)) {
                  $thumbnail = $storageData->storage_path;

                  $ext = ltrim(strrchr($thumbnail, '.'), '.');
                  $thumbnail_parsed = @parse_url($thumbnail);

                  if (@GetImageSize($thumbnail)) {
                    $valid_thumb = true;
                  } else {
                    $valid_thumb = false;
                  }

                  if ($valid_thumb && $thumbnail && $ext && $thumbnail_parsed && in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {
                    $tmp_file = APPLICATION_PATH . '/temporary/link_' . md5($thumbnail) . '.' . $ext;
                    $thumb_file = APPLICATION_PATH . '/temporary/link_thumb_' . md5($thumbnail) . '.' . $ext;
                    $src_fh = fopen($thumbnail, 'r');
                    $tmp_fh = fopen($tmp_file, 'w');
                    stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
                    $image = Engine_Image::factory();
                    $image->open($tmp_file)
                            ->resize(120, 240)
                            ->write($thumb_file)
                            ->destroy();

                    try {
                      $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
                          'parent_type' => 'sitereview_video',
                          'parent_id' => $listingVideo->video_id
                              ));

                      //REMOVE TEMP FILE
                      @unlink($thumb_file);
                      @unlink($tmp_file);
                    } catch (Exception $e) {
                      
                    }

                    $listingVideo->photo_id = $thumbFileRow->file_id;
                    $listingVideo->save();
                  }
                }
              }
              //END VIDEO THUMB WORK
              //START FETCH TAG
              $videoTags = $sitereviewVideo->tags()->getTagMaps();
              $tagString = '';

              foreach ($videoTags as $tagmap) {

                if ($tagString != '')
                  $tagString .= ', ';
                $tagString .= $tagmap->getTag()->getTitle();

                $owner = Engine_Api::_()->getItem('user', $sitereviewVideo->owner_id);
                $tags = preg_split('/[,]+/', $tagString);
                $tags = array_filter(array_map("trim", $tags));
                $listingVideo->tags()->setTagMaps($owner, $tags);
              }
              //END FETCH TAG

              $likeTable = Engine_Api::_()->getDbtable('likes', 'core');
              $likeTableName = $likeTable->info('name');

              //START FETCH LIKES
              $selectLike = $likeTable->select()
                      ->from($likeTableName, 'like_id')
                      ->where('resource_type = ?', 'video')
                      ->where('resource_id = ?', $sitereviewVideoData->video_id);
              $selectLikeDatas = $likeTable->fetchAll($selectLike);
              foreach ($selectLikeDatas as $selectLikeData) {
                $like = Engine_Api::_()->getItem('core_like', $selectLikeData->like_id);

                $newLikeEntry = $likeTable->createRow();
                $newLikeEntry->resource_type = 'sitereview_video';
                $newLikeEntry->resource_id = $listingVideo->video_id;
                $newLikeEntry->poster_type = 'user';
                $newLikeEntry->poster_id = $like->poster_id;
                $newLikeEntry->creation_date = $like->creation_date;
                $newLikeEntry->save();
              }
              //END FETCH LIKES

              $commentTable = Engine_Api::_()->getDbtable('comments', 'core');
              $commentTableName = $commentTable->info('name');

              //START FETCH COMMENTS
              $selectLike = $commentTable->select()
                      ->from($commentTableName, 'comment_id')
                      ->where('resource_type = ?', 'video')
                      ->where('resource_id = ?', $sitereviewVideoData->video_id);
              $selectLikeDatas = $commentTable->fetchAll($selectLike);
              foreach ($selectLikeDatas as $selectLikeData) {
                $comment = Engine_Api::_()->getItem('core_comment', $selectLikeData->comment_id);

                $newLikeEntry = $commentTable->createRow();
                $newLikeEntry->resource_type = 'sitereview_video';
                $newLikeEntry->resource_id = $listingVideo->video_id;
                $newLikeEntry->poster_type = 'user';
                $newLikeEntry->poster_id = $comment->poster_id;
                $newLikeEntry->body = $comment->body;
                $newLikeEntry->creation_date = $comment->creation_date;
                $newLikeEntry->like_count = $comment->like_count;
                $newLikeEntry->save();
              }
              //END FETCH COMMENTS
              //START UPDATE TOTAL LIKES IN LISTING-VIDEO TABLE
              $selectLikeCount = $likeTable->select()
                      ->from($likeTableName, array('COUNT(*) AS like_count'))
                      ->where('resource_type = ?', 'sitereview_video')
                      ->where('resource_id = ?', $listingVideo->video_id);
              $selectLikeCounts = $likeTable->fetchAll($selectLikeCount);
              if (!empty($selectLikeCounts)) {
                $selectLikeCounts = $selectLikeCounts->toArray();
                $listingVideo->like_count = $selectLikeCounts[0]['like_count'];
                $listingVideo->save();
              }
              //END UPDATE TOTAL LIKES IN LISTING-VIDEO TABLE
              //START FETCH RATTING DATA
              $selectVideoRating = $videoRating->select()
                      ->from($videoRatingName)
                      ->where('video_id = ?', $sitereviewVideoData->video_id);

              $videoRatingDatas = $videoRating->fetchAll($selectVideoRating);
              if (!empty($videoRatingDatas)) {
                $videoRatingDatas = $videoRatingDatas->toArray();
              }

              foreach ($videoRatingDatas as $videoRatingData) {

                $reviewVideoRatingTable->insert(array(
                    'videorating_id' => $listingVideo->video_id,
                    'user_id' => $videoRatingData['user_id'],
                    'rating' => $videoRatingData['rating']
                ));
              }
              //END FETCH RATTING DATA
              $sitereviewVideoTable->update(array('is_import' => 0), array('video_id = ?' => $sitereviewVideoData->video_id));
            }
          }
        }
      }

      if (isset($values['sitereview_show_video']) && ($type_video_value != $values['sitereview_show_video'])) {
        if (!empty($values['sitereview_show_video'])) {
          foreach ($listingTypes as $listingType) {
            $db->query("UPDATE `engine4_activity_actiontypes` SET `enabled` = '1' WHERE `engine4_activity_actiontypes`.`type` = 'video_sitereview_listtype_$listingType->listingtype_id' ");
            $db->query("UPDATE `engine4_activity_actiontypes` SET `enabled` = '0' WHERE `engine4_activity_actiontypes`.`type` = 'sitereview_video_new_listtype_$listingType->listingtype_id' ");
          }
        } elseif (empty($values['sitereview_show_video'])) {
          foreach ($listingTypes as $listingType) {
            $db->query("UPDATE `engine4_activity_actiontypes` SET `enabled` = '1' WHERE `engine4_activity_actiontypes`.`type` = 'sitereview_video_new_listtype_$listingType->listingtype_id' ");
            $db->query("UPDATE `engine4_activity_actiontypes` SET `enabled` = '0' WHERE `engine4_activity_actiontypes`.`type` = 'video_sitereview_listtype_$listingType->listingtype_id' ");
          }
        }
      }

      // Okay, save
      foreach ($values as $key => $value) {
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      
      if(isset($values['sitereview_show_video'])) {
				if (!empty($values['sitereview_show_video'])) {
					Engine_Api::_()->getDbtable('menuItems', 'core')->update(array('enabled' => 0), array('name = ?' => 'sitereview_admin_submain_setting_tab', 'module = ?' => 'sitereview'));
					Engine_Api::_()->getDbtable('menuItems', 'core')->update(array('enabled' => 0), array('name = ?' => 'sitereview_admin_submain_utilities_tab', 'module = ?' => 'sitereview'));
				} else {
					Engine_Api::_()->getDbtable('menuItems', 'core')->update(array('enabled' => 1), array('name = ?' => 'sitereview_admin_submain_setting_tab', 'module = ?' => 'sitereview'));
					Engine_Api::_()->getDbtable('menuItems', 'core')->update(array('enabled' => 1), array('name = ?' => 'sitereview_admin_submain_utilities_tab', 'module = ?' => 'sitereview'));
				}
      }
      return $this->_helper->redirector->gotoRoute(array('action' => 'show-video'));
    }
  }

  public function readmeAction() {

    $this->view->faq = 0;
    $this->view->faq_type = $this->_getParam('faq_type', 'general');
  }

  public function applayCompareAction() {
    //SET LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    //GET CATEGORY ID
    $this->view->category_id = $category_id = $this->_getParam('category_id');

    //GET CATEGORY ITEM
    $this->view->category = $category = Engine_Api::_()->getItem('sitereview_category', $category_id);

    if (!$this->getRequest()->isPost()) {
      return;
    }
    //UPDATE FILE ID IN CATEGORY TABLE
    $category->applyCompare();
    //$category->save();
    $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 1000,
        'parentRefresh' => true,
        'messages' => Zend_Registry::get('Zend_Translate')->_('Your changes have been saved successfully.')
    ));
  }

  //ACTION FOR DELETE THE LISTING
  public function deleteCategoryAction() {

    $this->_helper->layout->setLayout('admin-simple');
    $category_id = $this->_getParam('category_id');
    $listingtype_id = $this->_getParam('listingtype_id');
    $cat_dependency = $this->_getParam('cat_dependency');

    $this->view->category_id = $category_id;

    //GET CATEGORIES TABLE
    $tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');
    $tableCategoryName = $tableCategory->info('name');

    //GET LISTING TABLE
    $tableSitereview = Engine_Api::_()->getDbtable('listings', 'sitereview');

    if ($this->getRequest()->isPost()) {
      //if($cat_dependency != 0) {
      //IF SUB-CATEGORY AND 3RD LEVEL CATEGORY IS MAPPED
      $previous_cat_profile_type = $tableCategory->getProfileType(null, $category_id);

      if ($previous_cat_profile_type) {

        //SELECT LISTINGS WHICH HAVE THIS CATEGORY
        $listings = $tableSitereview->getCategoryList($category_id, 'category_id');

        foreach ($listings as $listing) {

          //DELETE ALL MAPPING VALUES FROM FIELD TABLES
          Engine_Api::_()->fields()->getTable('sitereview_listing', 'values')->delete(array('item_id = ?' => $listing->listing_id));
          Engine_Api::_()->fields()->getTable('sitereview_listing', 'search')->delete(array('item_id = ?' => $listing->listing_id));

          //UPDATE THE PROFILE TYPE OF ALREADY CREATED LISTINGS
          $tableSitereview->update(array('profile_type' => 0), array('listing_id = ?' => $listing->listing_id));

          //GET REVIEW TABLE
          $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
          $reviewTableName = $reviewTable->info('name');

          //REVIEW PROFILE TYPE UPDATION WORK
          $reviewIds = $reviewTable->select()
                  ->from($reviewTableName, 'review_id')
                  ->where('resource_id = ?', $listing->listing_id)
                  ->where('resource_type = ?', 'sitereview_listing')
                  ->query()
                  ->fetchAll(Zend_Db::FETCH_COLUMN)
          ;
          if (!empty($reviewIds)) {
            foreach ($reviewIds as $reviewId) {
              //DELETE ALL MAPPING VALUES FROM FIELD TABLES
              Engine_Api::_()->fields()->getTable('sitereview_review', 'values')->delete(array('item_id = ?' => $reviewId));
              Engine_Api::_()->fields()->getTable('sitereview_review', 'search')->delete(array('item_id = ?' => $reviewId));

              //UPDATE THE PROFILE TYPE OF ALREADY CREATED REVIEWS
              $reviewTable->update(array('profile_type_review' => 0), array('resource_id = ?' => $reviewId));
            }
          }
        }
      }

      //SITEREVIEW TABLE SUB-CATEGORY/3RD LEVEL DELETE WORK
      $tableSitereview->update(array('subcategory_id' => 0, 'subsubcategory_id' => 0), array('subcategory_id = ?' => $category_id));
      $tableSitereview->update(array('subsubcategory_id' => 0), array('subsubcategory_id = ?' => $category_id));

      $tableCategory->delete(array('cat_dependency = ?' => $category_id, 'subcat_dependency = ?' => $category_id));
      $tableCategory->delete(array('category_id = ?' => $category_id));

      //}
      //GET URL
      $url = $this->_helper->url->url(array('action' => 'categories', 'controller' => 'settings', 'listingtype_id' => $listingtype_id, 'perform' => 'add', 'category_id' => 0));
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRedirect' => $url,
          'parentRedirectTime' => 1,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_(''))
      ));
    }

    $this->renderScript('admin-settings/delete-category.tpl');
  }

  public function integrationsAction() {

    $pluginName = 'sitereview';
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_integrations');
  }

  //ACTION FOR AD SHOULD BE DISPLAY OR NOT ON PAGES
  public function adsettingsAction() {

    //GET NAVIGATION
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_ads');

    //FORM
    $this->view->form = $form = new Sitereview_Form_Admin_Adsettings();

    //CHECK THAT COMMUNITY AD PLUGIN IS ENABLED OR NOT
    $communityadEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('communityad');
    if ($communityadEnabled) {
      $this->view->ismoduleenabled = $ismoduleenabled = 1;
    } else {
      $this->view->ismoduleenabled = $ismoduleenabled = 0;
    }

    //CHECK FORM VALIDATION
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license2.php';
    }
  }
  
  public function templateAction() {
    
    //LIGHTBOX FOR OTHER PLUGINS    
    $this->view->template_type = $this->_getParam('template_type');  
    $this->view->pluginCounts = $this->_getParam('pluginCounts');  
    $this->view->title_plural = $this->_getParam('title_plural');
  }

}
