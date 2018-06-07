<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Contents.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_DbTable_Contents extends Engine_Db_Table {

  protected $_name = 'advancedactivity_contents';
  protected $_rowClass = 'Advancedactivity_Model_Content';

  public function getContentList($params = array()) {
    $moduleTableName = Engine_Api::_()->getDbtable('modules', 'core')->info('name');
    $tableName = $this->info('name');

    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($tableName, array("$tableName.*"))
            ->join($moduleTableName, "$tableName.module_name = $moduleTableName.name", array("$moduleTableName.title as module_title"))
            ->where($moduleTableName . '.enabled  = ?', 1)
            ->order($tableName . '.order');
    if (isset($params['content_tab'])) {
      $select->where($tableName . '.content_tab  = ?', $params['content_tab']);
    }
    if (isset($params['filter_type'])) {
      $select->where($tableName . '.filter_type  = ?', $params['filter_type']);
      return $this->fetchRow($select);
    }
    return $this->fetchAll($select);
  }

  public function getDefaultAddedModule() {


    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($this->info('name'), "module_name")
            ->where('`default` = ?', 1);
    return $select->query()
                    ->fetchAll(Zend_Db::FETCH_COLUMN);
  }

}