<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: CompareSettings.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_CompareSettings extends Engine_Db_Table {

  public function getCompareList($params = array()) {
    $categoriesTable = Engine_Api::_()->getDbtable('categories', 'sitereview');
    $listingtypesTable = Engine_Api::_()->getDbtable('listingtypes', 'sitereview');

    $tableName = $this->info('name');
    $categoriesTableName = $categoriesTable->info('name');
    $listingtypesTableName = $listingtypesTable->info('name');
    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($tableName)
            ->join($categoriesTableName, "$categoriesTableName.category_id = $tableName.category_id   ", array($categoriesTableName . '.category_name'))
            ->join($listingtypesTableName, "$listingtypesTableName.listingtype_id = $categoriesTableName.listingtype_id   ", array($listingtypesTableName . '.title_plural AS list_title', $listingtypesTableName . '.listingtype_id'));


    if (isset($params['listingtype_id']) && !empty($params['listingtype_id'])) {
      $select->where($listingtypesTableName . '.listingtype_id =? ', $params['listingtype_id']);
    }
    if (isset($params['category_id']) && !empty($params['category_id'])) {
      $select->where($categoriesTableName . '.category_id =? ', $params['category_id']);
    }

    if (isset($params['fetchRow']) && !empty($params['fetchRow'])) {
      return $this->fetchRow($select);
    }

    return $result = $this->fetchAll($select);
  }

}