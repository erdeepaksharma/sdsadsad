<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Options.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Options extends Engine_Db_Table {

  protected $_name = 'sitereview_listing_fields_options';
  protected $_rowClass = 'Sitereview_Model_Option';

  public function getAllProfileTypes() {
    $select = $this->select()
            ->where('field_id = ?', 1);
    $result = $this->fetchAll($select);
    return $result;
  }

  public function getFieldLabel($field_id) {
    
    $select = $this->select()
            ->where('field_id = ?', $field_id);
    $result = $this->fetchRow($select);
    
    return!empty($result) ? $result->label : '';
  }

  public function getProfileTypeLabel($option_id) {

    if (empty($option_id)) {
      return;
    }

    //GET FIELD OPTION TABLE NAME
    $tableFieldOptionsName = $this->info('name');

    //FETCH PROFILE TYPE LABEL
    $profileTypeLabel = $this->select()
            ->from($tableFieldOptionsName, array('label'))
            ->where('option_id = ?', $option_id)
            ->query()
            ->fetchColumn();

    return $profileTypeLabel;
  }

}