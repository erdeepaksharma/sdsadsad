<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: Url.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/

class Advancedactivity_Model_Helper_Url extends Advancedactivity_Model_Helper_Abstract
{
  /**
   * Generates a url for action
   * 
   * @param mixed $params
   * @param string $innerHTML
   * @return string
   */
  public function direct($params, $innerHTML)
  {
    // Passed an absolute url
    if( is_string($params) )
    {
      $uri = $params;
    }
    
    else if( is_array($params) && isset($params['uri']) )
    {
      $uri = $params['uri'];
    }

    // Passed a route array
    else if( is_array($params) )
    {
      $route = ( isset($params['route']) ? $params['route'] : 'default' );
      unset($params['route']);
      $uri = Zend_Controller_Front::getInstance()->getRouter()->assemble($params, $route, true);
    }

    // Whoops, just return the innerHTML
    else
    {
      return $innerHTML;
    }

    return '<a href="'.$uri.'">'.$innerHTML.'</a>';
  }
}