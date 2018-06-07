<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Advancedactivitys.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Controller_Action_Helper_Advancedactivitys extends Zend_Controller_Action_Helper_Abstract
{
  function postDispatch()
  {

    $session = new Zend_Session_Namespace();
    //IF USER IS BEING LOGGED OUT THEN WE NEED TO UNSET ALL SESSION VARIABLES WHICH ARE BEING SET FOR FACEBOOK PLUGIN
    $front = Zend_Controller_Front::getInstance();
    $request = $front->getRequest();
    $module = $request->getModuleName();
    $controller = $request->getControllerName();
    $action = $request->getActionName();
    $key = join('_', array($module, $controller, $action));
    if( $key === 'core_admin-index_change-environment-mode' ) {
      if( $request->getParam('environment_mode') === 'production' ) {
        Engine_Api::_()->getApi('cache', 'advancedactivity')->flushAll();
      }
    }

    if( $module == 'user' && $action == 'logout' ) {
      if( isset($_SESSION['facebook_lock']) )
        unset($_SESSION['facebook_lock']);
      if( isset($_SESSION['facebook_uid']) )
        unset($_SESSION['facebook_uid']);
      if( isset($session->aaf_redirect_uri) )
        unset($session->aaf_redirect_uri);
      if( isset($session->aaf_fbaccess_token) )
        unset($session->aaf_fbaccess_token);
      if( isset($session->fb_canread) )
        unset($session->fb_canread);
      if( isset($session->fb_can_managepages) )
        unset($session->fb_can_managepages);
      if( isset($session->fb_checkconnection) )
        unset($session->fb_checkconnection);
    }
  }

}

?>