<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: ShareAdvancedActivityLoop.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/

class Advancedactivity_View_Helper_ShareAdvancedActivityLoop extends Activity_View_Helper_Activity {

  public function shareAdvancedActivityLoop($actions = null, array $data = array()) {
    if (null == $actions || (!is_array($actions) && !($actions instanceof Zend_Db_Table_Rowset_Abstract))) {
      return '';
    }

    $form = new Activity_Form_Comment();
    $viewer = Engine_Api::_()->user()->getViewer();
    $activity_moderate = "";
    if ($viewer->getIdentity()) {
      $activity_moderate = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');
    }
    $data = array_merge($data, array(
        'actions' => $actions,
        'commentForm' => $form,
        'user_limit' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userlength'),
        'allow_delete' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userdelete'),
        'activity_moderate' => $activity_moderate,
            ));

    return $this->view->partial(
                    '_shareActivityText.tpl',
                    /*  Customization Start */ 'advancedactivity',
                    /*  Customization End */ $data
    );
  }

}