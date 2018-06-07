<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminFeelingController.php 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */

class Advancedactivity_AdminFeelingController extends Core_Controller_Action_Admin {
    public function init(){
        $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_feelingtype');
    }

    public function indexAction() {
    
        }

  public function deleteAction() {
    //LAYOUT
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->feeling_id = $feeling_id = $this->_getParam('feeling_id');

    if (!$this->getRequest()->isPost()) {
      return;
    }
    $values = $this->getRequest()->getPost();

    if ($values['confirm'] != $feeling_id) {
      return;
    }

    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();

    try {
     $feeling = Engine_Api::_()->getItem('advancedactivity_feeling', $feeling_id);		
     $feeling->delete();  
      $db->commit();
    } catch (Exception $ex) {
      $db->rollBack();
      throw $ex;
    }
    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 10,
      'parentRefresh' => 10,
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Deleted'))
    ));
  }

  public function editAction() {
    //LAYOUT
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->feeling_id = $feeling_id = $this->_getParam('feeling_id');
    // Get form
    $this->view->form = $form = new Sitereaction_Form_Admin_Collection_Sticker_Edit();
    $feeling = Engine_Api::_()->getItem('advancedactivity_feeling', $feeling_id);
    if (!$this->getRequest()->isPost()) {
      $form->populate($feeling->toArray());
      return;
    }

    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    // Process
    $values = $form->getValues();
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try {
      
      $feeling->setFromArray($values);		
      $feeling->save();
      $db->commit();
    } catch (Exception $ex) {
      $db->rollBack();
      throw $ex;
    }
    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 10,
      'parentRefresh' => 10,
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Saved Changes!'))
    ));
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
        $row = Engine_Api::_()->getItem('advancedactivity_feeling', (int) $value);		  
           if (!empty($row)) {		
            $row->order = $key + 1;		
            $row->save();		
           }		
        }  
        $db->commit();
        $this->_redirect('admin/advancedactivity/feelingtype/manage/feelingtype_id/' . $values['feelingtype_id']);
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
  }

}
