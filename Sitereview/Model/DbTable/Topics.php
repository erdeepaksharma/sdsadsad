<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Topics.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Topics extends Engine_Db_Table {

  protected $_rowClass = 'Sitereview_Model_Topic';

  public function getListingTopices($lisiibg_id) {

    //MAKE QUERY
    $select = $this->select()
            ->where('listing_id = ?', $lisiibg_id)
            ->order('sticky DESC')
            ->order('modified_date DESC');

    //RETURN RESULTS
    return Zend_Paginator::factory($select);
  }

}