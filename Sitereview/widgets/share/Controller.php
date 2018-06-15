<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Widget_ShareController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $social_share_default_code = '<div class="addthis_toolbox addthis_default_style ">
		<a class="addthis_button_preferred_1"></a>
		<a class="addthis_button_preferred_2"></a>
		<a class="addthis_button_preferred_3"></a>
		<a class="addthis_button_preferred_4"></a>
		<a class="addthis_button_preferred_5"></a>
		<a class="addthis_button_compact"></a>
		<a class="addthis_counter addthis_bubble_style"></a>
		</div>
		<script type="text/javascript">
		var addthis_config = {
							services_compact: "facebook, twitter, linkedin, google, digg, more",
							services_exclude: "print, email"
		}
		</script>
		<script type="text/javascript" src="https://s7.addthis.com/js/250/addthis_widget.js"></script>';

    $settings = Engine_Api::_()->getApi('settings', 'core');

    //GET CODE FROM LAYOUT SETTING
    $this->view->code = $settings->getSetting('sitereview.code.share', $social_share_default_code);

    //DONT RENDER IF NOT AUTHORIZED
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing') && !Engine_Api::_()->core()->hasSubject('sitereview_wishlist')) {
      return $this->setNoRender();
    }

    //GET VIEWER ID
    $this->view->viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    //GET SUBJECT
    $this->view->subject = $subject = Engine_Api::_()->core()->getSubject();

    //GET SETTINGS
    $this->view->optionsArray = $optionsArray = $this->_getParam('options', array("siteShare", "friend", "report", "print", "socialShare"));

    $this->view->withoutContainer = $this->_getParam('withoutContainer', false);
    if ($this->view->withoutContainer) {
      $this->getElement()->removeDecorator('Title');
      $this->getElement()->removeDecorator('Container');
    }
    
    $this->view->content_id = $this->_getParam('content_id', 0);
  }

}
