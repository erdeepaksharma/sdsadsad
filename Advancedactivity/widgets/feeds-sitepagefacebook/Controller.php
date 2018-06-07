<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitepagetwitter
 * @copyright  Copyright 2010-2011 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 2011-05-05 9:40:21Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Widget_FeedsSitepagefacebookController extends Engine_Content_Widget_Abstract {

  //ACTION FOR GETTING THE Twitter Feeds
  public function indexAction() {
 
    //DON'T RENDER IF SUNJECT IS NOT THERE
    if (!Engine_Api::_()->core()->hasSubject()) {
      return $this->setNoRender();
    }

    //GET SITEPAGE SUBJECT
    $this->view->sitepage = $sitepage = Engine_Api::_()->core()->getSubject('sitepage_page'); 
    //print_r($sitepage);die;
    if (!$sitepage || empty($sitepage->fbpage_id)) {
      return $this->setNoRender();
    }
   
    $facebook_pagefeed = Seaocore_Api_Facebook_Facebookinvite::getFBInstance();
    $session_pagefeed = $facebook_pagefeed;
    $callback = 'http://' . $_SERVER['HTTP_HOST'] .Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
    $queryString = explode("?", urldecode($callback));
     if (isset($queryString[1])) {
       $returnUrl = urldecode($callback) . '&redirect_page_fbpage=1';
     }
     else {
       $returnUrl = $callback . '?redirect_page_fbpage=1';
     }
     $this->view->loginUrl = '';
    $FBloginURL = $facebook_pagefeed->getLoginUrl(array(
          'redirect_uri' => $returnUrl,
          'scope' => join(',', array(
            'email',
            'user_birthday',
            'publish_actions',
         )),
    ));
    
    //IF THE USER FACEBOOK SESSION EXIST ON THE SITE THEN SHOW THE FACEBOOK PAGE FEEDS ELSE SHOW THE LOGIN PAGE.
    if ($facebook_pagefeed && Seaocore_Api_Facebook_Facebookinvite::checkConnection(null, $facebook_pagefeed)) { 
			try { 
			  $logged_user = $facebook_pagefeed->api('/me'); 
				$uid = $facebook_pagefeed->getUser();
				$this->view->fbPageUrl = urlencode('https://www.facebook.com/' . $sitepage->fbpage_id);
				$limit = $this->_getParam('feedCount', 5);
				$this->view->showfblikebutton = $this->_getParam('showLikeButton', 1);
				$this->view->widgetTitle = $this->_getParam('title', '');
        $loggeduser_fbpagefeed = $facebook_pagefeed->api('/' . $sitepage->fbpage_id . '/feed', array('limit' => $limit));
        
        if (!empty($loggeduser_fbpagefeed)) {
          $total_feedcount = count(@$loggeduser_fbpagefeed['data']);
          if ($total_feedcount < $limit) {
            $this->view->view_morefeed = false;
          } else {
            $this->view->view_morefeed = true;
          }
          if (!empty($getUpdate)) {
            $this->view->data = @array_reverse($loggeduser_fbpagefeed['data']);
          } else {
            $this->view->data = @$loggeduser_fbpagefeed['data'];
          }
          $this->view->loggeduser_fbpagefeed = $loggeduser_fbpagefeed;
        }
	   } catch (Exception $e) {
	     
	     $this->view->loginUrl = $FBloginURL;
	   }
 
   

 }
 else {
   $this->view->loginUrl = $FBloginURL;
 }

  }

}
?>