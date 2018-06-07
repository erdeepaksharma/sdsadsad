<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: IndexController.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_BuySellController extends Core_Controller_Action_Standard
{

  protected $_HOST_NAME;

  public function init()
  {
    $this->_HOST_NAME = $_SERVER['HTTP_HOST'];
  }

  public function createAction()
  {
    if( isset($_GET['ul']) )
      return $this->_forward('upload-photo', null, null, array('format' => 'json'));
    $viewer = Engine_Api::_()->user()->getViewer();

    // //CHECK FORM VALIDATION
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    $values = $this->getRequest()->getPost();
    $album = Engine_Api::_()->getItemTable('album_photo');
    $images = array();
    $image_ids = array();
    foreach( $values as $key => $value ) {
      if( $key != 'photo_ids' ) {
        $this->view->$key = $value;
      } else if( !empty($values['photo_ids']) ) {
        $photo_ids = explode(" ", $values['photo_ids']);

        foreach( array_filter($photo_ids) as $photo_id ) {
          $album = Engine_Api::_()->getItem('album_photo', $photo_id);
          $image_ids[] = $album->file_id;
          $images[] = $album->getPhotoUrl();
        }
      }
    }


    $this->view->title = $this->_getParam('title');
    $this->view->images = $images;
    $this->view->type = 'sell';
    $this->view->photo_id = $image_ids;
    $this->view->owner_id = $viewer->getIdentity();
  }

  public function uploadPhotoAction()
  {

    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid request method');
      return;
    }

    $values = $this->getRequest()->getPost();
    if( empty($values['Filename']) && !isset($_FILES['Filedata']) ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('No file');
      return;
    }

    if( !isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name']) ) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid Upload');
      return;
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $table = Engine_Api::_()->getDbtable('albums', 'album');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      $type = $this->_getParam('type', 'wall');

      if( empty($type) )
        $type = 'wall';
      $album = $table->getSpecialAlbum($viewer, $type);

      $photoTable = Engine_Api::_()->getDbtable('photos', 'album');
      $photo = $photoTable->createRow();
      $photo->setFromArray(array(
        'owner_type' => 'user',
        'owner_id' => Engine_Api::_()->user()->getViewer()->getIdentity()
      ));
      $photo->save();
      $photo->setPhoto($_FILES['Filedata']);
      $photo->order = $photo->photo_id;
      $photo->album_id = $album->album_id;
      $photo->save();

      if( !$album->photo_id ) {
        $album->photo_id = $photo->getIdentity();
        $album->save();
      }

      // Authorizations
      $auth = Engine_Api::_()->authorization()->context;
      $auth->setAllowed($photo, 'everyone', 'view', true);
      $auth->setAllowed($photo, 'everyone', 'comment', true);

      $db->commit();
      $this->view->status = true;
      $this->view->photo_id = $photo->photo_id;
      $this->view->album_id = $album->album_id;
      $this->view->imgSrc = $photo->getPhotoUrl();
    } catch( Exception $e ) {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_(' Invalid Upload ' . $e->getMessage());
      return;
    }
  }

  public function manageAction()
  {
    if( !$this->_helper->requireUser()->isValid() )
      return;

    $viewer = Engine_Api::_()->user()->getViewer();

    $values['user_id'] = $viewer->getIdentity();

    $customFieldValues = array();
    // Get paginator
    $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('advancedactivity_sell')->getSellsPaginator($values, $customFieldValues);
    $items_count = 5;
    $paginator->setItemCountPerPage($items_count);
    $this->view->paginator = $paginator->setCurrentPageNumber($this->_getParam('page', 1));

    $view = $this->view;
    $view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');
    $this->view->current_count = $paginator->getTotalItemCount();
  }
  
 
  public function deleteAction()
  {
    //LAYOUT

    $this->view->sell_id = $sell_id = $this->_getParam('sell_id');

    if( !$this->getRequest()->isPost() ) {
      return;
    }
    $values = $this->getRequest()->getPost();

    if( $values['confirm'] != $sell_id ) {
      return;
    }

    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();

    try {
      $row = Engine_Api::_()->getItem('advancedactivity_sell', $sell_id);
      $row->delete();
      $db->commit();
    } catch( Exception $ex ) {
      $db->rollBack();
      throw $ex;
    }
    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 10,
      'parentRefresh' => 10,
      'messages' => array(Zend_Registry::get('Zend_Translate')->_('Deleted'))
    ));
  }

  public function openCloseAction()
  {
    $this->view->sell_id = $sell_id = $this->_getParam('sell_id');

    $item = Engine_Api::_()->getItem('advancedactivity_sell', $sell_id);
    $item->closed = !$item->closed;
    $item->save();
    $isAjax = $this->_getParam('isAjax');
    if(empty($isAjax)){
        $this->_helper->redirector->gotoRoute(array('module' => 'advancedactivity', 'controller' => 'buy-sell', 'action' => 'manage'), 'default', true);
    }
    die(json_encode(array('success'=>true,'closed'=>$item->closed)));
  } 

  public function editAction()
  {
    if( !$this->_helper->requireUser()->isValid() && !$this->_getParam('sell_id'))
      return;
    if( isset($_GET['ul']) ) {
      return $this->_forward('upload-photo', null, null, array('format' => 'json'));
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $sell = Engine_Api::_()->getItem('advancedactivity_sell', $this->_getParam('sell_id'));
    if( !Engine_Api::_()->core()->hasSubject('advancedactivity_sell') ) {
      Engine_Api::_()->core()->setSubject($sell);
    }
    $this->view->sell = $sell;

    // Check auth
    if( !$this->_helper->requireSubject()->isValid() ) {
      return;
    }


    // Prepare form
    $this->view->form = $form = new Advancedactivity_Form_BuySell_Edit(array(
      'item' => $sell
    ));
   
    $form->populate($sell->toArray());
    $photo_ids = explode(" ", $sell->photo_id);
    foreach( array_filter($photo_ids) as $photo_id ) {
      $photo = Engine_Api::_()->getItem('album_photo', $photo_id);
      $photo ? $images[$photo_id] = $photo->getPhotoUrl() : '';
    } 
    
    $this->view->images = $images;
    if( !$this->getRequest()->getPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process
    // handle save for tags
    $values = $form->getValues();
    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();

    try {

      $sell->setFromArray($values);
      $sell->date = date('Y-m-d H:i:s');
      $sell->save();
      // Save custom fields
      $customfieldform = $form->getSubForm('fields');
      $customfieldform->setItem($sell);
      $customfieldform->saveValues();
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
    $this->_helper->redirector->gotoRoute(array('module' => 'advancedactivity', 'controller' => 'buy-sell', 'action' => 'manage'), 'default', true);
  }

  public function managePhotosAction()
  {
    if( isset($_GET['ul']) )
      return $this->_forward('upload-photo', null, null, array('format' => 'json'));
    $this->view->sell_id = $sell_id = $this->_getParam('sell_id');
    $item = Engine_Api::_()->getItem('advancedactivity_sell', $sell_id);

    $this->view->form = $form = new Advancedactivity_Form_BuySell_Photo();

    if( !$this->getRequest()->getPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $item->photo_id = $item->photo_id . $form->getValue('photo_id');
    $item->save();

    $this->_helper->redirector->gotoRoute(array('module' => 'advancedactivity', 'controller' => 'buy-sell', 'action' => 'manage'), 'default', true);
  }

}
