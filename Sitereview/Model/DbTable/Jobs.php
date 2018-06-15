<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2013-2014 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Jobs.php 6590 2014-05-19 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Jobs extends Engine_Db_Table {

  protected $_rowClass = 'Sitereview_Model_Job';

  /**
   * Return status
   *
   * @param array params
   * @return status
   */
  public function getApplyStatus($params) {

    $select =
            $this->select()->from($this->info('name'), 'user_id');
    if (isset($params['listing_id']) && !empty($params['listing_id'])) {
      $select = $select->where('listing_id = ?', $params['listing_id']);
    }
    if (isset($params['viewer_id']) && !empty($params['viewer_id'])) {
      $select = $select->where('user_id = ?', $params['viewer_id']);
    }
    return $select->query()
                    ->fetchColumn();
  }

  public function getApplications($listing_id = null) {

    $select = $this->select()
            ->where("listing_id = ?", $listing_id);
    return $paginator = Zend_Paginator::factory($select);
  }

}