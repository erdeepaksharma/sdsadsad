<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Ratings.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Ratings extends Engine_Db_Table {

  protected $_rowClass = "Sitereview_Model_Rating";

  /**
   * Update rating in the listing table
   *
   * @param Int $resource_id
   * @param Varchar $resource_type
   * @return Updated rating
   */
  public function listRatingUpdate($resource_id, $resource_type,$rating_only = 0) {

    //RETURN IF RESOURCE ID IS EMPTY
    if (empty($resource_id) || empty($resource_type)) {
      return;
    }
    $tableRatingName = $this->info('name');
    $tableReviewName = Engine_Api::_()->getDbtable('reviews', 'sitereview')->info('name');
    
    if(!empty($rating_only)) {
			$rating_avg = $this
							->select()
							->from($this->info('name'), array('AVG(rating) AS avg_rating'))
							->where($tableRatingName . ".ratingparam_id = ?", 0)
							->where($tableRatingName . ".resource_id = ?", $resource_id)
							->where($tableRatingName . ".resource_type = ?", $resource_type)
							->where($tableRatingName . ".rating != ?", 0)
							->group($tableRatingName . '.resource_id')
							->query()
							->fetchColumn();
            
    }
    else {
			$rating_avg = $this
				->select()
				->setIntegrityCheck(false)
				->from($this->info('name'), array('AVG(rating) AS avg_rating'))
				->join($tableReviewName, "$tableReviewName.review_id = $tableRatingName.review_id", null)
				->where($tableRatingName . ".ratingparam_id = ?", 0)
				->where($tableRatingName . ".resource_id = ?", $resource_id)
				->where($tableRatingName . ".resource_type = ?", $resource_type)
				->where($tableRatingName . ".rating != ?", 0)
				->where($tableReviewName . ".status = ?", 1)
				->group($tableRatingName . '.resource_id')
				->query()
				->fetchColumn();
    }

    $rating_editor = $this
            ->select()
            ->setIntegrityCheck(false)
            ->from($this->info('name'), array('AVG(rating) AS avg_rating'))
            ->join($tableReviewName, "$tableReviewName.review_id = $tableRatingName.review_id", null)
            ->where($tableRatingName . ".ratingparam_id = ?", 0)
            ->where($tableRatingName . ".resource_id = ?", $resource_id)
            ->where($tableRatingName . ".resource_type = ?", $resource_type)
            ->where($tableRatingName . ".type = ?", 'editor')
            ->where($tableRatingName . ".rating != ?", 0)
            ->where($tableReviewName . ".status = ?", 1)
            ->group($tableRatingName . '.resource_id')
            ->query()
            ->fetchColumn();

    if(!empty($rating_only)) {
			$rating_users = $this
							->select()
							->from($this->info('name'), array('AVG(rating) AS avg_rating'))
							->where($tableRatingName . ".ratingparam_id = ?", 0)
							->where($tableRatingName . ".resource_id = ?", $resource_id)
							->where($tableRatingName . ".resource_type = ?", $resource_type)
							->where($tableRatingName . ".type in (?) ", array('user', 'visitor'))
							->where($tableRatingName . ".rating != ?", 0)
							->group($tableRatingName . '.resource_id')
							->query()
							->fetchColumn();
    }
    else {
			$rating_users = $this
				->select()
				->setIntegrityCheck(false)
				->from($this->info('name'), array('AVG(rating) AS avg_rating'))
				->join($tableReviewName, "$tableReviewName.review_id = $tableRatingName.review_id", null)
				->where($tableRatingName . ".ratingparam_id = ?", 0)
				->where($tableRatingName . ".resource_id = ?", $resource_id)
				->where($tableRatingName . ".resource_type = ?", $resource_type)
				->where($tableRatingName . ".type in (?) ", array('user', 'visitor'))
				->where($tableRatingName . ".rating != ?", 0)
				->where($tableReviewName . ".status = ?", 1)
				->group($tableRatingName . '.resource_id')
				->query()
				->fetchColumn();
    }

    $list = Engine_Api::_()->getItem($resource_type, $resource_id);
    $list->rating_avg = round($rating_avg, 4);
    $list->rating_editor = round($rating_editor, 4);
    $list->rating_users = round($rating_users, 4);
    $list->save();
    return round($rating_users, 4);
  }

  /**
   * Get rating by category
   *
   * @param Int $resource_id
   * @param Varchar $type
   * @param Varchar $resource_type
   * @return Get rating by category
   */
  public function ratingbyCategory($resource_id, $type = null, $resource_type) {

    //RETURN IF PAGE ID IS EMPTY
    if (empty($resource_id) || empty($resource_type)) {
      return;
    }

    $tableRatingName = $this->info('name');
    $tableRatingParamsName = Engine_Api::_()->getDbtable('ratingparams', 'sitereview')->info('name');
    $tableReviewName = Engine_Api::_()->getDbtable('reviews', 'sitereview')->info('name');
    $select = $this
            ->select()
            ->setIntegrityCheck(false)
            ->from($tableRatingName, array('AVG(rating) AS avg_rating', 'ratingparam_id'))
            ->joinLeft($tableRatingParamsName, "$tableRatingName.ratingparam_id = $tableRatingParamsName.ratingparam_id", array('ratingparam_name'))
            ->join($tableReviewName, "$tableReviewName.review_id = $tableRatingName.review_id", null)
            ->where($tableRatingName . ".rating != ?", 0)
            ->where($tableRatingName . ".resource_id = ?", $resource_id)
            ->where($tableRatingName . ".resource_type = ?", $resource_type)
            ->where($tableReviewName . ".status = ?", 1)
            ->group($tableRatingName . '.ratingparam_id');

    if ($type == 'editor') {
      $select->where("$tableReviewName.type =?", $type);
    } elseif ($type == 'user' || $type == 'visitor') {
      $select->where("$tableReviewName.type in (?)", array($type, 'visitor'));
    }
    return $this->fetchAll($select)->toArray();
  }

  /**
   * Get ratings
   *
   * @param Int $review_id
   * @param Int $viewer_id
   * @param Int $resource_id
   * @param Int $ratingparam_id
   * @return Get ratings
   */
  public function ratingsData($review_id, $viewer_id=null, $resource_id=null, $ratingparam_id=-1) {

    $select = $this->select()
            ->from($this->info('name'), array('ratingparam_id', 'rating', 'user_id'))
            ->where("review_id = ?", $review_id);

    if (!empty($resource_id)) {
      $select->where("resource_id =?", $resource_id);
    }

    if (!empty($viewer_id)) {
      $select->where("user_id =?", $viewer_id);
    }
    if ($ratingparam_id != -1) {
      $select->where("ratingparam_id =?", $ratingparam_id);
    }
    return $this->fetchAll($select)->toArray();
  }

  /**
   * Get profile rating
   *
   * @param Int $review_id
   * @param Int $viewer_id
   * @return Get profile rating
   */
  public function profileRatingbyCategory($review_id, $viewer_id=null) {

    //RETURN IF REVIEW ID IS EMPTY
    if (empty($review_id)) {
      return;
    }

    //GET RATING TABLE NAME
    $tableRatingName = $this->info('name');

    //GET REVIEW PARAMETER TABLE INFO
    $tableRatingParamsName = Engine_Api::_()->getDbtable('ratingparams', 'sitereview')->info('name');

    //MAKE QUERY
    $select = $this
            ->select()
            ->setIntegrityCheck(false)
            ->from($tableRatingName, array('rating'))
            ->joinLeft($tableRatingParamsName, "$tableRatingName.ratingparam_id = $tableRatingParamsName.ratingparam_id", array('ratingparam_name'))
            ->where("review_id = ?", $review_id);

    if (!empty($viewer_id)) {
      $select->where("user_id =?", $viewer_id);
    }
    //RETURN RESULTS
    return $this->fetchAll($select)->toArray();
  }

  /**
   * Create Rating Data
   *
   * @param Array $postData
   * @param Varchar $type
   * @return Created Rating Data
   */
  public function createRatingData($postData, $type) {

    $str = "";
    //DO ENTRY IN REVIEW RATING TABLE
    foreach ($postData as $key => $ratingdata) {
      if (empty($ratingdata))
        continue;
      if (strstr($key, 'update_review_rate_')) {
        $string_exist = strstr($key, 'update_review_rate_');
        $str = 'update_review_rate_';
      } else {
        $string_exist = strstr($key, 'review_rate_');
        $str = 'review_rate_';
      }
      if ($string_exist) {
        if ($str)
          $ratingparam_id = explode($str, $key);
        $reviewRating = $this->createRow();
        $reviewRating->review_id = $postData['review_id'];
        $reviewRating->user_id = $postData['user_id'];
        $reviewRating->category_id = $postData['category_id'];
        $reviewRating->resource_id = $postData['resource_id'];
        $reviewRating->resource_type = $postData['resource_type'];
        $reviewRating->ratingparam_id = $ratingparam_id[1];
        $reviewRating->rating = $ratingdata;
        $reviewRating->type = $type;
        $reviewRating->save();
      }
    }
  }

  /**
   * Number of user rating
   *
   * @param Int $resource_id
   * @param Varchar $type
   * @param Int $ratingparam_id
   * @param Int $value
   * @param Int $user_id
   * @param Varchar $resource_type
   * @param Array $params
   * @return Number of user rating
   */
  public function getNumbersOfUserRating($resource_id, $type = null, $ratingparam_id=0, $value=0, $user_id = 0, $resource_type=null, $params=array()) {

    $allow_review = 1;
    if(!empty($resource_id)) {
			$listingTable = Engine_Api::_()->getDbtable('listings', 'sitereview');
			$listingtype_id = $listingTable->select()
													->from($listingTable->info('name'),'listingtype_id')
													->where('listing_id = ?',$resource_id)
													->query()
													->fetchColumn();
			$ratingTable = Engine_Api::_()->getDbtable('ratings', 'sitereview');
			$rating_value = $ratingTable->select()
													->from($ratingTable->info('name'),'rating')
													->where('resource_id = ?',$resource_id)
													->where('resource_type = ?',$resource_type)
													->where('user_id = ?',$user_id)
													->query()
													->fetchColumn();
		  $listingTypeTable = Engine_Api::_()->getDbtable('listingtypes', 'sitereview');
			$allow_review = $listingTypeTable->select()
													->from($listingTypeTable->info('name'),'allow_review')
													->where('listingtype_id = ?',$listingtype_id)
													->query()
													->fetchColumn();
    }
    else {
      $rating_value = 1;
    }
    $tableReviewName = Engine_Api::_()->getDbtable('reviews', 'sitereview')->info('name');
    $tableRatingName = $this->info('name');
    
    if(!empty($allow_review) && !empty($rating_value)) {
			$select = $this
							->select()
							->setIntegrityCheck(false)
							->from($tableRatingName, new Zend_Db_Expr('COUNT(rating_id)'))
							->join($tableReviewName, "$tableReviewName.review_id = $tableRatingName.review_id", null)
							->where($tableReviewName . ".status = ?", 1)
							->where("$tableRatingName.ratingparam_id = ?", $ratingparam_id);
    }
    else {
			$select = $this
				->select()
				->from($tableRatingName, new Zend_Db_Expr('COUNT(rating_id)'))
				->where("$tableRatingName.ratingparam_id = ?", $ratingparam_id);
    }
            
    if ($resource_id) {
      $select->where("$tableRatingName.resource_id = ?", $resource_id);
    }

    if ($resource_type) {
      $select->where("$tableRatingName.resource_type = ?", $resource_type);
    }

    if ($value) {
      $select->where("$tableRatingName.rating = ?", $value);
    }

    $select->where("$tableRatingName.rating <> ?", 0);

    if ($type == 'editor') {
      $select->where("$tableReviewName.type =?", $type);
    } elseif ($type == 'user' || $type == 'visitor') {
      if(!empty($allow_review) && !empty($rating_value)) {
				$select->where("$tableReviewName.type in (?)", array($type, 'visitor'));
      }
      else {
        $select->where("$tableRatingName.type in (?)", array($type, 'visitor'));
      }
    }

    if ($user_id) {
      $select->where("user_id = ?", $user_id);
    }
    if (isset($params['listingtype_id']) && !empty($params['listingtype_id'])) {
      //GET LISTING TABLE NAME
      $listingTable = Engine_Api::_()->getItemtable($resource_type);
      $sitereviewTableName = $listingTable->info('name');
      $primary = current($listingTable->info("primary"));
      $select->join($sitereviewTableName, "$tableReviewName.resource_id = $sitereviewTableName.$primary", array());
      $select->where("$sitereviewTableName.`listingtype_id` =?", $params['listingtype_id']);
    }
    $select->limit(1);

    $rating_users = $select
            ->query()
            ->fetchColumn();
    return $rating_users ? $rating_users : 0;
  }

  /**
   * If listing category is updated than update review and rating entries
   *
   * @param Int resource_id, pre_cat_id, curr_cat_id, resource_type
   */
  public function editListingCategory($resource_id, $pre_cat_id, $curr_cat_id, $resource_type) {

    //DELETE ENTRIES BELONGS TO THIS LISt ID 
    $this->delete(array('ratingparam_id != ?' => 0, 'resource_id = ?' => $resource_id, 'resource_type = ?' => $resource_type));

    //JUST UPDATE CATEGORY ID
    $this->update(array('category_id' => $curr_cat_id), array('category_id = ?' => $pre_cat_id, 'resource_id = ?' => $resource_id, 'resource_type = ?' => $resource_type, 'ratingparam_id = ?' => 0));
  }
  
  public function getOverallRating($resource_type = 'sitereview_listing', $resource_id = 0) {
    
    $overallRating = $this->select()
                          ->from($this->info('name'), 'rating')
                          ->where('ratingparam_id = ?', 0)
                          ->where('resource_type = ?', $resource_type)
                          ->where('review_id = ?', $resource_id)
                          ->query()
                          ->fetchColumn();
    
    return $overallRating;
  }
  
  public function getReviewId($viewer_id,$resource_type,$listing_id) {
  
		$selectReviewRatingTable = $this->select()
																    ->where('resource_id = ?',$listing_id)
																    ->where('resource_type = ?',$resource_type)
																    ->where('type = ?','user')
																    ->where('user_id = ?',$viewer_id);
		$review = $this->fetchRow($selectReviewRatingTable);
		return $review;
  }
  
  public function getReviewIdExist($viewer_id,$resource_type,$listing_id) {
  
		$selectReviewTable = $this->select()
															->where('resource_id = ?',$listing_id)
															->where('resource_type = ?',$resource_type)
															->where('type = ?','user')
															->where('user_id = ?',$viewer_id);
		$review = $this->fetchRow($selectReviewTable);
		$exist_review_id = $review->review_id;
		return $exist_review_id;
  }
  
  public function getTotalRatingUsers($listing_id) {
  
		$totalUsers = $this->select()
					->from($this->info('name'), 'COUNT(*) AS count')
					->where('resource_id = ?', $listing_id)
					->where('type = ?', 'user')
					->group('user_id')
					->query()->fetchColumn();
		if(!empty($totalUsers)) {
			return $totalUsers;
	  }
	  else {
	    return 0;
	  }
  
  }

}