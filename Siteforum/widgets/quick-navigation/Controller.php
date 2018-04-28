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
class Siteforum_Widget_QuickNavigationController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $front = Zend_Controller_Front::getInstance();
    $controller = $front->getRequest()->getControllerName();
    $action = $front->getRequest()->getActionName();
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $siteforumQuickNavigation = Zend_Registry::isRegistered('siteforumQuickNavigation') ? Zend_Registry::get('siteforumQuickNavigation') : null;
    $this->view->categoryTable = $categoryTable = Engine_Api::_()->getDbTable('categories', 'siteforum');
    $this->view->forumTable = $forumTable = Engine_Api::_()->getDbTable('forums', 'siteforum');
    $this->view->topicTable = $topicTable = Engine_Api::_()->getDbTable('topics', 'siteforum');
    $this->view->categories = $categories = $categoryTable->getCategories();

    $this->view->hierarchy = $hierarchy = $this->_getParam('hierarchy', 3);

    if ($controller == 'index' && $action == 'index') {
      $category_id = (int) $request->getParam('category_id');
      $subcategory_id = (int) $request->getParam('subcategory_id');
      if (!empty($category_id) && !empty($subcategory_id)) {
        $subcategory = Engine_Api::_()->getItem('forum_category', $subcategory_id);
        $selected = $subcategory->getTitle();
        if ($hierarchy == 1) {
          $catItem = Engine_Api::_()->getItem('forum_category', $subcategory->cat_dependency);
          $selected = $catItem->getTitle();
        }

        if (empty($siteforumQuickNavigation))
          return $this->setNoRender();

        $this->view->selected = $selected;
        $this->view->hierarchy = $this->_getParam('hierarchy', 3);
        $this->view->viewer = Engine_Api::_()->user()->getViewer();
        $this->view->show_navigation = $this->_getParam('show_navigation', array("navigation", "dashboard"));
        $this->view->show_empty_category = $this->_getParam('show_empty_category', 0);
      } elseif (!empty($category_id) && empty($subcategory_id)) {
        $category = Engine_Api::_()->getItem('forum_category', $category_id);
        $selected = $category->getTitle();
      } else {
        $selected = "Quick Navigation";
      }
    } elseif ($controller == 'forum' && $action == 'view') {
      $forum_id = (int) $request->getParam('forum_id');
      if (!empty($forum_id)) {
        $siteforum = Engine_Api::_()->getItem('forum_forum', $forum_id);
        $selected = $siteforum->getTitle();
        if ($hierarchy == 2) {
          if ($siteforum->subcategory_id) {
            $catItem = Engine_Api::_()->getItem('forum_category', $siteforum->subcategory_id);
            $selected = $catItem->getTitle();
          } else {
            $catItem = Engine_Api::_()->getItem('forum_category', $siteforum->category_id);
            $selected = $catItem->getTitle();
          }
        } else if ($hierarchy == 1) {
          $catItem = Engine_Api::_()->getItem('forum_category', $siteforum->category_id);
          $selected = $catItem->getTitle();
        }
      } else {
        $selected = "Quick Navigation";
      }
    } else {
      $selected = "Quick Navigation";
    }

    $this->view->selected = $selected;
    $this->view->hierarchy = $this->_getParam('hierarchy', 3);
    $this->view->viewer = Engine_Api::_()->user()->getViewer();
    $this->view->show_navigation = $this->_getParam('show_navigation', array("navigation", "dashboard"));
    $this->view->show_empty_category = $this->_getParam('show_empty_category', 0);
  }

}
