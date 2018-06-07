<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdvancedActivityShare.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_View_Helper_AdvancedActivityShare extends Zend_View_Helper_Abstract {

  public function advancedActivityShare(Activity_Model_Action $action = null, array $data = array()) {
    if (null === $action) {
      return '';
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $activity_moderate = Engine_Api::_()->getDbtable('permissions', 'authorization')
            ->getAllowed('user', $viewer->level_id, 'activity');
    $composerOptions = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.composer.options', array("emotions", "withtags"));
    $form = new Activity_Form_Comment();
    $data = array_merge($data, array(
        'actions' => array($action),
        'commentForm' => $form,
        'user_limit' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userlength'),
        'allow_delete' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userdelete'),
        'activity_moderate' => $activity_moderate,
        'allowEmotionsIcon' => in_array("emotions", $composerOptions)
            ));

    return $this->view->partial(
                    '_shareActivityText.tpl', 'advancedactivity', $data
    );
  }

}