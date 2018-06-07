<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: SocialLinkController.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_AdminSocialLinkController extends Core_Controller_Action_Admin {

   public function init() {
    //GET NAVIGATION
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_share');  
    
   }


    public function indexAction(){
     
      $linkTable = Engine_Api::_()->getDbTable('links','advancedactivity');
      $select = $linkTable->select()
                ->order('order');
      $this->view->links = $linkTable->fetchAll($select);
        
    }
   
   public function createAction(){
      $this->view->form = $form = new Advancedactivity_Form_Admin_Link_Create();
      if (!$this->getRequest()->isPost()) {
                return;
        }
    //CHECK VALIDITY
    if (!$form->isValid($this->getRequest()->getPost())) {
           return;
    }
      $values = $form->getValues();
      $linkTable = Engine_Api::_()->getDbTable('links','advancedactivity');
      
      if(isset($_FILES['icon'])){
        $photo =  $linkTable->setPhoto($form->icon);
      }
      if(!empty($photo)){
        $values['icon_id'] = $photo->file_id;
      } else {
        $values['icon_id'] = 0;
      }
      unset($values['icon']);
     
      $row = $linkTable->createRow();
      $row->setFromArray($values);
      $link_id = $row->save();
    

    $this->_helper->redirector->gotoRoute(array('action' => 'index'));
   }
    public function editAction(){
      $link_id  = $this->getParam('link_id','');
      if(empty($link_id)){
          return;
      }
      $this->view->form = $form = new Advancedactivity_Form_Admin_Link_Edit();
      $linkItem = Engine_Api::_()->getItem('advancedactivity_link',$link_id);
      $form->populate($linkItem->toArray());
      if (!$this->getRequest()->isPost()) {
                return;
        }
    //CHECK VALIDITY
    if (!$form->isValid($this->getRequest()->getPost())) {
           return;
    }
      $values = $form->getValues();
      
      $linkItem->title = $values['title'];
      $linkItem->icon_path = $values['icon_path'];
      $linkItem->save();
      

   $this->_helper->redirector->gotoRoute(array('action' => 'index'));
   }
   public function deleteAction(){
       $this->view->link_id = $link_id = $this->getParam('link_id','');
       $linkItem = Engine_Api::_()->getItem('advancedactivity_link',$link_id);
       if (!$this->getRequest()->isPost()) {
                return;
        }
      $linkItem->delete();
      $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 10,
      'parentRefresh' => 10,
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Deleted'))
    ));
   }
   
  public function enabledAction() {
    $this->view->link_id = $link_id = $this->_getParam('link_id');
    
      $linkItem = Engine_Api::_()->getItem('advancedactivity_link',$link_id);
      $linkItem->enabled = !$linkItem->enabled;
      $linkItem->save();
       
     $this->_helper->redirector->gotoRoute(array('action' => 'index'));
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
          $row = Engine_Api::_()->getItem('advancedactivity_link', (int) $value);
          if (!empty($row)) {
            $row->order = $key + 1;
            $row->save();
          }
        }
        $db->commit();
        $this->_helper->redirector->gotoRoute(array('action' => 'index'));
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
  }
}
