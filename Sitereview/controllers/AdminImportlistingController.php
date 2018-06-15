<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    SitereviewlistEnabled
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminImportlistingController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_AdminImportlistingController extends Core_Controller_Action_Admin {

  //ACTION FOR SHOWING IMPORT INSTRUCTIONS
  public function indexAction() {

    //INCREASE THE MEMORY ALLOCATION SIZE AND INFINITE SET TIME OUT
    ini_set('memory_limit', '2048M');
    set_time_limit(0);

    //GET LISTINGTYPE ID AND COUNT
    $this->view->listingTypeCount = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount();
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 1);
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $this->view->listingtypeArray = $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);

    $coreModuleTable = Engine_Api::_()->getDbtable('modules', 'core');
    $this->view->sitereviewlistingtypeInsalled = $coreModuleTable->hasModule('sitereviewlistingtype');
    $this->view->sitereviewlistingtypeEnabled = $coreModuleTable->isModuleEnabled('sitereviewlistingtype');
    $this->view->listEnabled = $listEnabled = $coreModuleTable->isModuleEnabled('list');
    if ($listEnabled) {
      $this->view->listVersion = $coreModuleTable->select()->from($coreModuleTable->info('name'), 'version')->where('name = ?', 'list')->query()->fetchColumn();
    }
    $this->view->recipeEnabled = $recipeEnabled = $coreModuleTable->isModuleEnabled('recipe');
    $this->view->blogEnabled = $blogEnabled = $coreModuleTable->isModuleEnabled('blog');
    $this->view->classifiedEnabled = $classifiedEnabled = $coreModuleTable->isModuleEnabled('classified');

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_import');

    //GET SITEREVIEW TABLES
    $reviewCategoryTable = Engine_Api::_()->getDbtable('categories', 'sitereview');
    $reviewCategoryTableName = $reviewCategoryTable->info('name');

    $reviewTable = Engine_Api::_()->getDbtable('listings', 'sitereview');

    $otherinfoTable = Engine_Api::_()->getDbTable('otherinfo', 'sitereview');

    $reviewFieldValueTable = Engine_Api::_()->fields()->getTable('sitereview_listing', 'values');
    $reviewFieldValueTableName = $reviewFieldValueTable->info('name');

    $reviewMetaTable = Engine_Api::_()->fields()->getTable('sitereview_listing', 'meta');
    $reviewMetaTableName = $reviewMetaTable->info('name');

    $reviewTopicTable = Engine_Api::_()->getDbtable('topics', 'sitereview');
    $reviewPostTable = Engine_Api::_()->getDbtable('posts', 'sitereview');

    $reviewTopicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'sitereview');
    $reviewTopicWatchesTableName = $reviewTopicWatchesTable->info('name');

    $reviewPhotoTable = Engine_Api::_()->getDbTable('photos', 'sitereview');
    $reviewPhotoTableName = $reviewPhotoTable->info('name');

    $albumTable = Engine_Api::_()->getDbtable('albums', 'sitereview');

    $reviewReviewTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
    $reviewRatingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');

    $reviewVideoTable = Engine_Api::_()->getDbtable('videos', 'sitereview');
    $reviewVideoTableName = $reviewVideoTable->info('name');

    $clasfVideoTable = Engine_Api::_()->getDbtable('clasfvideos', 'sitereview');
    $clasfVideoTableName = $clasfVideoTable->info('name');

    $reviewVideoRating = Engine_Api::_()->getDbTable('videoratings', 'sitereview');
    $reviewVideoRatingName = $reviewVideoRating->info('name');

    //GET CORE TABLES
    $likeTable = Engine_Api::_()->getDbtable('likes', 'core');
    $likeTableName = $likeTable->info('name');

    $commentTable = Engine_Api::_()->getDbtable('comments', 'core');
    $commentTableName = $commentTable->info('name');

    $storageTable = Engine_Api::_()->getDbtable('files', 'storage');

    $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');

    //START CODE FOR CREATING THE ListingToReviewImport.log FILE
    if (!file_exists(APPLICATION_PATH . '/temporary/log/ListingToReviewImport.log')) {
      $log = new Zend_Log();
      try {
        $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/ListingToReviewImport.log'));
      } catch (Exception $e) {
        //CHECK DIRECTORY
        if (!@is_dir(APPLICATION_PATH . '/temporary/log') && @mkdir(APPLICATION_PATH . '/temporary/log', 0777, true)) {
          $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/ListingToReviewImport.log'));
        } else {
          //Silence ...
          if (APPLICATION_ENV !== 'production') {
            $log->log($e->__toString(), Zend_Log::CRIT);
          } else {
            //MAKE SURE LOGGING DOESN'T CAUSE EXCEPTIONS
            $log->addWriter(new Zend_Log_Writer_Null());
          }
        }
      }
    }

    //GIVE WRITE PERMISSION IF FILE EXIST
    if (file_exists(APPLICATION_PATH . '/temporary/log/ListingToReviewImport.log')) {
      @chmod(APPLICATION_PATH . '/temporary/log/ListingToReviewImport.log', 0777);
    }
    //END CODE FOR CREATING THE ListingToReviewImport.log FILE
    //START IMPORTING WORK IF LIST AND SITEREVIEW IS INSTALLED AND ACTIVATE
    if ($listEnabled) {

      //GET LIST TABLES
      $listingTable = Engine_Api::_()->getDbTable('listings', 'list');
      $listingTableName = $listingTable->info('name');

      $listCategoryTable = Engine_Api::_()->getDbtable('categories', 'list');
      $listCategoryTableName = $listCategoryTable->info('name');

      $writeTable = Engine_Api::_()->getDbtable('writes', 'list');

      $listLocationTable = Engine_Api::_()->getDbtable('locations', 'list');

      $reviewLocationTable = Engine_Api::_()->getDbtable('locations', 'sitereview');

      $metaTable = Engine_Api::_()->fields()->getTable('list_listing', 'meta');
      $selectMetaData = $metaTable->select()->where('type = ?', 'currency');
      $metaData = $metaTable->fetchRow($selectMetaData);

      $listFieldValueTable = Engine_Api::_()->fields()->getTable('list_listing', 'values');
      $listFieldValueTableName = $listFieldValueTable->info('name');

      $topicTable = Engine_Api::_()->getDbtable('topics', 'list');
      $topicTableName = $topicTable->info('name');

      $postTable = Engine_Api::_()->getDbtable('posts', 'list');
      $postTableName = $postTable->info('name');

      $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'list');

      $listPhotoTable = Engine_Api::_()->getDbtable('photos', 'list');

      $listreviewTable = Engine_Api::_()->getDbtable('reviews', 'list');
      $listreviewTableName = $listreviewTable->info('name');

      if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('video')) {

        $listVideoRating = Engine_Api::_()->getDbTable('ratings', 'video');
        $listVideoRatingName = $listVideoRating->info('name');

        $listVideoTable = Engine_Api::_()->getDbtable('clasfvideos', 'list');
        $listVideoTableName = $listVideoTable->info('name');
      }

      //ADD NEW COLUMN IN LISTING TABLE
      $db = Engine_Db_Table::getDefaultAdapter();
      $is_review_import = $db->query("SHOW COLUMNS FROM engine4_list_listings LIKE 'is_review_import'")->fetch();
      if (empty($is_review_import)) {
        $db->query("ALTER TABLE `engine4_list_listings` ADD `is_review_import` TINYINT( 2 ) NOT NULL DEFAULT '0'");
      }

      //START IF IMPORTING IS BREAKED BY SOME REASON
      $selectListings = $listingTable->select()
              ->from($listingTableName, 'listing_id')
              ->where('is_review_import != ?', 1)
              ->where('category_id != ?', 0)
              ->order('listing_id ASC');
      $listingDatas = $listingTable->fetchAll($selectListings);

      $this->view->first_listing_id = $first_listing_id = 0;
      $this->view->last_listing_id = $last_listing_id = 0;

      if (!empty($listingDatas)) {

        $flag_first_listing_id = 1;

        foreach ($listingDatas as $listingData) {

          if ($flag_first_listing_id == 1) {
            $this->view->first_listing_id = $first_listing_id = $listingData->listing_id;
          }
          $flag_first_listing_id++;

          $this->view->last_listing_id = $last_listing_id = $listingData->listing_id;
        }

        if (isset($_GET['assigned_previous_id'])) {
          $this->view->assigned_previous_id = $assigned_previous_id = $_GET['assigned_previous_id'];
        } else {
          $this->view->assigned_previous_id = $assigned_previous_id = $first_listing_id;
        }
      }

      //START IMPORTING IF REQUESTED
      if (isset($_GET['start_import']) && $_GET['start_import'] == 1 && $_GET['module'] == 'list') {

        //ACTIVITY FEED IMPORT
        $activity_list = $this->_getParam('activity_list');

        $imported_listing_id = $db->query("SHOW COLUMNS FROM engine4_sitereview_listings LIKE 'imported_listing_id'")->fetch();

        //DO NOT RUN THIS CODE IN RECALL
        if (!isset($_GET['recall']) && empty($imported_listing_id)) {

          $db = Engine_Db_Table::getDefaultAdapter();
          $db->beginTransaction();

          try {

            //ADD MAPPING COLUMN IN SITEREVIEW TABLE
            $imported_listing_id = $db->query("SHOW COLUMNS FROM engine4_sitereview_listings LIKE 'imported_listing_id'")->fetch();
            if (empty($imported_listing_id)) {
              $db->query("ALTER TABLE `engine4_sitereview_listings` ADD `imported_listing_id` INT( 11 ) NOT NULL DEFAULT '0'");
            }

            //ADD MAPPING COLUMN IN SITEREVIEW TABLE
            $list_profile_type = $db->query("SHOW COLUMNS FROM engine4_sitereview_categories LIKE 'list_profile_type'")->fetch();
            if (empty($list_profile_type)) {
              $db->query("ALTER TABLE `engine4_sitereview_categories` ADD `list_profile_type` INT( 11 ) NOT NULL DEFAULT '0'");
            }

            //ADD MAPPING COLUMN IN SITEREVIEW TABLE
            $list_field_id = $db->query("SHOW COLUMNS FROM engine4_sitereview_listing_fields_meta LIKE 'list_field_id'")->fetch();
            if (empty($list_field_id)) {
              $db->query("ALTER TABLE `engine4_sitereview_listing_fields_meta` ADD `list_field_id` INT( 11 ) NOT NULL DEFAULT '0'");
            }

            //START FETCH CATEGORY WORK
            $selectReviewCategory = $reviewCategoryTable->select()
                    ->from($reviewCategoryTableName, 'category_name')
                    ->where('category_name != ?', '')
                    ->where('listingtype_id = ?', $listingtype_id)
                    ->where('cat_dependency = ?', 0);
            $reviewCategoryDatas = $reviewCategoryTable->fetchAll($selectReviewCategory);
            if (!empty($reviewCategoryDatas)) {
              $reviewCategoryDatas = $reviewCategoryDatas->toArray();
            }

            $reviewCategoryInArrayData = array();
            foreach ($reviewCategoryDatas as $reviewCategoryData) {
              $reviewCategoryInArrayData[] = $reviewCategoryData['category_name'];
            }

            $selectListCategory = $listCategoryTable->select()
                    ->from($listCategoryTableName)
                    ->where('category_name != ?', '')
                    ->where('cat_dependency = ?', 0);
            $listCategoryDatas = $listCategoryTable->fetchAll($selectListCategory);
            if (!empty($listCategoryDatas)) {
              $listCategoryDatas = $listCategoryDatas->toArray();
              foreach ($listCategoryDatas as $listCategoryData) {

                //RENAME THE CATEGORY IN SITEREVIEW TABLE IF ALREADY EXIST
                if (in_array($listCategoryData['category_name'], $reviewCategoryInArrayData)) {
                  $reviewCategoryTable->update(array('category_name' => $listCategoryData['category_name'] . "_old"), array('category_name = ?' => $listCategoryData['category_name'], 'listingtype_id = ?' => $listingtype_id));
                }

                if (!in_array($listCategoryData['category_name'], $reviewCategoryInArrayData)) {
                  $newCategory = $reviewCategoryTable->createRow();
                  $newCategory->listingtype_id = $listingtype_id;
                  $newCategory->category_name = $listCategoryData['category_name'];
                  $newCategory->cat_dependency = 0;
                  $newCategory->list_profile_type = Engine_Api::_()->getDbTable('profilemaps', 'list')->getProfileType($listCategoryData['category_id']);
                  $newCategory->cat_order = 9999;
                  $newCategory->save();

                  $newCategory->afterCreate();

                  $selectListSubCategory = $listCategoryTable->select()
                          ->from($listCategoryTableName)
                          ->where('category_name != ?', '')
                          ->where('cat_dependency = ?', $listCategoryData['category_id']);
                  $listSubCategoryDatas = $listCategoryTable->fetchAll($selectListSubCategory);
                  foreach ($listSubCategoryDatas as $listSubCategoryData) {
                    $newSubCategory = $reviewCategoryTable->createRow();
                    $newSubCategory->listingtype_id = $listingtype_id;
                    $newSubCategory->category_name = $listSubCategoryData->category_name;
                    $newSubCategory->cat_dependency = $newCategory->category_id;
                    $newSubCategory->cat_order = 9999;
                    $subcategory_id = $newSubCategory->save();
                    $newSubCategory->afterCreate();

                    $selectListSubSubCategory = $listCategoryTable->select()
                            ->from($listCategoryTableName)
                            ->where('category_name != ?', '')
                            ->where('cat_dependency = ?', $listSubCategoryData['category_id'])
                            ->where('subcat_dependency = ?', $listSubCategoryData['category_id']);
                    $listSubSubCategoryDatas = $listCategoryTable->fetchAll($selectListSubSubCategory);
                    foreach ($listSubSubCategoryDatas as $listSubSubCategoryData) {
                      $newSubSubCategory = $reviewCategoryTable->createRow();
                      $newSubSubCategory->listingtype_id = $listingtype_id;
                      $newSubSubCategory->category_name = $listSubSubCategoryData->category_name;
                      $newSubSubCategory->cat_dependency = $subcategory_id;
                      $newSubSubCategory->subcat_dependency = $subcategory_id;
                      $newSubSubCategory->cat_order = 9999;
                      $newSubSubCategory->save();
                      $newSubSubCategory->afterCreate();
                    }
                  }
                }
              }
            }

            //CUSTOM FIELDS WORK
            $options = array();
            $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('list_listing');
            if (count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type') {
              $profileTypeField = $topStructure[0]->getChild();
              $options = $profileTypeField->getOptions();
              if (count($options) > 0) {
                $options = $profileTypeField->getElementParams('list_listing');
                $optionsNew = $options['options']['multiOptions'];
              }
            }

            $reviewoptions = array();
            $reviewtopStructure = Engine_Api::_()->fields()->getFieldStructureTop('sitereview_listing');
            if (count($reviewtopStructure) == 1 && $reviewtopStructure[0]->getChild()->type == 'profile_type') {
              $reviewprofileTypeField = $reviewtopStructure[0]->getChild();
              $reviewoptions = $reviewprofileTypeField->getOptions();
              if (count($reviewoptions) > 0) {
                $reviewoptions = $reviewprofileTypeField->getElementParams('sitereview_listing');
                $reviewoptionsNew = $reviewoptions['options']['multiOptions'];
              }
            }

            foreach ($optionsNew as $key => $value) {

              if (empty($key) || empty($value)) {
                continue;
              }

              //COPY PROFILE TYPE
              $field = Engine_Api::_()->fields()->getField(1, 'sitereview_listing');
              $option = Engine_Api::_()->fields()->createOption('sitereview_listing', $field, array(
                  'label' => "Listings - " . $value,
                      ));
              $option_id = $option->option_id;

              //UPDATE THE PROFILE TYPE VALUE IN TABLE
              $reviewCategoryTable->update(array('profile_type' => $option_id), array('list_profile_type = ?' => $key));

              $field_map_array = $db->select()
                      ->from('engine4_list_listing_fields_maps')
                      ->where('option_id = ?', $key)
                      ->query()
                      ->fetchAll();
              $field_map_array_count = count($field_map_array);

              if ($field_map_array_count < 1)
                continue;

              $child_id_array = array();
              for ($c = 0; $c < $field_map_array_count; $c++) {
                $child_id_array[] = $field_map_array[$c]['child_id'];
              }
              unset($c);

              $field_meta_array = $db->select()
                      ->from('engine4_list_listing_fields_meta')
                      ->where('field_id IN (' . implode(', ', $child_id_array) . ')')
                      ->where('type != ?', 'profile_type')
                      ->query()
                      ->fetchAll();

              // Copy each row
              for ($c = 0; $c < Count($field_meta_array); $c++) {

                $formValues = array(
                    'option_id' => $option_id,
                    'type' => $field_meta_array[$c]['type'],
                    'label' => $field_meta_array[$c]['label'],
                    'description' => $field_meta_array[$c]['description'],
                    'alias' => $field_meta_array[$c]['alias'],
                    'required' => $field_meta_array[$c]['required'],
                    'display' => $field_meta_array[$c]['display'],
                    'publish' => 0,
                    'search' => 0, //$field_meta_array[$c]['search'],
                    //'show' => $field_meta_array[$c]['show'],
                    'order' => $field_meta_array[$c]['order'],
                    'config' => $field_meta_array[$c]['config'],
                    'validators' => $field_meta_array[$c]['validators'],
                    'filters' => $field_meta_array[$c]['filters'],
                    'style' => $field_meta_array[$c]['style'],
                    'error' => $field_meta_array[$c]['error'],
                );

                $field = Engine_Api::_()->fields()->createField('sitereview_listing', $formValues);

                $db->update('engine4_sitereview_listing_fields_meta', array('config' => $field_meta_array[$c]['config'], 'list_field_id' => $field_meta_array[$c]['field_id']), array('field_id = ?' => $field->field_id));

                if ($field_meta_array[$c]['type'] == 'select' || $field_meta_array[$c]['type'] == 'radio' || $field_meta_array[$c]['type'] == 'multiselect' || $field_meta_array[$c]['type'] == 'multi_checkbox') {
                  $field_options_array = $db->select()
                          ->from('engine4_list_listing_fields_options')
                          ->where('field_id = ?', $field_meta_array[$c]['field_id'])
                          ->query()
                          ->fetchAll();
                  $field_options_order = 0;
                  foreach ($field_options_array as $field_options) {
                    $field_options_order++;
                    $field = Engine_Api::_()->fields()->getField($field->field_id, 'sitereview_listing');
                    $option = Engine_Api::_()->fields()->createOption('sitereview_listing', $field, array(
                        'label' => $field_options['label'],
                        'order' => $field_options_order,
                            ));

                    $morefield_map_array = $db->select()
                            ->from('engine4_list_listing_fields_maps')
                            ->where('option_id = ?', $field_options['option_id'])
                            ->where('field_id = ?', $field_options['field_id'])
                            ->query()
                            ->fetchAll();
                    $morefield_map_array_count = count($morefield_map_array);

                    if ($morefield_map_array_count < 1)
                      continue;

                    $morechild_id_array = array();
                    for ($morec = 0; $morec < $morefield_map_array_count; $morec++) {
                      $morechild_id_array[] = $morefield_map_array[$morec]['child_id'];
                    }
                    unset($morec);

                    $morefield_meta_array = $db->select()
                            ->from('engine4_list_listing_fields_meta')
                            ->where('field_id IN (' . implode(', ', $morechild_id_array) . ')')
                            ->where('type != ?', 'profile_type')
                            ->query()
                            ->fetchAll();

                    // Copy each row
                    for ($morec = 0; $morec < Count($morefield_meta_array); $morec++) {

                      $moreformValues = array(
                          'option_id' => $option->option_id,
                          'type' => $morefield_meta_array[$morec]['type'],
                          'label' => $morefield_meta_array[$morec]['label'],
                          'description' => $morefield_meta_array[$morec]['description'],
                          'alias' => $morefield_meta_array[$morec]['alias'],
                          'required' => $morefield_meta_array[$morec]['required'],
                          'display' => $morefield_meta_array[$morec]['display'],
                          'publish' => 0,
                          'search' => 0, //$morefield_meta_array[$morec]['search'],
                          //'show' => $morefield_meta_array[$morec]['show'],
                          'order' => $morefield_meta_array[$morec]['order'],
                          'config' => $morefield_meta_array[$morec]['config'],
                          'validators' => $morefield_meta_array[$morec]['validators'],
                          'filters' => $morefield_meta_array[$morec]['filters'],
                          'style' => $morefield_meta_array[$morec]['style'],
                          'error' => $morefield_meta_array[$morec]['error'],
                      );

                      $morefield = Engine_Api::_()->fields()->createField('sitereview_listing', $moreformValues);

                      $db->update('engine4_sitereview_listing_fields_meta', array('config' => $morefield_meta_array[$morec]['config'], 'list_field_id' => $morefield_meta_array[$morec]['field_id']), array('field_id = ?' => $morefield->field_id));

                      if ($morefield_meta_array[$morec]['type'] == 'select' || $morefield_meta_array[$morec]['type'] == 'radio' || $morefield_meta_array[$morec]['type'] == 'multiselect' || $morefield_meta_array[$morec]['type'] == 'multi_checkbox') {
                        $morefield_options_array = $db->select()
                                ->from('engine4_list_listing_fields_options')
                                ->where('field_id = ?', $morefield_meta_array[$morec]['field_id'])
                                ->query()
                                ->fetchAll();
                        $morefield_options_order = 0;
                        foreach ($morefield_options_array as $morefield_options) {
                          $morefield_options_order++;
                          $morefield = Engine_Api::_()->fields()->getField($morefield->field_id, 'sitereview_listing');
                          $moreoption = Engine_Api::_()->fields()->createOption('sitereview_listing', $morefield, array(
                              'label' => $morefield_options['label'],
                              'order' => $morefield_options_order,
                                  ));
                        }
                      }
                    }
                  }
                }
              }
            }

            $db->commit();
          } catch (Exception $e) {
            $db->rollBack();

            //DELETE MAPPING COLUMN IN SITEREVIEW TABLE
            $imported_listing_id = $db->query("SHOW COLUMNS FROM engine4_sitereview_listings LIKE 'imported_listing_id'")->fetch();
            if (!empty($imported_listing_id)) {
              $db->query("ALTER TABLE `engine4_sitereview_listings` DROP `imported_listing_id`");
            }

            throw $e;
          }
        }
        //DO NOT RUN THE UPPER CODE IN RECALL
        //START IMPORTING CODE
        $selectListings = $listingTable->select()
                ->where('listing_id >= ?', $assigned_previous_id)
                ->from($listingTableName, 'listing_id')
                ->where('is_review_import != ?', 1)
                ->where('category_id != ?', 0)
                ->order('listing_id ASC');
        $listingDatas = $listingTable->fetchAll($selectListings);

        $next_import_count = 0;

        foreach ($listingDatas as $listingData) {
          $listing_id = $listingData->listing_id;

          $db = Engine_Db_Table::getDefaultAdapter();
          $db->beginTransaction();
          try {

            if (!empty($listing_id)) {

              $listing = Engine_Api::_()->getItem('list_listing', $listing_id);

              $sitereview = $reviewTable->createRow();
              $sitereview->title = $listing->title;

              if ($listingtypeArray->body_allow)
                $sitereview->body = $listing->body;

              $sitereview->owner_id = $listing->owner_id;
              $sitereview->listingtype_id = $listingtype_id;

              //START FETCH LIST CATEGORY AND SUB-CATEGORY
              if (!empty($listing->category_id)) {
                $listCategory = $listCategoryTable->fetchRow(array('category_id = ?' => $listing->category_id, 'cat_dependency = ?' => 0));
                if (!empty($listCategory)) {
                  $listCategoryName = $listCategory->category_name;

                  if (!empty($listCategoryName)) {
                    $reviewCategory = $reviewCategoryTable->fetchRow(array('category_name = ?' => $listCategoryName, 'cat_dependency = ?' => 0, 'listingtype_id =?' => $listingtype_id));
                    if (!empty($reviewCategory)) {
                      $reviewCategoryId = $sitereview->category_id = $reviewCategory->category_id;

                      $listSubCategory = $listCategoryTable->fetchRow(array('category_id = ?' => $listing->subcategory_id, 'cat_dependency = ?' => $listing->category_id));
                      if (!empty($listSubCategory)) {
                        $listSubCategoryName = $listSubCategory->category_name;

                        $reviewSubCategory = $reviewCategoryTable->fetchRow(array('category_name = ?' => $listSubCategoryName, 'cat_dependency = ?' => $reviewCategoryId, 'listingtype_id =?' => $listingtype_id));
                        if (!empty($reviewSubCategory)) {
                          $sitereview->subcategory_id = $reviewSubCategory->category_id;
                        }
                      }
                    }
                  }
                }
              } else {
                continue;
              }
              //END FETCH LIST CATEGORY AND SUB-CATEGORY

              $sitereview->profile_type = 0;

              $sitereview->photo_id = 0;

              //START FETCH PRICE
              if (!empty($metaData)) {
                $field_id = $metaData->field_id;

                $valueTable = Engine_Api::_()->fields()->getTable('list_listing', 'values');
                $selectValueData = $valueTable->select()->where('item_id = ?', $listing_id)->where('field_id = ?', $field_id);
                $valueData = $valueTable->fetchRow($selectValueData);
                if (!empty($valueData) && ($listingtypeArray->price)) {
                  $sitereview->price = $valueData->value;
                }
              }
              //END FETCH PRICE
              //START GET DATA FROM LISTING
              $sitereview->creation_date = $listing->creation_date;
              $sitereview->modified_date = $listing->modified_date;
              $sitereview->approved = $listing->approved;
              $sitereview->featured = $listing->featured;
              $sitereview->sponsored = $listing->sponsored;

              $sitereview->view_count = 1;
              if ($listing->view_count > 0) {
                $sitereview->view_count = $listing->view_count;
              }

              $sitereview->comment_count = $listing->comment_count;
              $sitereview->like_count = $listing->like_count;
              $sitereview->review_count = $listing->review_count;
              $sitereview->closed = $listing->closed;
              $sitereview->draft = !$listing->draft;

              if (!empty($listing->approved_date)) {
                $sitereview->approved_date = $listing->approved_date;
              }

              if (!empty($listing->end_date)) {
                $sitereview->end_date = $listing->end_date;
              }

              $sitereview->rating_avg = round($listing->rating, 4);
              $sitereview->rating_users = round($listing->rating, 4);
              $sitereview->imported_listing_id = $listing->listing_id;
              $sitereview->save();

              $sitereview->creation_date = $listing->creation_date;
              $sitereview->save();

              //FATCH REVIEW CATEGORIES
              $categoryIdsArray = array();
              $categoryIdsArray[] = $sitereview->category_id;
              $categoryIdsArray[] = $sitereview->subcategory_id;
              $categoryIdsArray[] = $sitereview->subsubcategory_id;
              $sitereview->profile_type = $reviewCategoryTable->getProfileType($categoryIdsArray, 0, 'profile_type');
              $sitereview->search = $listing->search;
              $sitereview->save();

              //START FETCH CUSTOM FIELD VALUES
              if (!empty($sitereview->profile_type)) {
                $reviewFieldValueTable->insert(array('item_id' => $sitereview->listing_id, 'field_id' => 1, 'index' => 0, 'value' => $sitereview->profile_type));
                $fieldValueSelect = $reviewMetaTable->select()
                        ->setIntegrityCheck(false)
                        ->from($reviewMetaTableName, array('field_id', 'type'))
                        ->joinInner($listFieldValueTableName, "$listFieldValueTableName.field_id = $reviewMetaTableName.list_field_id", array('value', 'index', 'field_id as list_field_id'))
                        ->where("$listFieldValueTableName.item_id = ?", $listing_id);
                $fieldValues = $reviewMetaTable->fetchAll($fieldValueSelect);
                foreach ($fieldValues as $fieldValue) {
                  if ($fieldValue->type != 'multi_checkbox' && $fieldValue->type != 'multiselect' && $fieldValue->type != 'radio' && $fieldValue->type != 'select') {
                    $reviewFieldValueTable->insert(array('item_id' => $sitereview->listing_id, 'field_id' => $fieldValue->field_id, 'index' => $fieldValue->index, 'value' => $fieldValue->value));
                  } else {

                    $listingFieldValues = $db->select()
                            ->from('engine4_list_listing_fields_options')
                            ->where('field_id = ?', $fieldValue->list_field_id)
                            ->query()
                            ->fetchAll(Zend_Db::FETCH_COLUMN);

                    $sitereviewFieldValues = $db->select()
                            ->from('engine4_sitereview_listing_fields_options')
                            ->where('field_id = ?', $fieldValue->field_id)
                            ->query()
                            ->fetchAll(Zend_Db::FETCH_COLUMN);

                    $mergeFieldValues = array_combine($sitereviewFieldValues, $listingFieldValues);
                    $value = array_search($fieldValue->value, $mergeFieldValues);
                    if (!empty($value)) {
                      $reviewFieldValueTable->insert(array('item_id' => $sitereview->listing_id, 'field_id' => $fieldValue->field_id, 'index' => $fieldValue->index, 'value' => $value));
                    }
                  }
                }
              }
              //END FETCH CUSTOM FIELD VALUES            

              $listing->is_review_import = 1;
              $listing->save();
              $next_import_count++;
              //END GET DATA FROM LISTING
              //GENERATE ACITIVITY FEED
              if ($sitereview->draft == 0 && $activity_list && $sitereview->search) {
                $action = $activityTable->addActivity($sitereview->getOwner(), $sitereview, 'sitereview_new_listtype_' . $listingtype_id);
                $action->date = $sitereview->creation_date;
                $action->save();

                if ($action != null) {
                  $activityTable->attachActivity($action, $sitereview);
                }
              }

              $row = $otherinfoTable->getOtherinfo($sitereview->getIdentity());

              if (empty($row)) {
                $about = "";
                $overview = "";

                //START FETCH engine4_list_writes DATA
                $writeData = $writeTable->fetchRow(array('listing_id = ?' => $listing_id));
                if (!empty($writeData)) {
                  $about = $writeData->text;
                }
                //END FETCH engine4_list_writes DATAS

                if ($listing->overview && $listingtypeArray->overview) {
                  $overview = $listing->overview;
                }

                Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->insert(array(
                    'listing_id' => $sitereview->getIdentity(),
                    'overview' => $overview,
                    'about' => $about
                ));
              }

              $locationData = $listLocationTable->fetchRow(array('listing_id = ?' => $listing_id));
              if (!empty($locationData) && $listingtypeArray->location) {
                $sitereview->location = $locationData->location;
                $sitereview->save();

                $reviewLocation = $reviewLocationTable->createRow();
                $reviewLocation->listing_id = $sitereview->listing_id;
                $reviewLocation->location = $sitereview->location;
                $reviewLocation->latitude = $locationData->latitude;
                $reviewLocation->longitude = $locationData->longitude;
                $reviewLocation->formatted_address = $locationData->formatted_address;
                $reviewLocation->country = $locationData->country;
                $reviewLocation->state = $locationData->state;
                $reviewLocation->zipcode = $locationData->zipcode;
                $reviewLocation->city = $locationData->city;
                $reviewLocation->address = $locationData->address;
                $reviewLocation->zoom = $locationData->zoom;
                $reviewLocation->save();
              }

              //START FETCH TAG
              $listTags = $listing->tags()->getTagMaps();
              $tagString = '';

              foreach ($listTags as $tagmap) {

                if ($tagString != '')
                  $tagString .= ', ';
                $tagString .= $tagmap->getTag()->getTitle();

                $tags = array_filter(array_map("trim", preg_split('/[,]+/', $tagString)));
                $sitereview->tags()->setTagMaps(Engine_Api::_()->getItem('user', $listing->owner_id), $tags);
              }
              //END FETCH TAG
              //START FETCH LIKES
              $selectLike = $likeTable->select()
                      ->from($likeTableName, 'like_id')
                      ->where('resource_type = ?', 'list_listing')
                      ->where('resource_id = ?', $listing_id);
              $selectLikeDatas = $likeTable->fetchAll($selectLike);
              foreach ($selectLikeDatas as $selectLikeData) {
                $like = Engine_Api::_()->getItem('core_like', $selectLikeData->like_id);

                $newLikeEntry = $likeTable->createRow();
                $newLikeEntry->resource_type = 'sitereview_listing';
                $newLikeEntry->resource_id = $sitereview->listing_id;
                $newLikeEntry->poster_type = 'user';
                $newLikeEntry->poster_id = $like->poster_id;
                $newLikeEntry->creation_date = $like->creation_date;
                $newLikeEntry->save();

                $newLikeEntry->creation_date = $like->creation_date;
                $newLikeEntry->save();
              }
              //END FETCH LIKES
              //START FETCH COMMENTS
              $selectLike = $commentTable->select()
                      ->from($commentTableName, 'comment_id')
                      ->where('resource_type = ?', 'list_listing')
                      ->where('resource_id = ?', $listing_id);
              $selectLikeDatas = $commentTable->fetchAll($selectLike);
              foreach ($selectLikeDatas as $selectLikeData) {
                $comment = Engine_Api::_()->getItem('core_comment', $selectLikeData->comment_id);

                $newLikeEntry = $commentTable->createRow();
                $newLikeEntry->resource_type = 'sitereview_listing';
                $newLikeEntry->resource_id = $sitereview->listing_id;
                $newLikeEntry->poster_type = 'user';
                $newLikeEntry->poster_id = $comment->poster_id;
                $newLikeEntry->body = $comment->body;
                $newLikeEntry->creation_date = $comment->creation_date;
                $newLikeEntry->like_count = $comment->like_count;
                $newLikeEntry->save();

                $newLikeEntry->like_count = $comment->like_count;
                $newLikeEntry->save();
              }
              //END FETCH COMMENTS
              //START FETCH PRIVACY
              $auth = Engine_Api::_()->authorization()->context;
              $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

              foreach ($roles as $role) {
                if ($auth->isAllowed($listing, $role, 'view')) {
                  $values['auth_view'] = $role;
                }
              }

              foreach ($roles as $role) {
                if ($auth->isAllowed($listing, $role, 'photo')) {
                  $values['auth_photo'] = $role;
                }
              }

              foreach ($roles as $role) {
                if ($auth->isAllowed($listing, $role, 'video')) {
                  $values['auth_video'] = $role;
                }
              }

              $viewMax = array_search($values['auth_view'], $roles);
              $photoMax = array_search($values['auth_photo'], $roles);
              $videoMax = array_search($values['auth_video'], $roles);

              foreach ($roles as $i => $role) {
                $auth->setAllowed($sitereview, $role, 'view', ($i <= $viewMax));
                $auth->setAllowed($sitereview, $role, "view_listtype_$listingtype_id", ($i <= $viewMax));
                $auth->setAllowed($sitereview, $role, "photo_listtype_$listingtype_id", ($i <= $photoMax));
                $auth->setAllowed($sitereview, $role, "video_listtype_$listingtype_id", ($i <= $videoMax));
              }

              $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
              foreach ($roles as $role) {
                if ($auth->isAllowed($listing, $role, 'comment')) {
                  $values['auth_comment'] = $role;
                }
              }
              $commentMax = array_search($values['auth_comment'], $roles);
              foreach ($roles as $i => $role) {
                $auth->setAllowed($sitereview, $role, 'comment', ($i <= $commentMax));
                $auth->setAllowed($sitereview, $role, "comment_listtype_$listingtype_id", ($i <= $commentMax));
              }
              //END FETCH PRIVACY

              $topicSelect = $topicTable->select()
                      ->from($topicTableName)
                      ->where('listing_id = ?', $listing_id);
              $topicSelectDatas = $topicTable->fetchAll($topicSelect);
              if (!empty($topicSelectDatas)) {
                $topicSelectDatass = $topicSelectDatas->toArray();

                foreach ($topicSelectDatass as $topicSelectData) {
                  $reviewTopic = $reviewTopicTable->createRow();
                  $reviewTopic->listing_id = $sitereview->listing_id;
                  $reviewTopic->user_id = $topicSelectData['user_id'];
                  $reviewTopic->title = $topicSelectData['title'];
                  $reviewTopic->creation_date = $topicSelectData['creation_date'];
                  $reviewTopic->modified_date = $topicSelectData['modified_date'];
                  $reviewTopic->sticky = $topicSelectData['sticky'];
                  $reviewTopic->closed = $topicSelectData['closed'];
                  $reviewTopic->view_count = $topicSelectData['view_count'];
                  $reviewTopic->lastpost_id = $topicSelectData['lastpost_id'];
                  $reviewTopic->lastposter_id = $topicSelectData['lastposter_id'];
                  $reviewTopic->save();

                  $reviewTopic->creation_date = $topicSelectData['creation_date'];
                  $reviewTopic->save();

                  //GENERATE ACTIVITY FEED
                  if ($activity_list) {
                    $action = $activityTable->addActivity($reviewTopic->getOwner(), $reviewTopic, 'sitereview_topic_create_listtype_' . $listingtype_id);
                    $action->date = $reviewTopic->creation_date;
                    $action->save();
                    if ($action) {
                      $action->attach($reviewTopic);
                    }
                  }

                  //START FETCH TOPIC POST'S
                  $postSelect = $postTable->select()
                          ->from($postTableName)
                          ->where('topic_id = ?', $topicSelectData['topic_id'])
                          ->where('listing_id = ?', $listing_id);
                  $postSelectDatas = $postTable->fetchAll($postSelect);
                  if (!empty($postSelectDatas)) {
                    $postSelectDatass = $postSelectDatas->toArray();

                    foreach ($postSelectDatass as $postSelectData) {
                      $reviewPost = $reviewPostTable->createRow();
                      $reviewPost->topic_id = $reviewTopic->topic_id;
                      $reviewPost->listing_id = $sitereview->listing_id;
                      $reviewPost->user_id = $postSelectData['user_id'];
                      $reviewPost->body = $postSelectData['body'];
                      $reviewPost->creation_date = $postSelectData['creation_date'];
                      $reviewPost->modified_date = $postSelectData['modified_date'];
                      $reviewPost->save();

                      $reviewPost->creation_date = $postSelectData['creation_date'];
                      $reviewPost->save();
                    }
                  }
                  //END FETCH TOPIC POST'S

                  $reviewTopic->post_count = $topicSelectData['post_count'];
                  $reviewTopic->save();

                  //START FETCH TOPIC WATCH
                  $topicWatchData = $topicWatchesTable->fetchAll(array('resource_id = ?' => $listing_id));
                  foreach ($topicWatchData as $watchData) {
                    if (!empty($watchData)) {
                      $topicwatchSelect = $reviewTopicWatchesTable->select()
                              ->from($reviewTopicWatchesTableName)
                              ->where('resource_id = ?', $reviewTopic->listing_id)
                              ->where('topic_id = ?', $reviewTopic->topic_id)
                              ->where('user_id = ?', $watchData->user_id);
                      $topicwatchSelectDatas = $reviewTopicWatchesTable->fetchRow($topicwatchSelect);

                      if (empty($topicwatchSelectDatas)) {
                        $reviewTopicWatchesTable->insert(array(
                            'resource_id' => $reviewTopic->listing_id,
                            'topic_id' => $reviewTopic->topic_id,
                            'user_id' => $watchData->user_id,
                            'watch' => $watchData->watch
                        ));
                      }
                    }
                  }
                  //END FETCH TOPIC WATCH
                }
              }

              //START FETCH PHOTO DATA
              $selectListPhoto = $listPhotoTable->select()
                      ->from($listPhotoTable->info('name'))
                      ->where('listing_id = ?', $listing_id);
              $listPhotoDatas = $listPhotoTable->fetchAll($selectListPhoto);

              $sitereview = Engine_Api::_()->getItem('sitereview_listing', $sitereview->listing_id);

              if (!empty($listPhotoDatas)) {

                $listPhotoDatas = $listPhotoDatas->toArray();

                if (empty($listing->photo_id)) {
                  foreach ($listPhotoDatas as $listPhotoData) {
                    $listing->photo_id = $listPhotoData['photo_id'];
                    break;
                  }
                }

                if (!empty($listing->photo_id)) {
                  $listPhotoData = $listPhotoTable->fetchRow(array('file_id = ?' => $listing->photo_id));
                  if (!empty($listPhotoData)) {
                    $storageData = $storageTable->fetchRow(array('file_id = ?' => $listPhotoData->file_id));

                    if (!empty($storageData) && !empty($storageData->storage_path)) {

                      $sitereview->setPhoto($storageData->storage_path);

                      $album_id = $albumTable->update(array('photo_id' => $sitereview->photo_id), array('listing_id = ?' => $sitereview->listing_id));

                      $reviewProfilePhoto = Engine_Api::_()->getDbTable('photos', 'sitereview')->fetchRow(array('file_id = ?' => $sitereview->photo_id));
                      if (!empty($reviewProfilePhoto)) {
                        $reviewProfilePhotoId = $reviewProfilePhoto->photo_id;
                      } else {
                        $reviewProfilePhotoId = $sitereview->photo_id;
                      }

                      //START FETCH LIKES
                      $selectLike = $likeTable->select()
                              ->from($likeTableName, 'like_id')
                              ->where('resource_type = ?', 'list_photo')
                              ->where('resource_id = ?', $listing->photo_id);
                      $selectLikeDatas = $likeTable->fetchAll($selectLike);
                      foreach ($selectLikeDatas as $selectLikeData) {
                        $like = Engine_Api::_()->getItem('core_like', $selectLikeData->like_id);
                        $newLikeEntry = $likeTable->createRow();
                        $newLikeEntry->resource_type = 'sitereview_photo';
                        $newLikeEntry->resource_id = $reviewProfilePhotoId;
                        $newLikeEntry->poster_type = 'user';
                        $newLikeEntry->poster_id = $like->poster_id;
                        $newLikeEntry->creation_date = $like->creation_date;
                        $newLikeEntry->save();

                        $newLikeEntry->creation_date = $like->creation_date;
                        $newLikeEntry->save();
                      }
                      //END FETCH LIKES
                      //START FETCH COMMENTS
                      $selectLike = $commentTable->select()
                              ->from($commentTableName, 'comment_id')
                              ->where('resource_type = ?', 'list_photo')
                              ->where('resource_id = ?', $listing->photo_id);
                      $selectLikeDatas = $commentTable->fetchAll($selectLike);
                      foreach ($selectLikeDatas as $selectLikeData) {
                        $comment = Engine_Api::_()->getItem('core_comment', $selectLikeData->comment_id);

                        $newLikeEntry = $commentTable->createRow();
                        $newLikeEntry->resource_type = 'sitereview_photo';
                        $newLikeEntry->resource_id = $reviewProfilePhotoId;
                        $newLikeEntry->poster_type = 'user';
                        $newLikeEntry->poster_id = $comment->poster_id;
                        $newLikeEntry->body = $comment->body;
                        $newLikeEntry->creation_date = $comment->creation_date;
                        $newLikeEntry->like_count = $comment->like_count;
                        $newLikeEntry->save();

                        $newLikeEntry->creation_date = $comment->creation_date;
                        $newLikeEntry->save();
                      }
                      //END FETCH COMMENTS
                      //START FETCH TAGGER DETAIL
                      $tagmapsTable = Engine_Api::_()->getDbtable('TagMaps', 'core');
                      $tagmapsTableName = $tagmapsTable->info('name');
                      $selectTagmaps = $tagmapsTable->select()
                              ->from($tagmapsTableName, 'tagmap_id')
                              ->where('resource_type = ?', 'list_photo')
                              ->where('resource_id = ?', $listing->photo_id);
                      $selectTagmapsDatas = $tagmapsTable->fetchAll($selectTagmaps);
                      foreach ($selectTagmapsDatas as $selectTagmapsData) {
                        $tagMap = Engine_Api::_()->getItem('core_tag_map', $selectTagmapsData->tagmap_id);

                        $newTagmapEntry = $tagmapsTable->createRow();
                        $newTagmapEntry->resource_type = 'sitereview_photo';
                        $newTagmapEntry->resource_id = $reviewProfilePhotoId;
                        $newTagmapEntry->tagger_type = 'user';
                        $newTagmapEntry->tagger_id = $tagMap->tagger_id;
                        $newTagmapEntry->tag_type = 'user';
                        $newTagmapEntry->tag_id = $tagMap->tag_id;
                        $newTagmapEntry->creation_date = $tagMap->creation_date;
                        $newTagmapEntry->extra = $tagMap->extra;
                        $newTagmapEntry->save();

                        $newTagmapEntry->creation_date = $tagMap->creation_date;
                        $newTagmapEntry->save();
                      }
                      //END FETCH TAGGER DETAIL
                    }
                  }

                  $fetchDefaultAlbum = $albumTable->fetchRow(array('listing_id = ?' => $sitereview->listing_id));
                  if (!empty($fetchDefaultAlbum)) {

                    $selectListPhoto = $listPhotoTable->select()
                            ->from($listPhotoTable->info('name'))
                            ->where('listing_id = ?', $listing_id);
                    $listPhotoDatas = $listPhotoTable->fetchAll($selectListPhoto);

                    $order = 999;
                    foreach ($listPhotoDatas as $listPhotoData) {

                      if ($listPhotoData['file_id'] != $listing->photo_id) {
                        $params = array(
                            'collection_id' => $fetchDefaultAlbum->album_id,
                            'album_id' => $fetchDefaultAlbum->album_id,
                            'listing_id' => $sitereview->listing_id,
                            'user_id' => $listPhotoData['user_id'],
                            'order' => $order,
                        );

                        $storageData = $storageTable->fetchRow(array('file_id = ?' => $listPhotoData['file_id']));
                        if (!empty($storageData) && !empty($storageData->storage_path)) {
                          $file = array();
                          $file['tmp_name'] = $storageData->storage_path;
                          $path_array = explode('/', $file['tmp_name']);
                          $file['name'] = end($path_array);

                          $reviewPhoto = Engine_Api::_()->sitereview()->createPhoto($params, $file);
                          if (!empty($reviewPhoto)) {

                            $order++;

                            //START FETCH LIKES
                            $selectLike = $likeTable->select()
                                    ->from($likeTableName, 'like_id')
                                    ->where('resource_type = ?', 'list_photo')
                                    ->where('resource_id = ?', $listPhotoData['photo_id']);
                            $selectLikeDatas = $likeTable->fetchAll($selectLike);
                            foreach ($selectLikeDatas as $selectLikeData) {
                              $like = Engine_Api::_()->getItem('core_like', $selectLikeData->like_id);

                              $newLikeEntry = $likeTable->createRow();
                              $newLikeEntry->resource_type = 'sitereview_photo';
                              $newLikeEntry->resource_id = $reviewPhoto->photo_id;
                              $newLikeEntry->poster_type = 'user';
                              $newLikeEntry->poster_id = $like->poster_id;
                              $newLikeEntry->creation_date = $like->creation_date;
                              $newLikeEntry->save();

                              $newLikeEntry->creation_date = $like->creation_date;
                              $newLikeEntry->save();
                            }
                            //END FETCH LIKES
                            //START FETCH COMMENTS
                            $selectLike = $commentTable->select()
                                    ->from($commentTableName, 'comment_id')
                                    ->where('resource_type = ?', 'list_photo')
                                    ->where('resource_id = ?', $listPhotoData['photo_id']);
                            $selectLikeDatas = $commentTable->fetchAll($selectLike);
                            foreach ($selectLikeDatas as $selectLikeData) {
                              $comment = Engine_Api::_()->getItem('core_comment', $selectLikeData->comment_id);

                              $newLikeEntry = $commentTable->createRow();
                              $newLikeEntry->resource_type = 'sitereview_photo';
                              $newLikeEntry->resource_id = $reviewPhoto->photo_id;
                              $newLikeEntry->poster_type = 'user';
                              $newLikeEntry->poster_id = $comment->poster_id;
                              $newLikeEntry->body = $comment->body;
                              $newLikeEntry->creation_date = $comment->creation_date;
                              $newLikeEntry->like_count = $comment->like_count;
                              $newLikeEntry->save();

                              $newLikeEntry->creation_date = $comment->creation_date;
                              $newLikeEntry->save();
                            }
                            //END FETCH COMMENTS
                            //START FETCH TAGGER DETAIL
                            $selectTagmaps = $tagmapsTable->select()
                                    ->from($tagmapsTableName, 'tagmap_id')
                                    ->where('resource_type = ?', 'list_photo')
                                    ->where('resource_id = ?', $listPhotoData['photo_id']);
                            $selectTagmapsDatas = $tagmapsTable->fetchAll($selectTagmaps);
                            foreach ($selectTagmapsDatas as $selectTagmapsData) {
                              $tagMap = Engine_Api::_()->getItem('core_tag_map', $selectTagmapsData->tagmap_id);

                              $newTagmapEntry = $tagmapsTable->createRow();
                              $newTagmapEntry->resource_type = 'sitereview_photo';
                              $newTagmapEntry->resource_id = $reviewPhoto->photo_id;
                              $newTagmapEntry->tagger_type = 'user';
                              $newTagmapEntry->tagger_id = $tagMap->tagger_id;
                              $newTagmapEntry->tag_type = 'user';
                              $newTagmapEntry->tag_id = $tagMap->tag_id;
                              $newTagmapEntry->creation_date = $tagMap->creation_date;
                              $newTagmapEntry->extra = $tagMap->extra;
                              $newTagmapEntry->save();

                              $newTagmapEntry->creation_date = $tagMap->creation_date;
                              $newTagmapEntry->save();
                            }
                            //END FETCH TAGGER DETAIL
                          }
                        }
                      }
                    }
                  }
                }

                //GENERATE ACTIVITY FEED
                if ($activity_list) {

                  $select = $reviewPhotoTable->select()->from($reviewPhotoTableName)->where('user_id = ?', $sitereview->owner_id)->where('listing_id = ?', $sitereview->listing_id);
                  $reviewPhotos = $reviewPhotoTable->fetchAll($select);
                  $count = count($reviewPhotos);
                  if ($count > 1) {
                    $action = $activityTable->addActivity($sitereview->getOwner(), $sitereview, 'sitereview_photo_upload_listtype_' . $listingtype_id, null, array('count' => $count, 'title' => $sitereview->title));
                    $count = 0;

                    foreach ($reviewPhotos as $reviewPhoto) {

                      if ($action instanceof Activity_Model_Action && $count < 8) {
                        $activityTable->attachActivity($action, $reviewPhoto, Activity_Model_Action::ATTACH_MULTI);
                      }
                      $count++;
                    }
                    $action->date = $reviewPhoto->creation_date;
                    $action->save();
                  }
                }
              }
              //END FETCH PHOTO DATA
              //START FETCH REVIEW DATA
              if ($listingtypeArray->reviews == 2 || $listingtypeArray->reviews == 3) {
                $listreviewTableSelect = $listreviewTable->select()
                        ->from($listreviewTableName, array('MAX(review_id) as review_id', 'owner_id', 'title', 'creation_date', 'modified_date', 'body'))
                        ->where('listing_id = ?', $listing_id)
                        //	->where('owner_id != ?', $listing->owner_id)
                        ->group('owner_id')
                        ->order('review_id ASC');
                $listreviewSelectDatas = $listreviewTable->fetchAll($listreviewTableSelect);
                if (!empty($listreviewSelectDatas)) {
                  $listreviewSelectDatas = $listreviewSelectDatas->toArray();
                  foreach ($listreviewSelectDatas as $listreviewSelectData) {
                    $review = Engine_Api::_()->getItem('list_reviews', $listreviewSelectData['review_id']);
                    if (!$listingtypeArray->allow_owner_review && $review->owner_id == $listing->owner_id) {
                      continue;
                    }
                    $reviewReview = $reviewReviewTable->createRow();
                    $reviewReview->resource_id = $sitereview->listing_id;
                    $reviewReview->resource_type = $sitereview->getType();
                    $reviewReview->owner_id = $review->owner_id;
                    $reviewReview->title = $review->title;
                    $reviewReview->body = $review->body;
                    $reviewReview->view_count = 1;
                    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.recommend', 1)) {
                      $reviewReview->recommend = 1;
                    } else {
                      $reviewReview->recommend = 0;
                    }
                    $reviewReview->creation_date = $review->creation_date;
                    $reviewReview->modified_date = $review->modified_date;
                    $reviewReview->type = 'user';
                    $reviewReview->save();

                    $reviewReview->creation_date = $review->creation_date;
                    $reviewReview->save();

                    //GENERATE ACTIVITY FEED
                    if ($activity_list) {
                      $action = $activityTable->addActivity($reviewReview->getOwner(), $sitereview, 'sitereview_review_add_listtype_' . $listingtype_id);
                      $action->date = $reviewReview->creation_date;
                      $action->save();

                      if ($action != null) {
                        $activityTable->attachActivity($action, $reviewReview);
                      }
                    }

                    //FATCH REVIEW CATEGORIES
                    $categoryIdsArray = array();
                    $categoryIdsArray[] = $sitereview->category_id;
                    $categoryIdsArray[] = $sitereview->subcategory_id;
                    $categoryIdsArray[] = $sitereview->subsubcategory_id;
                    $reviewReview->profile_type_review = $reviewCategoryTable->getProfileType($categoryIdsArray, 0, 'profile_type_review');
                    $reviewReview->save();

                    $reviewRating = $reviewRatingTable->createRow();
                    $reviewRating->review_id = $reviewReview->review_id;
                    $reviewRating->category_id = $sitereview->category_id;
                    $reviewRating->resource_id = $sitereview->listing_id;
                    $reviewRating->resource_type = $sitereview->getType();
                    $reviewRating->user_id = $review->owner_id;
                    $reviewRating->type = 'user';
                    $reviewRating->ratingparam_id = 0;
                    $reviewRating->rating = round($listing->rating, 4);
                    $reviewRating->save();
                  }
                }
              }
              //END FETCH REVIEW DATA
              //START FETCH VIDEO DATA
              if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('video')) {

                $selectListVideos = $listVideoTable->select()
                        ->from($listVideoTableName, 'video_id')
                        ->where('listing_id = ?', $listing_id)
                        ->group('video_id');
                $listVideoDatas = $listVideoTable->fetchAll($selectListVideos);
                foreach ($listVideoDatas as $listVideoData) {
                  $listVideo = Engine_Api::_()->getItem('video', $listVideoData->video_id);
                  if (!empty($listVideo)) {
                    $db = $reviewVideoTable->getAdapter();
                    $db->beginTransaction();

                    try {
                      $reviewVideo = $reviewVideoTable->createRow();
                      $reviewVideo->listing_id = $sitereview->listing_id;
                      $reviewVideo->title = $listVideo->title;
                      $reviewVideo->description = $listVideo->description;
                      $reviewVideo->search = $listVideo->search;
                      $reviewVideo->owner_id = $listVideo->owner_id;
                      $reviewVideo->creation_date = $listVideo->creation_date;
                      $reviewVideo->modified_date = $listVideo->modified_date;

                      $reviewVideo->view_count = 1;
                      if ($listVideo->view_count > 0) {
                        $reviewVideo->view_count = $listVideo->view_count;
                      }

                      $reviewVideo->comment_count = $listVideo->comment_count;
                      $reviewVideo->type = $listVideo->type;
                      $reviewVideo->code = $listVideo->code;
                      $reviewVideo->rating = $listVideo->rating;
                      $reviewVideo->status = $listVideo->status;
                      $reviewVideo->file_id = 0;
                      $reviewVideo->duration = $listVideo->duration;
                      $reviewVideo->save();

                      $reviewVideo->creation_date = $listVideo->creation_date;
                      $reviewVideo->save();

                      //GENERATE ACTIVITY FEED
                      if ($activity_list && $reviewVideo->search) {
                        //START VIDEO UPLOAD ACTIVITY FEED

                        $action = $activityTable->addActivity($reviewVideo->getOwner(), $sitereview, 'sitereview_video_new_listtype_' . $listingtype_id);
                        $action->date = $reviewVideo->creation_date;
                        $action->save();
                        if ($action != null) {
                          $activityTable->attachActivity($action, $reviewVideo);
                        }

                        foreach ($activityTable->getActionsByObject($reviewVideo) as $action) {
                          $activityTable->resetActivityBindings($action);
                        }
                      }

                      $db->commit();
                    } catch (Exception $e) {
                      $db->rollBack();
                      throw $e;
                    }

                    //START VIDEO THUMB WORK
                    if (!empty($reviewVideo->code) && !empty($reviewVideo->type) && !empty($listVideo->photo_id)) {
                      $storageData = $storageTable->fetchRow(array('file_id = ?' => $listVideo->photo_id));
                      if (!empty($storageData) && !empty($storageData->storage_path)) {
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
                                'parent_id' => $reviewVideo->video_id
                                    ));

                            //REMOVE TEMP FILE
                            @unlink($thumb_file);
                            @unlink($tmp_file);
                          } catch (Exception $e) {
                            
                          }

                          $reviewVideo->photo_id = $thumbFileRow->file_id;
                          $reviewVideo->save();
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
                      $reviewVideo->tags()->setTagMaps($owner, $tags);
                    }
                    //END FETCH TAG
                    //START FETCH LIKES
                    $selectLike = $likeTable->select()
                            ->from($likeTableName, 'like_id')
                            ->where('resource_type = ?', 'video')
                            ->where('resource_id = ?', $listVideoData->video_id);
                    $selectLikeDatas = $likeTable->fetchAll($selectLike);
                    foreach ($selectLikeDatas as $selectLikeData) {
                      $like = Engine_Api::_()->getItem('core_like', $selectLikeData->like_id);

                      $newLikeEntry = $likeTable->createRow();
                      $newLikeEntry->resource_type = 'sitereview_video';
                      $newLikeEntry->resource_id = $reviewVideo->video_id;
                      $newLikeEntry->poster_type = 'user';
                      $newLikeEntry->poster_id = $like->poster_id;
                      $newLikeEntry->creation_date = $like->creation_date;
                      $newLikeEntry->save();

                      $newLikeEntry->creation_date = $like->creation_date;
                      $newLikeEntry->save();
                    }
                    //END FETCH LIKES
                    //START FETCH COMMENTS
                    $selectLike = $commentTable->select()
                            ->from($commentTableName, 'comment_id')
                            ->where('resource_type = ?', 'video')
                            ->where('resource_id = ?', $listVideoData->video_id);
                    $selectLikeDatas = $commentTable->fetchAll($selectLike);
                    foreach ($selectLikeDatas as $selectLikeData) {
                      $comment = Engine_Api::_()->getItem('core_comment', $selectLikeData->comment_id);

                      $newLikeEntry = $commentTable->createRow();
                      $newLikeEntry->resource_type = 'sitereview_video';
                      $newLikeEntry->resource_id = $reviewVideo->video_id;
                      $newLikeEntry->poster_type = 'user';
                      $newLikeEntry->poster_id = $comment->poster_id;
                      $newLikeEntry->body = $comment->body;
                      $newLikeEntry->creation_date = $comment->creation_date;
                      $newLikeEntry->like_count = $comment->like_count;
                      $newLikeEntry->save();

                      $newLikeEntry->creation_date = $comment->creation_date;
                      $newLikeEntry->save();
                    }
                    //END FETCH COMMENTS
                    //START UPDATE TOTAL LIKES IN REVIEW-VIDEO TABLE
                    $selectLikeCount = $likeTable->select()
                            ->from($likeTableName, array('COUNT(*) AS like_count'))
                            ->where('resource_type = ?', 'sitereview_video')
                            ->where('resource_id = ?', $reviewVideo->video_id);
                    $selectLikeCounts = $likeTable->fetchAll($selectLikeCount);
                    if (!empty($selectLikeCounts)) {
                      $selectLikeCounts = $selectLikeCounts->toArray();
                      $reviewVideo->like_count = $selectLikeCounts[0]['like_count'];
                      $reviewVideo->save();
                    }
                    //END UPDATE TOTAL LIKES IN REVIEW-VIDEO TABLE

                    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video', 1)) {

                      $selectClasfVideo = $clasfVideoTable->select()
                              ->from($clasfVideoTableName, array('video_id'))
                              ->where('video_id =?', $reviewVideo->video_id);
                      $resultsClasfVideo = $clasfVideoTable->fetchRow($selectClasfVideo);

                      if (empty($resultsClasfVideo)) {
                        $reviewVideo->is_import = 1;
                        $reviewVideo->save();
                        $clasfVideoTableCreateRow = $clasfVideoTable->createRow();
                        $clasfVideoTableCreateRow->listing_id = $reviewVideo->listing_id;
                        $clasfVideoTableCreateRow->video_id = $reviewVideo->video_id;
                        $clasfVideoTableCreateRow->created = $reviewVideo->creation_date;
                        $clasfVideoTableCreateRow->is_import = $reviewVideo->is_import;
                        $clasfVideoTableCreateRow->save();
                      }
                    }

                    //START FETCH RATTING DATA
                    $selectVideoRating = $listVideoRating->select()
                            ->from($listVideoRatingName)
                            ->where('video_id = ?', $listVideoData->video_id);

                    $listVideoRatingDatas = $listVideoRating->fetchAll($selectVideoRating);
                    if (!empty($listVideoRatingDatas)) {
                      $listVideoRatingDatas = $listVideoRatingDatas->toArray();
                    }

                    foreach ($listVideoRatingDatas as $listVideoRatingData) {

                      $reviewVideoRating->insert(array(
                          'videorating_id' => $reviewVideo->video_id,
                          'user_id' => $listVideoRatingData['user_id'],
                          'rating' => $listVideoRatingData['rating']
                      ));
                    }
                    //END FETCH RATTING DATA
                  }
                }
              }
              //END FETCH VIDEO DATA
            }

            //CREATE LOG ENTRY IN LOG FILE
            if (file_exists(APPLICATION_PATH . '/temporary/log/ListingToReviewImport.log')) {
              $myFile = APPLICATION_PATH . '/temporary/log/ListingToReviewImport.log';
              $error = Zend_Registry::get('Zend_Translate')->_("can't open file");
              $fh = fopen($myFile, 'a') or die($error);
              $current_time = date('D, d M Y H:i:s T');
              $review_title = $sitereview->title;
              $stringData = $this->view->translate('Listing with ID ') . $listing_id . $this->view->translate(' is successfully imported into a Review Listing with ID ') . $sitereview->listing_id . $this->view->translate(' at ') . $current_time . $this->view->translate(". Title of that Review Listing is '") . $review_title . "'.\n\n";
              fwrite($fh, $stringData);
              fclose($fh);
            }

            $db->commit();

            $this->view->assigned_previous_id = $listing_id;
          } catch (Exception $e) {
            $db->rollback();
            throw($e);
          }

          if ($next_import_count >= 100) {
            $this->_redirect("admin/sitereview/importlisting/index?start_import=1&listingtype_id=$listingtype_id&module=list&recall=1&activity_list=$activity_list");
          }

          //exit();
        }
      }
    }

    //START CODE FOR CREATING THE ClassifiedToReviewImport.log FILE
    if (!file_exists(APPLICATION_PATH . '/temporary/log/ClassifiedToReviewImport.log')) {
      $log = new Zend_Log();
      try {
        $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/ClassifiedToReviewImport.log'));
      } catch (Exception $e) {
        //CHECK DIRECTORY
        if (!@is_dir(APPLICATION_PATH . '/temporary/log') && @mkdir(APPLICATION_PATH . '/temporary/log', 0777, true)) {
          $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/ClassifiedToReviewImport.log'));
        } else {
          //Silence ...
          if (APPLICATION_ENV !== 'production') {
            $log->log($e->__toString(), Zend_Log::CRIT);
          } else {
            //MAKE SURE LOGGING DOESN'T CAUSE EXCEPTIONS
            $log->addWriter(new Zend_Log_Writer_Null());
          }
        }
      }
    }

    //GIVE WRITE PERMISSION IF FILE EXIST
    if (file_exists(APPLICATION_PATH . '/temporary/log/ClassifiedToReviewImport.log')) {
      @chmod(APPLICATION_PATH . '/temporary/log/ClassifiedToReviewImport.log', 0777);
    }
    //END CODE FOR CREATING THE ClassifiedToReviewImport.log FILE
    //START IMPORTING WORK IF CLASSIFIED AND SITEREVIEW IS INSTALLED AND ACTIVATE
    if ($classifiedEnabled) {

      //GET CLASSIFIED TABLES
      $metaTable = Engine_Api::_()->fields()->getTable('classified', 'meta');
      $selectMetaDataPrice = $metaTable->select()->where('type = ?', 'currency');
      $metaDataPrice = $metaTable->fetchRow($selectMetaDataPrice);

      $selectMetaDataLocation = $metaTable->select()->where('type = ?', 'location');
      $metaDataLocation = $metaTable->fetchRow($selectMetaDataLocation);

      $classifiedPhotoTable = Engine_Api::_()->getDbtable('photos', 'classified');

      $classifiedTable = Engine_Api::_()->getDbTable('classifieds', 'classified');
      $classifiedTableName = $classifiedTable->info('name');

      $classifiedCategoryTable = Engine_Api::_()->getDbtable('categories', 'classified');
      $classifiedCategoryTableName = $classifiedCategoryTable->info('name');

      $classifiedFieldValueTable = Engine_Api::_()->fields()->getTable('classified', 'values');
      $classifiedFieldValueTableName = $classifiedFieldValueTable->info('name');

      //ADD NEW COLUMN IN Classified TABLE
      $db = Engine_Db_Table::getDefaultAdapter();
      $is_review_import = $db->query("SHOW COLUMNS FROM engine4_classified_classifieds LIKE 'is_review_import'")->fetch();
      if (empty($is_review_import)) {
        $run_query = $db->query("ALTER TABLE `engine4_classified_classifieds` ADD `is_review_import` TINYINT( 2 ) NOT NULL DEFAULT '0'");
      }

      //START IF IMPORTING IS BREAKED BY SOME REASON
      $selectClassifieds = $classifiedTable->select()
              ->from($classifiedTableName, 'classified_id')
              ->where('is_review_import != ?', 1)
              ->where('category_id != ?', 0)
              ->order('classified_id ASC');
      $classifiedDatas = $classifiedTable->fetchAll($selectClassifieds);

      $this->view->first_classified_id = $first_classified_id = 0;
      $this->view->last_classified_id = $last_classified_id = 0;

      if (!empty($classifiedDatas)) {

        $flag_first_classified_id = 1;

        foreach ($classifiedDatas as $classifiedData) {

          if ($flag_first_classified_id == 1) {
            $this->view->first_classified_id = $first_classified_id = $classifiedData->classified_id;
          }
          $flag_first_classified_id++;

          $this->view->last_classified_id = $last_classified_id = $classifiedData->classified_id;
        }

        if (isset($_GET['assigned_previous_id'])) {
          $this->view->classified_assigned_previous_id = $classified_assigned_previous_id = $_GET['assigned_previous_id'];
        } else {
          $this->view->classified_assigned_previous_id = $classified_assigned_previous_id = $first_classified_id;
        }
      }

      //START IMPORTING IF REQUESTED
      if (isset($_GET['start_import']) && $_GET['start_import'] == 1 && $_GET['module'] == 'classified') {

        //ACTIVITY FEED IMPORT
        $activity_classified = $this->_getParam('activity_classified');

        $imported_classified_id = $db->query("SHOW COLUMNS FROM engine4_sitereview_listings LIKE 'imported_classified_id'")->fetch();

        //DO NOT RUN THIS CODE IN RECALL
        if (!isset($_GET['recall']) && empty($imported_classified_id)) {

          $db = Engine_Db_Table::getDefaultAdapter();
          $db->beginTransaction();

          try {

            //ADD MAPPING COLUMN IN SITEREVIEW TABLE
            $imported_listing_id = $db->query("SHOW COLUMNS FROM engine4_sitereview_listings LIKE 'imported_classified_id'")->fetch();
            if (empty($imported_listing_id)) {
              $db->query("ALTER TABLE `engine4_sitereview_listings` ADD `imported_classified_id` INT( 11 ) NOT NULL DEFAULT '0'");
            }

            //ADD MAPPING COLUMN IN SITEREVIEW TABLE
            $list_field_id = $db->query("SHOW COLUMNS FROM engine4_sitereview_listing_fields_meta LIKE 'classified_field_id'")->fetch();
            if (empty($list_field_id)) {
              $db->query("ALTER TABLE `engine4_sitereview_listing_fields_meta` ADD `classified_field_id` INT( 11 ) NOT NULL DEFAULT '0'");
            }

            //CREATE PROFILE TYPE
            $field = Engine_Api::_()->fields()->getField(1, 'sitereview_listing');
            $option = Engine_Api::_()->fields()->createOption('sitereview_listing', $field, array(
                'label' => 'Classified - Default Type',
                    ));
            $classified_profile_type = $option_id = $option->option_id;

            $field_map_array = $db->select()
                    ->from('engine4_classified_fields_maps')
                    ->where('option_id = ?', 0)
                    ->where('field_id = ?', 0)
                    ->query()
                    ->fetchAll();
            $field_map_array_count = count($field_map_array);

            if ($field_map_array_count < 1)
              continue;

            $child_id_array = array();
            for ($c = 0; $c < $field_map_array_count; $c++) {
              $child_id_array[] = $field_map_array[$c]['child_id'];
            }
            unset($c);

            $field_meta_array = $db->select()
                    ->from('engine4_classified_fields_meta')
                    ->where('field_id IN (' . implode(', ', $child_id_array) . ')')
                    ->where('type != ?', 'profile_type')
                    ->query()
                    ->fetchAll();

            // Copy each row
            for ($c = 0; $c < Count($field_meta_array); $c++) {

              $formValues = array(
                  'option_id' => $option_id,
                  'type' => $field_meta_array[$c]['type'],
                  'label' => $field_meta_array[$c]['label'],
                  'description' => $field_meta_array[$c]['description'],
                  'alias' => $field_meta_array[$c]['alias'],
                  'required' => $field_meta_array[$c]['required'],
                  'display' => $field_meta_array[$c]['display'],
                  'publish' => 0,
                  'search' => 0, //$field_meta_array[$c]['search'],
                  //'show' => $field_meta_array[$c]['show'],
                  'order' => $field_meta_array[$c]['order'],
                  'config' => $field_meta_array[$c]['config'],
                  'validators' => $field_meta_array[$c]['validators'],
                  'filters' => $field_meta_array[$c]['filters'],
                  'style' => $field_meta_array[$c]['style'],
                  'error' => $field_meta_array[$c]['error'],
              );

              $field = Engine_Api::_()->fields()->createField('sitereview_listing', $formValues);

              $db->update('engine4_sitereview_listing_fields_meta', array('config' => $field_meta_array[$c]['config'], 'classified_field_id' => $field_meta_array[$c]['field_id']), array('field_id = ?' => $field->field_id));

              if ($field_meta_array[$c]['type'] == 'select' || $field_meta_array[$c]['type'] == 'radio' || $field_meta_array[$c]['type'] == 'multiselect' || $field_meta_array[$c]['type'] == 'multi_checkbox') {
                $field_options_array = $db->select()
                        ->from('engine4_classified_fields_options')
                        ->where('field_id = ?', $field_meta_array[$c]['field_id'])
                        ->query()
                        ->fetchAll();
                $field_options_order = 0;
                foreach ($field_options_array as $field_options) {
                  $field_options_order++;
                  $field = Engine_Api::_()->fields()->getField($field->field_id, 'sitereview_listing');
                  $option = Engine_Api::_()->fields()->createOption('sitereview_listing', $field, array(
                      'label' => $field_options['label'],
                      'order' => $field_options_order,
                          ));

                  $morefield_map_array = $db->select()
                          ->from('engine4_classified_fields_maps')
                          ->where('option_id = ?', $field_options['option_id'])
                          ->where('field_id = ?', $field_options['field_id'])
                          ->query()
                          ->fetchAll();
                  $morefield_map_array_count = count($morefield_map_array);

                  if ($morefield_map_array_count < 1)
                    continue;

                  $morechild_id_array = array();
                  for ($morec = 0; $morec < $morefield_map_array_count; $morec++) {
                    $morechild_id_array[] = $morefield_map_array[$morec]['child_id'];
                  }
                  unset($morec);

                  $morefield_meta_array = $db->select()
                          ->from('engine4_classified_fields_meta')
                          ->where('field_id IN (' . implode(', ', $morechild_id_array) . ')')
                          ->where('type != ?', 'profile_type')
                          ->query()
                          ->fetchAll();

                  // Copy each row
                  for ($morec = 0; $morec < Count($morefield_meta_array); $morec++) {

                    $moreformValues = array(
                        'option_id' => $option->option_id,
                        'type' => $morefield_meta_array[$morec]['type'],
                        'label' => $morefield_meta_array[$morec]['label'],
                        'description' => $morefield_meta_array[$morec]['description'],
                        'alias' => $morefield_meta_array[$morec]['alias'],
                        'required' => $morefield_meta_array[$morec]['required'],
                        'display' => $morefield_meta_array[$morec]['display'],
                        'publish' => 0,
                        'search' => 0, //$morefield_meta_array[$morec]['search'],
                        //'show' => $morefield_meta_array[$morec]['show'],
                        'order' => $morefield_meta_array[$morec]['order'],
                        'config' => $morefield_meta_array[$morec]['config'],
                        'validators' => $morefield_meta_array[$morec]['validators'],
                        'filters' => $morefield_meta_array[$morec]['filters'],
                        'style' => $morefield_meta_array[$morec]['style'],
                        'error' => $morefield_meta_array[$morec]['error'],
                    );

                    $morefield = Engine_Api::_()->fields()->createField('sitereview_listing', $moreformValues);

                    $db->update('engine4_sitereview_listing_fields_meta', array('config' => $morefield_meta_array[$morec]['config'], 'classified_field_id' => $morefield_meta_array[$morec]['field_id']), array('field_id = ?' => $morefield->field_id));

                    if ($morefield_meta_array[$morec]['type'] == 'select' || $morefield_meta_array[$morec]['type'] == 'radio' || $morefield_meta_array[$morec]['type'] == 'multiselect' || $morefield_meta_array[$morec]['type'] == 'multi_checkbox') {
                      $morefield_options_array = $db->select()
                              ->from('engine4_classified_fields_options')
                              ->where('field_id = ?', $morefield_meta_array[$morec]['field_id'])
                              ->query()
                              ->fetchAll();
                      $morefield_options_order = 0;
                      foreach ($morefield_options_array as $morefield_options) {
                        $morefield_options_order++;
                        $morefield = Engine_Api::_()->fields()->getField($morefield->field_id, 'sitereview_listing');
                        $moreoption = Engine_Api::_()->fields()->createOption('sitereview_listing', $morefield, array(
                            'label' => $morefield_options['label'],
                            'order' => $morefield_options_order,
                                ));
                      }
                    }
                  }
                }
              }
            }

            //START FETCH CATEGORY WORK
            $selectReviewCategory = $reviewCategoryTable->select()
                    ->from($reviewCategoryTableName, 'category_name')
                    ->where('category_name != ?', '')
                    ->where('listingtype_id = ?', $listingtype_id)
                    ->where('cat_dependency = ?', 0);
            $reviewCategoryDatas = $reviewCategoryTable->fetchAll($selectReviewCategory);
            if (!empty($reviewCategoryDatas)) {
              $reviewCategoryDatas = $reviewCategoryDatas->toArray();
            }

            $reviewCategoryInArrayData = array();
            foreach ($reviewCategoryDatas as $reviewCategoryData) {
              $reviewCategoryInArrayData[] = $reviewCategoryData['category_name'];
            }

            $selectClassifiedCategory = $classifiedCategoryTable->select()
                    ->from($classifiedCategoryTableName);
            $classifiedCategoryDatas = $classifiedCategoryTable->fetchAll($selectClassifiedCategory);
            if (!empty($classifiedCategoryDatas)) {
              $classifiedCategoryDatas = $classifiedCategoryDatas->toArray();
              foreach ($classifiedCategoryDatas as $classifiedCategoryData) {

                //RENAME THE CATEGORY IN SITEREVIEW TABLE IF ALREADY EXIST
                if (in_array($classifiedCategoryData['category_name'], $reviewCategoryInArrayData)) {
                  $reviewCategoryTable->update(array('category_name' => $classifiedCategoryData['category_name'] . "_old"), array('category_name = ?' => $classifiedCategoryData['category_name'], 'listingtype_id = ?' => $listingtype_id));
                }

                if (!in_array($classifiedCategoryData['category_name'], $reviewCategoryInArrayData)) {
                  $newCategory = $reviewCategoryTable->createRow();
                  $newCategory->listingtype_id = $listingtype_id;
                  $newCategory->category_name = $classifiedCategoryData['category_name'];
                  $newCategory->cat_dependency = 0;
                  $newCategory->profile_type = $classified_profile_type;
                  $newCategory->cat_order = 9999;
                  $newCategory->save();
                  $newCategory->afterCreate();
                }
              }
            }

            $db->commit();
          } catch (Exception $e) {
            $db->rollBack();

            //DELETE MAPPING COLUMN IN SITEREVIEW TABLE
            $imported_listing_id = $db->query("SHOW COLUMNS FROM engine4_sitereview_listings LIKE 'imported_classified_id'")->fetch();
            if (!empty($imported_listing_id)) {
              $db->query("ALTER TABLE `engine4_sitereview_listings` DROP `imported_classified_id`");
            }

            throw $e;
          }
        }

        //START CLASSIFIED IMPORT WORK
        $selectClassifieds = $classifiedTable->select()
                ->where('classified_id >= ?', $classified_assigned_previous_id)
                ->from($classifiedTableName, 'classified_id')
                ->where('is_review_import != ?', 1)
                ->where('category_id != ?', 0)
                ->order('classified_id ASC');
        $classifiedDatas = $classifiedTable->fetchAll($selectClassifieds);

        $next_import_count = 0;

        foreach ($classifiedDatas as $classifiedData) {
          $classified_id = $classifiedData->classified_id;

          if (!empty($classified_id)) {

            $classified = Engine_Api::_()->getItem('classified', $classified_id);

            $sitereview = $reviewTable->createRow();
            $sitereview->title = $classified->title;

            if ($listingtypeArray->body_allow)
              $sitereview->body = strip_tags($classified->body);

            $sitereview->owner_id = $classified->owner_id;
            $sitereview->listingtype_id = $listingtype_id;

            //START FETCH CLASSIFIED CATEGORY AND SUB-CATEGORY
            if (!empty($classified->category_id)) {
              $classifiedCategory = $classifiedCategoryTable->fetchRow(array('category_id = ?' => $classified->category_id));
              if (!empty($classifiedCategory)) {
                $classifiedCategoryName = $classifiedCategory->category_name;

                if (!empty($classifiedCategoryName)) {
                  $reviewCategory = $reviewCategoryTable->fetchRow(array('category_name = ?' => $classifiedCategoryName, 'cat_dependency = ?' => 0, 'listingtype_id =?' => $listingtype_id));
                  if (!empty($reviewCategory)) {
                    $sitereview->category_id = $reviewCategory->category_id;
                  }
                }
              }
            } else {
              continue;
            }
            //END FETCH CLASSIFIED CATEGORY AND SUB-CATEGORY

            $sitereview->profile_type = 0;

            $sitereview->photo_id = 0;

            //START FETCH PRICE
            if (!empty($metaDataPrice)) {
              $field_id = $metaDataPrice->field_id;

              $valueTable = Engine_Api::_()->fields()->getTable('classified', 'values');
              $selectValueData = $valueTable->select()->where('item_id = ?', $classified_id)->where('field_id = ?', $field_id);
              $valueData = $valueTable->fetchRow($selectValueData);
              if (!empty($valueData) && ($listingtypeArray->price)) {
                $sitereview->price = $valueData->value;
              }
            }
            //END FETCH PRICE           
            //START GET DATA FROM CLASSIFIED
            $sitereview->creation_date = $classified->creation_date;
            $sitereview->modified_date = $classified->modified_date;
            $sitereview->approved_date = $sitereview->creation_date;
            $sitereview->view_count = $classified->view_count;
            $sitereview->comment_count = $classified->comment_count;
            $sitereview->closed = $classified->closed;
            $sitereview->approved = 1;

            $sitereview->save();

            $sitereview->creation_date = $classified->creation_date;
            $sitereview->save();

            //FATCH REVIEW CATEGORIES
            $categoryIdsArray = array();
            $categoryIdsArray[] = $sitereview->category_id;
            $categoryIdsArray[] = $sitereview->subcategory_id;
            $categoryIdsArray[] = $sitereview->subsubcategory_id;
            $sitereview->profile_type = $reviewCategoryTable->getProfileType($categoryIdsArray, 0, 'profile_type');
            $sitereview->search = $classified->search;

            Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->insert(array(
                'listing_id' => $sitereview->getIdentity(),
                'overview' => !empty($listingtypeArray->overview) ? $classified->body : NULL,
            ));

            $sitereview->save();

            //START FETCH CUSTOM FIELD VALUES
            if (!empty($sitereview->profile_type)) {
              $reviewFieldValueTable->insert(array('item_id' => $sitereview->listing_id, 'field_id' => 1, 'index' => 0, 'value' => $sitereview->profile_type));
              $fieldValueSelect = $reviewMetaTable->select()
                      ->setIntegrityCheck(false)
                      ->from($reviewMetaTableName, array('field_id', 'type'))
                      ->joinInner($classifiedFieldValueTableName, "$classifiedFieldValueTableName.field_id = $reviewMetaTableName.classified_field_id", array('value', 'index', 'field_id as classified_field_id'))
                      ->where("$classifiedFieldValueTableName.item_id = ?", $classified_id);
              $fieldValues = $reviewMetaTable->fetchAll($fieldValueSelect);
              foreach ($fieldValues as $fieldValue) {
                if ($fieldValue->type != 'multi_checkbox' && $fieldValue->type != 'multiselect' && $fieldValue->type != 'radio' && $fieldValue->type != 'select') {
                  $reviewFieldValueTable->insert(array('item_id' => $sitereview->listing_id, 'field_id' => $fieldValue->field_id, 'index' => $fieldValue->index, 'value' => $fieldValue->value));
                } else {

                  $classifiedFieldValues = $db->select()
                          ->from('engine4_classified_fields_options')
                          ->where('field_id = ?', $fieldValue->classified_field_id)
                          ->query()
                          ->fetchAll(Zend_Db::FETCH_COLUMN);

                  $sitereviewFieldValues = $db->select()
                          ->from('engine4_sitereview_listing_fields_options')
                          ->where('field_id = ?', $fieldValue->field_id)
                          ->query()
                          ->fetchAll(Zend_Db::FETCH_COLUMN);

                  $mergeFieldValues = array_combine($sitereviewFieldValues, $classifiedFieldValues);
                  $value = array_search($fieldValue->value, $mergeFieldValues);
                  if (!empty($value)) {
                    $reviewFieldValueTable->insert(array('item_id' => $sitereview->listing_id, 'field_id' => $fieldValue->field_id, 'index' => $fieldValue->index, 'value' => $value));
                  }
                }
              }
            }
            //END FETCH CUSTOM FIELD VALUES             

            $classified->is_review_import = 1;
            $classified->save();

            $next_import_count++;
            //END GET DATA FROM CLASSIFIED
            //GENERATE ACTIVITY FEED
            if ($sitereview->draft == 0 && $activity_classified && $sitereview->search) {
              $action = $activityTable->addActivity($sitereview->getOwner(), $sitereview, 'sitereview_new_listtype_' . $listingtype_id);
              $action->date = $sitereview->creation_date;
              $action->save();

              if ($action != null) {
                $activityTable->attachActivity($action, $sitereview);
              }
            }

            $row = $otherinfoTable->getOtherinfo($sitereview->getIdentity());

            //START FETCH LOCATION
            if (!empty($metaDataLocation) && $listingtypeArray->location) {
              $field_id = $metaDataLocation->field_id;

              $valueTable = Engine_Api::_()->fields()->getTable('classified', 'values');
              $selectValueData = $valueTable->select()->where('item_id = ?', $classified_id)->where('field_id = ?', $field_id);
              $valueData = $valueTable->fetchRow($selectValueData);
              if (!empty($valueData)) {
                $sitereview->location = $valueData->value;
                $sitereview->save();
                $sitereview->setLocation();
              }
            }

            //START FETCH TAG
            $classifiedTags = $classified->tags()->getTagMaps();
            $tagString = '';

            foreach ($classifiedTags as $tagmap) {

              if ($tagString != '')
                $tagString .= ', ';
              $tagString .= $tagmap->getTag()->getTitle();

              $tags = array_filter(array_map("trim", preg_split('/[,]+/', $tagString)));
              $sitereview->tags()->setTagMaps(Engine_Api::_()->getItem('user', $classified->owner_id), $tags);
            }
            //END FETCH TAG
            //START FETCH LIKES
            $selectLike = $likeTable->select()
                    ->from($likeTableName, 'like_id')
                    ->where('resource_type = ?', 'classified')
                    ->where('resource_id = ?', $classified_id);
            $selectLikeDatas = $likeTable->fetchAll($selectLike);
            foreach ($selectLikeDatas as $selectLikeData) {
              $like = Engine_Api::_()->getItem('core_like', $selectLikeData->like_id);

              $newLikeEntry = $likeTable->createRow();
              $newLikeEntry->resource_type = 'sitereview_listing';
              $newLikeEntry->resource_id = $sitereview->listing_id;
              $newLikeEntry->poster_type = 'user';
              $newLikeEntry->poster_id = $like->poster_id;
              $newLikeEntry->creation_date = $like->creation_date;
              $newLikeEntry->save();

              $newLikeEntry->creation_date = $like->creation_date;
              $newLikeEntry->save();
            }
            //END FETCH LIKES
            //START FETCH COMMENTS
            $selectLike = $commentTable->select()
                    ->from($commentTableName, 'comment_id')
                    ->where('resource_type = ?', 'classified')
                    ->where('resource_id = ?', $classified_id);
            $selectLikeDatas = $commentTable->fetchAll($selectLike);
            foreach ($selectLikeDatas as $selectLikeData) {
              $comment = Engine_Api::_()->getItem('core_comment', $selectLikeData->comment_id);

              $newLikeEntry = $commentTable->createRow();
              $newLikeEntry->resource_type = 'sitereview_listing';
              $newLikeEntry->resource_id = $sitereview->listing_id;
              $newLikeEntry->poster_type = 'user';
              $newLikeEntry->poster_id = $comment->poster_id;
              $newLikeEntry->body = $comment->body;
              $newLikeEntry->creation_date = $comment->creation_date;
              $newLikeEntry->like_count = $comment->like_count;
              $newLikeEntry->save();

              $newLikeEntry->creation_date = $comment->creation_date;
              $newLikeEntry->save();
            }
            //END FETCH COMMENTS
            //START FETCH PRIVACY
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

            foreach ($roles as $role) {
              if ($auth->isAllowed($classified, $role, 'view')) {
                $values['auth_view'] = $role;
              }
            }

            foreach ($roles as $role) {
              if ($auth->isAllowed($classified, $role, 'photo')) {
                $values['auth_photo'] = $role;
              }
            }

            $viewMax = array_search($values['auth_view'], $roles);

            foreach ($roles as $i => $role) {
              $auth->setAllowed($sitereview, $role, 'view', ($i <= $viewMax));
              $auth->setAllowed($sitereview, $role, "view_listtype_$listingtype_id", ($i <= $viewMax));
            }

            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
            foreach ($roles as $role) {
              if ($auth->isAllowed($classified, $role, 'comment')) {
                $values['auth_comment'] = $role;
              }
            }
            $commentMax = array_search($values['auth_comment'], $roles);
            $photoMax = array_search('registered', $roles);
            $videoMax = array_search('registered', $roles);
            foreach ($roles as $i => $role) {
              $auth->setAllowed($sitereview, $role, 'comment', ($i <= $commentMax));
              $auth->setAllowed($sitereview, $role, "comment_listtype_$listingtype_id", ($i <= $commentMax));
              $auth->setAllowed($sitereview, $role, "photo_listtype_$listingtype_id", ($i <= $photoMax));
              $auth->setAllowed($sitereview, $role, "video_listtype_$listingtype_id", ($i <= $videoMax));
            }
            //END FETCH PRIVACY
            //START FETCH PHOTO DATA
            $selectClassifiedPhoto = $classifiedPhotoTable->select()
                    ->from($classifiedPhotoTable->info('name'))
                    ->where('classified_id = ?', $classified_id);
            $classifiedPhotoDatas = $classifiedPhotoTable->fetchAll($selectClassifiedPhoto);

            $sitereview = Engine_Api::_()->getItem('sitereview_listing', $sitereview->listing_id);

            if (!empty($classifiedPhotoDatas)) {

              $classifiedPhotoDatas = $classifiedPhotoDatas->toArray();

              if (empty($classified->photo_id)) {
                foreach ($classifiedPhotoDatas as $classifiedPhotoData) {
                  $classified->photo_id = $classifiedPhotoData['photo_id'];
                  break;
                }
              }

              if (!empty($classified->photo_id)) {
                $classifiedPhotoData = $classifiedPhotoTable->fetchRow(array('file_id = ?' => $classified->photo_id));
                if (!empty($classifiedPhotoData)) {
                  $storageData = $storageTable->fetchRow(array('file_id = ?' => $classifiedPhotoData->file_id));

                  if (!empty($storageData) && !empty($storageData->storage_path)) {

                    $sitereview->setPhoto($storageData->storage_path);

                    $album_id = $albumTable->update(array('photo_id' => $sitereview->photo_id), array('listing_id = ?' => $sitereview->listing_id));

                    $reviewProfilePhoto = Engine_Api::_()->getDbTable('photos', 'sitereview')->fetchRow(array('file_id = ?' => $sitereview->photo_id));
                    if (!empty($reviewProfilePhoto)) {
                      $reviewProfilePhotoId = $reviewProfilePhoto->photo_id;
                    } else {
                      $reviewProfilePhotoId = $sitereview->photo_id;
                    }

                    //START FETCH LIKES
                    $selectLike = $likeTable->select()
                            ->from($likeTableName, 'like_id')
                            ->where('resource_type = ?', 'classified_photo')
                            ->where('resource_id = ?', $classified->photo_id);
                    $selectLikeDatas = $likeTable->fetchAll($selectLike);
                    foreach ($selectLikeDatas as $selectLikeData) {
                      $like = Engine_Api::_()->getItem('core_like', $selectLikeData->like_id);
                      $newLikeEntry = $likeTable->createRow();
                      $newLikeEntry->resource_type = 'sitereview_photo';
                      $newLikeEntry->resource_id = $reviewProfilePhotoId;
                      $newLikeEntry->poster_type = 'user';
                      $newLikeEntry->poster_id = $like->poster_id;
                      $newLikeEntry->creation_date = $like->creation_date;
                      $newLikeEntry->save();

                      $newLikeEntry->creation_date = $like->creation_date;
                      $newLikeEntry->save();
                    }
                    //END FETCH LIKES
                    //START FETCH COMMENTS
                    $selectLike = $commentTable->select()
                            ->from($commentTableName, 'comment_id')
                            ->where('resource_type = ?', 'classified_photo')
                            ->where('resource_id = ?', $classified->photo_id);
                    $selectLikeDatas = $commentTable->fetchAll($selectLike);
                    foreach ($selectLikeDatas as $selectLikeData) {
                      $comment = Engine_Api::_()->getItem('core_comment', $selectLikeData->comment_id);

                      $newLikeEntry = $commentTable->createRow();
                      $newLikeEntry->resource_type = 'sitereview_photo';
                      $newLikeEntry->resource_id = $reviewProfilePhotoId;
                      $newLikeEntry->poster_type = 'user';
                      $newLikeEntry->poster_id = $comment->poster_id;
                      $newLikeEntry->body = $comment->body;
                      $newLikeEntry->creation_date = $comment->creation_date;
                      $newLikeEntry->like_count = $comment->like_count;
                      $newLikeEntry->save();

                      $newLikeEntry->creation_date = $comment->creation_date;
                      $newLikeEntry->save();
                    }
                    //END FETCH COMMENTS
                    //START FETCH TAGGER DETAIL
                    $tagmapsTable = Engine_Api::_()->getDbtable('TagMaps', 'core');
                    $tagmapsTableName = $tagmapsTable->info('name');
                    $selectTagmaps = $tagmapsTable->select()
                            ->from($tagmapsTableName, 'tagmap_id')
                            ->where('resource_type = ?', 'classified_photo')
                            ->where('resource_id = ?', $classified->photo_id);
                    $selectTagmapsDatas = $tagmapsTable->fetchAll($selectTagmaps);
                    foreach ($selectTagmapsDatas as $selectTagmapsData) {
                      $tagMap = Engine_Api::_()->getItem('core_tag_map', $selectTagmapsData->tagmap_id);

                      $newTagmapEntry = $tagmapsTable->createRow();
                      $newTagmapEntry->resource_type = 'sitereview_photo';
                      $newTagmapEntry->resource_id = $reviewProfilePhotoId;
                      $newTagmapEntry->tagger_type = 'user';
                      $newTagmapEntry->tagger_id = $tagMap->tagger_id;
                      $newTagmapEntry->tag_type = 'user';
                      $newTagmapEntry->tag_id = $tagMap->tag_id;
                      $newTagmapEntry->creation_date = $tagMap->creation_date;
                      $newTagmapEntry->extra = $tagMap->extra;
                      $newTagmapEntry->save();

                      $newTagmapEntry->creation_date = $tagMap->creation_date;
                      $newTagmapEntry->save();
                    }
                    //END FETCH TAGGER DETAIL
                  }
                }

                $fetchDefaultAlbum = $albumTable->fetchRow(array('listing_id = ?' => $sitereview->listing_id));
                if (!empty($fetchDefaultAlbum)) {

                  $selectClassifiedPhoto = $classifiedPhotoTable->select()
                          ->from($classifiedPhotoTable->info('name'))
                          ->where('classified_id = ?', $classified_id);
                  $classifiedPhotoDatas = $classifiedPhotoTable->fetchAll($selectClassifiedPhoto);

                  $order = 999;
                  foreach ($classifiedPhotoDatas as $classifiedPhotoData) {

                    if ($classifiedPhotoData['file_id'] != $classified->photo_id) {
                      $params = array(
                          'collection_id' => $fetchDefaultAlbum->album_id,
                          'album_id' => $fetchDefaultAlbum->album_id,
                          'listing_id' => $sitereview->listing_id,
                          'user_id' => $classifiedPhotoData['user_id'],
                          'order' => $order,
                      );

                      $storageData = $storageTable->fetchRow(array('file_id = ?' => $classifiedPhotoData['file_id']));
                      if (!empty($storageData) && !empty($storageData->storage_path)) {
                        $file = array();
                        $file['tmp_name'] = $storageData->storage_path;
                        $path_array = explode('/', $file['tmp_name']);
                        $file['name'] = end($path_array);


                        $reviewPhoto = Engine_Api::_()->sitereview()->createPhoto($params, $file);
                        if (!empty($reviewPhoto)) {

                          $order++;

                          //START FETCH LIKES
                          $selectLike = $likeTable->select()
                                  ->from($likeTableName, 'like_id')
                                  ->where('resource_type = ?', 'classified_photo')
                                  ->where('resource_id = ?', $classifiedPhotoData['photo_id']);
                          $selectLikeDatas = $likeTable->fetchAll($selectLike);
                          foreach ($selectLikeDatas as $selectLikeData) {
                            $like = Engine_Api::_()->getItem('core_like', $selectLikeData->like_id);

                            $newLikeEntry = $likeTable->createRow();
                            $newLikeEntry->resource_type = 'sitereview_photo';
                            $newLikeEntry->resource_id = $reviewPhoto->photo_id;
                            $newLikeEntry->poster_type = 'user';
                            $newLikeEntry->poster_id = $like->poster_id;
                            $newLikeEntry->creation_date = $like->creation_date;
                            $newLikeEntry->save();

                            $newLikeEntry->creation_date = $like->creation_date;
                            $newLikeEntry->save();
                          }
                          //END FETCH LIKES
                          //START FETCH COMMENTS
                          $selectLike = $commentTable->select()
                                  ->from($commentTableName, 'comment_id')
                                  ->where('resource_type = ?', 'classified_photo')
                                  ->where('resource_id = ?', $classifiedPhotoData['photo_id']);
                          $selectLikeDatas = $commentTable->fetchAll($selectLike);
                          foreach ($selectLikeDatas as $selectLikeData) {
                            $comment = Engine_Api::_()->getItem('core_comment', $selectLikeData->comment_id);

                            $newLikeEntry = $commentTable->createRow();
                            $newLikeEntry->resource_type = 'sitereview_photo';
                            $newLikeEntry->resource_id = $reviewPhoto->photo_id;
                            $newLikeEntry->poster_type = 'user';
                            $newLikeEntry->poster_id = $comment->poster_id;
                            $newLikeEntry->body = $comment->body;
                            $newLikeEntry->creation_date = $comment->creation_date;
                            $newLikeEntry->like_count = $comment->like_count;
                            $newLikeEntry->save();

                            $newLikeEntry->creation_date = $comment->creation_date;
                            $newLikeEntry->save();
                          }
                          //END FETCH COMMENTS
                          //START FETCH TAGGER DETAIL
                          $selectTagmaps = $tagmapsTable->select()
                                  ->from($tagmapsTableName, 'tagmap_id')
                                  ->where('resource_type = ?', 'classified_photo')
                                  ->where('resource_id = ?', $classifiedPhotoData['photo_id']);
                          $selectTagmapsDatas = $tagmapsTable->fetchAll($selectTagmaps);
                          foreach ($selectTagmapsDatas as $selectTagmapsData) {
                            $tagMap = Engine_Api::_()->getItem('core_tag_map', $selectTagmapsData->tagmap_id);

                            $newTagmapEntry = $tagmapsTable->createRow();
                            $newTagmapEntry->resource_type = 'sitereview_photo';
                            $newTagmapEntry->resource_id = $reviewPhoto->photo_id;
                            $newTagmapEntry->tagger_type = 'user';
                            $newTagmapEntry->tagger_id = $tagMap->tagger_id;
                            $newTagmapEntry->tag_type = 'user';
                            $newTagmapEntry->tag_id = $tagMap->tag_id;
                            $newTagmapEntry->creation_date = $tagMap->creation_date;
                            $newTagmapEntry->extra = $tagMap->extra;
                            $newTagmapEntry->save();

                            $newTagmapEntry->creation_date = $tagMap->creation_date;
                            $newTagmapEntry->save();
                          }
                          //END FETCH TAGGER DETAIL
                        }
                      }
                    }
                  }
                }
              }
            }
            //END FETCH PHOTO DATA
          }

          $this->view->classified_assigned_previous_id = $classified_id;

          //CREATE LOG ENTRY IN LOG FILE
          if (file_exists(APPLICATION_PATH . '/temporary/log/ListingToReviewImport.log')) {
            $myFile = APPLICATION_PATH . '/temporary/log/ListingToReviewImport.log';
            $error = Zend_Registry::get('Zend_Translate')->_("can't open file");
            $fh = fopen($myFile, 'a') or die($error);
            $current_time = date('D, d M Y H:i:s T');
            $review_title = $sitereview->title;
            $stringData = $this->view->translate('Classified with ID ') . $classified_id . $this->view->translate(' is successfully imported into a Review Listing with ID ') . $sitereview->listing_id . $this->view->translate(' at ') . $current_time . $this->view->translate(". Title of that Review Listing is '") . $review_title . "'.\n\n";
            fwrite($fh, $stringData);
            fclose($fh);
          }

          if ($next_import_count >= 100) {
            $this->_redirect("admin/sitereview/importlisting/index?start_import=1&listingtype_id=$listingtype_id&module=list&recall=1&activity_classified=$activity_classified");
          }
        }
      }
    }

    //START CODE FOR CREATING THE ListingToReviewImport.log FILE
    if (!file_exists(APPLICATION_PATH . '/temporary/log/RecipeToReviewImport.log')) {
      $log = new Zend_Log();
      try {
        $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/RecipeToReviewImport.log'));
      } catch (Exception $e) {
        //CHECK DIRECTORY
        if (!@is_dir(APPLICATION_PATH . '/temporary/log') && @mkdir(APPLICATION_PATH . '/temporary/log', 0777, true)) {
          $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/RecipeToReviewImport.log'));
        } else {
          //Silence ...
          if (APPLICATION_ENV !== 'production') {
            $log->log($e->__toString(), Zend_Log::CRIT);
          } else {
            //MAKE SURE LOGGING DOESN'T CAUSE EXCEPTIONS
            $log->addWriter(new Zend_Log_Writer_Null());
          }
        }
      }
    }

    //GIVE WRITE PERMISSION IF FILE EXIST
    if (file_exists(APPLICATION_PATH . '/temporary/log/RecipeToReviewImport.log')) {
      @chmod(APPLICATION_PATH . '/temporary/log/RecipeToReviewImport.log', 0777);
    }
    //END CODE FOR CREATING THE ListingToReviewImport.log FILE
    //START IMPORTING WORK IF LIST AND SITEREVIEW IS INSTALLED AND ACTIVATE
    if ($recipeEnabled) {

      //GET RECIPE TABLES
      $recipeTable = Engine_Api::_()->getDbTable('recipes', 'recipe');
      $recipeTableName = $recipeTable->info('name');

      $recipeCategoryTable = Engine_Api::_()->getDbtable('categories', 'recipe');
      $recipeCategoryTableName = $recipeCategoryTable->info('name');

      $writeTable = Engine_Api::_()->getDbtable('writes', 'recipe');

      $recipeLocationTable = Engine_Api::_()->getDbtable('locations', 'recipe');

      $metaTable = Engine_Api::_()->fields()->getTable('recipe', 'meta');
      $selectMetaData = $metaTable->select()->where('type = ?', 'currency');
      $metaData = $metaTable->fetchRow($selectMetaData);

      $topicTable = Engine_Api::_()->getDbtable('topics', 'recipe');
      $topicTableName = $topicTable->info('name');

      $postTable = Engine_Api::_()->getDbtable('posts', 'recipe');
      $postTableName = $postTable->info('name');

      $topicWatchesTable = Engine_Api::_()->getDbtable('topicWatches', 'recipe');

      $recipePhotoTable = Engine_Api::_()->getDbtable('photos', 'recipe');

      $recipereviewTable = Engine_Api::_()->getDbtable('reviews', 'recipe');
      $recipereviewTableName = $recipereviewTable->info('name');

      $recipeFieldValueTable = Engine_Api::_()->fields()->getTable('recipe', 'values');
      $recipeFieldValueTableName = $recipeFieldValueTable->info('name');

      if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('video')) {

        $recipeVideoRating = Engine_Api::_()->getDbTable('ratings', 'video');
        $recipeVideoRatingName = $recipeVideoRating->info('name');

        $recipeVideoTable = Engine_Api::_()->getDbtable('clasfvideos', 'recipe');
        $recipeVideoTableName = $recipeVideoTable->info('name');
      }

      //ADD NEW COLUMN IN LISTING TABLE
      $db = Engine_Db_Table::getDefaultAdapter();
      $is_review_import = $db->query("SHOW COLUMNS FROM engine4_recipe_recipes LIKE 'is_review_import'")->fetch();
      if (empty($is_review_import)) {
        $run_query = $db->query("ALTER TABLE `engine4_recipe_recipes` ADD `is_review_import` TINYINT( 2 ) NOT NULL DEFAULT '0'");
      }

      //START IF IMPORTING IS BREAKED BY SOME REASON
      $selectRecipes = $recipeTable->select()
              ->from($recipeTableName, 'recipe_id')
              ->where('is_review_import != ?', 1)
              ->where('category_id != ?', 0)
              ->order('recipe_id ASC');
      $recipeDatas = $recipeTable->fetchAll($selectRecipes);

      $this->view->first_recipe_id = $first_recipe_id = 0;
      $this->view->last_recipe_id = $last_recipe_id = 0;

      if (!empty($recipeDatas)) {

        $flag_first_recipe_id = 1;

        foreach ($recipeDatas as $recipeData) {

          if ($flag_first_recipe_id == 1) {
            $this->view->first_recipe_id = $first_recipe_id = $recipeData->recipe_id;
          }
          $flag_first_recipe_id++;

          $this->view->last_recipe_id = $last_recipe_id = $recipeData->recipe_id;
        }

        if (isset($_GET['recipe_assigned_previous_id'])) {
          $this->view->recipe_assigned_previous_id = $recipe_assigned_previous_id = $_GET['recipe_assigned_previous_id'];
        } else {
          $this->view->recipe_assigned_previous_id = $recipe_assigned_previous_id = $first_recipe_id;
        }
      }

      //START IMPORTING IF REQUESTED
      if (isset($_GET['start_import']) && $_GET['start_import'] == 1 && $_GET['module'] == 'recipe') {

        //ACTIVITY FEED IMPORT
        $activity_recipe = $this->_getParam('activity_recipe');

        $imported_recipe_id = $db->query("SHOW COLUMNS FROM engine4_sitereview_listings LIKE 'imported_recipe_id'")->fetch();

        //DO NOT RUN THIS CODE IN RECALL
        if (!isset($_GET['recall']) && empty($imported_recipe_id)) {

          $db = Engine_Db_Table::getDefaultAdapter();
          $db->beginTransaction();

          try {

            //ADD MAPPING COLUMN IN SITEREVIEW TABLE
            $imported_recipe_id = $db->query("SHOW COLUMNS FROM engine4_sitereview_listings LIKE 'imported_recipe_id'")->fetch();
            if (empty($imported_recipe_id)) {
              $db->query("ALTER TABLE `engine4_sitereview_listings` ADD `imported_recipe_id` INT( 11 ) NOT NULL DEFAULT '0'");
            }

            //ADD MAPPING COLUMN IN SITEREVIEW TABLE
            $recipe_field_id = $db->query("SHOW COLUMNS FROM engine4_sitereview_listing_fields_meta LIKE 'recipe_field_id'")->fetch();
            if (empty($recipe_field_id)) {
              $db->query("ALTER TABLE `engine4_sitereview_listing_fields_meta` ADD `recipe_field_id` INT( 11 ) NOT NULL DEFAULT '0'");
            }


            //CREATE PROFILE TYPE
            $field = Engine_Api::_()->fields()->getField(1, 'sitereview_listing');
            $option = Engine_Api::_()->fields()->createOption('sitereview_listing', $field, array(
                'label' => 'Recipe - Default Type',
                    ));
            $recipe_profile_type = $option_id = $option->option_id;

            //START: CREATE START MONTH AND END MONTH CUSTOM FIELDS
            $startEndMonths = array('Season Starting Month' => 'Please select the starting month for the season of this recipe.', 'Season Ending Month' => 'Please select the ending month for the season of this recipe.');

            foreach ($startEndMonths as $key => $startEndMonth) {
              $formValuesMonth = array(
                  'option_id' => $recipe_profile_type,
                  'type' => 'select',
                  'label' => $key,
                  'description' => $startEndMonth,
                  'display' => 1,
              );

              $fieldMonth = Engine_Api::_()->fields()->createField('sitereview_listing', $formValuesMonth);
              $months = array("1" => "January", "2" => "February", "3" => "March", "4" => "April", "5" => "May", "6" => "June", "7" => "July", "8" => "August", "9" => "September", "10" => "October", "11" => "November", "12" => "December");

              foreach ($months as $key => $month) {

                $option = Engine_Api::_()->fields()->createOption('sitereview_listing', $fieldMonth, array(
                    'label' => $month,
                    'order' => $key,
                        ));
              }
            }
            //END: CREATE START MONTH AND END MONTH CUSTOM FIELDS

            $field_map_array = $db->select()
                    ->from('engine4_recipe_fields_maps')
                    ->where('option_id = ?', 0)
                    ->where('field_id = ?', 0)
                    ->query()
                    ->fetchAll();
            $field_map_array_count = count($field_map_array);

            if ($field_map_array_count < 1)
              continue;

            $child_id_array = array();
            for ($c = 0; $c < $field_map_array_count; $c++) {
              $child_id_array[] = $field_map_array[$c]['child_id'];
            }
            unset($c);

            $field_meta_array = $db->select()
                    ->from('engine4_recipe_fields_meta')
                    ->where('field_id IN (' . implode(', ', $child_id_array) . ')')
                    ->where('type != ?', 'profile_type')
                    ->query()
                    ->fetchAll();

            // Copy each row
            for ($c = 0; $c < Count($field_meta_array); $c++) {

              $formValues = array(
                  'option_id' => $option_id,
                  'type' => $field_meta_array[$c]['type'],
                  'label' => $field_meta_array[$c]['label'],
                  'description' => $field_meta_array[$c]['description'],
                  'alias' => $field_meta_array[$c]['alias'],
                  'required' => $field_meta_array[$c]['required'],
                  'display' => $field_meta_array[$c]['display'],
                  'publish' => 0,
                  'search' => 0, //$field_meta_array[$c]['search'],
                  //'show' => $field_meta_array[$c]['show'],
                  'order' => $field_meta_array[$c]['order'],
                  'config' => $field_meta_array[$c]['config'],
                  'validators' => $field_meta_array[$c]['validators'],
                  'filters' => $field_meta_array[$c]['filters'],
                  'style' => $field_meta_array[$c]['style'],
                  'error' => $field_meta_array[$c]['error'],
              );

              $field = Engine_Api::_()->fields()->createField('sitereview_listing', $formValues);

              $db->update('engine4_sitereview_listing_fields_meta', array('config' => $field_meta_array[$c]['config'], 'recipe_field_id' => $field_meta_array[$c]['field_id']), array('field_id = ?' => $field->field_id));

              if ($field_meta_array[$c]['type'] == 'select' || $field_meta_array[$c]['type'] == 'radio' || $field_meta_array[$c]['type'] == 'multiselect' || $field_meta_array[$c]['type'] == 'multi_checkbox') {
                $field_options_array = $db->select()
                        ->from('engine4_recipe_fields_options')
                        ->where('field_id = ?', $field_meta_array[$c]['field_id'])
                        ->query()
                        ->fetchAll();
                $field_options_order = 0;
                foreach ($field_options_array as $field_options) {
                  $field_options_order++;
                  $field = Engine_Api::_()->fields()->getField($field->field_id, 'sitereview_listing');
                  $option = Engine_Api::_()->fields()->createOption('sitereview_listing', $field, array(
                      'label' => $field_options['label'],
                      'order' => $field_options_order,
                          ));

                  $morefield_map_array = $db->select()
                          ->from('engine4_recipe_fields_maps')
                          ->where('option_id = ?', $field_options['option_id'])
                          ->where('field_id = ?', $field_options['field_id'])
                          ->query()
                          ->fetchAll();
                  $morefield_map_array_count = count($morefield_map_array);

                  if ($morefield_map_array_count < 1)
                    continue;

                  $morechild_id_array = array();
                  for ($morec = 0; $morec < $morefield_map_array_count; $morec++) {
                    $morechild_id_array[] = $morefield_map_array[$morec]['child_id'];
                  }
                  unset($morec);

                  $morefield_meta_array = $db->select()
                          ->from('engine4_recipe_fields_meta')
                          ->where('field_id IN (' . implode(', ', $morechild_id_array) . ')')
                          ->where('type != ?', 'profile_type')
                          ->query()
                          ->fetchAll();

                  // Copy each row
                  for ($morec = 0; $morec < Count($morefield_meta_array); $morec++) {

                    $moreformValues = array(
                        'option_id' => $option->option_id,
                        'type' => $morefield_meta_array[$morec]['type'],
                        'label' => $morefield_meta_array[$morec]['label'],
                        'description' => $morefield_meta_array[$morec]['description'],
                        'alias' => $morefield_meta_array[$morec]['alias'],
                        'required' => $morefield_meta_array[$morec]['required'],
                        'display' => $morefield_meta_array[$morec]['display'],
                        'publish' => 0,
                        'search' => 0, //$morefield_meta_array[$morec]['search'],
                        //'show' => $morefield_meta_array[$morec]['show'],
                        'order' => $morefield_meta_array[$morec]['order'],
                        'config' => $morefield_meta_array[$morec]['config'],
                        'validators' => $morefield_meta_array[$morec]['validators'],
                        'filters' => $morefield_meta_array[$morec]['filters'],
                        'style' => $morefield_meta_array[$morec]['style'],
                        'error' => $morefield_meta_array[$morec]['error'],
                    );

                    $morefield = Engine_Api::_()->fields()->createField('sitereview_listing', $moreformValues);

                    $db->update('engine4_sitereview_listing_fields_meta', array('config' => $morefield_meta_array[$morec]['config'], 'recipe_field_id' => $morefield_meta_array[$morec]['field_id']), array('field_id = ?' => $morefield->field_id));

                    if ($morefield_meta_array[$morec]['type'] == 'select' || $morefield_meta_array[$morec]['type'] == 'radio' || $morefield_meta_array[$morec]['type'] == 'multiselect' || $morefield_meta_array[$morec]['type'] == 'multi_checkbox') {
                      $morefield_options_array = $db->select()
                              ->from('engine4_recipe_fields_options')
                              ->where('field_id = ?', $morefield_meta_array[$morec]['field_id'])
                              ->query()
                              ->fetchAll();
                      $morefield_options_order = 0;
                      foreach ($morefield_options_array as $morefield_options) {
                        $morefield_options_order++;
                        $morefield = Engine_Api::_()->fields()->getField($morefield->field_id, 'sitereview_listing');
                        $moreoption = Engine_Api::_()->fields()->createOption('sitereview_listing', $morefield, array(
                            'label' => $morefield_options['label'],
                            'order' => $morefield_options_order,
                                ));
                      }
                    }
                  }
                }
              }
            }

            //START FETCH CATEGORY WORK
            $selectReviewCategory = $reviewCategoryTable->select()
                    ->from($reviewCategoryTableName, 'category_name')
                    ->where('category_name != ?', '')
                    ->where('listingtype_id = ?', $listingtype_id)
                    ->where('cat_dependency = ?', 0);
            $reviewCategoryDatas = $reviewCategoryTable->fetchAll($selectReviewCategory);
            if (!empty($reviewCategoryDatas)) {
              $reviewCategoryDatas = $reviewCategoryDatas->toArray();
            }

            $reviewCategoryInArrayData = array();
            foreach ($reviewCategoryDatas as $reviewCategoryData) {
              $reviewCategoryInArrayData[] = $reviewCategoryData['category_name'];
            }

            $selectRecipeCategory = $recipeCategoryTable->select()
                    ->from($recipeCategoryTableName)
                    ->where('category_name != ?', '')
                    ->where('cat_dependency = ?', 0);
            $recipeCategoryDatas = $recipeCategoryTable->fetchAll($selectRecipeCategory);
            if (!empty($recipeCategoryDatas)) {
              $recipeCategoryDatas = $recipeCategoryDatas->toArray();
              foreach ($recipeCategoryDatas as $recipeCategoryData) {

                //RENAME THE CATEGORY IN SITEREVIEW TABLE IF ALREADY EXIST
                if (in_array($recipeCategoryData['category_name'], $reviewCategoryInArrayData)) {
                  $reviewCategoryTable->update(array('category_name' => $recipeCategoryData['category_name'] . "_old"), array('category_name = ?' => $recipeCategoryData['category_name'], 'listingtype_id = ?' => $listingtype_id));
                }

                if (!in_array($recipeCategoryData['category_name'], $reviewCategoryInArrayData)) {
                  $newCategory = $reviewCategoryTable->createRow();
                  $newCategory->listingtype_id = $listingtype_id;
                  $newCategory->category_name = $recipeCategoryData['category_name'];
                  $newCategory->cat_dependency = 0;
                  $newCategory->cat_order = 9999;
                  $newCategory->profile_type = $recipe_profile_type;
                  $newCategory->save();

                  $newCategory->afterCreate();

                  $selectRecipeSubCategory = $recipeCategoryTable->select()
                          ->from($recipeCategoryTableName)
                          ->where('category_name != ?', '')
                          ->where('cat_dependency = ?', $recipeCategoryData['category_id']);
                  $recipeSubCategoryDatas = $recipeCategoryTable->fetchAll($selectRecipeSubCategory);
                  foreach ($recipeSubCategoryDatas as $recipeSubCategoryData) {
                    $newSubCategory = $reviewCategoryTable->createRow();
                    $newSubCategory->listingtype_id = $listingtype_id;
                    $newSubCategory->category_name = $recipeSubCategoryData->category_name;
                    $newSubCategory->cat_dependency = $newCategory->category_id;
                    $newSubCategory->cat_order = 9999;
                    $subcategory_id = $newSubCategory->save();
                    $newSubCategory->afterCreate();
                  }
                }
              }
            }
            $db->commit();
          } catch (Exception $e) {
            $db->rollBack();

            //DELETE MAPPING COLUMN IN SITEREVIEW TABLE
            $imported_listing_id = $db->query("SHOW COLUMNS FROM engine4_sitereview_listings LIKE 'imported_recipe_id'")->fetch();
            if (!empty($imported_listing_id)) {
              $db->query("ALTER TABLE `engine4_sitereview_listings` DROP `imported_recipe_id`");
            }

            throw $e;
          }
        }
        //DO NOT RUN THE UPPER CODE IN RECALL   
        //START RECIPE IMPORTING WORK
        $selectRecipes = $recipeTable->select()
                ->where('recipe_id >= ?', $recipe_assigned_previous_id)
                ->from($recipeTableName, 'recipe_id')
                ->where('is_review_import != ?', 1)
                ->where('category_id != ?', 0)
                ->order('recipe_id ASC');
        $recipeDatas = $recipeTable->fetchAll($selectRecipes);

        $next_import_count = 0;

        foreach ($recipeDatas as $recipeData) {
          $recipe_id = $recipeData->recipe_id;

          if (!empty($recipe_id)) {

            $recipe = Engine_Api::_()->getItem('recipe', $recipe_id);

            $sitereview = $reviewTable->createRow();
            $sitereview->title = $recipe->title;

            if ($listingtypeArray->body_allow)
              $sitereview->body = $recipe->body;

            $sitereview->owner_id = $recipe->owner_id;
            $sitereview->listingtype_id = $listingtype_id;

            //START FETCH LIST CATEGORY AND SUB-CATEGORY
            if (!empty($recipe->category_id)) {
              $recipeCategory = $recipeCategoryTable->fetchRow(array('category_id = ?' => $recipe->category_id, 'cat_dependency = ?' => 0));
              if (!empty($recipeCategory)) {
                $recipeCategoryName = $recipeCategory->category_name;

                if (!empty($recipeCategoryName)) {
                  $reviewCategory = $reviewCategoryTable->fetchRow(array('category_name = ?' => $recipeCategoryName, 'cat_dependency = ?' => 0, 'listingtype_id =?' => $listingtype_id));
                  if (!empty($reviewCategory)) {
                    $reviewCategoryId = $sitereview->category_id = $reviewCategory->category_id;

                    $recipeSubCategory = $recipeCategoryTable->fetchRow(array('category_id = ?' => $recipe->subcategory_id, 'cat_dependency = ?' => $recipe->category_id));
                    if (!empty($recipeSubCategory)) {
                      $recipeSubCategoryName = $recipeSubCategory->category_name;

                      $reviewSubCategory = $reviewCategoryTable->fetchRow(array('category_name = ?' => $recipeSubCategoryName, 'cat_dependency = ?' => $reviewCategoryId, 'listingtype_id =?' => $listingtype_id));
                      if (!empty($reviewSubCategory)) {
                        $sitereview->subcategory_id = $reviewSubCategory->category_id;
                      }
                    }
                  }
                }
              }
            } else {
              continue;
            }
            //END FETCH LIST CATEGORY AND SUB-CATEGORY

            $sitereview->profile_type = 0;

            $sitereview->photo_id = 0;

            //START FETCH PRICE
            if (!empty($metaData)) {
              $field_id = $metaData->field_id;

              $valueTable = Engine_Api::_()->fields()->getTable('recipe', 'values');
              $selectValueData = $valueTable->select()->where('item_id = ?', $recipe_id)->where('field_id = ?', $field_id);
              $valueData = $valueTable->fetchRow($selectValueData);
              if (!empty($valueData) && ($listingtypeArray->price)) {
                $sitereview->price = $valueData->value;
              }
            }
            //END FETCH PRICE
            //START GET DATA FROM LISTING
            $sitereview->creation_date = $recipe->creation_date;
            $sitereview->modified_date = $recipe->modified_date;
            $sitereview->approved = $recipe->approved;
            $sitereview->featured = $recipe->featured;
            $sitereview->sponsored = $recipe->sponsored;

            $sitereview->view_count = 1;
            if ($recipe->view_count > 0) {
              $sitereview->view_count = $recipe->view_count;
            }

            $sitereview->comment_count = $recipe->comment_count;
            $sitereview->like_count = $recipe->like_count;
            $sitereview->review_count = $recipe->rate_count;
            $sitereview->closed = $recipe->closed;
            $sitereview->draft = !$recipe->draft;

            if (!empty($recipe->aprrove_date)) {
              $sitereview->approved_date = $recipe->aprrove_date;
            }

            $sitereview->rating_avg = round($recipe->rating, 4);
            $sitereview->rating_users = round($recipe->rating, 4);
            $sitereview->save();

            $sitereview->creation_date = $recipe->creation_date;
            $sitereview->save();

            //FATCH REVIEW CATEGORIES
            $categoryIdsArray = array();
            $categoryIdsArray[] = $sitereview->category_id;
            $categoryIdsArray[] = $sitereview->subcategory_id;
            $categoryIdsArray[] = $sitereview->subsubcategory_id;
            $sitereview->profile_type = $reviewCategoryTable->getProfileType($categoryIdsArray, 0, 'profile_type');
            $sitereview->search = $recipe->search;
            $sitereview->save();

            //START FETCH CUSTOM FIELD VALUES
            if (!empty($sitereview->profile_type)) {
              $reviewFieldValueTable->insert(array('item_id' => $sitereview->listing_id, 'field_id' => 1, 'index' => 0, 'value' => $sitereview->profile_type));
              $fieldValueSelect = $reviewMetaTable->select()
                      ->setIntegrityCheck(false)
                      ->from($reviewMetaTableName, array('field_id', 'type'))
                      ->joinInner($recipeFieldValueTableName, "$recipeFieldValueTableName.field_id = $reviewMetaTableName.recipe_field_id", array('value', 'index', 'field_id as recipe_field_id'))
                      ->where("$recipeFieldValueTableName.item_id = ?", $recipe_id);
              $fieldValues = $reviewMetaTable->fetchAll($fieldValueSelect);
              foreach ($fieldValues as $fieldValue) {
                if ($fieldValue->type != 'multi_checkbox' && $fieldValue->type != 'multiselect' && $fieldValue->type != 'radio' && $fieldValue->type != 'select') {
                  $reviewFieldValueTable->insert(array('item_id' => $sitereview->listing_id, 'field_id' => $fieldValue->field_id, 'index' => $fieldValue->index, 'value' => $fieldValue->value));
                } else {

                  $recipeFieldValues = $db->select()
                          ->from('engine4_recipe_fields_options')
                          ->where('field_id = ?', $fieldValue->recipe_field_id)
                          ->query()
                          ->fetchAll(Zend_Db::FETCH_COLUMN);

                  $sitereviewFieldValues = $db->select()
                          ->from('engine4_sitereview_listing_fields_options')
                          ->where('field_id = ?', $fieldValue->field_id)
                          ->query()
                          ->fetchAll(Zend_Db::FETCH_COLUMN);

                  $mergeFieldValues = array_combine($sitereviewFieldValues, $recipeFieldValues);
                  $value = array_search($fieldValue->value, $mergeFieldValues);
                  if (!empty($value)) {
                    $reviewFieldValueTable->insert(array('item_id' => $sitereview->listing_id, 'field_id' => $fieldValue->field_id, 'index' => $fieldValue->index, 'value' => $value));
                  }
                }
              }

              //FETCH THE VALUE OF START AND END MONTH
              $months = array("01" => "January", "02" => "February", "03" => "March", "04" => "April", "05" => "May", "06" => "June", "07" => "July", "08" => "August", "09" => "September", "10" => "October", "11" => "November", "12" => "December");
              if ($recipe->start_month) {
                $startMonthFieldId = $db->select()
                        ->from('engine4_sitereview_listing_fields_meta', 'field_id')
                        ->where('type = ?', 'select')
                        ->where('label = ?', 'Season Starting Month')
                        ->order('field_id ASC')
                        ->query()
                        ->fetchColumn();
                $startMonth = $months["$recipe->start_month"];
                if ($startMonth && $startMonthFieldId) {
                  $startMonthOptionId = $db->select()
                          ->from('engine4_sitereview_listing_fields_options', 'option_id')
                          ->where('field_id = ?', $startMonthFieldId)
                          ->where('label = ?', $startMonth)
                          ->query()
                          ->fetchColumn();
                  if ($startMonthOptionId) {
                    $reviewFieldValueTable->insert(array('item_id' => $sitereview->listing_id, 'field_id' => $startMonthFieldId, 'value' => $startMonthOptionId));
                  }
                }
              }

              if ($recipe->end_month) {
                $endMonthFieldId = $db->select()
                        ->from('engine4_sitereview_listing_fields_meta', 'field_id')
                        ->where('type = ?', 'select')
                        ->where('label = ?', 'Season Ending Month')
                        ->order('field_id ASC')
                        ->query()
                        ->fetchColumn();
                $endMonth = $months["$recipe->end_month"];
                if ($endMonth && $endMonthFieldId) {
                  $endMonthOptionId = $db->select()
                          ->from('engine4_sitereview_listing_fields_options', 'option_id')
                          ->where('field_id = ?', $endMonthFieldId)
                          ->where('label = ?', $endMonth)
                          ->query()
                          ->fetchColumn();
                  if ($endMonthOptionId) {
                    $reviewFieldValueTable->insert(array('item_id' => $sitereview->listing_id, 'field_id' => $endMonthFieldId, 'value' => $endMonthOptionId));
                  }
                }
              }
            }
            //END FETCH CUSTOM FIELD VALUES             

            $recipe->is_review_import = 1;
            $recipe->save();
            $next_import_count++;
            //END GET DATA FROM LISTING
            //GENERATE ACITIVITY FEED
            if ($sitereview->draft == 0 && $activity_recipe && $sitereview->search) {
              $action = $activityTable->addActivity($sitereview->getOwner(), $sitereview, 'sitereview_new_listtype_' . $listingtype_id);
              $action->date = $sitereview->creation_date;
              $action->save();
              if ($action != null) {
                $activityTable->attachActivity($action, $sitereview);
              }
            }

            $row = $otherinfoTable->getOtherinfo($sitereview->getIdentity());

            if (empty($row)) {
              $about = "";
              $overview = "";

              //START FETCH engine4_list_writes DATA
              $writeData = $writeTable->fetchRow(array('recipe_id = ?' => $recipe_id));
              if (!empty($writeData)) {
                $about = $writeData->text;
              }
              //END FETCH engine4_list_writes DATAS

              if ($recipe->overview && $listingtypeArray->overview) {
                $overview = $recipe->overview;
              }

              Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->insert(array(
                  'listing_id' => $sitereview->getIdentity(),
                  'overview' => $overview,
                  'about' => $about
              ));
            }

            $locationData = $recipeLocationTable->fetchRow(array('recipe_id = ?' => $recipe_id));
            if (!empty($locationData) && $listingtypeArray->location) {
              $sitereview->location = $locationData->location;
              $sitereview->save();

              $reviewLocation = $reviewLocationTable->createRow();
              $reviewLocation->listing_id = $sitereview->listing_id;
              $reviewLocation->location = $sitereview->location;
              $reviewLocation->latitude = $locationData->latitude;
              $reviewLocation->longitude = $locationData->longitude;
              $reviewLocation->formatted_address = $locationData->formatted_address;
              $reviewLocation->country = $locationData->country;
              $reviewLocation->state = $locationData->state;
              $reviewLocation->zipcode = $locationData->zipcode;
              $reviewLocation->city = $locationData->city;
              $reviewLocation->address = $locationData->address;
              $reviewLocation->zoom = $locationData->zoom;
              $reviewLocation->save();
            }

            //START FETCH TAG
            $recipeTags = $recipe->tags()->getTagMaps();
            $tagString = '';

            foreach ($recipeTags as $tagmap) {

              if ($tagString != '')
                $tagString .= ', ';
              $tagString .= $tagmap->getTag()->getTitle();

              $tags = array_filter(array_map("trim", preg_split('/[,]+/', $tagString)));
              $sitereview->tags()->setTagMaps(Engine_Api::_()->getItem('user', $recipe->owner_id), $tags);
            }
            //END FETCH TAG
            //START FETCH LIKES
            $selectLike = $likeTable->select()
                    ->from($likeTableName, 'like_id')
                    ->where('resource_type = ?', 'recipe')
                    ->where('resource_id = ?', $recipe_id);
            $selectLikeDatas = $likeTable->fetchAll($selectLike);
            foreach ($selectLikeDatas as $selectLikeData) {
              $like = Engine_Api::_()->getItem('core_like', $selectLikeData->like_id);

              $newLikeEntry = $likeTable->createRow();
              $newLikeEntry->resource_type = 'sitereview_listing';
              $newLikeEntry->resource_id = $sitereview->listing_id;
              $newLikeEntry->poster_type = 'user';
              $newLikeEntry->poster_id = $like->poster_id;
              $newLikeEntry->creation_date = $like->creation_date;
              $newLikeEntry->save();

              $newLikeEntry->creation_date = $like->creation_date;
              $newLikeEntry->save();
            }
            //END FETCH LIKES
            //START FETCH COMMENTS
            $selectLike = $commentTable->select()
                    ->from($commentTableName, 'comment_id')
                    ->where('resource_type = ?', 'recipe')
                    ->where('resource_id = ?', $recipe_id);
            $selectLikeDatas = $commentTable->fetchAll($selectLike);
            foreach ($selectLikeDatas as $selectLikeData) {
              $comment = Engine_Api::_()->getItem('core_comment', $selectLikeData->comment_id);

              $newLikeEntry = $commentTable->createRow();
              $newLikeEntry->resource_type = 'sitereview_listing';
              $newLikeEntry->resource_id = $sitereview->listing_id;
              $newLikeEntry->poster_type = 'user';
              $newLikeEntry->poster_id = $comment->poster_id;
              $newLikeEntry->body = $comment->body;
              $newLikeEntry->creation_date = $comment->creation_date;
              $newLikeEntry->like_count = $comment->like_count;
              $newLikeEntry->save();

              $newLikeEntry->creation_date = $comment->creation_date;
              $newLikeEntry->save();
            }
            //END FETCH COMMENTS
            //START FETCH PRIVACY
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

            foreach ($roles as $role) {
              if ($auth->isAllowed($recipe, $role, 'view')) {
                $values['auth_view'] = $role;
              }
            }

            foreach ($roles as $role) {
              if ($auth->isAllowed($recipe, $role, 'photo')) {
                $values['auth_photo'] = $role;
              }
            }

            foreach ($roles as $role) {
              if ($auth->isAllowed($recipe, $role, 'video')) {
                $values['auth_video'] = $role;
              }
            }

            $viewMax = array_search($values['auth_view'], $roles);
            $photoMax = array_search($values['auth_photo'], $roles);
            $videoMax = array_search($values['auth_video'], $roles);

            foreach ($roles as $i => $role) {
              $auth->setAllowed($sitereview, $role, 'view', ($i <= $viewMax));
              $auth->setAllowed($sitereview, $role, "view_listtype_$listingtype_id", ($i <= $viewMax));
              $auth->setAllowed($sitereview, $role, "photo_listtype_$listingtype_id", ($i <= $photoMax));
              $auth->setAllowed($sitereview, $role, "video_listtype_$listingtype_id", ($i <= $videoMax));
            }

            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
            foreach ($roles as $role) {
              if ($auth->isAllowed($recipe, $role, 'comment')) {
                $values['auth_comment'] = $role;
              }
            }
            $commentMax = array_search($values['auth_comment'], $roles);
            foreach ($roles as $i => $role) {
              $auth->setAllowed($sitereview, $role, 'comment', ($i <= $commentMax));
              $auth->setAllowed($sitereview, $role, "comment_listtype_$listingtype_id", ($i <= $commentMax));
            }
            //END FETCH PRIVACY

            $topicSelect = $topicTable->select()
                    ->from($topicTableName)
                    ->where('recipe_id = ?', $recipe_id);
            $topicSelectDatas = $topicTable->fetchAll($topicSelect);
            if (!empty($topicSelectDatas)) {
              $topicSelectDatass = $topicSelectDatas->toArray();

              foreach ($topicSelectDatass as $topicSelectData) {
                $reviewTopic = $reviewTopicTable->createRow();
                $reviewTopic->listing_id = $sitereview->listing_id;
                $reviewTopic->user_id = $topicSelectData['user_id'];
                $reviewTopic->title = $topicSelectData['title'];
                $reviewTopic->creation_date = $topicSelectData['creation_date'];
                $reviewTopic->modified_date = $topicSelectData['modified_date'];
                $reviewTopic->sticky = $topicSelectData['sticky'];
                $reviewTopic->closed = $topicSelectData['closed'];
                $reviewTopic->view_count = $topicSelectData['view_count'];
                $reviewTopic->lastpost_id = $topicSelectData['lastpost_id'];
                $reviewTopic->lastposter_id = $topicSelectData['lastposter_id'];
                $reviewTopic->save();

                $reviewTopic->creation_date = $topicSelectData['creation_date'];
                $reviewTopic->save();

                //GENERATE ACTIVITY FEED
                if ($activity_recipe) {
                  $action = $activityTable->addActivity($reviewTopic->getOwner(), $reviewTopic, 'sitereview_topic_create_listtype_' . $listingtype_id);
                  $action->date = $reviewTopic->creation_date;
                  $action->save();
                  if ($action) {
                    $action->attach($reviewTopic);
                  }
                }

                //START FETCH TOPIC POST'S
                $postSelect = $postTable->select()
                        ->from($postTableName)
                        ->where('topic_id = ?', $topicSelectData['topic_id'])
                        ->where('recipe_id = ?', $recipe_id);
                $postSelectDatas = $postTable->fetchAll($postSelect);
                if (!empty($postSelectDatas)) {
                  $postSelectDatass = $postSelectDatas->toArray();

                  foreach ($postSelectDatass as $postSelectData) {
                    $reviewPost = $reviewPostTable->createRow();
                    $reviewPost->topic_id = $reviewTopic->topic_id;
                    $reviewPost->listing_id = $sitereview->listing_id;
                    $reviewPost->user_id = $postSelectData['user_id'];
                    $reviewPost->body = $postSelectData['body'];
                    $reviewPost->creation_date = $postSelectData['creation_date'];
                    $reviewPost->modified_date = $postSelectData['modified_date'];
                    $reviewPost->save();

                    $reviewPost->creation_date = $postSelectData['creation_date'];
                    $reviewPost->save();
                  }
                }
                //END FETCH TOPIC POST'S

                $reviewTopic->post_count = $topicSelectData['post_count'];
                $reviewTopic->save();

                //START FETCH TOPIC WATCH
                $topicWatchData = $topicWatchesTable->fetchAll(array('resource_id = ?' => $recipe_id));
                foreach ($topicWatchData as $watchData) {
                  if (!empty($watchData)) {
                    $topicwatchSelect = $reviewTopicWatchesTable->select()
                            ->from($reviewTopicWatchesTableName)
                            ->where('resource_id = ?', $reviewTopic->listing_id)
                            ->where('topic_id = ?', $reviewTopic->topic_id)
                            ->where('user_id = ?', $watchData->user_id);
                    $topicwatchSelectDatas = $reviewTopicWatchesTable->fetchRow($topicwatchSelect);

                    if (empty($topicwatchSelectDatas)) {
                      $reviewTopicWatchesTable->insert(array(
                          'resource_id' => $reviewTopic->listing_id,
                          'topic_id' => $reviewTopic->topic_id,
                          'user_id' => $watchData->user_id,
                          'watch' => $watchData->watch
                      ));
                    }
                  }
                }
                //END FETCH TOPIC WATCH
              }
            }

            //START FETCH PHOTO DATA
            $selectRecipePhoto = $recipePhotoTable->select()
                    ->from($recipePhotoTable->info('name'))
                    ->where('recipe_id = ?', $recipe_id);
            $recipePhotoDatas = $recipePhotoTable->fetchAll($selectRecipePhoto);

            $sitereview = Engine_Api::_()->getItem('sitereview_listing', $sitereview->listing_id);

            if (!empty($recipePhotoDatas)) {

              $recipePhotoDatas = $recipePhotoDatas->toArray();

              if (empty($recipe->photo_id)) {
                foreach ($recipePhotoDatas as $recipePhotoData) {
                  $recipe->photo_id = $recipePhotoData['photo_id'];
                  break;
                }
              }

              if (!empty($recipe->photo_id)) {
                $recipePhotoData = $recipePhotoTable->fetchRow(array('file_id = ?' => $recipe->photo_id));
                if (!empty($recipePhotoData)) {
                  $storageData = $storageTable->fetchRow(array('file_id = ?' => $recipePhotoData->file_id));

                  if (!empty($storageData) && !empty($storageData->storage_path)) {

                    $sitereview->setPhoto($storageData->storage_path);

                    $album_id = $albumTable->update(array('photo_id' => $sitereview->photo_id), array('listing_id = ?' => $sitereview->listing_id));

                    $reviewProfilePhoto = Engine_Api::_()->getDbTable('photos', 'sitereview')->fetchRow(array('file_id = ?' => $sitereview->photo_id));
                    if (!empty($reviewProfilePhoto)) {
                      $reviewProfilePhotoId = $reviewProfilePhoto->photo_id;
                    } else {
                      $reviewProfilePhotoId = $sitereview->photo_id;
                    }

                    //START FETCH LIKES
                    $selectLike = $likeTable->select()
                            ->from($likeTableName, 'like_id')
                            ->where('resource_type = ?', 'recipe_photo')
                            ->where('resource_id = ?', $recipe->photo_id);
                    $selectLikeDatas = $likeTable->fetchAll($selectLike);
                    foreach ($selectLikeDatas as $selectLikeData) {
                      $like = Engine_Api::_()->getItem('core_like', $selectLikeData->like_id);
                      $newLikeEntry = $likeTable->createRow();
                      $newLikeEntry->resource_type = 'sitereview_photo';
                      $newLikeEntry->resource_id = $reviewProfilePhotoId;
                      $newLikeEntry->poster_type = 'user';
                      $newLikeEntry->poster_id = $like->poster_id;
                      $newLikeEntry->creation_date = $like->creation_date;
                      $newLikeEntry->save();

                      $newLikeEntry->creation_date = $like->creation_date;
                      $newLikeEntry->save();
                    }
                    //END FETCH LIKES
                    //START FETCH COMMENTS
                    $selectLike = $commentTable->select()
                            ->from($commentTableName, 'comment_id')
                            ->where('resource_type = ?', 'recipe_photo')
                            ->where('resource_id = ?', $recipe->photo_id);
                    $selectLikeDatas = $commentTable->fetchAll($selectLike);
                    foreach ($selectLikeDatas as $selectLikeData) {
                      $comment = Engine_Api::_()->getItem('core_comment', $selectLikeData->comment_id);

                      $newLikeEntry = $commentTable->createRow();
                      $newLikeEntry->resource_type = 'sitereview_photo';
                      $newLikeEntry->resource_id = $reviewProfilePhotoId;
                      $newLikeEntry->poster_type = 'user';
                      $newLikeEntry->poster_id = $comment->poster_id;
                      $newLikeEntry->body = $comment->body;
                      $newLikeEntry->creation_date = $comment->creation_date;
                      $newLikeEntry->like_count = $comment->like_count;
                      $newLikeEntry->save();

                      $newLikeEntry->creation_date = $comment->creation_date;
                      $newLikeEntry->save();
                    }
                    //END FETCH COMMENTS
                    //START FETCH TAGGER DETAIL
                    $tagmapsTable = Engine_Api::_()->getDbtable('TagMaps', 'core');
                    $tagmapsTableName = $tagmapsTable->info('name');
                    $selectTagmaps = $tagmapsTable->select()
                            ->from($tagmapsTableName, 'tagmap_id')
                            ->where('resource_type = ?', 'recipe_photo')
                            ->where('resource_id = ?', $recipe->photo_id);
                    $selectTagmapsDatas = $tagmapsTable->fetchAll($selectTagmaps);
                    foreach ($selectTagmapsDatas as $selectTagmapsData) {
                      $tagMap = Engine_Api::_()->getItem('core_tag_map', $selectTagmapsData->tagmap_id);

                      $newTagmapEntry = $tagmapsTable->createRow();
                      $newTagmapEntry->resource_type = 'sitereview_photo';
                      $newTagmapEntry->resource_id = $reviewProfilePhotoId;
                      $newTagmapEntry->tagger_type = 'user';
                      $newTagmapEntry->tagger_id = $tagMap->tagger_id;
                      $newTagmapEntry->tag_type = 'user';
                      $newTagmapEntry->tag_id = $tagMap->tag_id;
                      $newTagmapEntry->creation_date = $tagMap->creation_date;
                      $newTagmapEntry->extra = $tagMap->extra;
                      $newTagmapEntry->save();

                      $newTagmapEntry->creation_date = $tagMap->creation_date;
                      $newTagmapEntry->save();
                    }
                    //END FETCH TAGGER DETAIL
                  }
                }

                $fetchDefaultAlbum = $albumTable->fetchRow(array('listing_id = ?' => $sitereview->listing_id));
                if (!empty($fetchDefaultAlbum)) {

                  $selectRecipePhoto = $recipePhotoTable->select()
                          ->from($recipePhotoTable->info('name'))
                          ->where('recipe_id = ?', $recipe_id);
                  $recipePhotoDatas = $recipePhotoTable->fetchAll($selectRecipePhoto);

                  $order = 999;
                  foreach ($recipePhotoDatas as $recipePhotoData) {

                    if ($recipePhotoData['file_id'] != $recipe->photo_id) {
                      $params = array(
                          'collection_id' => $fetchDefaultAlbum->album_id,
                          'album_id' => $fetchDefaultAlbum->album_id,
                          'listing_id' => $sitereview->listing_id,
                          'user_id' => $recipePhotoData['user_id'],
                          'order' => $order,
                      );

                      $storageData = $storageTable->fetchRow(array('file_id = ?' => $recipePhotoData['file_id']));
                      if (!empty($storageData) && !empty($storageData->storage_path)) {
                        $file = array();
                        $file['tmp_name'] = $storageData->storage_path;
                        $path_array = explode('/', $file['tmp_name']);
                        $file['name'] = end($path_array);

                        $reviewPhoto = Engine_Api::_()->sitereview()->createPhoto($params, $file);
                        if (!empty($reviewPhoto)) {

                          $order++;

                          //START FETCH LIKES
                          $selectLike = $likeTable->select()
                                  ->from($likeTableName, 'like_id')
                                  ->where('resource_type = ?', 'recipe_photo')
                                  ->where('resource_id = ?', $recipePhotoData['photo_id']);
                          $selectLikeDatas = $likeTable->fetchAll($selectLike);
                          foreach ($selectLikeDatas as $selectLikeData) {
                            $like = Engine_Api::_()->getItem('core_like', $selectLikeData->like_id);

                            $newLikeEntry = $likeTable->createRow();
                            $newLikeEntry->resource_type = 'sitereview_photo';
                            $newLikeEntry->resource_id = $reviewPhoto->photo_id;
                            $newLikeEntry->poster_type = 'user';
                            $newLikeEntry->poster_id = $like->poster_id;
                            $newLikeEntry->creation_date = $like->creation_date;
                            $newLikeEntry->save();

                            $newLikeEntry->creation_date = $like->creation_date;
                            $newLikeEntry->save();
                          }
                          //END FETCH LIKES
                          //START FETCH COMMENTS
                          $selectLike = $commentTable->select()
                                  ->from($commentTableName, 'comment_id')
                                  ->where('resource_type = ?', 'recipe_photo')
                                  ->where('resource_id = ?', $recipePhotoData['photo_id']);
                          $selectLikeDatas = $commentTable->fetchAll($selectLike);
                          foreach ($selectLikeDatas as $selectLikeData) {
                            $comment = Engine_Api::_()->getItem('core_comment', $selectLikeData->comment_id);

                            $newLikeEntry = $commentTable->createRow();
                            $newLikeEntry->resource_type = 'sitereview_photo';
                            $newLikeEntry->resource_id = $reviewPhoto->photo_id;
                            $newLikeEntry->poster_type = 'user';
                            $newLikeEntry->poster_id = $comment->poster_id;
                            $newLikeEntry->body = $comment->body;
                            $newLikeEntry->creation_date = $comment->creation_date;
                            $newLikeEntry->like_count = $comment->like_count;
                            $newLikeEntry->save();

                            $newLikeEntry->creation_date = $comment->creation_date;
                            $newLikeEntry->save();
                          }
                          //END FETCH COMMENTS
                          //START FETCH TAGGER DETAIL
                          $selectTagmaps = $tagmapsTable->select()
                                  ->from($tagmapsTableName, 'tagmap_id')
                                  ->where('resource_type = ?', 'recipe_photo')
                                  ->where('resource_id = ?', $recipePhotoData['photo_id']);
                          $selectTagmapsDatas = $tagmapsTable->fetchAll($selectTagmaps);
                          foreach ($selectTagmapsDatas as $selectTagmapsData) {
                            $tagMap = Engine_Api::_()->getItem('core_tag_map', $selectTagmapsData->tagmap_id);

                            $newTagmapEntry = $tagmapsTable->createRow();
                            $newTagmapEntry->resource_type = 'sitereview_photo';
                            $newTagmapEntry->resource_id = $reviewPhoto->photo_id;
                            $newTagmapEntry->tagger_type = 'user';
                            $newTagmapEntry->tagger_id = $tagMap->tagger_id;
                            $newTagmapEntry->tag_type = 'user';
                            $newTagmapEntry->tag_id = $tagMap->tag_id;
                            $newTagmapEntry->creation_date = $tagMap->creation_date;
                            $newTagmapEntry->extra = $tagMap->extra;
                            $newTagmapEntry->save();

                            $newTagmapEntry->creation_date = $tagMap->creation_date;
                            $newTagmapEntry->save();
                          }
                          //END FETCH TAGGER DETAIL
                        }
                      }
                    }
                  }
                }
              }

              //GENERATE ACTIVITY FEED
              if ($activity_recipe) {

                $select = $reviewPhotoTable->select()->from($reviewPhotoTableName)->where('user_id = ?', $sitereview->owner_id)->where('listing_id = ?', $sitereview->listing_id);
                $reviewPhotos = $reviewPhotoTable->fetchAll($select);
                $count = count($reviewPhotos);
                if ($count > 1) {
                  $action = $activityTable->addActivity($sitereview->getOwner(), $sitereview, 'sitereview_photo_upload_listtype_' . $listingtype_id, null, array('count' => $count, 'title' => $sitereview->title));
                  $count = 0;
                  foreach ($reviewPhotos as $reviewPhoto) {

                    if ($action instanceof Activity_Model_Action && $count < 8) {
                      $activityTable->attachActivity($action, $reviewPhoto, Activity_Model_Action::ATTACH_MULTI);
                    }
                    $count++;
                  }
                  $action->date = $reviewPhoto->creation_date;
                  $action->save();
                }
              }
            }
            //END FETCH PHOTO DATA
            //START FETCH REVIEW DATA
            if ($listingtypeArray->reviews == 2 || $listingtypeArray->reviews == 3) {
              $recipereviewTableSelect = $recipereviewTable->select()
                      ->from($recipereviewTableName, array('MAX(review_id) as review_id', 'owner_id', 'title', 'creation_date', 'modified_date', 'body'))
                      ->where('recipe_id = ?', $recipe_id)
                      ->group('owner_id')
                      ->order('review_id ASC');
              $recipereviewSelectDatas = $recipereviewTable->fetchAll($recipereviewTableSelect);
              if (!empty($recipereviewSelectDatas)) {
                $recipereviewSelectDatas = $recipereviewSelectDatas->toArray();
                foreach ($recipereviewSelectDatas as $recipereviewSelectData) {
                  $review = Engine_Api::_()->getItem('recipe_reviews', $recipereviewSelectData['review_id']);
                  if (!$listingtypeArray->allow_owner_review && $review->owner_id == $recipe->owner_id) {
                    continue;
                  }
                  $reviewReview = $reviewReviewTable->createRow();
                  $reviewReview->resource_id = $sitereview->listing_id;
                  $reviewReview->resource_type = $sitereview->getType();
                  $reviewReview->owner_id = $review->owner_id;
                  $reviewReview->title = $review->title;
                  $reviewReview->body = $review->body;
                  $reviewReview->view_count = 1;
                  if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.recommend', 1)) {
                    $reviewReview->recommend = 1;
                  } else {
                    $reviewReview->recommend = 0;
                  }
                  $reviewReview->creation_date = $review->creation_date;
                  $reviewReview->modified_date = $review->modified_date;
                  $reviewReview->type = 'user';
                  $reviewReview->save();

                  $reviewReview->creation_date = $review->creation_date;
                  $reviewReview->save();

                  //GENERATE ACTIVITY FEED
                  if ($activity_recipe) {
                    $action = $activityTable->addActivity($reviewReview->getOwner(), $sitereview, 'sitereview_review_add_listtype_' . $listingtype_id);
                    $action->date = $reviewReview->creation_date;
                    $action->save();
                    if ($action != null) {
                      $activityTable->attachActivity($action, $reviewReview);
                    }
                  }

                  //FATCH REVIEW CATEGORIES
                  $categoryIdsArray = array();
                  $categoryIdsArray[] = $sitereview->category_id;
                  $categoryIdsArray[] = $sitereview->subcategory_id;
                  $categoryIdsArray[] = $sitereview->subsubcategory_id;
                  $reviewReview->profile_type_review = $reviewCategoryTable->getProfileType($categoryIdsArray, 0, 'profile_type_review');
                  $reviewReview->save();

                  $reviewRating = $reviewRatingTable->createRow();
                  $reviewRating->review_id = $reviewReview->review_id;
                  $reviewRating->category_id = $sitereview->category_id;
                  $reviewRating->resource_id = $sitereview->listing_id;
                  $reviewRating->resource_type = $sitereview->getType();
                  $reviewRating->user_id = $review->owner_id;
                  $reviewRating->type = 'user';
                  $reviewRating->ratingparam_id = 0;
                  $reviewRating->rating = round($recipe->rating, 4);
                  $reviewRating->save();
                }
              }
            }
            //END FETCH REVIEW DATA
            //START FETCH VIDEO DATA
            if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('video')) {

              $selectRecipeVideos = $recipeVideoTable->select()
                      ->from($recipeVideoTableName, 'video_id')
                      ->where('recipe_id = ?', $recipe_id)
                      ->group('video_id');
              $recipeVideoDatas = $recipeVideoTable->fetchAll($selectRecipeVideos);
              foreach ($recipeVideoDatas as $recipeVideoData) {
                $recipeVideo = Engine_Api::_()->getItem('video', $recipeVideoData->video_id);
                if (!empty($recipeVideo)) {
                  $db = $reviewVideoTable->getAdapter();
                  $db->beginTransaction();

                  try {
                    $reviewVideo = $reviewVideoTable->createRow();
                    $reviewVideo->listing_id = $sitereview->listing_id;
                    $reviewVideo->title = $recipeVideo->title;
                    $reviewVideo->description = $recipeVideo->description;
                    $reviewVideo->search = $recipeVideo->search;
                    $reviewVideo->owner_id = $recipeVideo->owner_id;
                    $reviewVideo->creation_date = $recipeVideo->creation_date;
                    $reviewVideo->modified_date = $recipeVideo->modified_date;

                    $reviewVideo->view_count = 1;
                    if ($recipeVideo->view_count > 0) {
                      $reviewVideo->view_count = $recipeVideo->view_count;
                    }

                    $reviewVideo->comment_count = $recipeVideo->comment_count;
                    $reviewVideo->type = $recipeVideo->type;
                    $reviewVideo->code = $recipeVideo->code;
                    $reviewVideo->rating = $recipeVideo->rating;
                    $reviewVideo->status = $recipeVideo->status;
                    $reviewVideo->file_id = 0;
                    $reviewVideo->duration = $recipeVideo->duration;
                    $reviewVideo->save();

                    $reviewVideo->creation_date = $recipeVideo->creation_date;
                    $reviewVideo->save();

                    //GENERATE ACTIVITY FEED
                    if ($activity_recipe && $reviewVideo->search) {
                      //START VIDEO UPLOAD ACTIVITY FEED

                      $action = $activityTable->addActivity($reviewVideo->getOwner(), $sitereview, 'sitereview_video_new_listtype_' . $listingtype_id);
                      $action->date = $reviewVideo->creation_date;
                      $action->save();
                      if ($action != null) {
                        $activityTable->attachActivity($action, $reviewVideo);
                      }

                      foreach ($activityTable->getActionsByObject($reviewVideo) as $action) {
                        $activityTable->resetActivityBindings($action);
                      }
                    }

                    $db->commit();
                  } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                  }

                  //START VIDEO THUMB WORK
                  if (!empty($reviewVideo->code) && !empty($reviewVideo->type) && !empty($recipeVideo->photo_id)) {
                    $storageData = $storageTable->fetchRow(array('file_id = ?' => $recipeVideo->photo_id));
                    if (!empty($storageData) && !empty($storageData->storage_path)) {
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
                              'parent_id' => $reviewVideo->video_id
                                  ));

                          //REMOVE TEMP FILE
                          @unlink($thumb_file);
                          @unlink($tmp_file);
                        } catch (Exception $e) {
                          
                        }

                        $reviewVideo->photo_id = $thumbFileRow->file_id;
                        $reviewVideo->save();
                      }
                    }
                  }
                  //END VIDEO THUMB WORK
                  //START FETCH TAG
                  $videoTags = $recipeVideo->tags()->getTagMaps();
                  $tagString = '';

                  foreach ($videoTags as $tagmap) {

                    if ($tagString != '')
                      $tagString .= ', ';
                    $tagString .= $tagmap->getTag()->getTitle();

                    $owner = Engine_Api::_()->getItem('user', $recipeVideo->owner_id);
                    $tags = preg_split('/[,]+/', $tagString);
                    $tags = array_filter(array_map("trim", $tags));
                    $reviewVideo->tags()->setTagMaps($owner, $tags);
                  }
                  //END FETCH TAG
                  //START FETCH LIKES
                  $selectLike = $likeTable->select()
                          ->from($likeTableName, 'like_id')
                          ->where('resource_type = ?', 'video')
                          ->where('resource_id = ?', $recipeVideoData->video_id);
                  $selectLikeDatas = $likeTable->fetchAll($selectLike);
                  foreach ($selectLikeDatas as $selectLikeData) {
                    $like = Engine_Api::_()->getItem('core_like', $selectLikeData->like_id);

                    $newLikeEntry = $likeTable->createRow();
                    $newLikeEntry->resource_type = 'sitereview_video';
                    $newLikeEntry->resource_id = $reviewVideo->video_id;
                    $newLikeEntry->poster_type = 'user';
                    $newLikeEntry->poster_id = $like->poster_id;
                    $newLikeEntry->creation_date = $like->creation_date;
                    $newLikeEntry->save();

                    $newLikeEntry->creation_date = $like->creation_date;
                    $newLikeEntry->save();
                  }
                  //END FETCH LIKES
                  //START FETCH COMMENTS
                  $selectLike = $commentTable->select()
                          ->from($commentTableName, 'comment_id')
                          ->where('resource_type = ?', 'video')
                          ->where('resource_id = ?', $recipeVideoData->video_id);
                  $selectLikeDatas = $commentTable->fetchAll($selectLike);
                  foreach ($selectLikeDatas as $selectLikeData) {
                    $comment = Engine_Api::_()->getItem('core_comment', $selectLikeData->comment_id);

                    $newLikeEntry = $commentTable->createRow();
                    $newLikeEntry->resource_type = 'sitereview_video';
                    $newLikeEntry->resource_id = $reviewVideo->video_id;
                    $newLikeEntry->poster_type = 'user';
                    $newLikeEntry->poster_id = $comment->poster_id;
                    $newLikeEntry->body = $comment->body;
                    $newLikeEntry->creation_date = $comment->creation_date;
                    $newLikeEntry->like_count = $comment->like_count;
                    $newLikeEntry->save();

                    $newLikeEntry->creation_date = $comment->creation_date;
                    $newLikeEntry->save();
                  }
                  //END FETCH COMMENTS
                  //START UPDATE TOTAL LIKES IN REVIEW-VIDEO TABLE
                  $selectLikeCount = $likeTable->select()
                          ->from($likeTableName, array('COUNT(*) AS like_count'))
                          ->where('resource_type = ?', 'sitereview_video')
                          ->where('resource_id = ?', $reviewVideo->video_id);
                  $selectLikeCounts = $likeTable->fetchAll($selectLikeCount);
                  if (!empty($selectLikeCounts)) {
                    $selectLikeCounts = $selectLikeCounts->toArray();
                    $reviewVideo->like_count = $selectLikeCounts[0]['like_count'];
                    $reviewVideo->save();
                  }
                  //END UPDATE TOTAL LIKES IN REVIEW-VIDEO TABLE

                  if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video', 1)) {

                    $selectClasfVideo = $clasfVideoTable->select()
                            ->from($clasfVideoTableName, array('video_id'))
                            ->where('video_id =?', $reviewVideo->video_id);
                    $resultsClasfVideo = $clasfVideoTable->fetchRow($selectClasfVideo);

                    if (empty($resultsClasfVideo)) {
                      $reviewVideo->is_import = 1;
                      $reviewVideo->save();
                      $clasfVideoTableCreateRow = $clasfVideoTable->createRow();
                      $clasfVideoTableCreateRow->listing_id = $reviewVideo->listing_id;
                      $clasfVideoTableCreateRow->video_id = $reviewVideo->video_id;
                      $clasfVideoTableCreateRow->created = $reviewVideo->creation_date;
                      $clasfVideoTableCreateRow->is_import = $reviewVideo->is_import;
                      $clasfVideoTableCreateRow->save();
                    }
                  }

                  //START FETCH RATTING DATA
                  $selectVideoRating = $recipeVideoRating->select()
                          ->from($recipeVideoRatingName)
                          ->where('video_id = ?', $recipeVideoData->video_id);

                  $recipeVideoRatingDatas = $recipeVideoRating->fetchAll($selectVideoRating);
                  if (!empty($recipeVideoRatingDatas)) {
                    $recipeVideoRatingDatas = $recipeVideoRatingDatas->toArray();
                  }

                  foreach ($recipeVideoRatingDatas as $recipeVideoRatingData) {

                    $reviewVideoRating->insert(array(
                        'videorating_id' => $reviewVideo->video_id,
                        'user_id' => $recipeVideoRatingData['user_id'],
                        'rating' => $recipeVideoRatingData['rating']
                    ));
                  }
                  //END FETCH RATTING DATA
                }
              }
            }
            //END FETCH VIDEO DATA
          }

          $this->view->recipe_assigned_previous_id = $recipe_id;

          //CREATE LOG ENTRY IN LOG FILE
          if (file_exists(APPLICATION_PATH . '/temporary/log/RecipeToReviewImport.log')) {
            $myFile = APPLICATION_PATH . '/temporary/log/RecipeToReviewImport.log';
            $error = Zend_Registry::get('Zend_Translate')->_("can't open file");
            $fh = fopen($myFile, 'a') or die($error);
            $current_time = date('D, d M Y H:i:s T');
            $review_title = $sitereview->title;
            $stringData = $this->view->translate('Recipe with ID ') . $recipe_id . $this->view->translate(' is successfully imported into a Review Listing with ID ') . $sitereview->listing_id . $this->view->translate(' at ') . $current_time . $this->view->translate(". Title of that Review Listing is '") . $review_title . "'.\n\n";
            fwrite($fh, $stringData);
            fclose($fh);
          }

          if ($next_import_count >= 100) {
            $this->_redirect("admin/sitereview/importlisting/index?start_import=1&listingtype_id=$listingtype_id&module=recipe&activity_recipe=$activity_recipe");
          }
        }
      }
    }


    //START CODE FOR CREATING THE BlogToReviewImport.log FILE
    if (!file_exists(APPLICATION_PATH . '/temporary/log/BlogToReviewImport.log')) {
      $log = new Zend_Log();
      try {
        $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/BlogToReviewImport.log'));
      } catch (Exception $e) {
        //CHECK DIRECTORY
        if (!@is_dir(APPLICATION_PATH . '/temporary/log') && @mkdir(APPLICATION_PATH . '/temporary/log', 0777, true)) {
          $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/BlogToReviewImport.log'));
        } else {
          //Silence ...
          if (APPLICATION_ENV !== 'production') {
            $log->log($e->__toString(), Zend_Log::CRIT);
          } else {
            //MAKE SURE LOGGING DOESN'T CAUSE EXCEPTIONS
            $log->addWriter(new Zend_Log_Writer_Null());
          }
        }
      }
    }

    //GIVE WRITE PERMISSION IF FILE EXIST
    if (file_exists(APPLICATION_PATH . '/temporary/log/BlogToReviewImport.log')) {
      @chmod(APPLICATION_PATH . '/temporary/log/BlogToReviewImport.log', 0777);
    }
    //END CODE FOR CREATING THE BlogToReviewImport.log FILE
    //START IMPORTING WORK IF BLOG AND SITEREVIEW IS INSTALLED AND ACTIVATE
    if ($blogEnabled) {

      //GET BLOG TABLES 
      $blogTable = Engine_Api::_()->getDbTable('blogs', 'blog');
      $blogTableName = $blogTable->info('name');

      $blogCategoryTable = Engine_Api::_()->getDbtable('categories', 'blog');
      $blogCategoryTableName = $blogCategoryTable->info('name');

      $blogSubscriptionTable = Engine_Api::_()->getDbtable('subscriptions', 'blog');
      $blogSubscriptionTableName = $blogSubscriptionTable->info('name');

      //GET SITEREVIEW TABLES
      $reviewSubscriptionTable = Engine_Api::_()->getDbtable('subscriptions', 'sitereview');
      $reviewSubscriptionTableName = $reviewSubscriptionTable->info('name');

      //GET STYLE TABLES
      $stylesTable = Engine_Api::_()->getDbtable('styles', 'core');
      $stylesTableName = $stylesTable->info('name');

      //ADD NEW COLUMN IN LISTING TABLE
      $db = Engine_Db_Table::getDefaultAdapter();
      $is_review_import = $db->query("SHOW COLUMNS FROM engine4_blog_blogs LIKE 'is_review_import'")->fetch();
      if (empty($is_review_import)) {
        $run_query = $db->query("ALTER TABLE `engine4_blog_blogs` ADD is_review_import TINYINT( 2 ) NOT NULL DEFAULT '0'");
      }

      //START IF IMPORTING IS BREAKED BY SOME REASON
      $selectBlogs = $blogTable->select()
              ->from($blogTableName, 'blog_id')
              ->where('is_review_import != ?', 1)
              //->where('category_id != ?', 0)
              ->order('blog_id ASC');
      $blogDatas = $blogTable->fetchAll($selectBlogs);

      $this->view->first_blog_id = $first_blog_id = 0;
      $this->view->last_blog_id = $last_blog_id = 0;

      if (!empty($blogDatas)) {

        $flag_first_blog_id = 1;

        foreach ($blogDatas as $blogData) {

          if ($flag_first_blog_id == 1) {
            $this->view->first_blog_id = $first_blog_id = $blogData->blog_id;
          }
          $flag_first_blog_id++;

          $this->view->last_blog_id = $last_blog_id = $blogData->blog_id;
        }

        if (isset($_GET['blog_assigned_previous_id'])) {
          $this->view->blog_assigned_previous_id = $blog_assigned_previous_id = $_GET['blog_assigned_previous_id'];
        } else {
          $this->view->blog_assigned_previous_id = $blog_assigned_previous_id = $first_blog_id;
        }
      }

      //START IMPORTING IF REQUESTED
      if (isset($_GET['start_import']) && $_GET['start_import'] == 1 && $_GET['module'] == 'blog') {

        //ACTIVITY FEED IMPORT
        $activity_blog = $this->_getParam('activity_blog');

        //START FETCH CATEGORY WORK
        $selectReviewCategory = $reviewCategoryTable->select()
                ->from($reviewCategoryTableName, 'category_name')
                ->where('category_name != ?', '')
                ->where('listingtype_id = ?', $listingtype_id)
                ->where('cat_dependency = ?', 0);
        $reviewCategoryDatas = $reviewCategoryTable->fetchAll($selectReviewCategory);
        if (!empty($reviewCategoryDatas)) {
          $reviewCategoryDatas = $reviewCategoryDatas->toArray();
        }

        $reviewCategoryInArrayData = array();
        foreach ($reviewCategoryDatas as $reviewCategoryData) {
          $reviewCategoryInArrayData[] = $reviewCategoryData['category_name'];
        }

        $selectBlogCategory = $blogCategoryTable->select()
                ->where('category_name != ?', '')
                ->from($blogCategoryTableName);
        $blogCategoryDatas = $blogCategoryTable->fetchAll($selectBlogCategory);
        if (!empty($blogCategoryDatas)) {
          $blogCategoryDatas = $blogCategoryDatas->toArray();
          foreach ($blogCategoryDatas as $blogCategoryData) {
            if (!in_array($blogCategoryData['category_name'], $reviewCategoryInArrayData)) {
              $newCategory = $reviewCategoryTable->createRow();
              $newCategory->listingtype_id = $listingtype_id;
              $newCategory->category_name = $blogCategoryData['category_name'];
              $newCategory->cat_dependency = 0;
              $newCategory->cat_order = 9999;
              $newCategory->save();
              $newCategory->afterCreate();
            }
          }
        }

        //START BLOG IMPORTING
        $selectBlogs = $blogTable->select()
                ->where('blog_id >= ?', $blog_assigned_previous_id)
                ->from($blogTableName, 'blog_id')
                ->where('is_review_import != ?', 1)
                ->where('category_id != ?', 0)
                ->order('blog_id ASC');
        $blogDatas = $blogTable->fetchAll($selectBlogs);

        $next_import_count = 0;

        foreach ($blogDatas as $blogData) {
          $blog_id = $blogData->blog_id;

          if (!empty($blog_id)) {

            $blog = Engine_Api::_()->getItem('blog', $blog_id);

            $sitereview = $reviewTable->createRow();
            $sitereview->title = $blog->title;

            $sitereview->owner_id = $blog->owner_id;
            $sitereview->listingtype_id = $listingtype_id;

            //START FETCH LIST CATEGORY AND SUB-CATEGORY
            if (!empty($blog->category_id)) {
              $blogCategory = $blogCategoryTable->fetchRow(array('category_id = ?' => $blog->category_id));
              if (!empty($blogCategory)) {
                $blogCategoryName = $blogCategory->category_name;

                if (!empty($blogCategoryName)) {
                  $reviewCategory = $reviewCategoryTable->fetchRow(array('category_name = ?' => $blogCategoryName, 'cat_dependency = ?' => 0, 'listingtype_id =?' => $listingtype_id));
                  if (!empty($reviewCategory)) {
                    $reviewCategoryId = $sitereview->category_id = $reviewCategory->category_id;
                  }
                }
              }
            } else {
              continue;
            }
            //END FETCH LIST CATEGORY AND SUB-CATEGORY
            //START GET DATA FROM LISTING
            $sitereview->creation_date = $blog->creation_date;
            $sitereview->modified_date = $blog->modified_date;
            $sitereview->approved = 1;
            $sitereview->featured = 0;
            $sitereview->sponsored = 0;

            $sitereview->view_count = 1;
            if ($blog->view_count > 0) {
              $sitereview->view_count = $blog->view_count;
            }

            $sitereview->comment_count = $blog->comment_count;
            $sitereview->save();

            $sitereview->creation_date = $blog->creation_date;
            $sitereview->save();

            //FATCH REVIEW CATEGORIES
            $categoryIdsArray = array();
            $categoryIdsArray[] = $sitereview->category_id;
            $categoryIdsArray[] = $sitereview->subcategory_id;
            $categoryIdsArray[] = $sitereview->subsubcategory_id;
            $sitereview->profile_type = $reviewCategoryTable->getProfileType($categoryIdsArray, 0, 'profile_type');
            $sitereview->search = $blog->search;
            $sitereview->draft = $blog->draft;
            $sitereview->save();

            $blog->is_review_import = 1;
            $blog->save();
            $next_import_count++;
            //END GET DATA FROM LISTING

            $overview = '';
            $row = $otherinfoTable->getOtherinfo($sitereview->getIdentity());

            if ($listingtypeArray->overview)
              $overview = $blog->body;

            if (empty($row)) {
              Engine_Api::_()->getDbTable('otherinfo', 'sitereview')->insert(array(
                  'listing_id' => $sitereview->getIdentity(),
                  'overview' => $overview
              ));
            }

            //GENERATE ACITIVITY FEED
            if ($sitereview->draft == 0 && $activity_blog && $sitereview->search) {
              $action = $activityTable->addActivity($sitereview->getOwner(), $sitereview, 'sitereview_new_listtype_' . $listingtype_id);
              $action->date = $sitereview->creation_date;
              $action->save();
              if ($action != null) {
                $activityTable->attachActivity($action, $sitereview);
              }
            }

            //START FETCH TAG
            $blogTags = $blog->tags()->getTagMaps();
            $tagString = '';

            foreach ($blogTags as $tagmap) {

              if ($tagString != '')
                $tagString .= ', ';
              $tagString .= $tagmap->getTag()->getTitle();

              $tags = array_filter(array_map("trim", preg_split('/[,]+/', $tagString)));
              $sitereview->tags()->setTagMaps(Engine_Api::_()->getItem('user', $blog->owner_id), $tags);
            }
            //END FETCH TAG
            //START FETCH LIKES
            $selectSubscribedBlog = $blogSubscriptionTable->select()
                    ->from($blogSubscriptionTableName, array('*'));
            $selectSubscribedBlogDatas = $blogSubscriptionTable->fetchAll($selectSubscribedBlog);

            foreach ($selectSubscribedBlogDatas as $value) {
              $checkReviewSubscribed = $reviewSubscriptionTable->checkSubscription(Engine_Api::_()->getItem('user', $value->user_id), Engine_Api::_()->getItem('user', $value->subscriber_user_id), $listingtype_id);
              if (!$checkReviewSubscribed) {
                if ($listingtypeArray->subscription) {
                  // Create
                  $reviewSubscriptionTable->insert(array(
                      'user_id' => $value->user_id,
                      'subscriber_user_id' => $value->subscriber_user_id,
                      'listingtype_id' => $listingtype_id
                  ));
                }
              }
            }

            //START FETCH LIKES
            $selectLike = $likeTable->select()
                    ->from($likeTableName, 'like_id')
                    ->where('resource_type = ?', 'blog')
                    ->where('resource_id = ?', $blog_id);
            $selectLikeDatas = $likeTable->fetchAll($selectLike);
            foreach ($selectLikeDatas as $selectLikeData) {
              $like = Engine_Api::_()->getItem('core_like', $selectLikeData->like_id);

              $newLikeEntry = $likeTable->createRow();
              $newLikeEntry->resource_type = 'sitereview_listing';
              $newLikeEntry->resource_id = $sitereview->listing_id;
              $newLikeEntry->poster_type = 'user';
              $newLikeEntry->poster_id = $like->poster_id;
              $newLikeEntry->creation_date = $like->creation_date;
              $newLikeEntry->save();

              $newLikeEntry->creation_date = $like->creation_date;
              $newLikeEntry->save();
            }
            //END FETCH LIKES
            //START FETCH COMMENTS
            $selectLike = $commentTable->select()
                    ->from($commentTableName, 'comment_id')
                    ->where('resource_type = ?', 'blog')
                    ->where('resource_id = ?', $blog_id);
            $selectLikeDatas = $commentTable->fetchAll($selectLike);
            foreach ($selectLikeDatas as $selectLikeData) {
              $comment = Engine_Api::_()->getItem('core_comment', $selectLikeData->comment_id);

              $newLikeEntry = $commentTable->createRow();
              $newLikeEntry->resource_type = 'sitereview_listing';
              $newLikeEntry->resource_id = $sitereview->listing_id;
              $newLikeEntry->poster_type = 'user';
              $newLikeEntry->poster_id = $comment->poster_id;
              $newLikeEntry->body = $comment->body;
              $newLikeEntry->creation_date = $comment->creation_date;
              $newLikeEntry->like_count = $comment->like_count;
              $newLikeEntry->save();

              $newLikeEntry->creation_date = $comment->creation_date;
              $newLikeEntry->save();
            }
            //END FETCH COMMENTS
            //START STYLES
            $selectStyles = $stylesTable->select()
                    ->from($stylesTableName, 'style')
                    ->where('type = ?', 'user_blog')
                    ->where('id = ?', $blog->owner_id);
            $selectStyleDatas = $stylesTable->fetchRow($selectStyles);
            if (!empty($selectStyleDatas)) {

              $selectReviewStyles = $stylesTable->select()
                      ->from($stylesTableName, 'style')
                      ->where('type = ?', 'sitereview_listing')
                      ->where('id = ?', $sitereview->listing_id);
              $selectReviewStyleDatas = $stylesTable->fetchRow($selectReviewStyles);
              if (empty($selectReviewStyleDatas)) {
                //CREATE
                $stylesTable->insert(array(
                    'type' => 'sitereview_listing',
                    'id' => $sitereview->listing_id,
                    'style' => $selectStyleDatas->style
                ));
              }
            }
            //END STYLES
            //START UPDATE TOTAL LIKES IN REVIEW TABLE
            $selectLikeCount = $likeTable->select()
                    ->from($likeTableName, array('COUNT(*) AS like_count'))
                    ->where('resource_type = ?', 'blog')
                    ->where('resource_id = ?', $blog->blog_id);
            $selectLikeCounts = $likeTable->fetchAll($selectLikeCount);
            if (!empty($selectLikeCounts)) {
              $selectLikeCounts = $selectLikeCounts->toArray();
              $sitereview->like_count = $selectLikeCounts[0]['like_count'];
              $sitereview->save();
            }
            //END UPDATE TOTAL LIKES IN REVIEW TABLE
            //START FETCH PRIVACY
            $auth = Engine_Api::_()->authorization()->context;
            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');

            foreach ($roles as $role) {
              if ($auth->isAllowed($blog, $role, 'view')) {
                $values['auth_view'] = $role;
              }
            }

            $viewMax = array_search($values['auth_view'], $roles);

            foreach ($roles as $i => $role) {
              $auth->setAllowed($sitereview, $role, 'view', ($i <= $viewMax));
              $auth->setAllowed($sitereview, $role, "view_listtype_$listingtype_id", ($i <= $viewMax));
            }

            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
            foreach ($roles as $role) {
              if ($auth->isAllowed($blog, $role, 'comment')) {
                $values['auth_comment'] = $role;
              }
            }
            $commentMax = array_search($values['auth_comment'], $roles);
            foreach ($roles as $i => $role) {
              $auth->setAllowed($sitereview, $role, 'comment', ($i <= $commentMax));
              $auth->setAllowed($sitereview, $role, "comment_listtype_$listingtype_id", ($i <= $commentMax));
            }

            $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
            foreach ($roles as $role) {
              $values['auth_photo'] = $role;
            }
            $photoMax = array_search($values['auth_photo'], $roles);

            foreach ($roles as $role) {
              $values['auth_video'] = $role;
            }
            $videoMax = array_search($values['auth_video'], $roles);

            foreach ($roles as $i => $role) {
              $auth->setAllowed($sitereview, $role, "photo_listtype_$listingtype_id", ($i <= $photoMax));
              $auth->setAllowed($sitereview, $role, "video_listtype_$listingtype_id", ($i <= $videoMax));
            }
            //END FETCH PRIVACY
          }

          $this->view->blog_assigned_previous_id = $blog_id;

          //CREATE LOG ENTRY IN LOG FILE
          if (file_exists(APPLICATION_PATH . '/temporary/log/BlogToReviewImport.log')) {
            $myFile = APPLICATION_PATH . '/temporary/log/BlogToReviewImport.log';
            $error = Zend_Registry::get('Zend_Translate')->_("can't open file");
            $fh = fopen($myFile, 'a') or /* die */($error);
            $current_time = date('D, d M Y H:i:s T');
            $review_title = $sitereview->title;
            $stringData = $this->view->translate('Blog with ID ') . $blog_id . $this->view->translate(' is successfully imported into a Review Listing with ID ') . $sitereview->listing_id . $this->view->translate(' at ') . $current_time . $this->view->translate(". Title of that Listing is '") . $review_title . "'.\n\n";
            fwrite($fh, $stringData);
            fclose($fh);
          }

          if ($next_import_count >= 100) {
            $this->_redirect("admin/sitereview/importlisting/index?start_import=1&listingtype_id=$listingtype_id&module=blog&activity_blog=$activity_blog");
          }
        }
      }
    }
  }

  //ACTION FOR IMPORTING DATA FROM CSV FILE
  public function importAction() {

    //INCREASE THE MEMORY ALLOCATION SIZE AND INFINITE SET TIME OUT
    ini_set('memory_limit', '2048M');
    set_time_limit(0);

    $this->_helper->layout->setLayout('admin-simple');

    //MAKE FORM
    $this->view->form = $form = new Sitereview_Form_Admin_Import_Import();

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      //MAKE SURE THAT FILE EXTENSION SHOULD NOT DIFFER FROM ALLOWED TYPE
      $ext = str_replace(".", "", strrchr($_FILES['filename']['name'], "."));
      if (!in_array($ext, array('csv', 'CSV'))) {
        $error = $this->view->translate("Invalid file extension. Only 'csv' extension is allowed.");
        $error = Zend_Registry::get('Zend_Translate')->_($error);

        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
        return;
      }

      //START READING DATA FROM CSV FILE
      $fname = $_FILES['filename']['tmp_name'];
      $fp = fopen($fname, "r");

      if ((!$fp)) {
        echo "$fname File opening error";
        exit;
      }

      $formData = array();
      $formData = $form->getValues();

      if ($formData['import_seperate'] == 1) {
        while ($buffer = fgets($fp, 4096)) {
          $explode_array[] = explode('|', $buffer);
        }
      } else {
        while ($buffer = fgets($fp, 4096)) {
          $explode_array[] = explode(',', $buffer);
        }
      }
      //END READING DATA FROM CSV FILE
      //GET LISTING TYPE COUNT
      $listingtypeTable = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');
      $listingTypeCount = $listingtypeTable->getListingTypeCount();

      $import_count = 0;
      foreach ($explode_array as $explode_data) {

        //GET SITEREVIEW DETAILS FROM DATA ARRAY
        $db = Engine_Db_Table::getDefaultAdapter();
        $columns = $db->query("SHOW COLUMNS FROM engine4_sitereview_imports")->fetchAll();
        $values = array();
        if ($listingTypeCount == 1) {
          $values['title'] = trim($explode_data[0]);
          $values['slug'] = $listingtypeTable->getListingTypeColumn(1, 'slug_singular');
          $values['category'] = trim($explode_data[1]);
          $values['sub_category'] = trim($explode_data[2]);
          $values['subsub_category'] = trim($explode_data[3]);
          $values['body'] = trim($explode_data[4]);
          $values['overview'] = trim($explode_data[5]);
          $values['tags'] = trim($explode_data[6]);
          $values['location'] = trim($explode_data[7]);
          $values['price'] = trim($explode_data[8]);
          $values['img_name'] = trim($explode_data[9]);
          $i = 1;
          $count_result = count($explode_data) - 10;
          $custom_field = '';
          foreach ($columns as $column) {
            $custom_field .= $column['Field'] . ',';
          }
          $custom_field = explode('img_name', $custom_field);
          $custom_field = ltrim($custom_field[1], ',');
          $custom_field = rtrim($custom_field, ',');
          $custom_field = explode(',', $custom_field);
          $required_field = 1;

          $count_value_checkbox = 0;
          $count_value_selectbox = 0;
          $count_value_etnicity = 0;
          $count_value_lookingfor = 0;
          $count_value_interestedIn = 0;
          if (isset($explode_data[10])) {
            foreach ($custom_field as $field) {
              if (empty($field))
                continue;
              $column_name = $field;
              $explode_column = explode('_', $column_name);
              $columnIsRequired = $db->select()->from('engine4_sitereview_listing_fields_meta', 'required')->where('field_id = ?', $explode_column[2])->query()->fetchColumn();
              $fieldType = $db->select()->from('engine4_sitereview_listing_fields_meta', 'type')->where('field_id = ?', $explode_column[1])->query()->fetchColumn();
              if ($fieldType == 'multi_checkbox' && !empty($columnIsRequired)) {
                if (($explode_data[9 + $i] != ' ' && !empty($explode_data[9 + $i])) || ($explode_data[8 + $i] == 0)) {
                  $count_value_checkbox++;
                }
                if ($count_value_checkbox == 0) {
                  $required_field = 0;
                  break;
                }
              } elseif ($fieldType == 'multiselect' && !empty($columnIsRequired)) {
                if (($explode_data[9 + $i] != ' ' && !empty($explode_data[9 + $i])) || ($explode_data[9 + $i] == 0)) {
                  $count_value_selectbox++;
                }
                if ($count_value_selectbox == 0) {
                  $required_field = 0;
                  break;
                }
              } elseif ($fieldType == 'looking_for' && !empty($columnIsRequired)) {
                if (($explode_data[9 + $i] != ' ' && !empty($explode_data[9 + $i])) || ($explode_data[9 + $i] == 0)) {
                  $count_value_lookingfor++;
                }
                if ($count_value_lookingfor == 0) {
                  $required_field = 0;
                  break;
                }
              } elseif ($fieldType == 'ethnicity' && !empty($columnIsRequired)) {
                if (($explode_data[9 + $i] != ' ' && !empty($explode_data[9 + $i])) || ($explode_data[9 + $i] == 0)) {
                  $count_value_etnicity++;
                }
                if ($count_value_etnicity == 0) {
                  $required_field = 0;
                  break;
                }
              } elseif ($fieldType == 'partner_gender' && !empty($columnIsRequired)) {
                if (($explode_data[9 + $i] != ' ' && !empty($explode_data[9 + $i])) || ($explode_data[9 + $i] == 0)) {
                  $count_value_interestedIn++;
                }
                if ($count_value_interestedIn == 0) {
                  $required_field = 0;
                  break;
                }
              } elseif (!empty($columnIsRequired) && ($explode_data[9 + $i] == ' ' || empty($explode_data[9 + $i]))) {
                $required_field = 0;
                break;
              }
              $values[$column_name] = trim($explode_data[9 + $i]);
              $i++;
            }
          }
        } else {
          $values['title'] = trim($explode_data[0]);
          $values['slug'] = trim($explode_data[1]);
          $values['category'] = trim($explode_data[2]);
          $values['sub_category'] = trim($explode_data[3]);
          $values['subsub_category'] = trim($explode_data[4]);
          $values['body'] = trim($explode_data[5]);
          $values['overview'] = trim($explode_data[6]);
          $values['tags'] = trim($explode_data[7]);
          $values['location'] = trim($explode_data[8]);
          $values['price'] = trim($explode_data[9]);
          $values['img_name'] = trim($explode_data[10]);
          $i = 1;
          $count_result = count($explode_data) - 11;
          $custom_field = '';
          foreach ($columns as $column) {
            $custom_field .= $column['Field'] . ',';
          }
          $custom_field = explode('img_name', $custom_field);
          $custom_field = ltrim($custom_field[1], ',');
          $custom_field = rtrim($custom_field, ',');
          $custom_field = explode(',', $custom_field);
          $required_field = 1;

          $count_value_checkbox = 0;
          $count_value_selectbox = 0;
          $count_value_etnicity = 0;
          $count_value_lookingfor = 0;
          $count_value_interestedIn = 0;
          if (isset($explode_data[11])) {
            foreach ($custom_field as $field) {
              if (empty($field))
                continue;
              $column_name = $field;
              $explode_column = explode('_', $column_name);
              $columnIsRequired = $db->select()->from('engine4_sitereview_listing_fields_meta', 'required')->where('field_id = ?', $explode_column[2])->query()->fetchColumn();
              $fieldType = $db->select()->from('engine4_sitereview_listing_fields_meta', 'type')->where('field_id = ?', $explode_column[1])->query()->fetchColumn();
              if ($fieldType == 'multi_checkbox' && !empty($columnIsRequired)) {
                if (($explode_data[10 + $i] != ' ' && !empty($explode_data[10 + $i])) || ($explode_data[10 + $i] == 0)) {
                  $count_value_checkbox++;
                }
                if ($count_value_checkbox == 0) {
                  $required_field = 0;
                  break;
                }
              } elseif ($fieldType == 'multiselect' && !empty($columnIsRequired)) {
                if (($explode_data[10 + $i] != ' ' && !empty($explode_data[10 + $i])) || ($explode_data[10 + $i] == 0)) {
                  $count_value_selectbox++;
                }
                if ($count_value_selectbox == 0) {
                  $required_field = 0;
                  break;
                }
              } elseif ($fieldType == 'looking_for' && !empty($columnIsRequired)) {
                if (($explode_data[10 + $i] != ' ' && !empty($explode_data[10 + $i])) || ($explode_data[10 + $i] == 0)) {
                  $count_value_lookingfor++;
                }
                if ($count_value_lookingfor == 0) {
                  $required_field = 0;
                  break;
                }
              } elseif ($fieldType == 'ethnicity' && !empty($columnIsRequired)) {
                if (($explode_data[10 + $i] != ' ' && !empty($explode_data[10 + $i])) || ($explode_data[10 + $i] == 0)) {
                  $count_value_etnicity++;
                }
                if ($count_value_etnicity == 0) {
                  $required_field = 0;
                  break;
                }
              } elseif ($fieldType == 'partner_gender' && !empty($columnIsRequired)) {
                if (($explode_data[10 + $i] != ' ' && !empty($explode_data[10 + $i])) || ($explode_data[10 + $i] == 0)) {
                  $count_value_interestedIn++;
                }
                if ($count_value_interestedIn == 0) {
                  $required_field = 0;
                  break;
                }
              } elseif (!empty($columnIsRequired) && ($explode_data[10 + $i] == ' ' || empty($explode_data[10 + $i]))) {
                $required_field = 0;
                break;
              }
              $values[$column_name] = trim($explode_data[10 + $i]);
              $i++;
            }
          }
        }

        //IF SITEREVIEW TITLE AND CATEGORY IS EMPTY THEN CONTINUE;
        if (empty($values['title']) || empty($values['slug']) || empty($values['category']) || empty($values['body'])) {
          continue;
        }

        $db = Engine_Api::_()->getDbtable('imports', 'sitereview')->getAdapter();
        $db->beginTransaction();

        try {
          $import = Engine_Api::_()->getDbtable('imports', 'sitereview')->createRow();
          $import->setFromArray($values);
          $import->save();

          //COMMIT
          $db->commit();

          if (empty($import_count)) {
            $first_import_id = $last_import_id = $import->import_id;

            //SAVE DATA IN `engine4_sitereview_importfiles` TABLE
            $db = Engine_Api::_()->getDbtable('importfiles', 'sitereview')->getAdapter();
            $db->beginTransaction();

            try {

              //SAVE OTHER DATA IN engine4_sitereview_importfiles TABLE
              $importFile = Engine_Api::_()->getDbtable('importfiles', 'sitereview')->createRow();
              $importFile->filename = $_FILES['filename']['name'];
              $importFile->status = 'Pending';
              $importFile->first_import_id = $first_import_id;
              $importFile->last_import_id = $last_import_id;
              $importFile->current_import_id = $first_import_id;
              $importFile->first_listing_id = 0;
              $importFile->last_listing_id = 0;
              $importFile->save();

              //COMMIT
              $db->commit();
            } catch (Exception $e) {
              $db->rollBack();
              throw $e;
            }
          } else {

            //UPDATE LAST IMPORT ID
            $last_import_id = $import->import_id;
            $importFile->last_import_id = $last_import_id;
            $importFile->save();
          }

          $import_count++;
        } catch (Exception $e) {
          $db->rollBack();
          throw $e;
        }
      }

      //CLOSE THE SMOOTHBOX
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => true,
          'parentRedirect' => $this->_helper->url->url(array('module' => 'sitereview', 'controller' => 'admin-importlisting', 'action' => 'manage')),
          'parentRedirectTime' => '15',
          'format' => 'smoothbox',
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('CSV file has been imported succesfully !'))
      ));
    }
  }

  //ACTION FOR IMPORTING DATA FROM CSV FILE
  public function dataImportAction() {

    //INCREASE THE MEMORY ALLOCATION SIZE AND INFINITE SET TIME OUT
    ini_set('memory_limit', '2048M');
    set_time_limit(0);

    $this->_helper->layout->setLayout('admin-simple');
    $this->view->importfile_id = $importfile_id = $this->_getParam('importfile_id');
    $import_current = $this->_getParam('import_current');

    $tableImportFile = Engine_Api::_()->getDbTable('importfiles', 'sitereview');
    $finalIds = array();
    if (empty($importfile_id) && isset($_GET['multi_import']) && !empty($_GET['multi_import'])) {

      if (!empty($import_current)) {

        foreach ($_GET as $key => $value) {
          if ($key == 'd_' . $value) {
            $finalIds[] = (int) $value;
          }
        }

        $firstFinalId = 0;
        foreach ($finalIds as $value) {
          $firstFinalId = $value;
          break;
        }

        if (!empty($finalIds) && !empty($firstFinalId) && is_numeric($firstFinalId)) {
          $this->view->importfile_id = $importfile_id = $firstFinalId;
        }
      } else {

        $importfile_ids = array();
        foreach ($_GET as $key => $value) {
          if ($key == 'd_' . $value) {
            $importfile_ids[] = (int) $value;
          }
        }

        $selectPendingIds = $tableImportFile->select()->from($tableImportFile->info('name'), 'importfile_id')->where('status = ?', 'Pending');
        $pendingIds = $selectPendingIds->query()->fetchAll(Zend_Db::FETCH_COLUMN);

        if (!empty($pendingIds) && !empty($importfile_ids)) {
          $finalIds = array_intersect($pendingIds, $importfile_ids);
        }

        $firstFinalId = 0;
        foreach ($finalIds as $value) {
          $firstFinalId = $value;
          break;
        }

        if (!empty($finalIds) && !empty($firstFinalId) && is_numeric($firstFinalId)) {
          $this->view->importfile_id = $importfile_id = $firstFinalId;
        }
      }
    }

    $session = new Zend_Session_Namespace();
    $session->importfile_id = $importfile_id;

    //GET VIEWER
    $viewer = Engine_Api::_()->user()->getViewer();

    //RETURN IF importfile_id IS EMPTY
    if (empty($importfile_id)) {
      return;
    }

    //GET IMPORT FILE OBJECT
    $importFile = Engine_Api::_()->getItem('sitereview_importfile', $importfile_id);
    if (empty($importFile)) {
      return;
    }

    //CHECK IF IMPORT WORK IS ALREADY IN RUNNING STATUS FOR SOME FILE
    $tableImportFile = Engine_Api::_()->getDbTable('importfiles', 'sitereview');
    $importFileStatusData = $tableImportFile->fetchRow(array('status = ?' => 'Running'));
    if (!empty($importFileStatusData) && empty($import_current)) {
      return;
    }

    //UPDATE THE STATUS
    $importFile->status = 'Running';
    $importFile->save();

    $first_import_id = $importFile->first_import_id;
    $last_import_id = $importFile->last_import_id;

    $import_current_id = $importFile->current_import_id;
    $return_import_current_id = $this->_getParam('import_current_id');
    if (!empty($return_import_current_id)) {
      $import_current_id = $this->_getParam('import_current_id');
    }

    //MAKE QUERY
    $tableImport = Engine_Api::_()->getDbtable('imports', 'sitereview');

    $sqlStr = "import_id BETWEEN " . "'" . $import_current_id . "'" . " AND " . "'" . $last_import_id . "'" . "";

    $select = $tableImport->select()
            ->from($tableImport->info('name'), array('import_id'))
            ->where($sqlStr);
    $importDatas = $select->query()->fetchAll();

    if (empty($importDatas)) {
      return;
    }

    //START CODE FOR CREATING THE CSVToSitereviewImport.log FILE
    if (!file_exists(APPLICATION_PATH . '/temporary/log/CSVToSitereviewImport.log')) {
      $log = new Zend_Log();
      try {
        $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/CSVToSitereviewImport.log'));
      } catch (Exception $e) {
        //CHECK DIRECTORY
        if (!@is_dir(APPLICATION_PATH . '/temporary/log') &&
                @mkdir(APPLICATION_PATH . '/temporary/log', 0777, true)) {
          $log->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/temporary/log/CSVToSitereviewImport.log'));
        } else {
          //Silence ...
          if (APPLICATION_ENV !== 'production') {
            $log->log($e->__toString(), Zend_Log::CRIT);
          } else {
            //MAKE SURE LOGGING DOESN'T CAUSE EXCEPTIONS
            $log->addWriter(new Zend_Log_Writer_Null());
          }
        }
      }
    }

    //GIVE WRITE PERMISSION TO LOG FILE IF EXIST
    if (file_exists(APPLICATION_PATH . '/temporary/log/CSVToSitereviewImport.log')) {
      @chmod(APPLICATION_PATH . '/temporary/log/CSVToSitereviewImport.log', 0777);
    }
    //END CODE FOR CREATING THE CSVToSitereviewImport.log FILE
    //GET SITEREVIEW TABLE
    $sitereviewTable = Engine_Api::_()->getItemTable('sitereview_listing');

    $import_count = 0;

    $previous_listingtype_id = 0;
    $previous_slug = '';

    //START THE IMPORT WORK
    foreach ($importDatas as $importData) {

      //GET IMPORT FILE OBJECT
      $importFile = Engine_Api::_()->getItem('sitereview_importfile', $importfile_id);

      //BREAK IF STATUS IS STOP
      if ($importFile->status == 'Stopped') {
        break;
      }

      $import_id = $importData['import_id'];
      if (empty($import_id)) {
        continue;
      }

      $import = Engine_Api::_()->getItem('sitereview_import', $import_id);
      if (empty($import)) {
        continue;
      }

      //GET SITEREVIEW DETAILS FROM DATA ARRAY
      $values = array();
      $values['title'] = $import->title;
      $sitereview_slug = $import->slug;
      $sitereview_category = $import->category;
      $sitereview_subcategory = $import->sub_category;
      $sitereview_subsubcategory = $import->subsub_category;
      $values['body'] = $import->body;
      $values['overview'] = $import->overview;
      $values['location'] = $import->location;
      $values['price'] = $import->price;
      $sitereview_tags = $import->tags;
      $values['owner_id'] = $viewer->getIdentity();


      //IF SITEREVIEW TITLE AND DESCRIPTION IS EMPTY THEN CONTINUE;
      if (empty($values['title']) || empty($sitereview_slug) || empty($sitereview_category) || empty($values['body'])) {
        continue;
      }

      $db = $sitereviewTable->getAdapter();
      $db->beginTransaction();

      try {

        $sitereview = $sitereviewTable->createRow();
        $sitereview->setFromArray($values);
        $sitereview->approved = 1;
        $sitereview->approved_date = date('Y-m-d H:i:s');
        $sitereview->save();
        $listing_id = $sitereview->listing_id;

        $importFile->current_import_id = $import->import_id;
        $importFile->last_listing_id = $listing_id;
        $importFile->save();

        if (empty($importFile->first_listing_id)) {
          $importFile->first_listing_id = $listing_id;
          $importFile->save();
        }
        $import_count++;

        //GET LISTING TYPE ID
        if ($previous_slug != $sitereview_slug) {
          $sitereview->listingtype_id = Engine_Api::_()->getDbtable('listingtypes', 'sitereview')->checkListingSlug($sitereview_slug);
          if (empty($sitereview->listingtype_id)) {
            $db->rollBack();
            continue;
          }
        } else {
          $sitereview->listingtype_id = $previous_listingtype_id;
        }

        $previous_listingtype_id = $sitereview->listingtype_id;
        $previous_slug = $sitereview_slug;

        //START CATEGORY WORK
        $sitereviewCategoryTable = Engine_Api::_()->getDbtable('categories', 'sitereview');
        $sitereviewCategory = $sitereviewCategoryTable->fetchRow(array('category_name = ?' => $sitereview_category, 'cat_dependency = ?' => 0, 'listingtype_id = ?' => $sitereview->listingtype_id));
        if (!empty($sitereviewCategory)) {
          $sitereview->category_id = $sitereviewCategory->category_id;

          $sitereviewSubcategory = $sitereviewCategoryTable->fetchRow(array('category_name = ?' => $sitereview_subcategory, 'cat_dependency = ?' => $sitereview->category_id));

          if (!empty($sitereviewSubcategory)) {
            $sitereview->subcategory_id = $sitereviewSubcategory->category_id;
            $sitereviewSubsubcategory = $sitereviewCategoryTable->fetchRow(array('category_name = ?' => $sitereview_subsubcategory, 'cat_dependency = ?' => $sitereviewSubcategory->category_id));
            if (!empty($sitereviewSubsubcategory)) {
              $sitereview->subsubcategory_id = $sitereviewSubsubcategory->category_id;
            }
          }
        }

        if (empty($sitereview->category_id)) {
          $db->rollBack();
          continue;
        }
        //END CATEGORY WORK

        $sitereview->save();

        $tableOtherinfo = Engine_Api::_()->getDbTable('otherinfo', 'sitereview');

        $row = $tableOtherinfo->getOtherinfo($sitereview->getIdentity());
        if (empty($row)) {
          $tableOtherinfo->insert(array(
              'listing_id' => $sitereview->getIdentity(),
              'overview' => $values['overview']
          ));
        }

        //SAVE TAGS
        $tags = preg_split('/[#]+/', $sitereview_tags);
        $tags = array_filter(array_map("trim", $tags));
        $sitereview->tags()->addTagMaps($viewer, $tags);
        $sitereview->save();


        //START PROFILE IMAGE IMPORTING WORK
        $import_image = 'sitereview_importfiles_' . $importfile_id;
        $archiveFilename = APPLICATION_PATH . "/public/$import_image" . DIRECTORY_SEPARATOR . $importFile->photo_filename;
        
//         if(!empty($import->img_name) && (strstr($import->img_name, 'http') || strstr($import->img_name, 'https'))) {
//           $sitereview->setPhoto($import->img_name);
//         }
          if (file_exists(APPLICATION_PATH . "/public/$import_image") && !empty($import->img_name)) {
          // Make temporary folder
          $archiveOutputPath = substr($archiveFilename, 0, strrpos($archiveFilename, '.'));

          if(file_exists(APPLICATION_PATH . "/public/$import_image" . DIRECTORY_SEPARATOR . $importFile->photo_filename)) {
						// Extract
						$zip = new ZipArchive;
						$res = $zip->open($archiveFilename);
						$zip->extractTo(APPLICATION_PATH . "/public/$import_image");
						$zip->close();
						@chmod($archiveOutputPath, 0777);
						@unlink($archiveFilename);
          }

          $archiveFilename1 = APPLICATION_PATH . "/public/$import_image";
          if (file_exists($archiveFilename1 . '/' . $import->img_name)) {
            $sitereview->setPhoto($archiveFilename1 . '/' . $import->img_name);
          }
        }
        //END PROFILE IMAGE IMPORTING WORK
        
        //START CUSTOM FIELDS IMPORT WORK 
        //GET PROFILE MAPPING ID
        $categoryIds = array();
        $categoryIds[] = $sitereview->category_id;
        $categoryIds[] = $sitereview->subcategory_id;
        $categoryIds[] = $sitereview->subsubcategory_id;
        $profile_type = Engine_Api::_()->getDbtable('categories', 'sitereview')->getProfileType($categoryIds, 0, 'profile_type');

        $reviewFieldValueTable = Engine_Api::_()->fields()->getTable('sitereview_listing', 'values');
        $reviewFieldOptionTable = Engine_Api::_()->fields()->getTable('sitereview_listing', 'options');
        $reviewFieldMapsTable = Engine_Api::_()->fields()->getTable('sitereview_listing', 'maps');
        $reviewFieldSearchTable = Engine_Api::_()->fields()->getTable('sitereview_listing', 'search');

        $db = Engine_Db_Table::getDefaultAdapter();
        $columns = $db->query("SHOW COLUMNS FROM engine4_sitereview_imports")->fetchAll();
        $custom_field = '';
        $countCustomFields = 0;
        foreach ($columns as $column) {
          $countCustomFields++;
          $custom_field .= $column['Field'] . ',';
        }
        $custom_field = explode('img_name', $custom_field);
        $custom_field = ltrim($custom_field[1], ',');
        $custom_field = rtrim($custom_field, ',');
        $custom_fields = explode(',', $custom_field);
        if($countCustomFields >12) {
					foreach ($custom_fields as $cloumn_name) {
						$field = explode('_', $cloumn_name);
						$field_id = $field[2];
						$selectFieldsMapTable = $reviewFieldMapsTable->select()
										->from($reviewFieldMapsTable->info('name'), array('option_id'))
										->where('child_id = ?', $field_id);
						$fieldsMappingResult = $selectFieldsMapTable->query()->fetchAll();
						$option_ids = array();
						foreach ($fieldsMappingResult as $map) {
							$option_ids[] = $map['option_id'];
						}
						if (!in_array($profile_type, $option_ids)) {
							continue;
						}

						$profileTypeExist = $db->select()->from('engine4_sitereview_listing_fields_values', 'value')->where('item_id = ?', $listing_id)->where('value = ?', $profile_type)->where('field_id = ?', 1)->query()->fetchColumn();
						if (empty($profileTypeExist)) {
							$reviewFieldValueTable->insert(array('item_id' => $listing_id, 'field_id' => 1, 'index' => 0, 'value' => $profile_type));
						}

						$reviewFieldMetaTable = Engine_Api::_()->fields()->getTable('sitereview_listing', 'meta');
						$selectMetaData = $reviewFieldMetaTable->select()->from($reviewFieldMetaTable->info('name'), array('type', 'alias', 'search', 'required'))->where('field_id = ?', $field_id);
						$metaData = $reviewFieldMetaTable->fetchRow($selectMetaData);
						$fieldType = $metaData->type;
						$fieldId = $field_id;
						$fieldAlias = $metaData->alias;
						$fieldSearch = $metaData->search;
						$fieldRequired = $metaData->required;

						if ($fieldType == 'multi_checkbox' || $fieldType == 'multiselect') {
							if ($import->$cloumn_name == 'Yes' || $import->$cloumn_name == 'yes') {
								$option_value = $field[4];
								$option_exist = 1;
							} else {
								$option_exist = 0;
							}
						} elseif (($fieldType == 'gender' || $fieldType == 'radio' || $fieldType == 'select') && !empty($import->$cloumn_name)) {
							$option_exist = 1;
							$selectOptionTable = $reviewFieldOptionTable->select()
											->from($reviewFieldOptionTable->info('name'), 'option_id')
											->where('label = ?', $import->$cloumn_name)
											->where('field_id = ?', $field_id);
							$optionTableResult = $reviewFieldOptionTable->fetchRow($selectOptionTable);
							$option_value = $optionTableResult->option_id;
						} else {
							if (!empty($import->$cloumn_name)) {
								$option_value = $import->$cloumn_name;
								$option_exist = 1;
							} else {
								$option_exist = 0;
							}
						}

						$selectValueTable = $reviewFieldValueTable->select()
										->from($reviewFieldValueTable->info('name'), 'index')
										->where('field_id = ?', $fieldId)
										->where('item_id = ?', $listing_id)
										->order('index DESC');
						$index_value = $reviewFieldValueTable->fetchRow($selectValueTable);

						if (!empty($option_exist) && !empty($option_value)) {
							if (count($index_value)) {
								$index_value = $index_value->index;
								$reviewFieldValueTable->insert(array('item_id' => $listing_id, 'field_id' => $fieldId, 'index' => $index_value + 1, 'value' => $option_value));
							} else {
								$reviewFieldValueTable->insert(array('item_id' => $listing_id, 'field_id' => $fieldId, 'index' => 0, 'value' => $option_value));
							}
						}
						if ($fieldSearch == 1 && !empty($fieldAlias)) {
							$field_label = $fieldAlias;
						} elseif ($fieldSearch == 1 && empty($fieldAlias)) {
							$field_label = 'field_' . $fieldId;
						}

						if (!empty($fieldSearch)) {
							$selectSearchTable = $reviewFieldSearchTable->select()
											->from($reviewFieldSearchTable->info('name'), $field_label)
											->where('item_id = ?', $listing_id)
											->where('profile_type = ?', (string)($profile_type));
							$fieldSearchValue = $reviewFieldSearchTable->fetchRow($selectSearchTable);

							if (!empty($option_exist)) {
								if (count($fieldSearchValue)) {
									if (!empty($fieldSearchValue->$field_label)) {
										$field_value = $fieldSearchValue->$field_label . ',' . $option_value;
									} else {
										$field_value = $option_value;
									}
								} else {
									$field_value = $option_value;
								}

								if (empty($fieldSearchValue)) {
									$db->insert('engine4_sitereview_listing_fields_search', array('item_id' => $listing_id, 'profile_type' => $profile_type, $field_label => $field_value));
								} else {
									$db->update('engine4_sitereview_listing_fields_search', array($field_label => $field_value), array('profile_type = ?' => $profile_type, 'item_id = ?' => $listing_id));
								}
							}
						}
					}
        }
        $sitereview->profile_type = $profile_type;
        $sitereview->save();
        //END CUSTOM FIELDS IMPORT WORK 

        $sitereview->setLocation();

        //SET PRIVACY
        $auth = Engine_Api::_()->authorization()->context;
        $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
        $privacyMax = array_search('everyone', $roles);

        foreach ($roles as $i => $role) {
          $auth->setAllowed($sitereview, $role, "view_listtype_$sitereview->listingtype_id", ($i <= $privacyMax));
          $auth->setAllowed($sitereview, $role, "view", ($i <= $privacyMax));
          $auth->setAllowed($sitereview, $role, "comment_listtype_$sitereview->listingtype_id", ($i <= $privacyMax));
          $auth->setAllowed($sitereview, $role, "comment", ($i <= $privacyMax));
        }

        //Commit
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      //IF ALL SITEREVIEWS HAS BEEN IMPORTED THAN CHANGE THE STATUS
      if ($importFile->current_import_id == $importFile->last_import_id) {
        $importFile->status = 'Completed';
      }
      $importFile->save();

      //CREATE LOG ENTRY IN LOG FILE
      if (file_exists(APPLICATION_PATH . '/temporary/log/CSVToSitereviewImport.log')) {

        $stringData = '';
        if ($import_count == 1) {
          $stringData .= "\n\n----------------------------------------------------------------------------------------------------------------\n";
          $stringData .= $this->view->translate("Import History of '") . $importFile->filename . $this->view->translate("' with file id: ") . $importFile->importfile_id . $this->view->translate(", created on ") . $importFile->creation_date . $this->view->translate(" is given below.");
          $stringData .= "\n----------------------------------------------------------------------------------------------------------------\n\n";
        }

        $myFile = APPLICATION_PATH . '/temporary/log/CSVToSitereviewImport.log';
        $fh = fopen($myFile, 'a') or die("can't open file");
        $current_time = date('D, d M Y H:i:s T');
        $listing_id = $sitereview->listing_id;
        $sitereview_title = $sitereview->title;
        $stringData .= $this->view->translate("Successfully created a new listing at ") . $current_time . $this->view->translate(". ID and title of that List are ") . $listing_id . $this->view->translate(" and '") . $sitereview_title . $this->view->translate("' respectively.") . "\n\n";
        fwrite($fh, $stringData);
        fclose($fh);
      }

      if ($import_count >= 100) {

        if (!empty($finalIds)) {
          $queryString = '';
          foreach ($finalIds as $key => $value) {
            $queryString .= "d_$value=$value&";
          }
          $queryString = rtrim($queryString, '&');

          $import_current_id = $importFile->current_import_id + 1;
          $this->_redirect("admin/sitereview/importlisting/data-import?multi_import=1&import_current_id=$import_current_id&import_current=1&$queryString");
        } else {
          $import_current_id = $importFile->current_import_id + 1;
          $this->_redirect("admin/sitereview/importlisting/data-import?importfile_id=$importfile_id&import_current_id=$import_current_id&import_current=1");
        }
      } elseif (!empty($finalIds) && $importFile->status == 'Completed') {

        foreach ($finalIds as $key => $value) {
          if ($value == $importfile_id) {
            unset($finalIds[$key]);
          }
        }

        $queryString = '';
        foreach ($finalIds as $key => $value) {
          $queryString .= "d_$value=$value&";
        }
        $queryString = rtrim($queryString, '&');

        if (!empty($finalIds)) {
          $this->_redirect("admin/sitereview/importlisting/data-import?multi_import=1&$queryString");
        }
      }
    }

    return $this->_helper->redirector->gotoRoute(array('module' => 'sitereview', 'controller' => 'importlisting', 'action' => 'manage'), "admin_default", true);
  }

  //ACTION FOR MANAGING THE CSV FILES DATAS
  public function manageAction() {

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_import');

    //FORM CREATION FOR SORTING
    $this->view->formFilter = $formFilter = new Sitereview_Form_Admin_Import_Filter();
    $sitereview = $this->_getParam('page', 1);
    
    $tableImportFile = Engine_Api::_()->getDbTable('importfiles', 'sitereview');
    $select = $tableImportFile->select();

    //IF IMPORT IS IN RUNNING STATUS FOR SOME FILE THAN DONT SHOW THE START BUTTON FOR ALL
    $importFileStatusData = $tableImportFile->fetchRow(array('status = ?' => 'Running'));
    $this->view->runningSomeImport = 0;
    if (!empty($importFileStatusData)) {
      $this->view->runningSomeImport = 1;
    }

    $values = array();
    if ($formFilter->isValid($this->_getAllParams())) {
      $values = $formFilter->getValues();
    }

    foreach ($values as $key => $value) {
      if (null === $value) {
        unset($values[$key]);
      }
    }

    $values = array_merge(array(
        'order' => 'importfile_id',
        'order_direction' => 'DESC',
            ), $values);

    $this->view->assign($values);

    $select->order((!empty($values['order']) ? $values['order'] : 'importfile_id' ) . ' ' . (!empty($values['order_direction']) ? $values['order_direction'] : 'DESC' ));

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $this->view->total_slideshows = $paginator->getTotalItemCount();
    $this->view->paginator->setItemCountPerPage(200);
    $this->view->paginator = $paginator->setCurrentPageNumber($sitereview);
  }

  //ACTION FOR STOP IMPORTING DATA
  public function stopAction() {

    //UPDATE THE STATUS TO STOP
    $session = new Zend_Session_Namespace();
    $importfile_id = $session->importfile_id;

    if (empty($importfile_id)) {
      $importfile_id = $this->_getParam('importfile_id');
    }

    if (empty($importfile_id)) {
      return;
    }

    $importFile = Engine_Api::_()->getItem('sitereview_importfile', $importfile_id);
    $importFile->status = 'Stopped';
    $importFile->save();

    //UNSET THE SESSION VARIABLE
    if (isset($session->importfile_id)) {
      unset($session->importfile_id);
    }

    //REDIRECTING TO MANAGE SITEREVIEW IF FORCE STOP
    $forceStop = $this->_getParam('forceStop');
    if (!empty($forceStop)) {
      //return $this->_helper->redirector->gotoRoute(array('action' => 'manage'));
      $this->_redirect('admin/sitereview/importlisting/manage');
    }
  }

  //ACTION FOR ROLLBACK IMPORTING DATA
  public function rollbackAction() {

    //INCREASE THE MEMORY ALLOCATION SIZE AND INFINITE SET TIME OUT
    ini_set('memory_limit', '2048M');
    set_time_limit(0);

    $this->_helper->layout->setLayout('admin-simple');
    $this->view->importfile_id = $importfile_id = $this->_getParam('importfile_id');

    //FETCH IMPORT FILE OBJECT
    $importFile = Engine_Api::_()->getItem('sitereview_importfile', $importfile_id);

    //IF STATUS IS PENDING THAN RETURN
    if ($importFile->status == 'Pending') {
      return;
    }

    $returend_current_listing_id = $this->_getParam('current_listing_id');

    $redirect = 0;
    if (isset($_GET['redirect'])) {
      $redirect = $_GET['redirect'];
    }

    if (empty($redirect) && isset($_POST['redirect'])) {
      $redirect = $_POST['redirect'];
    }

    //START ROLLBACK IF CONFIRM BY USER OR RETURNED CURRENT SITEREVIEW ID IS NOT EMPTY
    if (!empty($redirect)) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {

        $first_listing_id = $importFile->first_listing_id;
        $last_listing_id = $importFile->last_listing_id;

        if (!empty($first_listing_id) && !empty($last_listing_id)) {
          $sitereviewTable = Engine_Api::_()->getDbtable('listings', 'sitereview');

          $current_listing_id = $first_listing_id;

          if (!empty($returend_current_listing_id)) {
            $current_listing_id = $returend_current_listing_id;
          }

          //MAKE QUERY
          $sqlStr = "listing_id BETWEEN " . "'" . $current_listing_id . "'" . " AND " . "'" . $last_listing_id . "'" . "";

          $select = $sitereviewTable->select()
                  ->from($sitereviewTable->info('name'), array('listing_id'))
                  ->where($sqlStr);
          $sitereviewDatas = $select->query()->fetchAll();

          if (!empty($sitereviewDatas)) {
            $rollback_count = 0;
            foreach ($sitereviewDatas as $sitereviewData) {
              $listing_id = $sitereviewData['listing_id'];

              //DELETE SITEREVIEW
              $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
              $sitereview->delete();

              $db->commit();

              $rollback_count++;

              //REDIRECTING TO SAME ACTION AFTER EVERY 100 ROLLBACKS
              if ($rollback_count >= 100) {
                $current_listing_id = $listing_id + 1;
                $this->_redirect("admin/sitereview/importlisting/rollback?importfile_id=$importfile_id&current_listing_id=$current_listing_id&redirect=1");
              }
            }
          }
        }
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      //UPDATE THE DATA IN engine4_sitereview_importfiles TABLE
      $importFile->status = 'Pending';
      $importFile->first_listing_id = 0;
      $importFile->last_listing_id = 0;
      $importFile->current_import_id = $importFile->first_import_id;
      $importFile->save();

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('Rollbacked successfully !'))
      ));
    }
    $this->renderScript('admin-importlisting/rollback.tpl');
  }

  //ACTION FOR DELETE IMPORT FILES AND IMPORT DATA
  public function deleteAction() {

    //SET LAYOUT
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->importfile_id = $importfile_id = $this->_getParam('importfile_id');

    //IF CONFIRM FOR DATA DELETION
    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        //IMPORT FILE OBJECT
        $importFile = Engine_Api::_()->getItem('sitereview_importfile', $importfile_id);
        
        if (!empty($importFile)) {
          $import_image_path = 'sitereview_importfiles_'.$importfile_id;
          if(file_exists(APPLICATION_PATH . "/public/".$import_image_path)) {
            foreach(scandir(APPLICATION_PATH . "/public/".$import_image_path) as $fileName) {
              if(file_exists(APPLICATION_PATH . "/public/".$import_image_path.'/'.$fileName)) {
                if($fileName == $importFile->photo_filename) {
                  @unlink(APPLICATION_PATH . "/public/".$import_image_path.'/'.$fileName);
                }
                else {
                  $explode_string = explode('.',$importFile->photo_filename);
                  if(file_exists(APPLICATION_PATH . "/public/".$import_image_path.'/'.$explode_string[0])) {
										foreach(scandir(APPLICATION_PATH . "/public/".$import_image_path.'/'.$explode_string[0]) as $fileName) {
										  if(file_exists(APPLICATION_PATH . "/public/".$import_image_path.'/'.$explode_string[0].'/'.$fileName)) {
												@unlink(APPLICATION_PATH . "/public/".$import_image_path.'/'.$explode_string[0].'/'.$fileName);
											}
										}
										@rmdir(APPLICATION_PATH . "/public/".$import_image_path.'/'.$explode_string[0]);
									}
								}
							}
						}	
						@rmdir(APPLICATION_PATH . "/public/".$import_image_path);
					}
          $first_import_id = $importFile->first_import_id;
          $last_import_id = $importFile->last_import_id;

          //MAKE QUERY FOR FETCH THE DATA
          $tableImport = Engine_Api::_()->getDbtable('imports', 'sitereview');

          $sqlStr = "import_id BETWEEN " . "'" . $first_import_id . "'" . " AND " . "'" . $last_import_id . "'" . "";

          $select = $tableImport->select()
                  ->from($tableImport->info('name'), array('import_id'))
                  ->where($sqlStr);
          $importDatas = $select->query()->fetchAll();

          if (!empty($importDatas)) {
            foreach ($importDatas as $importData) {
              $import_id = $importData['import_id'];

              //DELETE IMPORT DATA BELONG TO IMPORT FILE
              $tableImport->delete(array('import_id = ?' => $import_id));
            }
          }

          //FINALLY DELETE IMPORT FILE DATA
          Engine_Api::_()->getDbtable('importfiles', 'sitereview')->delete(array('importfile_id = ?' => $importfile_id));
        }

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('Import data has been deleted successfully !'))
      ));
    }
    $this->renderScript('admin-importlisting/delete.tpl');
  }

  //ACTION FOR DELETE SLIDESHOW AND THEIR BELONGINGS
  public function multiDeleteAction() {

    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();

      //IF ADMIN CLICK ON DELETE SELECTED BUTTON

      foreach ($values as $key => $value) {
        if ($key == 'd_' . $value) {
          $importfile_id = (int) $value;
          $db = Engine_Db_Table::getDefaultAdapter();
          $db->beginTransaction();
          try {
            //IMPORT FILE OBJECT
            $importFile = Engine_Api::_()->getItem('sitereview_importfile', $importfile_id);

            if (!empty($importFile)) {
              $import_image_path = 'sitereview_importfiles_'.$importfile_id;
							if(file_exists(APPLICATION_PATH . "/public/".$import_image_path)) {
								foreach(scandir(APPLICATION_PATH . "/public/".$import_image_path) as $fileName) {
									if(file_exists(APPLICATION_PATH . "/public/".$import_image_path.'/'.$fileName)) {
										if($fileName == $importFile->photo_filename) {
											@unlink(APPLICATION_PATH . "/public/".$import_image_path.'/'.$fileName);
										}
										else {
											$explode_string = explode('.',$importFile->photo_filename);
											if(file_exists(APPLICATION_PATH . "/public/".$import_image_path.'/'.$explode_string[0])) {
												foreach(scandir(APPLICATION_PATH . "/public/".$import_image_path.'/'.$explode_string[0]) as $fileName) {
													if(file_exists(APPLICATION_PATH . "/public/".$import_image_path.'/'.$explode_string[0].'/'.$fileName)) {
														@unlink(APPLICATION_PATH . "/public/".$import_image_path.'/'.$explode_string[0].'/'.$fileName);
													}
												}
												@rmdir(APPLICATION_PATH . "/public/".$import_image_path.'/'.$explode_string[0]);
											}
										}
									}
								}	
								@rmdir(APPLICATION_PATH . "/public/".$import_image_path);
							}
              $first_import_id = $importFile->first_import_id;
              $last_import_id = $importFile->last_import_id;

              //MAKE QUERY FOR FETCH THE DATA
              $tableImport = Engine_Api::_()->getDbtable('imports', 'sitereview');

              $sqlStr = "import_id BETWEEN " . "'" . $first_import_id . "'" . " AND " . "'" . $last_import_id . "'" . "";

              $select = $tableImport->select()
                      ->from($tableImport->info('name'), array('import_id'))
                      ->where($sqlStr);
              $importDatas = $select->query()->fetchAll();

              if (!empty($importDatas)) {
                foreach ($importDatas as $importData) {
                  $import_id = $importData['import_id'];

                  //DELETE IMPORT DATA BELONG TO IMPORT FILE
                  $tableImport->delete(array('import_id = ?' => $import_id));
                }
              }

              //FINALLY DELETE IMPORT FILE DATA
              Engine_Api::_()->getDbtable('importfiles', 'sitereview')->delete(array('importfile_id = ?' => $importfile_id));
            }

            $db->commit();
          } catch (Exception $e) {
            $db->rollBack();
            throw $e;
          }
        }
      }
    }
    return $this->_helper->redirector->gotoRoute(array('action' => 'manage'));
  }

  //ACTION FOR DOWNLOADING THE CSV TEMPLATE FILE
  public function downloadAction() {

    $path_import = $this->_getPathImport();

    $db = Engine_Db_Table::getDefaultAdapter();
    $import_id = $db->select('importfile_id')
            ->from('engine4_sitereview_importfiles')
            ->query()
            ->fetchColumn();

    if (file_exists($path_import) && is_file($path_import) && !empty($import_id)) {
        
			//KILL ZEND'S OB
			$isGZIPEnabled = false;
			if (ob_get_level()) {
					$isGZIPEnabled = true;
						@ob_end_clean();
			}

      header("Content-Disposition: attachment; filename=" . urlencode(basename($path_import)), true);
      header("Content-Transfer-Encoding: Binary", true);
      header("Content-Type: application/x-tar", true);
      //header("Content-Type: application/force-download", true);
      header("Content-Type: application/octet-stream", true);
      header("Content-Type: application/download", true);
      header("Content-Description: File Transfer", true);
			if(empty($isGZIPEnabled)){
				header("Content-Length: " . filesize($path_import), true);
			}       
      readfile("$path_import");
    } else {
      $path = $this->_getPath();
      $file_path = "$path/previous_listing_import.csv";

      @chmod($path, 0777);
      @chmod($file_path, 0777);

      $listingTypeCount = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount();

      $file_string = "";
      if ($listingTypeCount == 1) {
        $file_string = "Title|Category|Sub-Category|3rd Level Category|Description|Overview|Tag_String|Location|Price|img_name";
      } else {
        $file_string = "Title|Singular/Plural Slug of Existing Listing Type|Category|Sub-Category|3rd Level Category|Description|Overview|Tag_String|Location|Price|Photo Name.ext";
      }

      @chmod($path, 0777);
      @chmod($file_path, 0777);
      $fp = fopen(APPLICATION_PATH . '/temporary/previous_listing_import.csv', 'w+');
      fwrite($fp, $file_string);
      fclose($fp);

        //KILL ZEND'S OB
        $isGZIPEnabled = false;
        if (ob_get_level()) {
            $isGZIPEnabled = true;
              @ob_end_clean();
        }

      $path = APPLICATION_PATH . "/temporary/previous_listing_import.csv";
      header("Content-Disposition: attachment; filename=" . urlencode(basename($path)), true);
      header("Content-Transfer-Encoding: Binary", true);
      //header("Content-Type: application/x-tar", true);
      //header("Content-Type: application/force-download", true);
      header("Content-Type: application/octet-stream", true);
      header("Content-Type: application/download", true);
      header("Content-Description: File Transfer", true);
        if(empty($isGZIPEnabled)){
          header("Content-Length: " . filesize($path), true);
        } 
      readfile("$path");
    }

    exit();
  }

  //ACTION FOR DOWNLOADING THE CSV TEMPLATE FILE
  public function downloadSampleAction() {

    $path = $this->_getPath();
    $file_path = "$path/example_listing_import.csv";

    @chmod($path, 0777);
    @chmod($file_path, 0777);

    $listingTypeCount = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount();

    $file_string = "";
    if ($listingTypeCount == 1) {
      $file_string = "Title|Category|Sub-Category|3rd Level Category|Description|Overview|Tag_String|Location|Price|img_name";
    } else {
      $file_string = "Title|Singular/Plural Slug of Existing Listing Type|Category|Sub-Category|3rd Level Category|Description|Overview|Tag_String|Location|Price|img_name";
    }

    @chmod($path, 0777);
    @chmod($file_path, 0777);
    $fp = fopen(APPLICATION_PATH . '/temporary/example_listing_import.csv', 'w+');
    fwrite($fp, $file_string);
    fclose($fp);

    //KILL ZEND'S OB
    $isGZIPEnabled = false;
    if (ob_get_level()) {
        $isGZIPEnabled = true;
          @ob_end_clean();
    }

    $path = APPLICATION_PATH . "/temporary/example_listing_import.csv";
    header("Content-Disposition: attachment; filename=" . urlencode(basename($path)), true);
    header("Content-Transfer-Encoding: Binary", true);
    //header("Content-Type: application/x-tar", true);
    // header("Content-Type: application/force-download", true);
    header("Content-Type: application/octet-stream", true);
    header("Content-Type: application/download", true);
    header("Content-Description: File Transfer", true);
    if(empty($isGZIPEnabled)){
      header("Content-Length: " . filesize($path), true);
    } 
    
    readfile("$path");

    exit();
  }

  protected function _getPath($key = 'path') {

    $basePath = realpath(APPLICATION_PATH . "/temporary");
    return $this->_checkPath($this->_getParam($key, ''), $basePath);
  }

  protected function _getPathImport($key = 'path') {

    $basePath = realpath(APPLICATION_PATH . "/public/sitereview_listing");
    return $this->_checkPath($this->_getParam($key, ''), $basePath);
  }

  protected function _checkPath($path, $basePath) {

    //SANATIZE
    $path = preg_replace('/\.{2,}/', '.', $path);
    $path = preg_replace('/[\/\\\\]+/', '/', $path);
    $path = trim($path, './\\');
    $path = $basePath . '/' . $path;

    //Resolve
    $basePath = realpath($basePath);
    $path = realpath($path);

    //CHECK IF THIS IS A PARENT OF THE BASE PATH
    if ($basePath != $path && strpos($basePath, $path) !== false) {
      return $this->_helper->redirector->gotoRoute(array());
    }
    return $path;
  }

  public function showCountryAction() {

    $locale = Zend_Registry::get('Zend_Translate')->getLocale();
    $territories = Zend_Locale::getTranslationList('territory', $locale, 2);
    $fieldType = $this->_getParam('field');

    if ($fieldType == 'ethnicity') {
      $this->view->values = array(
          'asian' => 'Asian',
          'black' => 'Black / African descent',
          'hispanic' => 'Latino / Hispanic',
          'pacific' => 'Pacific Islander',
          'white' => 'White / Caucasian',
          'other' => 'Other'
      );
    } elseif ($fieldType == 'occupation') {
      $this->view->values = array('admn' => 'Administrative / Secretarial',
          'arch' => 'Architecture / Interior design',
          'crea' => 'Artistic / Creative / Performance',
          'educ' => 'Education / Teacher / Professor',
          'mngt' => 'Executive / Management',
          'fash' => 'Fashion / Model / Beauty',
          'fina' => 'Financial / Accounting / Real Estate',
          'labr' => 'Labor / Construction',
          'lawe' => 'Law enforcement / Security / Military',
          'legl' => 'Legal',
          'medi' => 'Medical / Dental / Veterinary / Fitness',
          'nonp' => 'Nonprofit / Volunteer / Activist',
          'poli' => 'Political / Govt / Civil Service / Military',
          'retl' => 'Retail / Food services',
          'retr' => 'Retired',
          'sale' => 'Sales / Marketing',
          'self' => 'Self-Employed / Entrepreneur',
          'stud' => 'Student',
          'tech' => 'Technical / Science / Computers / Engineering',
          'trav' => 'Travel / Hospitality / Transportation',
          'othr' => 'Other profession'
      );
    } elseif ($fieldType == 'education_level') {
      $this->view->values = array(
          'high_school' => 'High School',
          'some_college' => 'Some College',
          'associates' => 'Associates Degree',
          'bachelors' => 'Bachelors Degree',
          'graduate' => 'Graduate Degree',
          'phd' => 'PhD / Post Doctoral'
      );
    } elseif ($fieldType == 'relationship_status') {
      $this->view->values = array(
          'single' => 'Single',
          'relationship' => 'In a Relationship',
          'engaged' => 'Engaged',
          'married' => 'Married',
          'complicated' => 'Its Complicated',
          'open' => 'In an Open Relationship',
          'widow' => 'Widowed'
      );
    } elseif ($fieldType == 'looking_for') {
      $this->view->values = array(
          'friendship' => 'Friendship',
          'dating' => 'Dating',
          'relationship' => 'A Relationship',
          'networking' => 'Networking'
      );
    } elseif ($fieldType == 'weight') {
      $this->view->values = array(
          'slender' => 'Slender',
          'average' => 'Average',
          'athletic' => 'Athletic',
          'heavy' => 'Heavy',
          'stocky' => 'Stocky',
          'little_fat' => 'A few extra pounds'
      );
    } elseif ($fieldType == 'religion') {
      $this->view->values = array(
          'agnostic' => 'Agnostic',
          'atheist' => 'Atheist',
          'buddhist' => 'Buddhist',
          'taoist' => 'Taoist',
          'catholic' => 'Christian (Catholic)',
          'mormon' => 'Christian (LDS)',
          'protestant' => 'Christian (Protestant)',
          'hindu' => 'Hindu',
          'jewish' => 'Jewish',
          'muslim' => 'Muslim ',
          'spiritual' => 'Spiritual',
          'other' => 'Other'
      );
    } elseif ($fieldType == 'political_views') {
      $this->view->values = array(
          'mid' => 'Middle of the Road',
          'far_right' => 'Very Conservative',
          'right' => 'Conservative',
          'left' => 'Liberal',
          'far_left' => 'Very Liberal',
          'anarchy' => 'Non-conformist',
          'libertarian' => 'Libertarian',
          'green' => 'Green',
          'other' => 'Other'
      );
    } elseif ($fieldType == 'income') {
      $this->view->values = array(
          '0' => 'Less than $25,000',
          '25_35' => '$25,001 to $35,000',
          '35_50' => '$35,001 to $50,000',
          '50_75' => '$50,001 to $75,000',
          '75_100' => '$75,001 to $100,000',
          '100_150' => '$100,001 to $150,000',
          '1' => '$150,001'
      );
    } elseif ($fieldType == 'partner_gender') {
      $this->view->values = array(
          'men' => 'Men',
          'women' => 'Women'
      );
    } elseif ($fieldType == 'country') {
      $this->view->values = $territories;
    } elseif ($fieldType == 'zodiac') {
      $this->view->values = array(
          'apricorn' => 'Apricorn',
          'aquarius' => 'Aquarius',
          'pisces' => 'Pisces',
          'aries' => 'Aries',
          'taurus' => 'Taurus',
          'gemini' => 'Gemini',
          'cancer' => 'Cancer',
          'leo' => 'Leo',
          'virgo' => 'Virgo',
          'libra' => 'Libra',
          'scorpio' => 'Scorpio',
          'sagittarius' => 'Sagittarius'
      );
    }
    elseif($fieldType == 'date') {
			$this->view->values = array(
          'YYYY-MM-DD' => '2013-8-15'
      );
    }
  }

  public function uploadAction() {
    $importfile_id = $this->_getParam('importfile_id');
    $basePath = 'sitereview_importfiles_' . $importfile_id;

    if (is_dir(APPLICATION_PATH . "/public/$basePath") != 1) {
      @mkdir(APPLICATION_PATH . "/public/$basePath", 0777, true);
    }

    @chmod(APPLICATION_PATH . "/public/$basePath", 0777);
    $this->view->path = $path = APPLICATION_PATH . "/public/$basePath";

    // Check method
    if (!$this->getRequest()->isPost()) {
      return;
    }

    // Check ul bit
    if (null === $this->_getParam('ul')) {
      return;
    }

    // Prepare
    if (empty($_FILES['Filedata'])) {
      $this->view->error = 'File failed to upload. Check your server settings (such as php.ini max_upload_filesize).';
      return;
    }

    // Prevent evil files from being uploaded
    $disallowedExtensions = array('zip');
    if (!in_array(end(explode(".", $_FILES['Filedata']['name'])), $disallowedExtensions)) {
      $this->view->error = 'File type or extension forbidden.';
      return;
    }


    $info = $_FILES['Filedata'];
    $targetFile = $path . '/' . $info['name'];
    $vals = array();

    if (file_exists($targetFile)) {
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("File already exists.");
      return;
    }

    // Try to move uploaded file
    if (!move_uploaded_file($info['tmp_name'], $targetFile)) {
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Unable to move file to upload directory.");
      return;
    }

    $reviewimportfileTable = Engine_Api::_()->getDbtable('importfiles', 'sitereview');
    
    $selectImportTable = $reviewimportfileTable->select()
                           ->from($reviewimportfileTable->info('name'),'photo_filename')
														->where('importfile_id = ?',$importfile_id);
		$fileName = $selectImportTable->query()->fetchColumn();
    
    if(!empty($fileName)) {
      $import_image_path = 'sitereview_importfiles_'.$importfile_id;
      @unlink(APPLICATION_PATH . "/public/".$import_image_path.'/'.$fileName);
    }
    
    $reviewimportfileTable->update(array('photo_filename' => $_FILES['Filedata']['name']), array('importfile_id = ?' => $importfile_id));

    $this->view->target_path = $info['tmp_name'];
    $this->view->status = 1;

    // Redirect
    if (null === $this->_helper->contextSwitch->getCurrentContext()) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    } else if ('smoothbox' === $this->_helper->contextSwitch->getCurrentContext()) {
      return $this->_forward('success', 'utility', 'core', array(
                  'smoothboxClose' => true,
                  'parentRefresh' => true,
              ));
    }
  }

  public function uploadPhotoAction() {
    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_import');

    $this->view->importfile_id = $importfile_id = $this->_getParam('importfile_id');

    $this->view->importFile = Engine_Api::_()->getItem('sitereview_importfile', $importfile_id);

    $tableImportFile = Engine_Api::_()->getDbTable('importfiles', 'sitereview');
    $select = $tableImportFile->select();

    //IF IMPORT IS IN RUNNING STATUS FOR SOME FILE THAN DONT SHOW THE START BUTTON FOR ALL
    $importFileStatusData = $tableImportFile->fetchRow(array('status = ?' => 'Running'));
    $this->view->runningSomeImport = 0;
    if (!empty($importFileStatusData)) {
      $this->view->runningSomeImport = 1;
    }
  }

}
