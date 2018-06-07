<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminManageController.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_AdminManageController extends Core_Controller_Action_Admin {

  function init() {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_manage');
  }

  public function indexAction() {
    $this->view->subnavigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_manage', array(), 'advancedactivity_admin_manage_general');
    $this->view->form = $form = new Advancedactivity_Form_Admin_Manage_Genral();
    if (!$this->getRequest()->isPost()) {
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    // Process
    $values = $form->getValues();
    if ($form->advancedactivity_friendlist_filtering_dummy)
      unset($values['advancedactivity_friendlist_filtering_dummy']);
    $settings = Engine_Api::_()->getApi('settings', 'core');
    include APPLICATION_PATH . '/application/modules/Advancedactivity/controllers/license/license2.php';
  }

  public function contentListsAction() {
    $this->view->subnavigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_manage', array(), 'advancedactivity_admin_manage_content');
    include APPLICATION_PATH . '/application/modules/Advancedactivity/controllers/license/license2.php';
  }

  public function addContentAction() {
    $this->view->subnavigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_manage', array(), 'advancedactivity_admin_manage_content');
    $this->view->form = $form = new Advancedactivity_Form_Admin_Manage_Content();
    if (!$this->getRequest()->isPost()) {
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    // Process
    $values = $form->getValues();
    $contentTable = Engine_Api::_()->getItemTable('advancedactivity_content');
    $contentCheck = $contentTable->fetchRow(array('filter_type = ?' => $values['filter_type']));
    if (!empty($contentCheck)) {
      $itemError = Zend_Registry::get('Zend_Translate')->_("Content Type already exists.");
      $form->getDecorator('errors')->setOption('escape', false);
      $form->addError($itemError);
      return;
    }
    $content = $contentTable->createRow();
    $content->setFromArray($values);
    $content->save();
    $this->_redirect('admin/advancedactivity/manage/content-lists');
  }

  public function editContentAction() {
    $this->view->subnavigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_manage', array(), 'advancedactivity_admin_manage_content');
    $filter_type = $this->_getParam('filter_type');
    $content = Engine_Api::_()->getItemTable('advancedactivity_content')->fetchRow(array('filter_type = ?' => $filter_type));
    $this->view->form = $form = new Advancedactivity_Form_Admin_Manage_EditContent();
    if ($filter_type == 'all') {
      $form->removeElement('content_tab');
      $form->addElement('Dummy', 'content_tab_dummy', array(
          'order' => 1,
          'label' => 'Filtering Activity Feeds',
          'description' => "<div class='tip'> <span>The \"All Updates\" option cannot be disabled from the lists entries.</span></div>",
      ));
      $form->getElement('content_tab_dummy')->getDecorator('Description')->setOptions(array('placement', Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }
    if (!$this->getRequest()->isPost()) {

      $form->populate($content->toarray());
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    // Process
    $values = $form->getValues();
    $content->setFromArray($values);
    $content->save();
    $this->_redirect('admin/advancedactivity/manage/content-lists');
  }

  public function deleteContentAction() {
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->filter_type = $filter_type = $this->_getParam('filter_type');

    if ($this->getRequest()->isPost()) {

      $content = Engine_Api::_()->getItemTable('advancedactivity_content')->fetchRow(array('filter_type = ?' => $filter_type));
      $content->delete();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_(''))
      ));
    }
    //  $this->renderScript('manage/delete.tpl');
  }

  public function enabledContentTabAction() {
    $filter_type = $this->_getParam('filter_type');
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    $content = Engine_Api::_()->getItemTable('advancedactivity_content')->fetchRow(array('filter_type = ?' => $filter_type));
    try {
      $content->content_tab = !$content->content_tab;
      $content->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->_redirect('admin/advancedactivity/manage/content-lists');
  }

  public function customListsAction() {
    $this->view->subnavigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_manage', array(), 'advancedactivity_admin_manage_custom');
    include APPLICATION_PATH . '/application/modules/Advancedactivity/controllers/license/license2.php';
  }

  public function addCustomAction() {
    $this->view->subnavigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_manage', array(), 'advancedactivity_admin_manage_custom');
    $this->view->form = $form = new Advancedactivity_Form_Admin_Manage_CustomType();
    if (!$this->getRequest()->isPost()) {
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    // Process
    $values = $form->getValues();
    $customTable = Engine_Api::_()->getItemTable('advancedactivity_customtype');
    $customCheck = $customTable->fetchRow(array('resource_type = ?' => $values['resource_type']));
    if (!empty($customCheck)) {
      $itemError = Zend_Registry::get('Zend_Translate')->_("Content Type already exists.");
      $form->getDecorator('errors')->setOption('escape', false);
      $form->addError($itemError);
      return;
    }
    $custom = $customTable->createRow();
    $custom->setFromArray($values);
    $custom->save();
    $this->_redirect('admin/advancedactivity/manage/custom-lists');
  }

  public function editCustomAction() {
    $this->view->subnavigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_manage', array(), 'advancedactivity_admin_manage_custom');
    $resource_type = $this->_getParam('resource_type');
    $custom = Engine_Api::_()->getItemTable('advancedactivity_customtype')->fetchRow(array('resource_type = ?' => $resource_type));
    $this->view->form = $form = new Advancedactivity_Form_Admin_Manage_EditCustomType();

    if (!$this->getRequest()->isPost()) {
      $form->populate($custom->toarray());
      $options = array_keys($form->getElement('module_name')->getMultiOptions());
      unset($options[0]);
      if (!in_array($custom->module_name, $options)) {
        $form->removeElement('module_name');
        $module_title = Engine_Api::_()->getDbTable('modules', 'core')->getModule($custom->module_name)->title; 
        if($custom->module_name=='sitereviewlistingtype'){
         $module_title .=" (". Engine_Api::_()->getItemByGuid(str_replace('sitereview_listing_listtype','sitereview_listingtype',$resource_type))->getTitle(true).")";
        }
        $form->addElement('Dummy', 'module_name_dummy', array(
            'order' => 0,
            'label' => 'Content Module',
            'description' => $module_title,
        ));
      }
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    // Process
    $values = $form->getValues();
    $custom->setFromArray($values);
    $custom->save();
    $this->_redirect('admin/advancedactivity/manage/custom-lists');
  }

  public function deleteCustomAction() {
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->resource_type = $resource_type = $this->_getParam('resource_type');

    if ($this->getRequest()->isPost()) {

      $custom = Engine_Api::_()->getItemTable('advancedactivity_customtype')->fetchRow(array('resource_type = ?' => $resource_type));
      $custom->delete();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_(''))
      ));
    }
    //  $this->renderScript('manage/delete.tpl');
  }

  public function enabledCustomListAction() {
    $resource_type = $this->_getParam('resource_type');
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    $custom = Engine_Api::_()->getItemTable('advancedactivity_customtype')->fetchRow(array('resource_type = ?' => $resource_type));
    try {
      $custom->enabled = !$custom->enabled;
      $custom->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
    $this->_redirect('admin/advancedactivity/manage/custom-lists');
  }

  //ACTION FOR UPDATE ORDER 
  public function updateOrderAction() {
    //CHECK POST
    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      $values = $_POST;
      try {
        foreach ($values['order'] as $key => $value) {
          $content = Engine_Api::_()->getItem($_POST['item_type'], (int) $value);
          if (!empty($content)) {
            $content->order = $key + 1;
            $content->save();
          }
        }
        $db->commit();
        if ($_POST['item_type'] == 'advancedactivity_content') {
          $this->_redirect('admin/advancedactivity/manage/content-lists');
        } else {
          $this->_redirect('admin/advancedactivity/manage/custom-lists');
        }
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
  }

}