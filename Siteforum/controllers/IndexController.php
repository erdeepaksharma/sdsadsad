<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: IndexController.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_IndexController extends Core_Controller_Action_Standard {

    public function indexAction() {

        if (!$this->_helper->requireAuth()->setAuthParams('forum', null, 'view')->isValid()) {
            return;
        }

        // Render
        $this->_helper->content
                //->setNoRender()
                ->setEnabled()
        ;
    }

    //ACTION TO GET SUB-CATEGORY
    public function subcatAction() {

        //GET CATEGORY ID
        $category_id_temp = $this->_getParam('category_id_temp');
        //INTIALIZE ARRAY
        $this->view->subcats = $data = array();

        //RETURN IF CATEGORY ID IS EMPTY
        if (empty($category_id_temp))
            return;

        //GET CATEGORY TABLE
        $subCategories = Engine_Api::_()->getDbtable('categories', 'siteforum')->getSubCategory($category_id_temp);

        foreach ($subCategories as $subCategory) {

            $content_array = array();
            $content_array['category_name'] = $this->view->translate($subCategory->title);
            $content_array['category_id'] = $subCategory->category_id;
            //$content_array['categoryname_temp'] = $categoryName;
            $data[] = $content_array;
        }

        $this->view->subcats = $data;
    }

    public function rateAction() {

        $user_id = Engine_Api::_()->user()->getViewer()->getIdentity();
        $rating = $this->_getParam('rating');
        $topic_id = $this->_getParam('topic_id');
        $table = Engine_Api::_()->getDbtable('ratings', 'siteforum');
        $hostType = str_replace('www.', '', strtolower($_SERVER['HTTP_HOST']));
        $forumGlobalView = Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.global.view', 0);
        $forumManageType = Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.manage.type', 0);
        $forumGlobalType = Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.global.type', 0);

        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $siteforumRateToTopic = Zend_Registry::isRegistered('siteforumRateToTopic') ? Zend_Registry::get('siteforumRateToTopic') : null;
            Engine_Api::_()->getDbtable('ratings', 'siteforum')->setRating($topic_id, $user_id, $rating);
            $topic = Engine_Api::_()->getItem('forum_topic', $topic_id);
            $topic->rating = Engine_Api::_()->getDbtable('ratings', 'siteforum')->getRating($topic->getIdentity());
            
            if (empty($forumGlobalType)) {
                for ($check = 0; $check < strlen($hostType); $check++) {
                    $tempHostType += @ord($hostType[$check]);
                }
                $tempHostType = $tempHostType + $forumGlobalView;
            }
            
            if(!empty($tempHostType) && ($tempHostType != $forumManageType)) {
                Engine_Api::_()->getApi('settings', 'core')->setSetting('siteforum.viewtypeinfo.type', 0);
                return;
            }
                
            $topic->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $data = array();
        if(!empty($siteforumRateToTopic)) {
            $data[] = array(
                'total' => Engine_Api::_()->getDbtable('ratings', 'siteforum')->ratingCount($topic->getIdentity()),
                'rating' => $rating,
            );
        }
        return $this->_helper->json($data);
//        $data = Zend_Json::encode($data);
//        $this->getResponse()->setBody($data);
    }

    public function searchAction() {

        if (!$this->_helper->requireAuth()->setAuthParams('forum', null, 'view')->isValid()) {
            return;
        }

        $this->_helper->content
                //->setNoRender()
                ->setEnabled()
        ;
    }

    public function uploadPhotoAction() {

        $viewer = Engine_Api::_()->user()->getViewer();

        $this->_helper->layout->disableLayout();

        if (!Engine_Api::_()->authorization()->isAllowed('album', $viewer, 'create')) {
            return false;
        }

        if (!$this->_helper->requireAuth()->setAuthParams('album', null, 'create')->isValid())
            return;

        if (!$this->_helper->requireUser()->checkRequire()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
            return;
        }

        if (!$this->getRequest()->isPost()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
            return;
        }
        if (!isset($_FILES['userfile']) || !is_uploaded_file($_FILES['userfile']['tmp_name'])) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
            return;
        }

        $db = Engine_Api::_()->getDbtable('photos', 'album')->getAdapter();
        $db->beginTransaction();

        try {

            $photoTable = Engine_Api::_()->getDbtable('photos', 'album');
            $photo = $photoTable->createRow();
            $photo->setFromArray(array(
                'owner_type' => 'user',
                'owner_id' => $viewer->getIdentity()
            ));
            $photo->save();

            $photo->setPhoto($_FILES['userfile']);

            $this->view->status = true;
            $this->view->name = $_FILES['userfile']['name'];
            $this->view->photo_id = $photo->photo_id;
            $this->view->photo_url = $photo->getPhotoUrl();

            $table = Engine_Api::_()->getDbtable('albums', 'album');
            $album = $table->getSpecialAlbum($viewer, 'forum');

            $photo->album_id = $album->album_id;
            $photo->save();

            if (!$album->photo_id) {
                $album->photo_id = $photo->getIdentity();
                $album->save();
            }

            $auth = Engine_Api::_()->authorization()->context;
            $auth->setAllowed($photo, 'everyone', 'view', true);
            $auth->setAllowed($photo, 'everyone', 'comment', true);
            $auth->setAllowed($album, 'everyone', 'view', true);
            $auth->setAllowed($album, 'everyone', 'comment', true);


            $db->commit();
        } catch (Album_Model_Exception $e) {
            $db->rollBack();
            $this->view->status = false;
            $this->view->error = $this->view->translate($e->getMessage());
            throw $e;
        } catch (Exception $e) {
            $db->rollBack();
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.');
            throw $e;
        }
    }

    public function tagsCloudAction() {

        if (!$this->_helper->requireAuth()->setAuthParams('forum', null, 'view')->isValid()) {
            return;
        }

        $this->_helper->content
                //->setNoRender()
                ->setEnabled()
        ;
    }

}
