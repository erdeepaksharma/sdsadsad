<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Claims.php 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Claims extends Engine_Db_Table {

  protected $_rowClass = 'Sitereview_Model_Claim';

  /**
   * Return status
   *
   * @param array params
   * @return status
   */
  public function getClaimStatus($params) {

    $select = $this->select()->from($this->info('name'), 'status');
    if (isset($params['listing_id']) && !empty($params['listing_id'])) {
      $select = $select->where('listing_id = ?', $params['listing_id']);
    }
    if (isset($params['viewer_id']) && !empty($params['viewer_id'])) {
      $select = $select->where('user_id = ?', $params['viewer_id']);
    }
    return $this->fetchRow($select);
  }

  /**
   * Return viewer claim id
   *
   * @param int $viewer_id
   */
  public function getViewerClaims($viewer_id) {

    $claim_id = 0;
    $claim_id = $this
            ->select()
            ->from($this->info('name'), array('claim_id'))
            ->where("user_id = ?", $viewer_id)
            ->order('creation_date')
            ->query()
            ->fetchColumn();
    return $claim_id;
  }

  /**
   * Gets claim listings 
   *
   * @param string $viewer_id
   * @param  Zend_Db_Table_Select
   */
  public function getMyClaimListings($viewer_id, $listingtype_id) {

    //GET LISTING TABLE AND ITS NAME
    $tableListing = Engine_Api::_()->getDbtable('listings', 'sitereview');
    $tableListingName = $tableListing->info('name');
    $tableClaimName = $this->info('name');
    //SELECT
    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($this->info('name'))
            ->joinInner($tableListingName, "$tableClaimName.listing_id = $tableListingName.listing_id", array('listingtype_id', 'listing_id', 'photo_id', 'title', 'owner_id'))
            ->where($tableListingName . '.listingtype_id = ?', $listingtype_id)
            ->where($tableClaimName . '.user_id = ?', $viewer_id)
            ->order('claim_id DESC');

    return Zend_Paginator::factory($select);
  }

}