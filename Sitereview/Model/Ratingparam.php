<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Ratingparam.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_Ratingparam extends Core_Model_Item_Abstract {

  protected $_searchTriggers = false;

  /**
   * Delete the listing and belongings
   * 
   */
  public function _delete() {

    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try {

      Engine_Api::_()->getDbTable('ratings', 'sitereview')->delete(array('ratingparam_id = ?' => $this->ratingparam_id));

      $db->commit();
    } catch (Exception $e) {
      $db->rollBack();
      throw $e;
    }

    //DELETE LISTING
    parent::_delete();
  }

}