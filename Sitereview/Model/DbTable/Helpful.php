<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Helpful.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Helpful extends Engine_Db_Table {

  /**
   * Get previous helpful answer
   * @param int $review_id : sitereview id
   * @param int $viewer_id : viewer id
   */
  public function getHelpful($review_id, $viewer_id, $helpfull) {

    //RETURN NULL IF REVIEW ID IS NULL
    if (empty($review_id) || empty($viewer_id)) {
      return 0;
    }

    $helpful = 0;

    //FETCH DATA
    if ($helpfull) {
      $helpful = $this->select()
              ->from($this->info('name'), array('helpful'))
              ->where('review_id = ?', $review_id)
              ->where('helpful = ?', $helpfull)
              ->where('owner_id = ?', $viewer_id)
              ->query()
              ->fetchColumn();
    }

    return $helpful;
  }

  public function getCountHelpful($review_id, $helpful) {

    $total_count = $this->select()
            ->from($this->info('name'), array('COUNT(review_id) AS total_count'))
            ->where('review_id = ?', $review_id)
            ->where('helpful = ?', $helpful)
            ->query()
            ->fetchColumn();
    return $total_count;
  }

  /**
   * Get helpful datas
   * @param int $review_id
   * @param int $final_value
   * @return helpful datas
   */
  public function countHelpfulPercentage($review_id, $final_value = 1) {

    //RETURN NULL IF REVIEW ID IS NULL
    if (empty($review_id)) {
      return null;
    }

    $totalHelpsData = array();

    //FETCH TOTAL YES
    $totalHelpsData['total_yes'] = $this->select()
            ->from($this->info('name'), array('COUNT(review_id) AS total_count'))
            ->where('review_id = ?', $review_id)
            ->where('helpful = ?', 1)
            ->query()
            ->fetchColumn();

    //FETCH TOTAL NO
    $totalHelpsData['total_no'] = $this->select()
            ->from($this->info('name'), array('COUNT(review_id) AS total_count'))
            ->where('review_id = ?', $review_id)
            ->where('helpful = ?', 2)
            ->query()
            ->fetchColumn();

    //GET TOTAL
    $totalHelpsData['total_marks'] = $totalHelpsData['total_yes'] + $totalHelpsData['total_no'];

    //RETURN VALUE
    if (!empty($final_value)) {

      if (empty($totalHelpsData['total_yes']) && !empty($totalHelpsData['total_no'])) {
        return 200;
      } elseif (!empty($totalHelpsData['total_yes']) && empty($totalHelpsData['total_no'])) {
        return 100;
      } elseif (empty($totalHelpsData['total_yes']) && empty($totalHelpsData['total_no'])) {
        return null;
      } else {
        $final_value = ($totalHelpsData['total_yes'] / ($totalHelpsData['total_marks'])) * 100;
        $final_value = round($final_value);
        return $final_value;
      }
    } else {
      return $totalHelpsData;
    }
  }

  /**
   * Make review helpful
   * @param int $review_id : sitefaq id
   * @param int $owner_id : user id
   * @param int $helpful : helpful value
   */
  public function setHelful($review_id, $owner_id, $helpful) {

    //FETCH DATA
    $done_helpful = $this->select()
            ->from($this->info('name'), array('review_id'))
            ->where('review_id = ?', $review_id)
            ->where('owner_id = ?', $owner_id)
            ->query()
            ->fetchColumn();

    //INSERT HELPFUL ENTRIES IN TABLE
    if (empty($done_helpful)) {
      $this->insert(array(
          'review_id' => $review_id,
          'owner_id' => $owner_id,
          'helpful' => $helpful,
      ));
    } else {
      $this->update(array(
          'helpful' => $helpful
              ), array(
          'review_id = ?' => $review_id,
          'owner_id = ?' => $owner_id,
      ));
    }

    $helpful_value = $this->countHelpfulPercentage($review_id, 1);

    if ($helpful_value == null) {
      $helpful_value = -1;
    } elseif ($helpful_value == 200) {
      $helpful_value = 0;
    }

    Engine_Api::_()->getDbtable('reviews', 'sitereview')->update(array(
        'helpful_count' => $helpful_value
            ), array(
        'review_id = ?' => $review_id,
    ));
  }

}