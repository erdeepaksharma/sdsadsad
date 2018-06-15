<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Widget_CategoriesMiddleSitereviewController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $this->view->show3rdlevelCategory = $show3rdlevelCategory = $this->_getParam('show3rdlevelCategory', 1);
    $this->view->show2ndlevelCategory = $show2ndlevelCategory = $this->_getParam('show2ndlevelCategory', 1);
    $showCount = $this->_getParam('showCount', 0);
    $showAllCategories = $this->_getParam('showAllCategories', 0);

    //GET LISTING TYPE ID
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id');
    if (empty($listingtype_id)) {
      $this->view->listingtype_id = $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);
    }
    
    $this->view->sitereviewCategoryMiddleReview = $sitereviewCategoryMiddleReview = Zend_Registry::isRegistered('sitereviewCatMiddReview') ?  Zend_Registry::get('sitereviewCatMiddReview') : null;

    $this->view->tableCategory = $tableCategory = Engine_Api::_()->getDbtable('categories', 'sitereview');

    $this->view->categories = $categories = array();

    //GET LISTING TABLE
    $tableSitereview = Engine_Api::_()->getDbtable('listings', 'sitereview');

    if ($showAllCategories) {

      $category_info = $tableCategory->getCategories(null, 0, $listingtype_id, 0, 1, 0, 'cat_order', 0, array('category_id', 'category_name', 'cat_order', 'listingtype_id'));
      foreach ($category_info as $value) {

        $sub_cat_array = array();

        if (!empty($show2ndlevelCategory)) {

          $category_info2 = $tableCategory->getSubcategories($value->category_id, array('category_id', 'category_name', 'cat_order'));

          foreach ($category_info2 as $subresults) {

            if (!empty($show3rdlevelCategory)) {

              $subcategory_info2 = $tableCategory->getSubcategories($subresults->category_id);
              $treesubarrays[$subresults->category_id] = array();
              foreach ($subcategory_info2 as $subvalues) {
                if ($showCount) {
                  $treesubarrays[$subresults['category_id']][] = $treesubarray = array('tree_sub_cat_id' => $subvalues->category_id,
                      'tree_sub_cat_name' => $subvalues->category_name,
                      'count' => $tableSitereview->getListingsCount($subvalues->category_id, 'subsubcategory_id', $listingtype_id, 1),
                      'order' => $subvalues->cat_order,
                  );
                } else {
                  $treesubarrays[$subresults['category_id']][] = $treesubarray = array('tree_sub_cat_id' => $subvalues->category_id,
                      'tree_sub_cat_name' => $subvalues->category_name,
                      'order' => $subvalues->cat_order,
                  );
                }
              }

              if ($showCount) {
                $sub_cat_array[] = $tmp_array = array('sub_cat_id' => $subresults->category_id,
                    'sub_cat_name' => $subresults->category_name,
                    'tree_sub_cat' => $treesubarrays[$subresults->category_id],
                    'count' => $tableSitereview->getListingsCount($subresults->category_id, 'subcategory_id', $listingtype_id),
                    'order' => $subresults->cat_order);
              } else {
                $sub_cat_array[] = $tmp_array = array('sub_cat_id' => $subresults->category_id,
                    'sub_cat_name' => $subresults->category_name,
                    'tree_sub_cat' => $treesubarrays[$subresults->category_id],
                    'order' => $subresults->cat_order);
              }
            } else {
              if ($showCount) {
                $sub_cat_array[] = $tmp_array = array('sub_cat_id' => $subresults->category_id,
                    'sub_cat_name' => $subresults->category_name,
                    'count' => $tableSitereview->getListingsCount($subresults->category_id, 'subcategory_id', $listingtype_id, 1),
                    'order' => $subresults->cat_order);
              } else {
                $sub_cat_array[] = $tmp_array = array('sub_cat_id' => $subresults->category_id,
                    'sub_cat_name' => $subresults->category_name,
                    'order' => $subresults->cat_order);
              }
            }
          }
        }

        if ($showCount) {
          $categories[] = $category_array = array('category_id' => $value->category_id,
              'category_name' => $value->category_name,
              'order' => $value->cat_order,
              'count' => $tableSitereview->getListingsCount($value->category_id, 'category_id', $listingtype_id, 1),
              'sub_categories' => $sub_cat_array,
              'listingtype_id' => $value->listingtype_id
          );
        } else {
          $categories[] = $category_array = array('category_id' => $value->category_id,
              'category_name' => $value->category_name,
              'order' => $value->cat_order,
              'sub_categories' => $sub_cat_array,
              'listingtype_id' => $value->listingtype_id
          );
        }
      }
    } else {
      $category_info = $tableCategory->getCategorieshaslistings($listingtype_id, 0, 'category_id', null, array(), array('category_id', 'category_name', 'cat_order', 'listingtype_id'));
      foreach ($category_info as $value) {

        $sub_cat_array = array();

        if (!empty($show2ndlevelCategory)) {

          $category_info2 = $tableCategory->getCategorieshaslistings($listingtype_id, $value->category_id, 'subcategory_id', null, array(), array('category_id', 'category_name', 'cat_order'));

          foreach ($category_info2 as $subresults) {

            if (!empty($show3rdlevelCategory)) {

              $subcategory_info2 = $tableCategory->getCategorieshaslistings($listingtype_id, $subresults->category_id, 'subsubcategory_id', null, array(), array('category_id', 'category_name', 'cat_order'));
              $treesubarrays[$subresults->category_id] = array();
              foreach ($subcategory_info2 as $subvalues) {
                if ($showCount) {
                  $treesubarrays[$subresults['category_id']][] = $treesubarray = array('tree_sub_cat_id' => $subvalues->category_id,
                      'tree_sub_cat_name' => $subvalues->category_name,
                      'order' => $subvalues->cat_order,
                      'count' => $tableSitereview->getListingsCount($subvalues->category_id, 'subsubcategory_id', $listingtype_id, 1),
                  );
                } else {
                  $treesubarrays[$subresults['category_id']][] = $treesubarray = array('tree_sub_cat_id' => $subvalues->category_id,
                      'tree_sub_cat_name' => $subvalues->category_name,
                      'order' => $subvalues->cat_order
                  );
                }
              }

              if ($showCount) {
                $sub_cat_array[] = $tmp_array = array('sub_cat_id' => $subresults->category_id,
                    'sub_cat_name' => $subresults->category_name,
                    'tree_sub_cat' => $treesubarrays[$subresults->category_id],
                    'count' => $tableSitereview->getListingsCount($subresults->category_id, 'subcategory_id', $listingtype_id, 1),
                    'order' => $subresults->cat_order);
              } else {
                $sub_cat_array[] = $tmp_array = array('sub_cat_id' => $subresults->category_id,
                    'sub_cat_name' => $subresults->category_name,
                    'tree_sub_cat' => $treesubarrays[$subresults->category_id],
                    'order' => $subresults->cat_order);
              }
            } else {
              if ($showCount) {
                $sub_cat_array[] = $tmp_array = array('sub_cat_id' => $subresults->category_id,
                    'sub_cat_name' => $subresults->category_name,
                    'count' => $tableSitereview->getListingsCount($subresults->category_id, 'subcategory_id', $listingtype_id, 1),
                    'order' => $subresults->cat_order);
              } else {
                $sub_cat_array[] = $tmp_array = array('sub_cat_id' => $subresults->category_id,
                    'sub_cat_name' => $subresults->category_name,
                    'order' => $subresults->cat_order);
              }
            }
          }
        }

        if ($showCount) {
          $categories[] = $category_array = array('category_id' => $value->category_id,
              'category_name' => $value->category_name,
              'order' => $value->cat_order,
              'sub_categories' => $sub_cat_array,
              'listingtype_id' => $value->listingtype_id,
              'count' => $tableSitereview->getListingsCount($value->category_id, 'category_id', $listingtype_id, 1),
          );
        } else {
          $categories[] = $category_array = array('category_id' => $value->category_id,
              'category_name' => $value->category_name,
              'order' => $value->cat_order,
              'sub_categories' => $sub_cat_array,
              'listingtype_id' => $value->listingtype_id
          );
        }
      }
    }

    $this->view->categories = $categories;

    //SET NO RENDER
    if (!(count($this->view->categories) > 0) || empty($sitereviewCategoryMiddleReview)) {
      return $this->setNoRender();
    }
  }

}