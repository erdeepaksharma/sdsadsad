<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: ReviewDescriptions.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_ReviewDescriptions extends Engine_Db_Table {

  protected $_rowClass = 'Sitereview_Model_Reviewdescription';

  /**
   * Review Descriptions
   *
   * @param Int $review_id
   * @return Review Descriptions
   */
  public function getReviewDescriptions($review_id) {

    $tableReviewDescriptionName = $this->info('name');
    $select = $this->select()->from($tableReviewDescriptionName, array('*'))
            ->where("$tableReviewDescriptionName.review_id =?", $review_id)
            ->order("$tableReviewDescriptionName.review_id DESC")
            ->order("$tableReviewDescriptionName.modified_date DESC");

    return Zend_Paginator::factory($select);
  }

  /**
   * Count Review Descriptions Ids
   *
   * @param Int $review_id
   * @return Count Review Descriptions Ids
   */
  public function getCount($review_id) {

    return $this->select()
                    ->from($this, new Zend_Db_Expr('COUNT(reviewdescription_id)'))
                    ->where('review_id = ?', $review_id)
                    ->limit(1)
                    ->query()
                    ->fetchColumn();
  }

}