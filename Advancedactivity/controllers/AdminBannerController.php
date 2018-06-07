<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminBannerController.php 6590 2016-07-07 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */

class Advancedactivity_AdminBannerController extends Core_Controller_Action_Admin {
    public function init(){
        $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_feelingtype');
    }

    public function indexAction() {
        $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_settings');
        $table = Engine_Api::_()->getDbtable('banners', 'advancedactivity');	
        $select = $table->select()		
         ->order('order ASC');		
        $this->view->banners = $table->fetchAll($select); 
    }
    
    public function createAction(){
        $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_settings');
         $this->view->form = $form = new Advancedactivity_Form_Admin_FeedBanner();
         if (!$this->getRequest()->isPost()) {
            return;
         }
         if (!$form->isValid($this->getRequest()->getPost())) {
            return;
         }
        $upload = new Zend_File_Transfer_Adapter_Http();
        $files = $upload->getFileInfo();
        $bannerFiles = array();
        foreach( $files as $file => $fileInfo ) {
          if( $upload->isUploaded($file) ) {
            if( $upload->isValid($file) ) {
              if( $upload->receive($file) ) {
                $info = $upload->getFileInfo($file);
                $bannerFiles[] = $info[$file];
                // here $tmp is the location of the uploaded file on the server
                // var_dump($info); to see all the fields you can use
              }
            }
          }
        }

        $filesCounter = count($bannerFiles);
        $values = $form->getValues();
         $table = Engine_Api::_()->getDbtable('banners', 'advancedactivity');
         $i = 0;
         do {
          $row = $table->createRow();
          $row->setFromArray($values);
          $row->save();
          if( $files ) {
            $row->file_id = $table->setPhoto($bannerFiles[$i]);
            $row->save();
          }
          $i++;
        } while( $i < $filesCounter );
         $this->_helper->redirector->gotoRoute(array('module' => 'advancedactivity', 'controller' => 'banner'), 'admin_default', true);

    }
    public function editAction(){
      $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_settings');
      $this->view->banner_id = $banner_id = $this->getParam('banner_id','');
      if (empty($banner_id)){
            return;
      }
      $this->view->item = $item = Engine_Api::_()->getItem('advancedactivity_banner',$banner_id);
      $this->view->form = $form = new Advancedactivity_Form_Admin_FeedBanner();
      if(!empty($item->file_id)){
          $file = Engine_Api::_()->getDbtable('files', 'storage')->getFile($item->file_id);
          $this->view->image = $file ? $file->getHref() : '';
      }
      if(!empty($item->gradient)){
          $form->gradient_enabled->setValue(1);
      }
      $form->setTitle('Edit Banner')
              ->setDescription('Here,you can edit this banner according to below form.');
      $form->submit->setLabel('Save Changes');
      $form->populate($item->toArray());
      if (!$this->getRequest()->isPost()) {
            return;
      }
      if (!$form->isValid($this->getRequest()->getPost())) {
            return;
      }
      $values = $form->getValues();
      if(!empty($values['banner'])){
          $table = Engine_Api::_()->getDbtable('banners', 'advancedactivity');
          $item->file_id = $table->setPhoto($form->banner);
          $item->save();
         }
      $item->setFromArray($values);
      $item->save();
      $this->_helper->redirector->gotoRoute(array('module' => 'advancedactivity', 'controller' => 'banner'), 'admin_default', true);
  }
    public function deleteAction(){
       $this->view->banner_id = $banner_id = $this->getParam('banner_id','');
       $item = Engine_Api::_()->getItem('advancedactivity_banner',$banner_id);
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
      $this->view->banner_id = $banner_id = $this->_getParam('banner_id');
    
      $item = Engine_Api::_()->getItem('advancedactivity_banner',$banner_id);
      $item->enabled = !$item->enabled;
      $item->save();
       
      $this->_helper->redirector->gotoRoute(array('module' => 'advancedactivity', 'controller' => 'banner'), 'admin_default', true);
  }
  public function highlightAction() {
      $this->view->banner_id = $banner_id = $this->_getParam('banner_id');
    
      $item = Engine_Api::_()->getItem('advancedactivity_banner',$banner_id);
      $item->highlighted = !$item->highlighted;
      $item->save();
       
      $this->_helper->redirector->gotoRoute(array('module' => 'advancedactivity', 'controller' => 'banner'), 'admin_default', true);
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
        $row = Engine_Api::_()->getItem('advancedactivity_banner', (int) $value);		  
           if (!empty($row)) {		
            $row->order = $key + 1;		
            $row->save();		
           }		
        }  
        $db->commit();
        $this->_helper->redirector->gotoRoute(array('module' => 'advancedactivity', 'controller' => 'banner'), 'admin_default', true);
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
  }
}
