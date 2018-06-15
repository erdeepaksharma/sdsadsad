<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AddToWishlist.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_View_Helper_AddToWishlist extends Zend_View_Helper_Abstract {

  /**
   * Assembles action string
   * 
   * @return string
   */
  public function addToWishlist($item, $params = null) {
    
    $listingtype_id = $item->listingtype_id;
    $listingTypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    
    if(Engine_Api::_()->sitereview()->hasPackageEnable()) {
     if (Engine_Api::_()->sitereviewpaidlisting()->allowPackageContent($item->package_id, "wishlist"))
      $canAddWishlist = 1;
      else
      $canAddWishlist = 0;
    }
    else {
     $canAddWishlist = 1;
    }
    if(empty($listingTypeArray->wishlist) || empty($canAddWishlist)) {
      return;
    }
    
    $data['item'] = $item;
    $data['classIcon'] = isset($params['classIcon']) ? $params['classIcon'] : '';
    $data['classLink'] = isset($params['classLink']) ? $params['classLink']: '';
    $data['text'] = isset($params['text']) ? $params['text'] : "Add to Wishlist";
    
    return $this->view->partial('_addToWishlist.tpl', 'sitereview', $data);
  }

}
