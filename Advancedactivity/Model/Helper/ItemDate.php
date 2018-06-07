<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: Var.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/

class Advancedactivity_Model_Helper_ItemDate extends Advancedactivity_Model_Helper_Abstract
{
  /**
   * 
   * @param string $value
   * @return string
   */
  public function direct($type, $id)
  {
		$object = Engine_Api::_()->getItem(strtolower($type), $id);
  
    if($object)
    return $object->getItemDate();
  }
}