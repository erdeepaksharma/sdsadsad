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
class Sitereview_Widget_WishlistCreationlinkController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

      $this->view->favourite = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0);
      $viewer = Engine_Api::_()->user()->getViewer();
      $viewer_id = $viewer->getIdentity();     
      
      if($this->view->favourite && !$viewer_id) {
          return $this->setNoRender();
      }
      
      $this->view->wishlist_id = 0;
      if($this->view->favourite && $viewer_id) {
          $this->view->wishlist_id = Engine_Api::_()->getDbtable('wishlists', 'sitereview')->recentWishlistId($viewer_id);
      }
  }

}
