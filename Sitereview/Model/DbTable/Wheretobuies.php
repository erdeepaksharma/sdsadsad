<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Wheretobuies.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Wheretobuies extends Engine_Db_Table {

  protected $_name = 'sitereview_wheretobuy';
  protected $_rowClass = "Sitereview_Model_WhereToBuy";

  public function getList($params = array()) {
    
    $select = $this->select();
    if (isset($params['enabled'])) {
      $select->where('enabled = ?', $params['enabled']);
    }
    
    return $this->fetchAll($select);
  }

}
