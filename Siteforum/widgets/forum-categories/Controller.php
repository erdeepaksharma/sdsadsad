<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Widget_ForumCategoriesController extends Engine_Content_Widget_Abstract {

    public function indexAction() {
  
        $viewer = Engine_Api::_()->user()->getViewer();

        $request = Zend_Controller_Front::getInstance()->getRequest();

        $category_id = $request->getParam('category_id');
        $subcategory_id = $request->getParam('subcategory_id');
        $siteforumCategories = Zend_Registry::isRegistered('siteforumCategories') ? Zend_Registry::get('siteforumCategories') : null;
        $show_icon = $this->_getParam('show_icon', array("0" => "category", "1" => "subcategory", "2" => "forum"));
        $categoryTable = Engine_Api::_()->getDbTable('categories', 'siteforum');
        $forumTable = Engine_Api::_()->getDbTable('forums', 'siteforum');
        $subCategories = array();
        $siteforum = array();
        $params = array();

        if (empty($category_id) && empty($subcategory_id)) {
           
            $empty_category = array();
            $empty_subcategory = array();
            $categories = $categoryTable->getCategories();
            foreach ($categories as $category) {
                $f = 1;
                $subCategories[$category->category_id] = $categoryTable->getSubCategories($category->category_id);
                foreach ($subCategories[$category->category_id] as $subCategory) {
                    $f = 1;
                    $params['category_id'] = $subCategory->category_id;
                    $siteforum[$subCategory->category_id] = $forumTable->getForums($params);
                    $siteforumArray = $siteforum[$subCategory->category_id]->toArray();
                    if (empty($siteforumArray) && $f)
                        $f = 1;
                    else
                        $f = 0;

                    if ($f)
                        $empty_subcategory[$category->category_id][$subCategory->category_id] = 1;
                    else
                        $empty_subcategory[$category->category_id][$subCategory->category_id] = 0;
                }
                $params['category_id'] = $category->category_id;
                $siteforum[$category->category_id] = $forumTable->getForums($params);
                $siteforumArray = $siteforum[$category->category_id]->toArray();
                $subCategoryArray = $subCategories[$category->category_id]->toArray();

                if (empty($siteforumArray) && $f)
                    $f = 1;
                else
                    $f = 0;

                if ($f)
                    $empty_category[$category->category_id] = 1;
                else
                    $empty_category[$category->category_id] = 0;
            }

            $this->view->empty_category = $empty_category;
            $this->view->empty_subcategory = $empty_subcategory;
            $this->view->categories = $categories;
        } elseif (!empty($category_id) && empty($subcategory_id)) {
            $category = Engine_Api::_()->getItem('forum_category', $category_id);
            $rName = $categoryTable->info('name');
            $select = $categoryTable->select()
                    ->from($rName)
                    ->where($rName . '.category_id = ?', $category_id);
            $categories = $categoryTable->category($category_id);
            $subCategories[$category->category_id] = $categoryTable->getSubCategories($category->category_id);
            foreach ($subCategories[$category->category_id] as $subCategory) {
                $params['category_id'] = $subCategory->category_id;
                $siteforum[$subCategory->category_id] = $forumTable->getForums($params);
            }
            $params['category_id'] = $category->category_id;
            $siteforum[$category->category_id] = $forumTable->getForums($params);

            $this->view->categories = $categories;
        } elseif (!empty($category_id) && !empty($subcategory_id)) {
            $category = Engine_Api::_()->getItem('forum_category', $subcategory_id);

            $categories = $categoryTable->category($subcategory_id);

            $params['category_id'] = $category->category_id;
            $siteforum[$category->category_id] = $forumTable->getForums($params);
            $this->view->categories = $categories;
            $this->view->isSubCategory = 1;
            $this->view->category_id = $category_id;
        }
        if (!empty($show_icon) && in_array('category', $show_icon)) {
            $this->view->show_category_icon = 1;
        }
        if (!empty($show_icon) && in_array('subcategory', $show_icon)) {
            $this->view->show_subcategory_icon = 1;
        }
        if (!empty($show_icon) && in_array('forum', $show_icon)) {
            $this->view->show_forum_icon = 1;
        }
        
        if(empty($siteforumCategories))
            return $this->setNoRender();

        $this->view->subCategories = $subCategories;
        $this->view->siteforum = $siteforum;
        $this->view->show_expand = $this->_getParam('show_expand', 1);
        $this->view->show_empty_category = $this->_getParam('show_empty_category', 0);
        $this->view->storage = Engine_Api::_()->storage();
        $this->view->truncationLastPost = $this->_getParam('truncationLastPost', 30) ? $this->_getParam('truncationLastPost', 30) : 30;
    }

}
