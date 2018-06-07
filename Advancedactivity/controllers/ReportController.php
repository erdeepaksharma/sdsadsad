<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: ReportController.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_ReportController extends Core_Controller_Action_Standard {

//   public function init()
//   {
//     $this->_helper->requireUser();
//     $this->_helper->requireSubject();
//   }

  public function createAction() {

    $subject = null;

    //GET THE VIEWER
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject_guid = $this->_getParam('subject', null);
    if ($subject_guid) {
      $subject = Engine_Api::_()->getItemByGuid($subject_guid);
    }

    $this->view->form = $form = new Advancedactivity_Form_Report();
    $form->populate($this->_getAllParams());

    if (!$this->getRequest()->isPost()) {
      return;
    }
    if (!$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    // PROCESS
    $table = Engine_Api::_()->getItemTable('advancedactivity_report');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      $report = $table->createRow();
      $report->setFromArray(array_merge($form->getValues(), array(
                  // 'subject_type' => $subject->getType(),
                  'action_id' => $subject->getIdentity(),
                  'user_id' => $viewer->getIdentity(),
              )));
      $report->save();
      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    // CLOSE SMOTTHBOX
//     $currentContext = $this->_helper->contextSwitch->getCurrentContext();
//     if( null === $currentContext ) {
//       return $this->_helper->redirector->gotoRoute(array(), 'default', true);
//     } else if( 'smoothbox' === $currentContext ) {
    return $this->_forward('success', 'utility', 'core', array(
                'messages' => $this->view->translate('Your report has been submitted.'),
                'smoothboxClose' => true,
                'parentRefresh' => false,
            ));
    //}
  }

}