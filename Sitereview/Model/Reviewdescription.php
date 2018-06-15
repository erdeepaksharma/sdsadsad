<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Reviewdescription.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_ReviewDescription extends Core_Model_Item_Abstract {

  /**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
   */
  public function getHref($params = array()) {
    //GET CONTENT ID
    $content_id = Engine_Api::_()->sitereview()->existWidget('sitereview_reviews', 0);

    //GET LISTING TYPE ID
    $listingtype_id = Engine_Api::_()->getDbTable('listings', 'sitereview')->getListingTypeId($this->listing_id);

    $params = array_merge(array(
        'route' => "sitereview_entry_view_listtype_$listingtype_id",
        'reset' => true,
        'listing_id' => $this->listing_id,
        'slug' => $this->getSlug(),
        'tab' => $content_id,
            ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
                    ->assemble($params, $route, $reset);
  }

}