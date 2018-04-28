<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminManageController.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_AdminManageController extends Core_Controller_Action_Admin {

    public function indexAction() {

        $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
                ->getNavigation('siteforum_admin_main', array(), 'siteforum_admin_main_manage');

        include_once APPLICATION_PATH . '/application/modules/Siteforum/controllers/license/license2.php';
    }

    public function forumPhotoUploadAction() {

        $form = $this->view->form = new Siteforum_Form_Admin_Photo_Upload();
        $form->getElement('photo')->setDescription('The recommended dimension for the icon is: 48 x 48 pixels.');
        
        if (!$this->getRequest()->isPost()) {
            return;
        }
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }
        $table = Engine_Api::_()->getItemTable('forum_forum');
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $select = $table->select()->where('forum_id = ?', $this->getParam('forum_id'));
            $row = $table->fetchRow($select);
            $values = $form->getValues();
            $row->setFromArray($values);
            if (isset($_FILES['photo'])) {
                $photoFile = $row->setPhoto($form->photo);
                if (!empty($photoFile->file_id)) {
                    $row->photo_id = $photoFile->file_id;
                }
            }
            $row->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Icon has been uploaded successfully.')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true,
        ));
    }

    public function forumPhotoDeleteAction() {

        $form = $this->view->form = new Siteforum_Form_Admin_Photo_Delete();
        $form->setDescription('Are you sure you want to delete this forum icon?');

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        try {
            // This is dangerous, what if something throws an exception in postDelete
            // after the files are deleted?
            $table = Engine_Api::_()->getItemTable('forum_forum');
            $select = $table->select()->where('forum_id = ?', $this->getParam('forum_id'));
            $row = $table->fetchRow($select);
            $this->file_id = $row->photo_id;
            $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id);
            if ($file)
                $file->remove();
            $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, 'thumb.normal');
            if ($file)
                $file->remove();
            $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, 'thumb.large');
            if ($file)
                $file->remove();
            $row->photo_id = 0;
            $row->save();
        } catch (Exception $e) {
            // @todo completely silencing them probably isn't good enough
            //throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Icon has been deleted successfully.')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true,
        ));
    }

    public function deletePhotoAction() {

        $form = $this->view->form = new Siteforum_Form_Admin_Photo_Delete();

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        try {
            // This is dangerous, what if something throws an exception in postDelete
            // after the files are deleted?
            $table = Engine_Api::_()->getItemTable('forum_category');
            $select = $table->select()->where('category_id = ?', $this->getParam('category_id'));

            $row = $table->fetchRow($select);
            $this->file_id = $row->photo_id;
            $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id);
            if ($file)
                $file->remove();
            $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, 'thumb.normal');
            if ($file)
                $file->remove();
            $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, 'thumb.large');
            if ($file)
                $file->remove();
            $row->photo_id = 0;
            $row->save();
        } catch (Exception $e) {
            // @todo completely silencing them probably isn't good enough
            //throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Icon has been deleted successfully.')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true,
        ));
    }

    public function editForumPhotoAction() {

        $form = $this->view->form = new Siteforum_Form_Admin_Photo_Edit();
        $form->setTitle("Edit Forum Icon");
        $form->getElement('photo')->setDescription('
                The recommended dimension for the icon is: 48 x 48 pixels.');
        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $table = Engine_Api::_()->getItemTable('forum_forum');
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $select = $table->select()->where('forum_id = ?', $this->getParam('forum_id'));
            $row = $table->fetchRow($select);

            // This is dangerous, what if something throws an exception in postDelete
            // after the files are deleted?
            $select = $table->select()->where('forum_id = ?', $this->getParam('forum_id'));
            $row = $table->fetchRow($select);
            $this->file_id = $row->photo_id;
            $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id);
            if ($file)
                $file->remove();
            $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, 'thumb.normal');
            if ($file)
                $file->remove();
            $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, 'thumb.large');
            if ($file)
                $file->remove();

            $values = $form->getValues();
            $row->setFromArray($values);
            if (isset($_FILES['photo'])) {
                $photoFile = $row->setPhoto($form->photo);
                if (!empty($photoFile->file_id)) {
                    $row->photo_id = $photoFile->file_id;
                }
            }
            $row->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Icon has been edit successfully.')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true,
        ));
    }

    public function editPhotoAction() {

        $form = $this->view->form = new Siteforum_Form_Admin_Photo_Edit();

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $table = Engine_Api::_()->getItemTable('forum_category');
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $select = $table->select()->where('category_id = ?', $this->getParam('category_id'));
            $row = $table->fetchRow($select);

            // This is dangerous, what if something throws an exception in postDelete
            // after the files are deleted?
            $select = $table->select()->where('category_id = ?', $this->getParam('category_id'));
            $row = $table->fetchRow($select);
            $this->file_id = $row->photo_id;
            $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id);
            if ($file)
                $file->remove();
            $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, 'thumb.normal');
            if ($file)
                $file->remove();
            $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, 'thumb.large');
            if ($file)
                $file->remove();

            $values = $form->getValues();
            $row->setFromArray($values);
            if (isset($_FILES['photo'])) {
                $photoFile = $row->setPhoto($form->photo);
                if (!empty($photoFile->file_id)) {
                    $row->photo_id = $photoFile->file_id;
                }
            }
            $row->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Icon has been edit successfully.')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true,
        ));
    }

    public function photoUploadAction() {
        $form = $this->view->form = new Siteforum_Form_Admin_Photo_Upload();
        if (!$this->getRequest()->isPost()) {
            return;
        }
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }
        $table = Engine_Api::_()->getItemTable('forum_category');
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $select = $table->select()->where('category_id = ?', $this->getParam('category_id'));
            $row = $table->fetchRow($select);
            $values = $form->getValues();
            $row->setFromArray($values);
            if (isset($_FILES['photo'])) {
                $photoFile = $row->setPhoto($form->photo);
                if (!empty($photoFile->file_id)) {
                    $row->photo_id = $photoFile->file_id;
                }
            }
            $row->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Icon has been uploaded successfully.')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true,
        ));
    }

    public function moveSiteforumAction() {
        if ($this->getRequest()->isPost()) {
            $forum_id = $this->_getParam('forum_id');
            $siteforum = Engine_Api::_()->getItem('forum_forum', $forum_id);
            $siteforum->moveUp();
        }
    }

    public function moveCategoryAction() {

        if ($this->getRequest()->isPost()) {
            $category_id = $this->_getParam('category_id');
            $category = Engine_Api::_()->getItem('forum_category', $category_id);
            $category->moveUp();
        }
    }

    public function moveSubCategoryAction() {

        if ($this->getRequest()->isPost()) {
            $category_id = $this->_getParam('category_id');
            $category = Engine_Api::_()->getItem('forum_category', $category_id);
            $category->moveSubCategoryUp($category->cat_dependency);
        }
    }

    public function editSiteforumAction() {
        $form = $this->view->form = new Siteforum_Form_Admin_Siteforum_Edit();

        $forum_id = $this->getRequest()->getParam('forum_id');
        $siteforum = Engine_Api::_()->getItem('forum_forum', $forum_id);

        // Populate
        $form->populate($siteforum->toArray());
        $form->populate(array(
            'title' => htmlspecialchars_decode($siteforum->title),
            'description' => htmlspecialchars_decode($siteforum->description),
        ));

        $auth = Engine_Api::_()->authorization()->context;
        $allowed = array();
        if ($auth->isAllowed($siteforum, 'everyone', 'view')) {
            
        } else {
            $levels = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll();
            foreach ($levels as $level) {
                //              if (Engine_Api::_()->authorization()->context->isAllowed($siteforum, $level, 'view')) {
                $allowed[] = $level->getIdentity();
                //            }
            }
            if (count($allowed) == 0 || count($allowed) == count($levels)) {
                $allowed = null;
            }
        }
        if (!empty($allowed)) {
            $form->populate(array(
                'levels' => $allowed,
            ));
        }

        // Check request/method
        if (!$this->getRequest()->isPost()) {
            return;
        }
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $values = $form->getValues();

        $table = Engine_Api::_()->getItemTable('forum_forum');
        $db = $table->getAdapter();
        $db->beginTransaction();

        try {
            $params = array();
            $siteforum->setFromArray($values);

            $subCategory = Engine_Api::_()->getDbTable('categories', 'siteforum')->getSubCategories($values['category_id']);
            if (!empty($subCategory)) {
                $subCategoryArray = $subCategory->toArray();
            }
            if (empty($subCategoryArray)) {
                $values['subcategory_id'] = 0;
            }
            if($siteforum->category_id != $values['category_id'] && $siteforum->subcategory_id != $values['subcategory_id']){
            if (empty($values['subcategory_id'])) {
                $params['category_id'] = $values['category_id'];
                $siteforum->order = Engine_Api::_()->getItem('forum_forum', $siteforum->getIdentity())->getHighestOrder($params) + 1;
                $siteforum->subcategory_id = 0;
            } else {
                $params['subcategory_id'] = $values['subcategory_id'];
                $siteforum->order = Engine_Api::_()->getItem('forum_forum', $siteforum->getIdentity())->getHighestOrder($params) + 1;
            }
            }

            $siteforum->title = htmlspecialchars($values['title']);
            $siteforum->description = htmlspecialchars($values['description']);
            $siteforum->save();

            // Handle permissions
            $levels = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll();

            // Clear permissions
            $auth->setAllowed($siteforum, 'everyone', 'view', false);
            foreach ($levels as $level) {
                $auth->setAllowed($siteforum, $level, 'view', false);
            }

            // Add
            if (count($values['levels']) == 0 || count($values['levels']) == count($form->getElement('levels')->options)) {
                $auth->setAllowed($siteforum, 'everyone', 'view', true);
            } else {
                foreach ($values['levels'] as $levelIdentity) {
                    $level = Engine_Api::_()->getItem('authorization_level', $levelIdentity);
                    $auth->setAllowed($siteforum, $level, 'view', true);
                }
            }

            // Extra auth stuff
            $auth->setAllowed($siteforum, 'registered', 'topic.create', true);
            $auth->setAllowed($siteforum, 'registered', 'post.create', true);
            $auth->setAllowed($siteforum, 'registered', 'comment', true);

            // Make mod list now
            $list = $siteforum->getModeratorList();
            $auth->setAllowed($siteforum, $list, 'topic.edit', true);
            $auth->setAllowed($siteforum, $list, 'topic.delete', true);

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Forum has been saved successfully.')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true,
        ));
    }

    public function addSubCategoryAction() {

        $form = $this->view->form = new Siteforum_Form_Admin_Category_Create();
        $form->setTitle('Create Subcategory');

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $table = Engine_Api::_()->getItemTable('forum_category');
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $values = $form->getValues();
            $category = $table->createRow();
            $category->title = htmlspecialchars($values['title']);
            $category->order = $table->getMaxSubCategoryOrder($this->getParam('category_id')) + 1;
            $category->cat_dependency = $this->getParam('category_id');
            $category->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Subcategory has been added successfully.')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true,
        ));
    }

    public function editCategoryAction() {

        $form = $this->view->form = new Siteforum_Form_Admin_Category_Edit();

        $category_id = $this->getRequest()->getParam('category_id');
        $category = Engine_Api::_()->getItem('forum_category', $category_id);
        $form->title->setValue(htmlspecialchars_decode($category->title));

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $category->title = htmlspecialchars($form->getValue('title'));
        $category->save();

        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Category has been renamed successfully.')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true,
        ));
    }

    public function addCategoryAction() {

        $form = $this->view->form = new Siteforum_Form_Admin_Category_Create();

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $table = Engine_Api::_()->getItemTable('forum_category');
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $values = $form->getValues();
            $category = $table->createRow();
            $category->title = htmlspecialchars($values['title']);
            $category->order = $table->getMaxCategoryOrder() + 1;
            $category->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Category has been added successfully.')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true,
        ));
    }

    public function addSiteforumAction() {

        $form = $this->view->form = new Siteforum_Form_Admin_Siteforum_Create();

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $values = $form->getValues();

        //CATEGORY IS REQUIRED FIELD
        if (empty($_POST['category_id'])) {
            $form->getDecorator('errors')->setOption('escape', false);
            $form->addError('Please complete Category field - it is required.');
            return;
        }

        $table = Engine_Api::_()->getItemTable('forum_forum');
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $siteforum = $table->createRow();
            $siteforum->setFromArray($values);
            $siteforum->title = htmlspecialchars($values['title']);
            $siteforum->description = htmlspecialchars($values['description']);
            $siteforum->order = $siteforum->getCollection()->getHighestOrder() + 1;
            if($values['subcategory_id'] == NULL){
              $siteforum->subcategory_id = 0;
            }
            $siteforum->save();

            // Handle permissions
            $auth = Engine_Api::_()->authorization()->context;
            $levels = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchAll();

            // Clear permissions
            $auth->setAllowed($siteforum, 'everyone', 'view', false);
            foreach ($levels as $level) {
                $auth->setAllowed($siteforum, $level, 'view', false);
            }

            // Add
            if (count($values['levels']) == 0 || count($values['levels']) == count($form->getElement('levels')->options)) {
                $auth->setAllowed($siteforum, 'everyone', 'view', true);
            } else {
                foreach ($values['levels'] as $levelIdentity) {
                    $level = Engine_Api::_()->getItem('authorization_level', $levelIdentity);
                    $auth->setAllowed($siteforum, $level, 'view', true);
                }
            }

            // Extra auth stuff
            $auth->setAllowed($siteforum, 'registered', 'topic.create', true);
            $auth->setAllowed($siteforum, 'registered', 'post.create', true);
            $auth->setAllowed($siteforum, 'registered', 'comment', true);

            // Make mod list now
            $list = $siteforum->getModeratorList();
            $auth->setAllowed($siteforum, $list, 'topic.edit', true);
            $auth->setAllowed($siteforum, $list, 'topic.delete', true);

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Forum has been added successfully.')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true,
        ));
    }

    public function addModeratorAction() {

        $forum_id = $this->getRequest()->getParam('forum_id');

        $this->view->siteforum = $siteforum = Engine_Api::_()->getItem('forum_forum', $forum_id);

        $form = $this->view->form = new Siteforum_Form_Admin_Moderator_Create();

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $values = $form->getValues();
        $user_id = $values['user_id'];

        $moderator = Engine_Api::_()->getItem('user', $user_id);

        $list = $siteforum->getModeratorList();
        $list->add($moderator);
        
        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($moderator, $moderator, $siteforum, 'siteforum_promote');
        
        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Moderator has been added successfully.')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true,
        ));
    }

    public function userSearchAction() {

        $page = $this->getRequest()->getParam('page', 1);
        $username = $this->getRequest()->getParam('username');
        $table = Engine_Api::_()->getDbtable('users', 'user');
        $select = $table->select();
        if (!empty($username)) {
            $select = $select->where('username LIKE ? || displayname LIKE ?', '%' . $username . '%');
        }
        $forum_id = $this->getRequest()->getParam('forum_id');
        $this->view->siteforum = $siteforum = Engine_Api::_()->getItem('forum_forum', $forum_id);
        $this->view->paginator = $paginator = Zend_Paginator::factory($select);
        $this->view->paginator = $paginator->setCurrentPageNumber($page);
        $this->view->paginator->setItemCountPerPage(20);
    }

    public function removeModeratorAction() {

        $form = $this->view->form = new Siteforum_Form_Admin_Moderator_Delete();

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $user_id = $this->getRequest()->getParam('user_id');
        $user = Engine_Api::_()->getItem('user', $user_id);

        $forum_id = $this->getRequest()->getParam('forum_id');
        $siteforum = Engine_Api::_()->getItem('forum_forum', $forum_id);
        $list = $siteforum->getModeratorList();
        $list->remove($user);
        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Moderator has been removed successfully.')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true,
        ));
    }

    public function deleteCategoryAction() {
      
        $category_id = $this->getRequest()->getParam('category_id');
        $form = $this->view->form = new Siteforum_Form_Admin_Category_Delete();
        
        $categoryItem = Engine_Api::_()->getItem('forum_category', $category_id);
        
        if($categoryItem->cat_dependency){
          $form->setTitle('Delete Subcategory');
          $form->setDescription('Are you sure that you want to delete this subcategory? It will not be recoverable after being deleted.');
        }
        
        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $table = Engine_Api::_()->getItemTable('forum_category');
        $db = $table->getAdapter();
        $db->beginTransaction();
        
        try {
            $category = Engine_Api::_()->getItem('forum_category', $category_id);
            $category->delete();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Category has been delete successfully.')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true
        ));
    }

    public function deleteSiteforumAction() {

        $form = $this->view->form = new Siteforum_Form_Admin_Siteforum_Delete();

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $table = Engine_Api::_()->getItemTable('forum_forum');
        $db = $table->getAdapter();
        $db->beginTransaction();
        $forum_id = $this->getRequest()->getParam('forum_id');
        try {

            $siteforum = Engine_Api::_()->getItem('forum_forum', $forum_id);

            $siteforum->delete();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
        return $this->_forward('success', 'utility', 'core', array(
                    'messages' => array(Zend_Registry::get('Zend_Translate')->_('Forum has been deleted successfully.')),
                    'layout' => 'default-simple',
                    'parentRefresh' => true
        ));
    }

}
