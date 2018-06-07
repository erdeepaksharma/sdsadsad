<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: TakeActionController.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_TakeActionController extends Core_Controller_Action_Standard {

  //FUNCTION FOR TAKE ACTION ON THE REPORT SEND BY USER.
  public function actionAction() {
    // Check report ID and report
    $report_id = $this->_getParam('id', $this->_getParam('report_id'));
    if (!$report_id) {
      $this->view->closeSmoothbox = true;
      return;
    }
    $report = Engine_Api::_()->getItem('advancedactivity_report', $report_id);
    if (!$report) {
      $this->view->closeSmoothbox = true;
      return;
    }

    // Get subject
    try {
      $this->view->subject = $subject = $report->getSubject();
    } catch (Exception $e) {
      $this->view->subject = $subject = null;
    }

    // Get subject owner
    if ($subject instanceof Core_Model_Item_Abstract) {
      try {
        $this->view->subjectOwner = $subjectOwner = $subject->getOwner('user');
      } catch (Exception $e) {
        // Silence
        $this->view->subjectOwner = $subjectOwner = null;
      }
    } else {
      $this->view->subjectOwner = $subjectOwner = null;
    }

    // Get member
    if ($subject instanceof User_Model_User) {
      $user = $subject;
    } else if ($subjectOwner instanceof User_Model_User) {
      $user = $subjectOwner;
    } else {
      $user = null;
    }

    // Get member level
    if ($user) {
      $level = Engine_Api::_()->getItem('authorization_level', $user->level_id);
      if ($level->type == 'admin') {
        $user = null; // Can't delete admins
      }
    }

    // Make form
    $this->view->form = $form = new Advancedactivity_Form_Admin_Report_Action();

    //HERE SUBJECT TYPE IS EMPTY THEN BOTH OPTION IS NOT SHOWN IN THE TAKE ACTION FORM.
    if (empty($subject)) {
      $form->removeElement('action');
      $form->removeElement('action_content');
    }

//     if( !$subject ) {
//       $form->removeElement('action');
//       $form->removeElement('poster_action');
//       $form->removeElement('ban');
//     } else if( $subject instanceof User_Model_User ) {
//       $form->removeElement('action');
//       $form->getElement('action_poster')->setLabel('Action');
//       $form->getElement('ban')->setLabel('Ban IP Address?');
//     } else if( !$subjectOwner || !$user ) {
//       $form->removeElement('ban');
//       $form->removeElement('action_poster');
//     }

    if (!$this->getRequest()->isPost()) {
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }
    // PROCESS
    $values = $form->getValues();

    // DELETE ACTIVITY FEED.
    if (!empty($values['action'])) {
      if ($values['action'] == 1 && $subject instanceof Core_Model_Item_Abstract) {
        $subject->delete();
      }
    }
    // DELETE CONTENT.
    if (!empty($values['action_content'])) {
      $contentObject = $subject->getObject();
      if ($values['action_content'] == 1 && $contentObject instanceof Core_Model_Item_Abstract) {
        $contentObject->delete();
      }
    }

    // DELETE REPORT.
    if (!empty($values['dismiss'])) {
      $report->delete();
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh' => 10,
          'messages' => array(Zend_Registry::get('Zend_Translate')->_('You have successfully delete this abuse report.'))
      ));
    }

//     // Process poster action
//     if( !empty($values['action_poster']) && $user ) {
//       $level = Engine_Api::_()->getItem('authorization_level', $user->level_id);
//       if( $level->type == 'admin' ) {
//         // Ignore
//       } else {
//         if( $values['action_poster'] == 'delete' ) {
//           $user->delete();
//         } else if( $values['action_poster'] == 'disable' ) {
//           $user->enabled = $user->approved = false;
//           $user->save();
//         }
//       }
//     }
//     // Process ban
//     if( !empty($values['ban']) ) {
//       if( $user instanceof User_Model_User ) {
//         $bannedIpsTable = Engine_Api::_()->getDbtable('BannedIps', 'core');
//         if( !empty($user->lastlogin_ip) ) {
//           $bannedIpsTable->addAddress($user->lastlogin_ip);
//         }
//         if( !empty($user->signup_ip) ) {
//           $bannedIpsTable->addAddress($user->signup_ip);
//         }
//       }
//     }
    // Done
    $this->view->closeSmoothbox = true;
  }

}