<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Locations.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Locations extends Engine_Db_Table {

  protected $_rowClass = "Sitereview_Model_Location";

  /**
   * Get location
   *
   * @param array $params
   * @return object
   */
  public function getLocation($params = array()) {

    $listingtype_id = $params['listingtype_id'];
    if ($listingtype_id > 0) {
      Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
      $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    }
    if ($listingtype_id < 1 || $listingtypeArray->location) {

      $locationName = $this->info('name');

      $select = $this->select();
      if (isset($params['id'])) {
        $select->where('listing_id = ?', $params['id']);
        return $this->fetchRow($select);
      }

      if (isset($params['listing_ids'])) {
        $select->where('listing_id IN (?)', (array) $params['listing_ids']);
        return $this->fetchAll($select);
      }
    }
  }

}