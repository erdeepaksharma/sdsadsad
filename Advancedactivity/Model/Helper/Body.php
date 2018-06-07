<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Body.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_Helper_Body extends Advancedactivity_Model_Helper_Abstract
{

  private $_body;

  /**
   * Body helper
   * 
   * @param string $body
   * @return string
   */
  public function direct($body)
  {
    if( empty($body) ) {
      return;
    }
    $this->_body = $body;
    $this->convertBody();
    $view = Zend_Registry::get('Zend_View');
    $body = '<br />';
    return $body . '<span class="feed_item_bodytext">' . $this->_body . '</span>';
  }

  private function convertBody()
  {
    if( !Zend_Registry::isRegistered('Zend_View') || empty($this->_body) ) {
      return;
    }
    $body = $this->_body;
    $view = Zend_Registry::get('Zend_View');
    $session = new Zend_Session_Namespace('siteViewModeSM');
    if( isset($session->siteViewModeSM) && in_array($session->siteViewModeSM, array("mobile", "tablet")) ) {
      $body = $view->viewMore($body, null, null, null, false);
      $body = nl2br($body);
    } else {
      $body = $view->viewMoreAdvancedActivity($body);
    }
    $this->_body = $body;
  }
}
