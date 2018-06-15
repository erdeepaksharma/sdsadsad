<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminVideoController.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_AdminVideoController extends Core_Controller_Action_Admin {

  //ACTION FOR MANAGING THE LISTING VIDEOS
  public function manageAction() {

    //GET NAGIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_video');

    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sitereview_admin_submain', array(), 'sitereview_admin_submain_manage_tab');

    $this->view->enable_video = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('video');

    //FORM GENERATION
    $this->view->formFilter = $formFilter = new Sitereview_Form_Admin_Manage_Filter();

    //USER TABLE NAME
    $tableUser = Engine_Api::_()->getItemTable('user')->info('name');

    //LISTING TABLE NAME
    $tablesitereview = Engine_Api::_()->getItemTable('sitereview_listing')->info('name');

    //LISTING-VIDEO TABLE
    $table = Engine_Api::_()->getDbtable('videos', 'sitereview');
    $rName = $table->info('name');

    $this->view->type_video = $type_video = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video');
    $videoTable = Engine_Api::_()->getDbTable('clasfvideos', 'sitereview');
    if ($type_video) {

      //VIDEO TABLE NAME
      $videoTableName = $videoTable->info('name');

      //GET CORE VIDEO TABLE
      $coreVideoTable = Engine_Api::_()->getDbtable('videos', 'video');
      $coreVideoTableName = $coreVideoTable->info('name');

      //MAKE QUERY
      $select = $coreVideoTable->select()
              ->setIntegrityCheck(false)
              ->from($coreVideoTableName)
              ->join($videoTableName, $coreVideoTableName . '.video_id = ' . $videoTableName . '.video_id', array())
              ->join($tablesitereview, "$videoTableName.listing_id = $tablesitereview.listing_id", array('title AS sitereview_title', 'listing_id'))
              ->join($tableUser, "$coreVideoTableName.owner_id = $tableUser.user_id", 'displayname')
              ->group($coreVideoTableName . '.video_id');
    } else {
      //MAKE QUERY
      $select = $table->select()
              ->setIntegrityCheck(false)
              ->from($rName)
              ->joinLeft($tableUser, "$rName.owner_id = $tableUser.user_id", 'displayname')
              ->joinLeft($tablesitereview, "$rName.listing_id = $tablesitereview.listing_id", 'title AS sitereview_title');
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

    $values = array_merge(array(
        'order' => 'video_id',
        'order_direction' => 'DESC',
            ), $values);

    $this->view->assign($values);
    $select->order((!empty($values['order']) ? $values['order'] : 'video_id' ) . ' ' . (!empty($values['order_direction']) ? $values['order_direction'] : 'DESC' ));

    $listing = $this->_getParam('listing', 1);
    $this->view->paginator = array();
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $this->view->paginator->setItemCountPerPage(50);
    $this->view->paginator = $paginator->setCurrentPageNumber($listing);
  }

  //ACTION FOR MULTI-VIDEO DELETE
  public function deleteSelectedAction() {

    //GET VIDEO IDS
    $this->view->ids = $ids = $this->_getParam('ids', null);

    //COUNT IDS
    $ids_array = explode(",", $ids);
    $this->view->count = count($ids_array);

    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        $video_id = (int) $value;
        $type_video = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video');
        if ($type_video) {
          $clasfVideoTable = Engine_Api::_()->getDbtable('clasfvideos', 'sitereview');
          $selectClasfvideoTable = $clasfVideoTable->select()
                  ->where('video_id =?', $video_id);
          $objectClasfvideo = $clasfVideoTable->fetchRow($selectClasfvideoTable);
          if ($objectClasfvideo) {
            //FINALLY DELETE VIDEO OBJECT
            $objectClasfvideo->delete();
          }
        } else {
          //GET LISTING VIDEO OBJECT
          $sitereview = Engine_Api::_()->getItem('sitereview_video', $video_id);

          if ($sitereview) {

            //DELETE RATING DATA
            Engine_Api::_()->getDbtable('videoratings', 'sitereview')->delete(array('videorating_id =?' => $video_id));

            //FINALLY DELETE VIDEO OBJECT
            $sitereview->delete();
          }
        }
      }
      $this->_helper->redirector->gotoRoute(array('action' => 'manage'));
    }
  }

  //ACTION FOR DELETE THE LISTING-VIDEO
  public function deleteAction() {

    //DEFAULT LAYOUT
    $this->_helper->layout->setLayout('admin-simple');

    //GET VIDEO ID
    $this->view->video_id = $video_id = $this->_getParam('video_id');

    if ($this->getRequest()->isPost()) {

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {

        $type_video = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video');
        if ($type_video) {
          $clasfVideoTable = Engine_Api::_()->getDbtable('clasfvideos', 'sitereview');
          $selectClasfvideoTable = $clasfVideoTable->select()
                  ->where('video_id =?', $video_id);
          $objectClasfvideo = $clasfVideoTable->fetchRow($selectClasfvideoTable);
          if ($objectClasfvideo) {
            //FINALLY DELETE VIDEO OBJECT
            $objectClasfvideo->delete();
          }
        } else {

          //GET LISTING VIDEO OBJECT
          $sitereview = Engine_Api::_()->getItem('sitereview_video', $video_id);

          //DELETE RATING DATA
          Engine_Api::_()->getDbtable('videoratings', 'sitereview')->delete(array('videorating_id =?' => $video_id));

          //FINALLY DELETE VIDEO OBJECT
          $sitereview->delete();
        }
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_(''))
      ));
    }
    $this->renderScript('admin-video/delete.tpl');
  }

  public function killAction() {
    
    $video_id = $this->_getParam('video_id', null);
    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();

      try {
        $sitereview = Engine_Api::_()->getItem('sitereview_video', $video_id);
        $sitereview->status = 3;
        $sitereview->save();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
  }

  //ACTION OF SETTING FOR CREATING VIDEO FROM MY COMPUTER
  public function utilityAction() {

    //GET NAGIGATION
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitereview_admin_main', array(), 'sitereview_admin_main_video');

    $this->view->subNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('sitereview_admin_submain', array(), 'sitereview_admin_submain_utilities_tab');

    $ffmpeg_path = Engine_Api::_()->getApi('settings', 'core')->sitereview_video_ffmpeg_path;

    $command = "$ffmpeg_path -version 2>&1";
    $this->view->version = $version = @shell_exec($command);

    $command = "$ffmpeg_path -formats 2>&1";
    $this->view->format = $format = @shell_exec($command);
  }

}