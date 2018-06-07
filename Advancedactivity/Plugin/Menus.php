<?php
 /**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Facebookse
 * @copyright  Copyright 2010-2011 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Menus.php 6590 2011-01-06 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Plugin_Menus
{
   
  //Executes if sitemobile plugin is there.
  public function canViewSMFeeds()
  { 

    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer || !$viewer->getIdentity() ) {
      return false;
    }
    $settingsFB = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.facebook');
    $settingsTwitter = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.twitter');
    $linkedin_apikey = Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.apikey');
    $linkedin_secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('linkedin.secretkey');
    $instagram_apikey = Engine_Api::_()->getApi('settings', 'core')->getSetting('instagram.apikey');
    $instagram_secret = Engine_Api::_()->getApi('settings', 'core')->getSetting('instagram.secretkey');
     if ((empty($settingsFB['secret']) || empty($settingsFB['appid'])) && (empty($settingsTwitter['secret']) || empty($settingsTwitter['key'])) && (empty($linkedin_apikey) || empty($linkedin_secret))&& (empty($instagram_apikey) || empty($instagram_secret)))
      return false;
     else 
       return true; 
   
  }
  
  //Executes if sitemobile plugin is there.
  public function canViewSMSocialFeeds() {

    $canView = $this->canViewSMFeeds();
    return $canView;
  }

}