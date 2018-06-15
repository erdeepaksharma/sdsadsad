<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Vieweds.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Vieweds extends Engine_Db_Table {

  protected $_rowClass = "Sitereview_Model_Viewed";

  public function setVieweds($listing_id, $viewer_id) {
    
    if(empty($viewer_id)) {
      return;
    }
    
    //GET IF ENTRY IS EXIST FOR SAME LISTING TYPE AND SAME VIEWER ID
    $select = $this->select()
            ->where('listing_id = ?', $listing_id)
            ->where('viewer_id = ?', $viewer_id);    
    $vieweds = $this->fetchRow($select);

    if (empty($vieweds)) {
      $row = $this->createRow();
      $row->listing_id = $listing_id;
      $row->viewer_id = $viewer_id;
      $row->date = new Zend_Db_Expr('NOW()');
      $row->save();      
    }
    else {
      $vieweds->date = new Zend_Db_Expr('NOW()');
      $vieweds->save();     
    }
    
		//DELETE ENTRIES IF MORE THAN 10
		$count = $this->select()
						->from($this->info('name'), array('COUNT(viewed_id) as total_entries'))
						->where('viewer_id = ?', $viewer_id)
						->query()
						->fetchColumn();
		if($count > 10) {
			
			//DELETE ENTRIES IF MORE THAN 10
			$select = $this->select()
							->from($this->info('name'), array('viewed_id'))
							->where('viewer_id = ?', $viewer_id)
							->order('date ASC')
							->limit($count-10);
			$oldDatas = $this->fetchAll($select);
			foreach($oldDatas as $oldData) {
				$this->delete(array('viewed_id = ?' => $oldData->viewed_id));
			}
		}       
  }

}
