<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: DashboardController.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_DashboardController extends Core_Controller_Action_Standard {

    public function init() {

        if (!$this->_helper->requireUser()->isValid())
            return;

        if (!$this->_helper->requireAuth()->setAuthParams('forum', null, 'view')->isValid()) {
            return;
        }

        $this->view->viewer = Engine_Api::_()->user()->getViewer();
    }

    public function signatureAction() {

        $user_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        //MAKE FORM
        $this->view->form = $form = new Siteforum_Form_Signature();

        $tableSignature = Engine_Api::_()->getDbTable('signatures', 'siteforum');

        //SAVE THE VALUE
        if ($this->getRequest()->isPost()) {

            $params = array();
            $params['user_id'] = $user_id;
            $params['body'] = $_POST['signature'];

            $tableSignature->setColumnValue($params);

            $this->view->form = $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved successfully.'));
        }

        //POPULATE FORM
        $values['signature'] = $tableSignature->getColumnValue($user_id, 'body');
        $form->populate($values);
    }

    public function myTopicsAction() {

        //GET VIEWER
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();

        $params = array();
        $params['user_id'] = $viewer->getIdentity();

        $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('topics', 'siteforum')->getPopularTopics($params);
        $paginator->setCurrentPageNumber($this->_getParam('page'));
        $paginator->setItemCountPerPage(25);
    }

    public function myPostsAction() {

        //GET VIEWER
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();

        $params = array();
        $params['user_id'] = $viewer->getIdentity();

        $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('posts', 'siteforum')->getPopularPosts($params);
        $paginator->setCurrentPageNumber($this->_getParam('page'));
        $paginator->setItemCountPerPage(25);
    }

    public function mySubscriptionsAction() {

        //GET VIEWER
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();

        $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('topics', 'siteforum')->getSubscribedTopics($viewer->getIdentity());
        $paginator->setCurrentPageNumber($this->_getParam('page'));
        $paginator->setItemCountPerPage(25);
    }
// Sticky Topic Work
    public function bookmarkedTopicsAction() {

        $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('topics', 'siteforum')->getBookmarkedTopics(Engine_Api::_()->user()->getViewer()->getIdentity());
        $paginator->setCurrentPageNumber($this->_getParam('page'));
        $paginator->setItemCountPerPage(25);
    }

    public function likedTopicsAction() {

        $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('topics', 'siteforum')->getLikedTopics(Engine_Api::_()->user()->getViewer()->getIdentity());
        $paginator->setCurrentPageNumber($this->_getParam('page'));
        $paginator->setItemCountPerPage(25);
    }
// Topic I Viewed
//    public function viewedTopicsAction() {
//
//        $this->view->paginator = $paginator = Engine_Api::_()->getDbTable('topics', 'siteforum')->getViewedTopics(Engine_Api::_()->user()->getViewer()->getIdentity());
//        $paginator->setCurrentPageNumber($this->_getParam('page'));
//        $paginator->setItemCountPerPage(25);
//    }

}
