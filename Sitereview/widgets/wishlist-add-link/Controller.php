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
class Sitereview_Widget_WishlistAddlinkController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    //DONT RENDER IF SUBJECT IS NOT SET
    if (!Engine_Api::_()->core()->hasSubject('sitereview_listing')) {
      return $this->setNoRender();
    }

    //GET SUBJECT
    $this->view->sitereview = $sitereview = Engine_Api::_()->core()->getSubject('sitereview_listing');
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
		if(!Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id)->wishlist)
			return $this->setNoRender();
    
    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.favourite', 0)) {
      $viewer = Engine_Api::_()->user()->getViewer();
      $viewer_id = $viewer->getIdentity();         
      if(empty($viewer_id)) {
          return $this->setNoRender();
      }
      $wishlist_id = Engine_Api::_()->getDbTable('wishlists', 'sitereview')->recentWishlistId($viewer_id);
      $isItemAdded = Engine_Api::_()->getDbTable('wishlistmaps', 'sitereview')->isItemAdded($sitereview->listing_id, $viewer_id, $wishlist_id);
      if(!empty($isItemAdded)) {
          return $this->setNoRender();
      }
    }
  }

}