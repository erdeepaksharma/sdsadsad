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
class Sitereview_Widget_ListtypesCategoriesController extends Engine_Content_Widget_Abstract {

    public function indexAction() {

        $listingtypeId = $this->_getParam('listingtype_id', -1);
        $this->view->viewDisplayHR = $this->_getParam('viewDisplayHR', 1);
        if ($this->view->viewDisplay) {
            $element = $this->getElement();
            $this->view->widgetTitle = $element->getTitle();
            $element->setTitle('');
        }

        $params = array();
        $params['visible'] = 1;
        $params['member_level_allow'] = 1;
        
        if($listingtypeId > 0) {
            $listingTypes = Engine_Api::_()->getDbtable('listingtypes', 'sitereview')->getListingTypes($listingtypeId, $params);
        }
        else {
            $listingTypes = $this->_getCachedListingType($listingtypeId, $params);
        }
        
        $sitereviewListtypeCategories = Zend_Registry::isRegistered('sitereviewListtypeCategories') ? Zend_Registry::get('sitereviewListtypeCategories') : null;
        $this->view->listingTypesCount = $listingTypesCount = Count($listingTypes);

        $beforeNavigation = $this->_getParam('beforeNavigation', 0);
        if (empty($listingTypesCount) || ($listingTypesCount <= 1 && ($beforeNavigation)) || empty($sitereviewListtypeCategories)) {
            return $this->setNoRender();
        }

        Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtypeId);
        
        //GET LISTING CATEGORY TABLE
        $this->view->tableCategory = $tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');

        $this->view->listingTypesArray = $this->_getCachedHierarchy($listingTypes, $tableCategory, $listingtypeId);

        $this->view->requestAllParams = $requestAllParams = Zend_Controller_Front::getInstance()->getRequest()->getParams();
    }
    
    protected function _getCachedListingType($listingtypeId, $params) {
        
        $cache = Zend_Registry::get('Zend_Cache');
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if (!empty($viewer_id)) {
            $viewer_level_id = Engine_Api::_()->user()->getViewer()->level_id;
          } else {
            $viewer_level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
          }
        
        
        if($listingtypeId == -1) {
            $cacheName = 'listtype_categories_all_listingtypes_'. $viewer_level_id;
        } else {
            $cacheName = 'listtype_categories_listingtypes_'. $viewer_level_id . '_'. $listingtypeId;
        }
        
        $listingTypesArray = $cache->load($cacheName);
        if (!empty($listingTypesArray)) {
            return $listingTypesArray;
        } else {
            return Engine_Api::_()->getDbtable('listingtypes', 'sitereview')->getListingTypes($listingtypeId, $params);
        }        
    }

    protected function _getCachedHierarchy($listingTypes, $tableCategory, $listingtype_id) {

        $cache = Zend_Registry::get('Zend_Cache');
        
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        if (!empty($viewer_id)) {
            $viewer_level_id = Engine_Api::_()->user()->getViewer()->level_id;
          } else {
            $viewer_level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
          }
          
        if($listingtype_id == -1) {
          $cacheName = 'listtype_categories_all_' . $viewer_level_id;
        } else {
            $cacheName = 'listtype_categories_'. $viewer_level_id . '_'. $listingtype_id;
        }
         
        $listingTypesArray = $cache->load($cacheName);
        if (!empty($listingTypesArray)) {
            return $listingTypesArray;
        } else {

            $listingTypesArray = array();
            foreach ($listingTypes as $listingType) {

                $categoriesArray = array();
                $categories = $tableCategory->getCategories(null, 0, $listingType->listingtype_id, 0, 1, 0, 'cat_order', 0, array('category_id', 'category_name', 'category_slug', 'listingtype_id', 'cat_dependency', 'subcat_dependency'));
                foreach ($categories as $category) {
                    $subcategoriesArray = array();
                    $subcategories = $tableCategory->getSubCategories($category->category_id, array('category_id', 'category_name', 'category_slug', 'listingtype_id', 'cat_dependency', 'subcat_dependency'));
                    foreach ($subcategories as $subcategory) {
                        $subsubcatgories = $tableCategory->getSubCategories($subcategory->category_id, array('category_id', 'category_name', 'category_slug', 'listingtype_id', 'cat_dependency', 'subcat_dependency'));
                        $ssb_cat_count = count($subsubcatgories);
                        if (empty($ssb_cat_count)) {
                            $subsubcatgories = array();
                        }
                        $subcategoriesArray[$subcategory->category_id] = array(
                            'subcategory' => $subcategory,
                            'subsubcatgories' => $subsubcatgories
                        );
                    }

                    $categoriesArray[$category->category_id] = array(
                        'category' => $category,
                        'subcategories' => $subcategoriesArray,
                    );
                }

                $listingTypesArray[$listingType->listingtype_id] = array(
                    'list_type' => $listingType,
                    'categories' => $categoriesArray
                );
            }
            
            $cache->setLifetime(7 * 86400);
            $cache->save($listingTypesArray, $cacheName);
            
            return $listingTypesArray;
        }
    }

}
