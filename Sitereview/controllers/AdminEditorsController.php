<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminEditorsController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_AdminEditorsController extends Core_Controller_Action_Admin {

  public function manageAction() {

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_editors');

    //GET PAGE
    $page = $this->_getParam('page', 1);

    //FILTER FORM
    $this->view->formFilter = $formFilter = new Sitereview_Form_Admin_Filter();

    $this->view->listingTypeCount = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount();

    //GET LISTINGTYPE ID
    if ($this->view->listingTypeCount > 1) {
      $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 0);
    } else {
      $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id', 1);
    }

    //GET EDITOR TABLE
    $this->view->tableEditor = $tableEditor = Engine_Api::_()->getDbtable('editors', 'sitereview');
    $tableEditorName = $tableEditor->info('name');

    $this->view->totalEditors = $tableEditor->getEditorsCount(0);

    //GET USER TABLE
    $tableUser = Engine_Api::_()->getDbtable('users', 'user');
    $tableUserName = $tableUser->info('name');

    //SELECTING THE USERS WHOSE PAGE CAN BE CLAIMED
    $select = $tableUser->select()
            ->setIntegrityCheck(false)
            ->from($tableEditorName, array('editor_id', 'listingtype_id', 'designation', 'details', 'about', 'badge_id', 'super_editor'))
            ->join($tableUserName, $tableUserName . '.user_id = ' . $tableEditorName . '.user_id');

    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }

    $values = array();

    if ($formFilter->isValid($this->_getAllParams())) {
      $values = $formFilter->getValues();
    }

    foreach ($values as $key => $value) {
      if (null === $value) {
        unset($values[$key]);
      }
    }

    //VALUES
    $values = array_merge(array(
        'order' => "$tableEditorName.user_id",
        'order_direction' => 'DESC',
            ), $values);

    $this->view->assign($values);

    //SELECT
    $select->order((!empty($values['order']) ? $values['order'] : "$tableEditorName.user_id" ) . ' ' . (!empty($values['order_direction']) ? $values['order_direction'] : 'DESC' ));

    $select->group($tableEditorName . '.user_id');

    include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license2.php';
  }

  //ACTION FOR ADDING THE EDITORS
  public function createAction() {

    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_editors');

    //GET LISTING TYPE ID		
    $this->view->listingtype_id = $listingtype_id = $this->_getParam('listingtype_id');

    //FORM
    $this->view->form = $form = new Sitereview_Form_Admin_Editors_Create();

    //CHECK FORM VALIDATION
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      //GET VALUES
      $values = $form->getValues();

      //GET USER ID
      $user_id = $values['user_id'];

      //GET EDITORS TABLE
      $tableEditor = Engine_Api::_()->getDbTable('editors', 'sitereview');

      //USER IS ALREADY ADDED IN SOME OTHER LISTINGTYPE
      $same_user_editor_id = $tableEditor->select()
              ->from($tableEditor->info('name'), 'editor_id')
              ->where('user_id = ?', $user_id)
              ->query()
              ->fetchColumn();

      //CHECK USER ID
      if ($user_id == 0 || !empty($same_user_editor_id)) {
        $this->view->status = false;
        $error = Zend_Registry::get('Zend_Translate')->_('This is not a valid user name. Please select a user name from the auto-suggest.');
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
        return;
      }
      if (isset($values['listingtypes']) && empty($values['listingtypes'])) {
        $this->view->status = false;
        $error = Zend_Registry::get('Zend_Translate')->_('Please choose a listing type.');
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
        return;
      }

      if (!isset($values['listingtypes'])) {
        $values['listingtypes'] = array('0' => 1);
      }

      //GET DB
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {

        foreach ($values['listingtypes'] as $listingtype_id) {
          $editor = $tableEditor->createRow();
          $editor->user_id = $user_id;
          $editor->listingtype_id = $listingtype_id;
          $editor->designation = $values['designation'];
          $editor->details = $values['details'];
          $editor->email_notify = $values['email_notify'];
          $editor->save();
        }

        //GET EDITOR DETAILS
        $getDetails = $tableEditor->getEditorDetails($editor->user_id);
        $getCount = Count($getDetails);
        $count = 0;
        $listing_type = "";
        $Zend_router = Zend_Controller_Front::getInstance()->getRouter();
        $http = _ENGINE_SSL ? 'https://' : 'http://';
        foreach ($getDetails as $getDetail) {
          $count++;

          $listing_type .= '<a href =' . $http . $_SERVER['HTTP_HOST'] . $Zend_router->assemble(array(), 'sitereview_general_listtype_' . $getDetail->listingtype_id) . ">$getDetail->title_plural</a>";

          if ($count < $getCount) {
            $listing_type = $listing_type . ',';
          };
        }

        $viewer = Engine_Api::_()->user()->getViewer();
        $viewer_id = $viewer->getIdentity();
        $newEditor = Engine_Api::_()->getItem('user', $user_id);

        $host = $_SERVER['HTTP_HOST'];
        $editor_page_url = (_ENGINE_SSL ? 'https://' : 'http://') . $host . $editor->getHref();
        $viewer_page_url = (_ENGINE_SSL ? 'https://' : 'http://') . $host . $viewer->getHref();
        $viewer_fullhref = '<a href="' . $viewer_page_url . '">' . $viewer->getTitle() . '</a>';

        Engine_Api::_()->getApi('mail', 'core')->sendSystem($newEditor->email, 'SITEREVIEW_EDITOR_ASSIGN_EMAIL', array(
            'sender' => $viewer_fullhref,
            'listing_type' => $listing_type,
            'editor_page_url' => $editor_page_url,
            'queue' => true
        ));

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      return $this->_helper->redirector->gotoRoute(array('module' => 'sitereview', 'controller' => 'editors', 'action' => 'manage'), "admin_default", true);
    }
  }

  //ACTION FOR GETTING THE MEMBER
  function getMemberAction() {

    //GET SETTINGS
    $listingtype_id = $this->_getParam('listingtype_id', 0);
    $featured_editor = $this->_getParam('featured_editor', 0);
    $text = $this->_getParam('text');
    $limit = $this->_getParam('limit', 40);

    //FETCH USER LIST
    $userLists = Engine_Api::_()->getDbTable('editors', 'sitereview')->getMembers($text, $limit, $listingtype_id, $featured_editor);

    //MAKING DATA
    $data = array();
    $mode = $this->_getParam('struct');
    if ($mode == 'text') {
      foreach ($userLists as $userList) {
        $content_photo = $this->view->itemPhoto($userList, 'thumb.icon');
        $data[] = array('id' => $userList->user_id, 'label' => $userList->getTitle(), 'photo' => $content_photo);
      }
    } else {
      foreach ($userLists as $userList) {
        $content_photo = $this->view->itemPhoto($userList, 'thumb.icon');
        $data[] = array('id' => $userList->user_id, 'label' => $userList->getTitle(), 'photo' => $content_photo);
      }
    }
    return $this->_helper->json($data);
  }

  //ADD EDITORS DETAILS
  public function editAction() {

    //LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    //GET USER ID AND EDITOR
    $editor_id = $this->_getParam('editor_id');
    $editor = Engine_Api::_()->getItem('sitereview_editor', $editor_id);

    $previousIds = Engine_Api::_()->getDbTable('editors', 'sitereview')->getListingTypeIds($editor->user_id);

    //GENERATE FORM
    $this->view->form = $form = new Sitereview_Form_Admin_Editors_Edit(array('item' => $editor));
    $form->populate($editor->toArray());

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      //GET VALUES
      $values = $form->getValues();

      if (isset($values['listingtypes']) && empty($values['listingtypes'])) {
        $this->view->status = false;
        $error = Zend_Registry::get('Zend_Translate')->_('Please choose a listing type.');
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
        return;
      }

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();

      try {

        //GET EDITOR TABLE
        $tableEditor = Engine_Api::_()->getDbTable('editors', 'sitereview');

        $tableEditor->update(array('email_notify' => $values['email_notify'], 'designation' => $values['designation'], 'details' => $values['details']), array('user_id = ?' => $editor->user_id));

        //ADD AND DELETE EDITOR FOR LISTING TYPE
        if (isset($values['listingtypes'])) {

          foreach ($values['listingtypes'] as $listingtype_id) {

            //IF EDITOR IS NOT EXIST
            $isExist = $tableEditor->isEditor($editor->user_id, $listingtype_id);
            if (empty($isExist)) {
              $editorNew = $tableEditor->createRow();
              $editorNew->user_id = $editor->user_id;
              $editorNew->listingtype_id = $listingtype_id;
              $editorNew->designation = $values['designation'];
              $editorNew->details = $values['details'];
              $editorNew->email_notify = $values['email_notify'];
              $editorNew->save();
            }
          }

          foreach ($previousIds as $previousId) {
            if (!in_array($previousId, $values['listingtypes'])) {
              $tableEditor->delete(array('listingtype_id = ?' => $previousId, 'user_id = ?' => $editor->user_id));
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
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved successfully.'))
      ));
    }

    $this->renderScript('admin-editors/edit.tpl');
  }

  //ADD EDITORS DETAILS
  public function addAction() {

    //LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    //GET USER ID AND EDITOR
    $editor_id = $this->_getParam('editor_id');
    $editor = Engine_Api::_()->getItem('sitereview_editor', $editor_id);

    //GENERATE FORM
    $this->view->form = $form = new Sitereview_Form_Admin_Editors_Add();
    $form->populate($editor->toArray());

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      //GET VALUES
      $values = $form->getValues();

      if (isset($values['listingtypes']) && empty($values['listingtypes'])) {
        $this->view->status = false;
        $error = Zend_Registry::get('Zend_Translate')->_('Please choose a listing type.');
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
        return;
      }

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();

      try {

        //GET EDITOR TABLE
        $tableEditor = Engine_Api::_()->getDbTable('editors', 'sitereview');

        //ADD AND DELETE EDITOR FOR LISTING TYPE
        if (isset($values['listingtypes'])) {

          foreach ($values['listingtypes'] as $listingtype_id) {

            //IF EDITOR IS NOT EXIST
            $isExist = $tableEditor->isEditor($editor->user_id, $listingtype_id);
            if (empty($isExist)) {
              $editorNew = $tableEditor->createRow();
              $editorNew->user_id = $editor->user_id;
              $editorNew->listingtype_id = $listingtype_id;
              $editorNew->designation = $editor->designation;
              $editorNew->details = $editor->details;
              $editorNew->about = $editor->about;
              $editorNew->save();
            }
          }

          //GET EDITOR DETAILS
          $getCount = Count($values['listingtypes']);
          $count = 0;
          $listing_type = "";
          $Zend_router = Zend_Controller_Front::getInstance()->getRouter();
          $http = _ENGINE_SSL ? 'https://' : 'http://';
          $listingTypesTable = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');
          foreach ($values['listingtypes'] as $listingtype_id) {
            $count++;
            $title_plural = $listingTypesTable->getListingTypeColumn($listingtype_id, 'title_plural');
            $listing_type .= '<a href =' . $http . $_SERVER['HTTP_HOST'] . $Zend_router->assemble(array(), 'sitereview_general_listtype_' . $listingtype_id) . ">$title_plural</a>";

            if ($count < $getCount) {
              $listing_type = $listing_type . ',';
            };
          }

          $viewer = Engine_Api::_()->user()->getViewer();
          $viewer_id = $viewer->getIdentity();
          $newEditor = Engine_Api::_()->getItem('user', $editor->user_id);

          $host = $_SERVER['HTTP_HOST'];
          $editor_page_url = (_ENGINE_SSL ? 'https://' : 'http://') . $host . $editor->getHref();
          $viewer_page_url = (_ENGINE_SSL ? 'https://' : 'http://') . $host . $viewer->getHref();
          $viewer_fullhref = '<a href="' . $viewer_page_url . '">' . $viewer->getTitle() . '</a>';

          Engine_Api::_()->getApi('mail', 'core')->sendSystem($newEditor->email, 'SITEREVIEW_EDITOR_ASSIGN_EMAIL', array(
              'sender' => $viewer_fullhref,
              'listing_type' => $listing_type,
              'editor_page_url' => $editor_page_url,
              'queue' => true
          ));
        }

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved successfully.'))
      ));
    }

    $this->renderScript('admin-editors/add.tpl');
  }

  //ACTION FOR MAKING THE EDITOR AS A SUPER EDITOR
  public function superEditorAction() {

    //LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    $editor_id = $this->_getParam('editor_id');

    $this->view->super_editor = $this->_getParam('super_editor', 0);

    $this->view->listingTypeCount = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypeCount();

    if ($this->getRequest()->isPost()) {

      $editorTable = Engine_Api::_()->getDbTable('editors', 'sitereview');
      $db = $editorTable->getAdapter();
      $db->beginTransaction();

      try {

        $editor = Engine_Api::_()->getItem('sitereview_editor', $editor_id);

        $editorTable->update(array('super_editor' => 0), array('super_editor = ?' => 1));
        $editorTable->update(array('super_editor' => 1), array('user_id = ?' => $editor->user_id));

        $listingtypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes();

        foreach ($listingtypes as $listingtype) {

          //IF EDITOR IS NOT EXIST
          $isExist = $editorTable->isEditor($editor->user_id, $listingtype->listingtype_id);
          if (empty($isExist)) {
            $editorNew = $editorTable->createRow();
            $editorNew->user_id = $editor->user_id;
            $editorNew->listingtype_id = $listingtype->listingtype_id;
            $editorNew->designation = $editor->designation;
            $editorNew->details = $editor->details;
            $editorNew->about = $editor->about;
            $editorNew->super_editor = 1;
            $editorNew->save();
          }
        }

        //COMMIT
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }

      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('Super Editor has been made successfully.'))
      ));
    }

    $this->renderScript('admin-editors/super-editor.tpl');
  }

  //ACTION FOR REMOVING EDITORS
  public function deleteAction() {

    //LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    //GET EDTITOR ID AND DETAILS
    $this->view->editor_id = $editor_id = $this->_getParam('editor_id');
    $editor = Engine_Api::_()->getItem('sitereview_editor', $editor_id);

    if ($editor->super_editor) {
      return;
    }

    //GET FORM
    $this->view->form = $form = new Sitereview_Form_Admin_Editors_Map();

    //CHECK FORM VALIDATION
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      //GET DB
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {

        $editorTable = Engine_Api::_()->getDbtable('editors', 'sitereview');
        $reviewTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
        $reviewTableName = $reviewTable->info('name');
        $listingTable = Engine_Api::_()->getDbtable('listings', 'sitereview');
        $listingTableName = $listingTable->info('name');

        foreach ($form->getValues() as $key => $value) {
          $keys = explode('_listtype_', $key);
          $listingtype_id = $keys[1];

          if (!empty($listingtype_id) && is_numeric($listingtype_id)) {
            $select = $reviewTable->select()
                    ->setIntegrityCheck(false)
                    ->from($reviewTableName, 'review_id')
                    ->joinInner($listingTableName, "$reviewTableName.resource_id = $listingTableName.listing_id", array())
                    ->where($reviewTableName . '.resource_type = ?', 'sitereview_listing')
                    ->where($reviewTableName . '.type = ?', 'editor')
                    ->where($reviewTableName . '.owner_id = ?', $editor->user_id)
                    ->where($listingTableName . '.listingtype_id = ?', $listingtype_id)
            ;
            $reviews = $reviewTable->fetchAll($select);

            if (!empty($value)) {
              foreach ($reviews as $review) {
                $reviewTable->update(array('owner_id' => $value), array('review_id = ?' => $review->review_id));
                Engine_Api::_()->getDbTable('ratings', 'sitereview')->update(array('user_id' => $value), array('review_id = ?' => $review->review_id, 'type' => 'editor'));
              }
            } else {
              foreach ($reviews as $review) {
                Engine_Api::_()->getItem('sitereview_review', $review->review_id)->delete();
              }
            }
          }

          $editorTable->delete(array('user_id = ?' => $editor->user_id, 'listingtype_id = ?' => $listingtype_id));
        }

        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('Editor has been deleted successfully.'))
      ));
    }
    //OUTPUT
    $this->renderScript('admin-editors/delete.tpl');
  }

}
