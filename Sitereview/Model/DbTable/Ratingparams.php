<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Ratingparams.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Ratingparams extends Engine_Db_Table {

  protected $_rowClass = 'Sitereview_Model_Ratingparam';

  /**
   * Review parameters
   *
   * @param Array $categoryIdsArray
   * @param Varchar $resource_type
   * @return Review parameters
   */
  public function reviewParams($categoryIdsArray = array(), $resource_type = null) {

    if (empty($categoryIdsArray)) {
      return null;
    }

    //MAKE QUERY
    $select = $this->select()
            ->from($this->info('name'), array('ratingparam_id', 'ratingparam_name'))
            ->where("category_id IN (?)", (array) $categoryIdsArray)
            ->order("category_id");

    if (!empty($resource_type)) {
      $select->where("resource_type =?", $resource_type);
    }

    //RETURN RESULTS
    return $this->fetchAll($select);
  }

}