<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Customblocks.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_DbTable_Customblocks extends Engine_Db_Table {

  protected $_name = 'advancedactivity_customblocks';
  protected $_rowClass = 'Advancedactivity_Model_Customblock';

  public function setValue($values) {
    // Save value in the data-base for new created "Custom Block".
    if (!empty($values)) {
      $row = $this->createRow();
      $row->title = $values['title'];
      $row->description = $values['description'];
      $row->networks = $values['networks'];
      $row->levels = $values['levels'];
      $row->limitation = $values['limitation'];
      $row->limitation_value = $values['limitation_value'];
      $row->save();
    }
  }

  public function setUpdate($values, $id) {
    if (!empty($values)) {
      foreach ($values as $key => $value) {
        $this->update(array($key => $value), array('customblock_id =?' => $id));
      }
    }
  }

  // Function: Fetch the "Custom Block" from the table. After satisfied the "Member-Network" & "Member-Level" of the custom block.
  public function getCustomBlock() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    $tableName = $this->info('name');

    $select = $this->select()->from($tableName, array('customblock_id', 'limitation', 'limitation_value'))->where($tableName . '.enabled =?', 1);

    // QUERYS FOR MEMBER-LEVELS.
    if (!empty($viewer_id)) {
      $level_id = Engine_Api::_()->user()->getViewer()->level_id;
    } else {
      $level_id = Engine_Api::_()->getDbtable('levels', 'authorization')->fetchRow(array('type = ?' => "public"))->level_id;
    }
    $select->where($tableName . ". levels  LIKE ?", '%"' . $level_id . '"%');

    // QUERYS FOR MEMBER-NETWORKS.
    $tableNetwork = Engine_Api::_()->getDbTable('membership', 'network');
    $tableNetworkName = $tableNetwork->info('name');
    $selectId = $tableNetwork->select()
            ->from($tableNetworkName, 'resource_id')
            ->where('user_id = ?', $viewer_id);
    $networkIdArray = $tableNetwork->fetchAll($selectId)->toArray();
    $query = '';
    if (!empty($networkIdArray)) {
      $count = 0;
      foreach ($networkIdArray as $key => $image_ids) {
        $id = strval($image_ids['resource_id']);
        $network_id = '"' . $id . '"';

        if ($count == 0) {
          $query .= "(engine4_advancedactivity_customblocks.networks  LIKE '%$network_id%')";
        } else {
          $query .= " OR (engine4_advancedactivity_customblocks.networks  LIKE '%$network_id%')";
        }
        $count++;
      }
    }
    if (!empty($query)) {
      $select->where($query);
    }
    return $select->query()->fetchAll();
  }

  // Function: Return the query of passing Ids.
  public function getObj($getIdArray) {
    if (empty($getIdArray)) {
      return;
    }
    $select = $this->select()->where('customblock_id IN (?)', (array) $getIdArray)->order('order ASC');
    return $select;
  }

}