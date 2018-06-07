<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Report.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_Report extends Core_Model_Item_Abstract {

  protected $_searchTriggers = false;
  protected $_modifiedTriggers = false;

  public function getSubject() {

    if (empty($this->action_id)) {
      return null;
    }
    try {
      $subject = Engine_Api::_()->getItem('activity_action', $this->action_id);
    } catch (Exception $e) {
      return null;
    }
//     if( !($subject instanceof Core_Model_Item_Abstract) ) {
//       return null;
//     }
    return $subject;
  }

}