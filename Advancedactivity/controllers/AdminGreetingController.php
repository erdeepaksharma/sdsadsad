<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminGreetingController.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_AdminGreetingController extends Core_Controller_Action_Admin {

  function init() {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_greeting');
  }

   public function indexAction(){
     
      $greetingTable = Engine_Api::_()->getDbTable('greetings','advancedactivity');
      
      $this->view->greetings = $greetingTable->fetchAll();
        
    }
  public function createAction() {
    
    $this->view->form = $form = new Advancedactivity_Form_Admin_Greeting_Create();
    
    if (!$this->getRequest()->isPost()) {
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    $greeting = Engine_Api::_()->getDbTable('greetings','advancedactivity');
    // Process
    $values = $form->getValues();
    if(date('H:i:s',strtotime($values['endtime'])) == '00:00:00') {
      $values['endtime'] = date('Y-m-d H:i:s',strtotime("-1 second", strtotime($values['endtime'])));
    }
    
    $row = $greeting->createRow();
    $row->setFromArray($values);
    $row->save();
    $this->_helper->redirector->gotoRoute(array('action' => 'index'));
  }

  public function editAction(){
      $greeting_id = $this->getParam('greeting_id','');
      if (empty($greeting_id)){
            return;
      }
      $item = Engine_Api::_()->getItem('advancedactivity_greeting',$greeting_id);
      $this->view->form = $form = new Advancedactivity_Form_Admin_Greeting_Edit();
      $form->setTitle('Edit Greeting / Announcement')
              ->setDescription('Here,you can edit this greeting according to below form.');
      $form->submit->setLabel('Edit');
      $form->preview->setLabel('Preview');
      $form->populate($item->toArray());
      $this->view->repeat = $item->repeat;
      if (!$this->getRequest()->isPost()) {
            return;
      }
      if (!$form->isValid($this->getRequest()->getPost())) {
            return;
      }
      $values = $form->getValues();
      if(date('H:i:s',strtotime($values['endtime'])) == '00:00:00') {
      $values['endtime'] = date('Y-m-d H:i:s',strtotime("-1 second", strtotime($values['endtime'])));
      }
      
      $item->setFromArray($values);
      $item->save();
      $this->_helper->redirector->gotoRoute(array('action' => 'index'));
  }

  public function uploadPhotoAction()
  {
      
    $viewer = Engine_Api::_()->user()->getViewer();

    $this->_helper->layout->disableLayout();

  
    if( !$this->_helper->requireUser()->checkRequire() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded (probably).');
      return;
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }
    if( !isset($_FILES['userfile']) || !is_uploaded_file($_FILES['userfile']['tmp_name']) )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
      return;
    }

    $db = Engine_Api::_()->getDbtable('photos', 'album')->getAdapter();
    $db->beginTransaction();

    try
    {
      
      $photoTable = Engine_Api::_()->getDbtable('photos', 'album');
      $photo = $photoTable->createRow();
      $photo->setFromArray(array(
        'owner_type' => 'admin',
        'owner_id' => $viewer->getIdentity()
      ));
      $photo->save();

      $photo->setPhoto($_FILES['userfile']);

      $this->view->status = true;
      $this->view->name = $_FILES['userfile']['name'];
      $this->view->photo_id = $photo->photo_id;
      $this->view->photo_url = $photo->getPhotoUrl();
      $db->commit();

    } catch( Album_Model_Exception $e ) {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = $this->view->translate($e->getMessage());
      throw $e;
      return;

    } catch( Exception $e ) {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('An error occurred.');
      throw $e;
      return;
    }
  }
  
   public function deleteAction(){
       $this->view->greeting_id = $greeting_id = $this->getParam('greeting_id','');
       $item = Engine_Api::_()->getItem('advancedactivity_greeting',$greeting_id);
       if (!$this->getRequest()->isPost()) {
                return;
        }
      $item->delete();
      $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 10,
      'parentRefresh' => 10,
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Deleted'))
    ));
   }
   
  public function enabledAction() {
      $this->view->greeting_id = $greeting_id = $this->_getParam('greeting_id');
    
      $item = Engine_Api::_()->getItem('advancedactivity_greeting',$greeting_id);
      $item->enabled = !$item->enabled;
      $item->save();
       
     $this->_helper->redirector->gotoRoute(array('action' => 'index'));
  }
  
  public function previewAction(){
      $greeting_id = $this->_getParam('greeting_id');
      if(empty($greeting_id)){
          return;
      }
      $item = Engine_Api::_()->getItem('advancedactivity_greeting',$greeting_id);
      $this->view->body = $item->body;
  }
}