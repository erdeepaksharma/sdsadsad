<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Videoratings.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Videoratings extends Engine_Db_Table {

  protected $_rowClass = "Sitereview_Model_Videorating";

  /**
   * Return video rating
   *
   * @param int $video_id
   * @return video rating
   */
  public function rateVideo($video_id) {

    $select = $this->select()
            ->from($this->info('name'), array('*', 'AVG(rating) AS avg_rating'))
            ->where('videorating_id = ?', $video_id)
            ->group('videorating_id');
    $avgRateData = $this->fetchAll($select)->toArray();
    
    if(isset($avgRateData[0]['avg_rating']))
        return $avgRateData[0]['avg_rating'];
    
    return 0;
  }

  /**
   * Check for already rated video by same user
   *
   * @param int $video_id
   * @param int $user_id
   */
  public function checkRated($video_id, $user_id) {

    $select = $this->select()
            ->where('videorating_id = ?', $video_id)
            ->where('user_id = ?', $user_id)
            ->limit(1);
    $row = $this->fetchAll($select);

    if (count($row) > 0)
      return true;
    return false;
  }

  /**
   * Do video rating
   *
   * @param int $video_id
   * @param int $user_id
   * @param int $rating
   */
  public function setRating($video_id, $user_id, $rating) {

    $ratingTableName = $this->info('name');
    $select = $this->select()
            ->from($ratingTableName)
            ->where($ratingTableName . '.videorating_id = ?', $video_id)
            ->where($ratingTableName . '.user_id = ?', $user_id);
    $row = $this->fetchRow($select);
    if (empty($row)) {
      // create rating
      $this->insert(array(
          'videorating_id' => $video_id,
          'user_id' => $user_id,
          'rating' => $rating
      ));
    }
  }

  /**
   * Return total video ratings count
   *
   * @param int $video_id
   */
  public function ratingCount($video_id) {

    $ratingTableName = $this->info('name');
    $select = $this->select()
            ->from($ratingTableName)
            ->where($ratingTableName . '.videorating_id = ?', $video_id);
    $row = $this->fetchAll($select);
    $total = count($row);
    return $total;
  }

}