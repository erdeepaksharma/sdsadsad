<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Otherinfo.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Otherinfo extends Engine_Db_Table {

  protected $_rowClass = "Sitereview_Model_Otherinfo";

  public function getOtherinfo($listing_id) {

    $rName = $this->info('name');
    $select = $this->select()
            ->where($rName . '.listing_id = ?', $listing_id);

    $row = $this->fetchRow($select);

    if (empty($row))
      return;

    return $row;
  }

  public function getColumnValue($listing_id, $column_name) {

    $select = $this->select()
            ->from($this->info('name'), array("$column_name"));

    $select->where('listing_id = ?', $listing_id);


    return $select->limit(1)->query()->fetchColumn();
  }

}