<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Priceinfo.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Priceinfo extends Engine_Db_Table {

  protected $_name = 'sitereview_priceinfo';

  public function getPriceDetails($listing_id, $params=array()) {

    $tableName = $this->info('name');
    $whereToBuyTableName = Engine_Api::_()->getItemTable('sitereview_wheretobuy')->info('name');

    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($tableName)->where('listing_id = ?', $listing_id)
            ->join($whereToBuyTableName, "$whereToBuyTableName.wheretobuy_id = $tableName.wheretobuy_id   ", array($whereToBuyTableName . '.photo_id', $whereToBuyTableName . '.title as wheretobuy_title'))
            ->where('enabled = ?', 1);

    if (isset($params['limit']) && $params['limit'] > 0) {
      $select->limit($params['limit']);
      $select->order('RAND()');
    } else {
      $select->order('price');
    }

    return $this->fetchAll($select);
  }

  public function getPriceInfo($priceinfo_id) {

    $select = $this->select()->where('priceinfo_id = ?', $priceinfo_id);

    return $this->fetchRow($select);
  }

  public function getMaxPrice($listing_id) {

    $tableName = $this->info('name');
    $whereToBuyTableName = Engine_Api::_()->getItemTable('sitereview_wheretobuy')->info('name');
    $maxPrice = $this->select()
            ->setIntegrityCheck(false)
            ->from($this->info('name'), array('max(price)'))
            ->join($whereToBuyTableName, "$whereToBuyTableName.wheretobuy_id = $tableName.wheretobuy_id ", array())
            ->where('listing_id = ?', $listing_id)
            ->group('listing_id')
            ->query()
            ->fetchColumn();

    return $maxPrice;
  }

  public function getMinPrice($listing_id) {

    $tableName = $this->info('name');
    $whereToBuyTableName = Engine_Api::_()->getItemTable('sitereview_wheretobuy')->info('name');
    $minPrice = $this->select()
            ->setIntegrityCheck(false)
            ->from($this->info('name'), array('min(price)'))
            ->join($whereToBuyTableName, "$whereToBuyTableName.wheretobuy_id = $tableName.wheretobuy_id ", array())
            ->where('listing_id = ?', $listing_id)
            ->group('listing_id')
            ->query()
            ->fetchColumn();

    return $minPrice;
  }

}
