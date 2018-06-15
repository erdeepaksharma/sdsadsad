<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminWhereToBuyController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_AdminWhereToBuyController extends Core_Controller_Action_Admin {

  public function init() {
    
    //GET NAVIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_wheretobuy');
  }

  public function indexAction() {
    
    include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license2.php';
    $this->view->totalCount = count($this->view->list);
  }

  public function addAction() {

    $this->view->form = $form = new Sitereview_Form_Admin_WhereToBuy_Add();
    $table = Engine_Api::_()->getItemTable('sitereview_wheretobuy');
    //CHECK POST
    if (!$this->getRequest()->isPost()) {
      return;
    }

    //CHECK VALIDITY
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    //PROCESS
    $values = $form->getValues();
    $table = Engine_Api::_()->getItemTable('sitereview_wheretobuy');

    $db = $table->getAdapter();
    $db->beginTransaction();
    try {

      include_once APPLICATION_PATH . '/application/modules/Sitereview/controllers/license/license2.php';
      
      if (!empty($values['photo'])) {
        $wheretobuy->setPhoto($form->photo);
      }
      //COMMIT
      $db->commit();
      return $this->_forward('success', 'utility', 'core', array(
                  'smoothboxClose' => true,
                  'parentRefresh' => true,
                  'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your \'Where to Buy\' option has been added successfully.'))
              ));
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }

  public function editAction() {
    
    $id = $this->_getParam('id');

    $this->view->item = $item = Engine_Api::_()->getItem('sitereview_wheretobuy', $id);
    $this->view->form = $form = new Sitereview_Form_Admin_WhereToBuy_Edit();
    $form->populate($item->toarray());
    $table = Engine_Api::_()->getItemTable('sitereview_wheretobuy');
    if ($id == 1) {
      $form->removeElement('photo');
    }
    //CHECK POST
    if (!$this->getRequest()->isPost()) {
      return;
    }

    //CHECK VALIDITY
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    //PROCESS
    $values = $form->getValues();
    $table = Engine_Api::_()->getItemTable('sitereview_wheretobuy');

    $db = $table->getAdapter();
    $db->beginTransaction();
    try {

      //SET PERMISSION
      $item->setFromArray($values);
      $item->save();

      if (!empty($values['photo'])) {
        $item->setPhoto($form->photo);
      }

      //COMMIT
      $db->commit();
      //LAYOUT
      if (null === $this->_helper->ajaxContext->getCurrentContext()) {
        $this->_helper->layout->setLayout('default-simple');
      } else {
        $this->_helper->layout->disableLayout(true);
      }
      return $this->_forward('success', 'utility', 'core', array(
                  'smoothboxClose' => true,
                  'parentRefresh' => true,
                  'smoothboxClose' => 1000,
                  'parentRefresh' => 1000,
                  'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your \'Where to Buy\' option has been edited successfully.'))
              ));
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }
  }

  public function deleteAction() {
    
    $this->_helper->layout->setLayout('admin-simple');

    $this->view->id = $id = $this->_getParam('id');

    if ($this->getRequest()->isPost()) {
      $item = Engine_Api::_()->getItem('sitereview_wheretobuy', $id);

      $item->delete();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array('Deleted Succesfully.')
      ));
    }
    //$this->renderScript('admin-where-to-buy/delete.tpl');
  }

  public function enabledAction() {
    
    $id = $this->_getParam('id');
    if (!empty($id)) {
      $item = Engine_Api::_()->getItem('sitereview_wheretobuy', $id);
      $item->enabled = !$item->enabled;
      $item->save();
    }
    
    $this->_redirect('admin/sitereview/where-to-buy');
  }

  public function removeIconAction() {
    
    $this->_helper->layout->setLayout('admin-simple');

    $this->view->id = $id = $this->_getParam('id');

    if ($this->getRequest()->isPost()) {

      $item = Engine_Api::_()->getItem('sitereview_wheretobuy', $id);

      $file = Engine_Api::_()->getItemTable('storage_file')->getFile($item->photo_id);
      if ($file)
        $file->remove();

      $item->photo_id = 0;
      $item->save();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 1000,
          'parentRefresh' => 1000,
          'messages' => array('Deleted Succesfully.')
      ));
    }
  }

}