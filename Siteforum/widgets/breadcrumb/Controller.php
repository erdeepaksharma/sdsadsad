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
class Siteforum_Widget_BreadcrumbController extends Engine_Content_Widget_Abstract {

    public function indexAction() {

        $front = Zend_Controller_Front::getInstance();
        $module = $front->getRequest()->getModuleName();
        $controller = $front->getRequest()->getControllerName();
        $action = $front->getRequest()->getActionName();

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $category_id = (int) $request->getParam('category_id', 0);
        $subCategory_id = (int) $request->getParam('subcategory_id', 0);
        $siteforumBreadcrumb = Zend_Registry::isRegistered('siteforumBreadcrumb') ? Zend_Registry::get('siteforumBreadcrumb') : null;

        if ($module == 'siteforum' && $controller == 'index' && $action == 'index') {

            if (!empty($category_id)) {
                $categoryItem = Engine_Api::_()->getItem('forum_category', $category_id);
                $navigation = array("Forum" => array('route' => 'siteforum_general'),
                    $categoryItem->getTitle() => "",
                );
            }

            if (empty($category_id) || empty($categoryItem)) {
                return $this->setNoRender();
            }

            if (!empty($subCategory_id)) {
                $subCategoryItem = Engine_Api::_()->getItem('forum_category', $subCategory_id);

                $navigation = array("Forum" => array('route' => 'siteforum_general'),
                    $categoryItem->getTitle() => array('route' => 'siteforum_category', 'category_id' => $category_id),
                    $subCategoryItem->getTitle() => "",
                );
            }
        } elseif ($module == 'siteforum' && $controller == 'forum' && $action == 'view') {

            $forum_id = (int) $request->getParam('forum_id');
            $siteforum = Engine_Api::_()->getItem('forum_forum', $forum_id);

            $category_id = $siteforum->subcategory_id ? $siteforum->subcategory_id : $siteforum->category_id;
            if (!empty($category_id)) {
                $categoryItem = Engine_Api::_()->getItem('forum_category', $category_id);
                $isNotCategory = $categoryItem->cat_dependency ? 1 : 0;

                if ($isNotCategory) {

                    $subCategoryItem = Engine_Api::_()->getItem('forum_category', $category_id);
                    $category_id = $categoryItem->cat_dependency;
                    $categoryItem = Engine_Api::_()->getItem('forum_category', $category_id);

                    $navigation = array("Forum" => array('route' => 'siteforum_general'),
                        $categoryItem->getTitle() => array('route' => 'siteforum_category', 'category_id' => $categoryItem->category_id),
                        $subCategoryItem->getTitle() => array('route' => 'siteforum_subcategory', 'category_id' => $categoryItem->category_id, 'subcategory_id' => $subCategoryItem->category_id),
                        $siteforum->getTitle() => "",
                    );
                } else {

                    $navigation = array("Forum" => array('route' => 'siteforum_general'),
                        $categoryItem->getTitle() => array('route' => 'siteforum_category', 'category_id' => $categoryItem->category_id),
                        $siteforum->getTitle() => ""
                    );
                }
            }
        } elseif ($module == 'siteforum' && $controller == 'forum' && $action == 'topic-create') {

            $forum_id = (int) $request->getParam('forum_id');
            $siteforum = Engine_Api::_()->getItem('forum_forum', $forum_id);

            $category_id = $siteforum->subcategory_id ? $siteforum->subcategory_id : $siteforum->category_id;
            if (!empty($category_id)) {
                $categoryItem = Engine_Api::_()->getItem('forum_category', $category_id);
                $isNotCategory = $categoryItem->cat_dependency ? 1 : 0;

                if ($isNotCategory) {

                    $subCategoryItem = Engine_Api::_()->getItem('forum_category', $category_id);
                    $category_id = $categoryItem->cat_dependency;
                    $categoryItem = Engine_Api::_()->getItem('forum_category', $category_id);

                    $navigation = array("Forum" => array('route' => 'siteforum_general'),
                        $categoryItem->getTitle() => array('route' => 'siteforum_category', 'category_id' => $categoryItem->category_id),
                        $subCategoryItem->getTitle() => array('route' => 'siteforum_subcategory', 'category_id' => $categoryItem->category_id, 'subcategory_id' => $subCategoryItem->category_id),
                        $siteforum->getTitle() => array('route' => 'siteforum_forum', 'forum_id' => $forum_id),
                        'Post Topic' => '',
                    );
                } else {

                    $navigation = array("Forum" => array('route' => 'siteforum_general'),
                        $categoryItem->getTitle() => array('route' => 'siteforum_category', 'category_id' => $categoryItem->category_id),
                        $siteforum->getTitle() => array('route' => 'siteforum_forum', 'forum_id' => $forum_id),
                        'Post Topic' => '',
                    );
                }
            }
        } elseif ($module == 'siteforum' && $controller == 'topic' && $action == 'view') {

            $topic_id = (int) $request->getParam('topic_id');
            $topicItem = Engine_Api::_()->getItem('forum_topic', $topic_id);
            $siteforum = $topicItem->getParent();
            $forum_id = $siteforum->getIdentity();
            $siteforumItem = Engine_Api::_()->getItem('forum_forum', $forum_id);

            $category_id = $siteforum->subcategory_id ? $siteforum->subcategory_id : $siteforum->category_id;
            if (!empty($category_id)) {
                $categoryItem = Engine_Api::_()->getItem('forum_category', $category_id);

                $isNotCategory = $categoryItem->cat_dependency ? 1 : 0;
                if ($isNotCategory) {
                    $subCategoryItem = Engine_Api::_()->getItem('forum_category', $category_id);
                    $category_id = $categoryItem->cat_dependency;
                    $categoryItem = Engine_Api::_()->getItem('forum_category', $category_id);

                    $navigation = array("Forum" => array('route' => 'siteforum_general'),
                        $categoryItem->getTitle() => array('route' => 'siteforum_category', 'category_id' => $categoryItem->category_id),
                        $subCategoryItem->getTitle() => array('route' => 'siteforum_subcategory', 'category_id' => $categoryItem->category_id, 'subcategory_id' => $subCategoryItem->category_id),
                        $siteforumItem->getTitle() => array('route' => 'siteforum_forum', 'forum_id' => $forum_id),
                        $topicItem->getTitle() => ""
                    );
                } else {

                    $navigation = array("Forum" => array('route' => 'siteforum_general'),
                        $categoryItem->getTitle() => array('route' => 'siteforum_category', 'category_id' => $categoryItem->category_id),
                        $siteforum->getTitle() => array('route' => 'siteforum_forum', 'forum_id' => $forum_id),
                        $topicItem->getTitle() => ""
                    );
                }
            }
        } else {
            return $this->setNoRender();
        }
        
        if(empty($siteforumBreadcrumb))
            return $this->setNoRender();
        
        $this->view->showDashboardLink = $this->_getParam('showDashboardLink', 1);
        $this->view->viewer = Engine_Api::_()->user()->getViewer();
        $this->view->navigation = $navigation;
    }

}
