<?php

 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: Actors.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/
class Advancedactivity_Model_Helper_ItemSeaoChild extends Advancedactivity_Model_Helper_Item {

  public function direct($item, $type = null, $child_id = null, $text = null) {
		
		$item = $this->_getItem($item, false);

			// Check to make sure we have an item
			if (!($item instanceof Core_Model_Item_Abstract)) {
				return false;
			}
			
    $isGetTitle = false;
    if (Engine_Api::_()->hasItemType($type)) {
      $child_type = $type;
      $isGetTitle = true;
    } else {	
      $child_type = $item->getType() . '_' . $type;
      if (!Engine_Api::_()->hasItemType($child_type)) {
        return false;
      }
    }

    try {
      $item = Engine_Api::_()->getItem($child_type, $child_id);
     
    } catch (Exception $e) {
      // With no alarms and no surprises
      // No alarms and no surprises
      // No alarms and no surprises
      // Silent, silent
    }

    if (!($item instanceof Core_Model_Item_Abstract)) {
      return false;
    }

		if (!$text){
			if($isGetTitle)
			$text = $item->getTitle();
			else if(!$text)
			$text= $type;
		}
		
    return parent::direct($item, $text);
  }

}
