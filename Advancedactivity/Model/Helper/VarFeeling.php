<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: VarFeeling.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/

class Advancedactivity_Model_Helper_VarFeeling extends Advancedactivity_Model_Helper_Abstract
{
  /**
   * 
   * @param string $value
   * @return string
   */
  public function direct($value, $noTranslate = true)
  {
      return $value;
  }
}