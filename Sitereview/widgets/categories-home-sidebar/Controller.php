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
class Sitereview_Widget_CategoriesHomeSidebarController extends Engine_Content_Widget_Abstract {

    public function indexAction() {

        //GET LISTINGTYPES
        $listingTypesArray = array();
        $params = array();
        $params['visible'] = 1;
        $params['member_level_allow'] = 1;
        $listingTypes = $this->_getCachedListingType(0, $params);

        //RENDER IF LISTING TYPES ARE MORE THEN ONE
        $listingTypesCount = Count($listingTypes);
        if ($listingTypesCount <= 1) {
            $this->setNoRender();
        }

        //GET LISTING CATEGORY TABLE
        $this->view->tableCategory = $tableCategory = Engine_Api::_()->getDbTable('categories', 'sitereview');

        $this->view->listingTypesArray = $this->_getCachedHierarchy($listingTypes, $tableCategory);

        if (!(count($this->view->listingTypesArray) > 0)) {
            return $this->setNoRender();
        }
    }
    
    protected function _getCachedListingType($listingtypeId, $params) {
        
        $cache = Zend_Registry::get('Zend_Cache');
        $cacheName = 'listtype_categories_listingtypes';
        
        $listingTypesArray = $cache->load($cacheName);
        if (!empty($listingTypesArray)) {
            return $listingTypesArray;
        } else {
            return Engine_Api::_()->getDbtable('listingtypes', 'sitereview')->getListingTypes($listingtypeId, $params);
        }        
    }    

    protected function _getCachedHierarchy($listingTypes, $tableCategory) {

        $cache = Zend_Registry::get('Zend_Cache');
        $cacheName = 'categories_home_sidebar';

        $listingTypesArray = $cache->load($cacheName);
        if (!empty($listingTypesArray)) {
            return $listingTypesArray;
        } else {

            $listingTypesArray = array();
            foreach ($listingTypes as $listingType) {

                $categoriesArray = array();
                $categories = $tableCategory->getCategories(null, 0, $listingType->listingtype_id, 0, 1, 0, 'cat_order', 0, array('category_id', 'category_name'));
                foreach ($categories as $category) {

                    $subcategoriesArray[$category->category_id] = array();
                    $subcategories = $tableCategory->getSubcategories($category->category_id, array('category_id', 'category_name'));
                    foreach ($subcategories as $subcategory) {
                        $subcategoriesArray[$category->category_id][] = array(
                            'subcategory_id' => $subcategory->category_id,
                            'subcategory_name' => $subcategory->category_name,
                        );
                    }

                    $tmp_categoriesArray = array(
                        'category_id' => $category->category_id,
                        'category_name' => $category->category_name,
                        'subcategories' => $subcategoriesArray[$category->category_id],
                    );
                    $categoriesArray[] = $tmp_categoriesArray;
                }

                $listingTypesArray[] = array(
                    'listingtype_id' => $listingType->listingtype_id,
                    'title_plural' => $listingType->title_plural,
                    'categories' => $categoriesArray
                );
            }

            $cache->setLifetime(7 * 86400);
            $cache->save($listingTypesArray, $cacheName);

            return $listingTypesArray;
        }
    }

}
