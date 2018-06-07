<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Customtypes.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_DbTable_Customtypes extends Engine_Db_Table {

  protected $_name = 'advancedactivity_customtypes';
  protected $_rowClass = 'Advancedactivity_Model_Customtype';

  public function getCustomTypeList($params = array()) {
    $moduleTableName = Engine_Api::_()->getDbtable('modules', 'core')->info('name');
    $tableName = $this->info('name');

    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($tableName, array("$tableName.*"))
            ->join($moduleTableName, "$tableName.module_name = $moduleTableName.name", array("$moduleTableName.title as module_title"))
            ->where($moduleTableName . '.enabled  = ?', 1)
            ->order($tableName . '.order');
    if (isset($params['enabled'])) {
      $select->where($tableName . '.enabled= ?', $params['enabled']);
    }
    return $this->fetchAll($select);
  }

  public function getEnableCustomType($params = array()) {
    $moduleTableName = Engine_Api::_()->getDbtable('modules', 'core')->info('name');
    $tableName = $this->info('name');

    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($tableName, $tableName . ".resource_type")
            ->join($moduleTableName, "$tableName.module_name = $moduleTableName.name", array("$moduleTableName.title as module_title"))
            ->where($moduleTableName . '.enabled  = ?', 1)
            ->order($tableName . '.order');
    if (isset($params['enabled'])) {
      $select->where($tableName . '.enabled= ?', $params['enabled']);
    }
    $enableResource = $select->query()
            ->fetchAll(Zend_Db::FETCH_COLUMN);

    if (Engine_Api::_()->hasItemType('sitereview_listing')) {
      foreach ($enableResource as $value) {
        if (strpos($value, 'sitereview_listing_listtype_') !== false) {
          $enableResource[] = 'sitereview_listing';
          break;
        }
      }
    }
    return $enableResource;
  }

}