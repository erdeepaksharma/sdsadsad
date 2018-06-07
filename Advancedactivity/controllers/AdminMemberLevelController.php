<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2016-2017 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdminLevelController.php 2017-03-08 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_AdminMemberLevelController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
  	$this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
    ->getNavigation('advancedactivity_admin_main', array(), 'advancedactivity_admin_main_member_level');
    $this->view->form = $form = new Advancedactivity_Form_Admin_MemberLevel();
    
       // Get level id
    if( null !== ($id = $this->_getParam('id')) ) {
      $level = Engine_Api::_()->getItem('authorization_level', $id);
    } else {
      $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
    }
    if( !$level instanceof Authorization_Model_Level ) {
      throw new Engine_Exception('missing level');
    }

    $id = $level->level_id;
    $form->level_id->setValue($id);
    if(($level->type == 'public'))
    { $elements = array('aaf_pinunpin_enable', 'aaf_schedule_post_enable', 'aaf_targeted_post_enable','aaf_memories_enable', 'aaf_add_feeling_enable', 'aaf_advertise_enable','aaf_greeting_enable','aaf_feed_banner_enable');
      foreach($elements as $k=> $element){
          $form->removeElement($element);
      }
      $form->addNotice('No settings are available for this member level.');
    }
 // Populate values
    $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
    $form->populate($permissionsTable->getAllowed('advancedactivity_feed', $id, array_keys($form->getValues())));

 // Check post
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    // Check validitiy
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process

    $values = $form->getValues();
    $db = $permissionsTable->getAdapter();
    $db->beginTransaction();

    try
    {
    $permissionsTable->setAllowed('advancedactivity_feed', $id, $values);
      // Commit
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
    $form->addNotice('Your changes have been saved.');

  }
  

}
