<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminProfilemapsReviewController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_AdminProfilemapsReviewController extends Core_Controller_Action_Admin {

  //ACTION FOR MANAGING THE PROFILE-CATEGORY MAPPING
  public function manageAction() {

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_review');

    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sitereview_admin_reviewmain', array(), 'sitereview_admin_reviewmain_profilemaps');

    //GET LISTINGTYPE ID
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 1);

    //CHECK REVIEW IS ALLOWED OR NOT
    $this->view->allowReviews = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeColumn($listingtype_id, 'reviews');

    //GET FIELD OPTION TABLE NAME
    $tableFieldOptions = Engine_Api::_()->getDbtable('optionsReview', 'sitereview');

    //GET TOTAL PROFILES
    $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('sitereview_review');
    $this->view->totalProfileTypes = 1;
    if (count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type_review') {
      $profileTypeField = $topStructure[0]->getChild();
      $options = $profileTypeField->getOptions();
      $this->view->totalProfileTypes = Count($options);
    }

    //GET REVIEW PARAMETER TABLE NAME
    $tableReviewCats = Engine_Api::_()->getDbtable('ratingparams', 'sitereview');
    $tableReviewCatsName = $tableReviewCats->info('name');

    $tableCategory = Engine_Api::_()->getDbtable('categories', 'sitereview');
    $categories = array();
    $category_info = $tableCategory->getCategories(null, 0, $this->view->listingtype_id, 0, 1, 0, 'cat_order', 0, array('category_id', 'category_name', 'cat_order', 'profile_type_review'));
    foreach ($category_info as $value) {

      $sub_cat_array = array();
      $category_info2 = $tableCategory->getSubCategories($value->category_id, array('category_id', 'profile_type_review', 'cat_order', 'category_name'));
      foreach ($category_info2 as $subresults) {

        $treesubarray = array();
        $subcategory_info2 = $tableCategory->getSubCategories($subresults->category_id, array('category_id', 'profile_type_review', 'cat_order', 'category_name'));
        $treesubarrays[$subresults->category_id] = array();
        foreach ($subcategory_info2 as $subvalues) {

          $tree_profile_type_review_label = '---';
          if (!empty($subvalues->profile_type_review)) {
            $tree_profile_type_review_label = $tableFieldOptions->getProfileTypeLabelReview($subvalues->profile_type_review);
          }

          $treesubarrays[$subresults->category_id][] = $treesubarray = array(
              'tree_sub_cat_id' => $subvalues->category_id,
              'tree_sub_cat_name' => $subvalues->category_name,
              'order' => $subvalues->cat_order,
              'tree_profile_type_review_id' => $subvalues->profile_type_review,
              'tree_profile_type_review_label' => $tree_profile_type_review_label
          );
        }

        $subcat_profile_type_review_label = '---';
        if (!empty($subresults->profile_type_review)) {
          $subcat_profile_type_review_label = $tableFieldOptions->getProfileTypeLabelReview($subresults->profile_type_review);
        }

        $tmp_array = array('sub_cat_id' => $subresults->category_id,
            'sub_cat_name' => $subresults->category_name,
            'tree_sub_cat' => $treesubarrays[$subresults->category_id],
            'order' => $subresults->cat_order,
            'subcat_profile_type_review_id' => $subresults->profile_type_review,
            'subcat_profile_type_review_label' => $subcat_profile_type_review_label
        );
        $sub_cat_array[] = $tmp_array;
      }

      $cat_profile_type_review_label = '---';
      if (!empty($value->profile_type_review)) {
        $cat_profile_type_review_label = $tableFieldOptions->getProfileTypeLabelReview($value->profile_type_review);
      }

      $categories[] = $category_array = array(
          'category_id' => $value->category_id,
          'category_name' => $value->category_name,
          'order' => $value->cat_order,
          'sub_categories' => $sub_cat_array,
          'cat_profile_type_review_id' => $value->profile_type_review,
          'cat_profile_type_review_label' => $cat_profile_type_review_label,
      );
    }

    $this->view->categories = $categories;
  }

  //ACTION FOR MAP THE PROFILE WITH CATEGORY
  public function mapAction() {

    //DEFAULT LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    //GET CATEGORY ID
    $this->view->category_id = $category_id = $this->_getParam('category_id');

    //GET CHIELD MAPPING
    $chieldMapping = Engine_Api::_()->getDbTable('categories', 'sitereview')->getChildMapping($category_id, 'profile_type_review');
    $countChieldMapping = Count($chieldMapping);

    //GENERATE THE FORM
    $this->view->form = $form = new Sitereview_Form_Admin_Profilemapsreview_Map(array('countChieldMapping' => $countChieldMapping));

    //GET MAPPING ITEM
    $category = Engine_Api::_()->getItem('sitereview_category', $category_id);

    //POST DATA
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      //GET DATA
      $tempCatFlag = false;
      $values = $form->getValues();

      //GET REVIEW TABLE
      $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
      $reviewTableName = $reviewTable->info('name');

      //GET LISTING TABLE
      $listingTable = Engine_Api::_()->getDbTable('listings', 'sitereview');
      $listingTableName = $listingTable->info('name');

      //BEGIN TRANSCATION
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();

      try {

        include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license2.php';
        if( empty($tempCatFlag) ) {
          return;
        }
        //IF YES BUTTON IS CLICKED THEN CHANGE MAPPING OF ALL LISTINGS
        if (isset($_POST['yes_button'])) {

          if (!empty($countChieldMapping)) {

            $chieldMappingArray = array();
            foreach ($chieldMapping as $chieldMappingItem) {
              $chieldMappingArray[] = $chieldMappingItem->category_id;
            }

            //HAVE TO UPDATE PREVIOUS MAPPED LISTINGS
            $select = $reviewTable->select()
                    ->from($reviewTableName, array('review_id'))
                    ->joinLeft($listingTableName, "$listingTableName.listing_id = $reviewTableName.resource_id", array())
                    ->where('resource_type = ?', 'sitereview_listing')
                    ->where("category_id = $category_id OR category_id IN (?) OR subcategory_id IN (?) OR subsubcategory_id IN (?)", $chieldMappingArray)
                    ->where("profile_type_review != ?", $values['profile_type_review']);
            $reviews = $reviewTable->fetchAll($select);

            $chieldMappingArrayStr = "(" . join(",", $chieldMappingArray) . ")";
            Zend_Db_Table_Abstract::getDefaultAdapter()->query("UPDATE `engine4_sitereview_categories` SET `profile_type_review` = 0 WHERE category_id IN $chieldMappingArrayStr");
          } else {
            //SELECT LISTINGS WHICH HAVE THIS CATEGORY AND THIS PROFILE TYPE
            $reviews = $reviewTable->getMappedReviews($category_id, 'sitereview_listing');
          }

          if (!empty($reviews)) {
            foreach ($reviews as $review) {
              $review_id = $review['review_id'];

              //GET FIELD VALUE TABLE
              $fieldvalueTable = Engine_Api::_()->fields()->getTable('sitereview_review', 'values');

              //DELETE ALL MAPPING VALUES FROM FIELD TABLES
              Engine_Api::_()->fields()->getTable('sitereview_review', 'values')->delete(array('item_id = ?' => $review_id));
              Engine_Api::_()->fields()->getTable('sitereview_review', 'search')->delete(array('item_id = ?' => $review_id));

              //PUT NEW PROFILE TYPE
              $fieldvalueTable->insert(array(
                  'item_id' => $review_id,
                  'field_id' => 1,
                  'index' => 0,
                  'value' => $category->profile_type_review,
              ));

              $reviewTable->update(array('profile_type_review' => $category->profile_type_review), array('review_id = ?' => $review_id));
            }
          }
        }

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('Mapping has been done successfully.'))
      ));
    }

    $this->renderScript('admin-profilemaps-review/map.tpl');
  }

  public function editAction() {

    //DEFAULT LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    //GET CATEGORY ID
    $this->view->category_id = $category_id = $this->_getParam('category_id');

    //GET PROFILE TYPE
    $old_profile_type_review_id = $this->_getParam('profile_type_review');

    $topStructure = Engine_Api::_()->fields()->getFieldStructureTop('sitereview_review');
    $this->view->totalProfileTypes = 1;
    if (count($topStructure) == 1 && $topStructure[0]->getChild()->type == 'profile_type_review') {
      $profileTypeField = $topStructure[0]->getChild();
      $options = $profileTypeField->getOptions();
      $this->view->totalProfileTypes = Count($options);
    }

    //GENERATE THE FORM
    $this->view->form = $form = new Sitereview_Form_Admin_Profilemapsreview_Edit();

    //POST DATA
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      //GET DATA
      $values = $form->getValues();
      $new_profile_type_review_id = $values['profile_type_review'];

      if ($old_profile_type_review_id != $new_profile_type_review_id) {

        //BEGIN TRANSCATION
        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try {

          //GET MAPPING ITEM
          $category = Engine_Api::_()->getItem('sitereview_category', $category_id);

          //GET LISTING TABLE
          $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
          $reviewTableName = $reviewTable->info('name');

          //GET LISTING TABLE
          $listingTable = Engine_Api::_()->getDbTable('listings', 'sitereview');
          $listingTableName = $listingTable->info('name');

          //FOR CATEGORY
          if ($category->cat_dependency == 0 && $category->subcat_dependency == 0) {
            $select = $reviewTable->select()
                    ->from($reviewTableName, array('review_id'))
                    ->joinLeft($listingTableName, "$listingTableName.listing_id = $reviewTableName.resource_id", array())
                    ->where('resource_type = ?', 'sitereview_listing')
                    ->where('category_id = ?', $category->category_id)
                    ->where('subcategory_id = ?', 0)
            ;
          }
          //FOR SUBCATEGORY
          elseif ($category->cat_dependency != 0 && $category->subcat_dependency == 0) {
            $select = $reviewTable->select()
                    ->from($reviewTableName, array('review_id'))
                    ->joinLeft($listingTableName, "$listingTableName.listing_id = $reviewTableName.resource_id", array())
                    ->where('resource_type = ?', 'sitereview_listing')
                    ->where('subcategory_id = ?', $category->category_id)
                    ->where('subsubcategory_id = ?', 0)
            ;
          } elseif ($category->cat_dependency != 0 && $category->subcat_dependency != 0) {
            $select = $reviewTable->select()
                    ->from($reviewTableName, array('review_id'))
                    ->joinLeft($listingTableName, "$listingTableName.listing_id = $reviewTableName.resource_id", array())
                    ->where('resource_type = ?', 'sitereview_listing')
                    ->where('subsubcategory_id = ?', $category->category_id)
            ;
          }

          $listingIds = $select->query()->fetchAll(Zend_Db::FETCH_COLUMN);

          if (!empty($listingIds)) {

            $fieldmapsTable = Engine_Api::_()->getDbTable('mapsReview', 'sitereview');

            $old_meta_ids = $fieldmapsTable->getMappingIdsReview($old_profile_type_review_id);
            $new_meta_ids = $fieldmapsTable->getMappingIdsReview($new_profile_type_review_id);
            $array_diff = array_diff($old_meta_ids, $new_meta_ids);

            Engine_Api::_()->fields()->getTable('sitereview_review', 'values')->update(array('value' => $new_profile_type_review_id), array('item_id IN (?)' => "'" . join("',", $listingIds) . "'", 'field_id = ?' => 1, 'value = ?' => $old_profile_type_review_id));

            //DELETE UN-COMMON VALUES FROM CUSTOM TABLES
            if (!empty($array_diff)) {
              Engine_Api::_()->fields()->getTable('sitereview_review', 'values')->delete(array('item_id IN (?)' => "'" . join("',", $listingIds) . "'", 'field_id IN (?)' => "'" . join("',", $array_diff) . "'"));
            }

            //UPDATE PROFILE-TYPE VALUE IN LISTING TABLE
            $reviewTable->update(array('profile_type_review' => $new_profile_type_review_id), array('review_id IN (?)' => (array) $listingIds));
          }

          $category->profile_type_review = $new_profile_type_review_id;
          $category->save();

          $db->commit();
        } catch (Exception $e) {
          $db->rollBack();
          throw $e;
        }
      }

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('Mapping has been edited successfully.'))
      ));
    }
  }

  //ACTION FOR DELETE MAPPING 
  public function removeAction() {

    //DEFAULT LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    //GET MAPPING ID
    $this->view->category_id = $category_id = $this->_getParam('category_id');

    //GET CHILD CATEGORIES
    $categoryTable = Engine_Api::_()->getDbTable('categories', 'sitereview');
    $getChilds = $categoryTable->getChilds($category_id, array('category_id', 'profile_type_review'));
    $this->view->countChilds = Count($getChilds);

    //GET MAPPING ITEM
    $this->view->category = $category = Engine_Api::_()->getItem('sitereview_category', $category_id);

    //POST DATA
    if ($this->getRequest()->isPost()) {

      //BEGIN TRANSCATION
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {

        //GET REVIEW TABLE
        $reviewTable = Engine_Api::_()->getDbTable('reviews', 'sitereview');
        $reviewTableName = $reviewTable->info('name');

        //GET LISTING TABLE
        $listingTable = Engine_Api::_()->getDbTable('listings', 'sitereview');
        $listingTableName = $listingTable->info('name');

        if (!isset($_POST['import_profile'])) {

          //SELECT LISTINGS WHICH HAVE THIS CATEGORY
          $reviews = $reviewTable->getMappedReviews($category_id, 'sitereview_listing');
        } else {

          foreach ($getChilds as $getChild) {
            $child = Engine_Api::_()->getItem('sitereview_category', $getChild->category_id);
            $child->profile_type_review = $category->profile_type_review;
            $child->save();
          }

          //FOR CATEGORY
          if ($category->cat_dependency == 0 && $category->subcat_dependency == 0) {
            $select = $reviewTable->select()
                    ->from($reviewTableName, array('review_id'))
                    ->joinLeft($listingTableName, "$listingTableName.listing_id = $reviewTableName.resource_id", array())
                    ->where('resource_type = ?', 'sitereview_listing')
                    ->where('category_id = ?', $category->category_id)
                    ->where('subcategory_id = ?', 0)
            ;
          }
          //FOR SUBCATEGORY
          elseif ($category->cat_dependency != 0 && $category->subcat_dependency == 0) {
            $select = $reviewTable->select()
                    ->from($reviewTableName, array('review_id'))
                    ->joinLeft($listingTableName, "$listingTableName.listing_id = $reviewTableName.resource_id", array())
                    ->where('resource_type = ?', 'sitereview_listing')
                    ->where('subcategory_id = ?', $category->category_id)
                    ->where('subsubcategory_id = ?', 0)
            ;
          }

          $reviews = $reviewTable->fetchAll($select);

          if (!empty($reviews)) {
            $reviews = $reviews->toArray();
          }
        }

        foreach ($reviews as $review) {

          //GET LISTING ID
          $review_id = $review['review_id'];

          //DELETE ALL MAPPING VALUES FROM FIELD TABLES
          Engine_Api::_()->fields()->getTable('sitereview_review', 'values')->delete(array('item_id = ?' => $review_id));
          Engine_Api::_()->fields()->getTable('sitereview_review', 'search')->delete(array('item_id = ?' => $review_id));

          //UPDATE THE PROFILE TYPE OF ALREADY CREATED LISTINGS
          $reviewTable->update(array('profile_type_review' => 0), array('review_id = ?' => $review_id));
        }

        //DELETE MAPPING
        $category->profile_type_review = 0;
        $category->save();


        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('Mapping has been deleted successfully.'))
      ));
    }
    $this->renderScript('admin-profilemaps-review/remove.tpl');
  }

}