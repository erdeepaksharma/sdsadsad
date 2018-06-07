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

class Activity_Model_Helper_VarCheckin extends Activity_Model_Helper_Abstract
{
  /**
   * 
   * @param string $value
   * @return string
   */
  public function direct($value)
  {
		$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
		$haystack = $value;
		if (strpos($haystack,' on ') !== false) {
			return $view->translate(" on ") . $view->locale()->toDate(str_replace(" on ", "", $value), array('format' => 'MMMM dd, YYYY'));
		} else if (strpos($haystack,' in ') !== false) {
      if(strlen($value) > 8 ) {
				return $view->translate(" in ") . $view->locale()->toDate(str_replace(" in ", "", $value), array('format' => 'MMMM, YYYY'));
      } else {
				return $view->translate(" in ") . $view->locale()->toDate(str_replace(" in ", "", $value), array('format' => 'YYYY'));
			}
		}
	}
}