<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Reviews.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Reviews extends Engine_Db_Table {

  protected $_rowClass = "Sitereview_Model_Review";

  /**
   * Return list reviews
   * @param Array $params
   * @return Zend_Db_Table_Select
   */
  public function listReviews($params = array()) {

    $reviewTableName = $this->info('name');
    $tableRating = Engine_Api::_()->getDbtable('ratings', 'sitereview');
    $tableRatingName = $tableRating->info('name');
    //MAKE QUERY
    $select = $this->select()->from($reviewTableName, array('*'));

    if (isset($params['resource_id']) && !empty($params['resource_id'])) {
      $select->where("$reviewTableName.resource_id = ?", $params['resource_id']);
    }

    if (isset($params['resource_type']) && !empty($params['resource_type'])) {
      $select->where("$reviewTableName.resource_type = ?", $params['resource_type']);
    }

    if (isset($params['owner_id']) && !empty($params['owner_id'])) {
      $select->where("$reviewTableName.owner_id = ?", $params['owner_id']);
    }

    if (isset($params['owner_ids']) && !empty($params['owner_ids'])) {
      $select->where("$reviewTableName.owner_id In(?)", (array) $params['owner_ids']);
    }

    if (isset($params['type']) && $params['type'] == 'editor') {
      $select->where("$reviewTableName.type =?", $params['type']);
    } elseif (isset($params['type']) && ($params['type'] == 'user' || $params['type'] == 'visitor')) {
      $select->where("$reviewTableName.type in (?)", array($params['type'], 'visitor'));
    }

    if (isset($params['order'])) {
      if (isset($params['rating']) && $params['rating'] == 'rating') {
        $select->setIntegrityCheck(false)
                ->join($tableRatingName, "$tableRatingName.review_id = $reviewTableName.review_id", array('rating'));
        $select->group("$tableRatingName.review_id");
        $select->where("ratingparam_id = ?", 0);
        if (isset($params['rating_value']) && !empty($params['rating_value'])) {
          $select->where("rating =?", $params['rating_value']);
        }
        if ($params['order'] == 'highestRating') {
          $select->order("$tableRatingName.rating DESC");
        } else if ($params['order'] == 'lowestRating') {
          $select->order("$tableRatingName.rating ASC");
        }
      }

      if ($params['order'] == 'creationDate') {
        $select->order("$reviewTableName.review_id DESC");
      } else if ($params['order'] == 'helpful') {
        $select->order("$reviewTableName.helpful_count DESC")
                ->order("$reviewTableName.modified_date DESC");
      } else if ($params['order'] == 'featured') {
        $select->order("$reviewTableName.featured DESC");
      } else if ($params['order'] == 'recommend') {
        $select->order("$reviewTableName.recommend DESC");
      }
    }

    if (isset($params['review_id']) && !empty($params['review_id'])) {
      $select->where("$reviewTableName.review_id <> ?", $params['review_id']);
    }

    $select->where("status =?", 1);

    if (isset($params['limit'])) {
      $select->limit($params['limit']);
    }

    //RETURN RESULTS
    return Zend_Paginator::factory($select);
  }

  /**
   * Return paginator
   * @param Array $params
   * @param Array $customParams
   * @return paginator
   */
  public function getReviewsPaginator($params = array(), $customParams = null) {

    $paginator = Zend_Paginator::factory($this->getReviewsSelect($params, $customParams));
    if (!empty($params['page'])) {
      $paginator->setCurrentPageNumber($params['page']);
    }

    if (!empty($params['limit'])) {
      $paginator->setItemCountPerPage($params['limit']);
    }

    return $paginator;
  }

  /**
   * Return paginator
   * @param Array $params
   * @param Array $customParams
   * @return Zend_Db_Table_Select
   */
  public function getReviewsSelect($params = array(), $customParams = null) {

    $reviewTableName = $this->info('name');
    //GET LISTING TABLE NAME
    $listingTable = Engine_Api::_()->getItemtable($params['resource_type']);
    $sitereviewTableName = $listingTable->info('name');
    $primary = current($listingTable->info("primary"));

    //MAKE QUERY
    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($reviewTableName, array('*'))
            ->join($sitereviewTableName, "$reviewTableName.resource_id = $sitereviewTableName.$primary", array())
            ->where($reviewTableName .".status =?", 1)
            ->group("$reviewTableName.review_id");

    if (isset($customParams)) {
      //GET SEARCH TABLE
      $searchTable = Engine_Api::_()->fields()->getTable('sitereview_review', 'search')->info('name');
      //PROCESS OPTIONS
      $tmp = array();
      foreach ($customParams as $k => $v) {
        if (null == $v || '' == $v || (is_array($v) && count(array_filter($v)) == 0)) {
          continue;
        } else if (false !== strpos($k, '_field_')) {
          list($null, $field) = explode('_field_', $k);
          $tmp['field_' . $field] = $v;
        } else if (false !== strpos($k, '_alias_')) {
          list($null, $alias) = explode('_alias_', $k);
          $tmp[$alias] = $v;
        } else {
          $tmp[$k] = $v;
        }
      }
      $customParams = $tmp;

      $select = $select
              ->setIntegrityCheck(false)
              ->joinLeft($searchTable, "$searchTable.item_id = $sitereviewTableName.listing_id", null);

      $searchParts = Engine_Api::_()->fields()->getSearchQuery('sitereview_review', $customParams);
      foreach ($searchParts as $k => $v) {
        $select->where("`{$searchTable}`.{$k}", $v);
      }
    }

    if (!isset($params['order'])) {
      $params['order'] = null;
    }

    if (isset($params['type']) && $params['type'] == 'editor') {
      $select->where("$reviewTableName.type =?", $params['type']);
    } elseif (isset($params['type']) && ($params['type'] == 'user' || $params['type'] == 'visitor')) {
      $select->where("$reviewTableName.type in (?)", array($params['type'], 'visitor'));
    }

    if (isset($params['resource_type']) && !empty($params['resource_type'])) {
      $select->where("$reviewTableName.resource_type = ?", $params['resource_type']);
    }

    if (isset($params['resource_id']) && !empty($params['resource_id'])) {
      $select->where("$reviewTableName.resource_id = ?", $params['resource_id']);
    }

    if (isset($params['search']) && !empty($params['search'])) {
      $searchTable = Engine_Api::_()->getDbtable('search', 'core');
      $db = $searchTable->getAdapter();
      $sName = $searchTable->info('name');
      $select
              ->joinRight($sName, $sName . '.id=' . $reviewTableName . '.review_id', null)
              ->where($sName . '.type = ?', 'sitereview_review')
              ->where(new Zend_Db_Expr($db->quoteInto('MATCH(' . $sName . '.`title`, ' . $sName . '.`description`, ' . $sName . '.`keywords`, ' . $sName . '.`hidden`) AGAINST (? IN BOOLEAN MODE)', $params['search'])))
              ->order(new Zend_Db_Expr($db->quoteInto('MATCH(' . $sName . '.`title`, ' . $sName . '.`description`, ' . $sName . '.`keywords`, ' . $sName . '.`hidden`) AGAINST (?) DESC', $params['search'])))
      ;
    }

    if (isset($params['listingtype_id']) && !empty($params['listingtype_id'])) {
      $select->where("$sitereviewTableName.`listingtype_id` =?", $params['listingtype_id']);
    }

    if (isset($params['listing_id']) && !empty($params['listing_id'])) {
      $select->where("$sitereviewTableName.$primary =?", $params['listing_id']);
    }

    if (isset($params['category_id']) && !empty($params['category_id'])) {
      $select->where("$sitereviewTableName.`category_id` =?", $params['category_id']);
    }

    if (isset($params['subcategory_id']) && !empty($params['subcategory_id'])) {
      $select->where("$sitereviewTableName.`subcategory_id` =?", $params['subcategory_id']);
    }

    if (isset($params['subsubcategory_id']) && !empty($params['subsubcategory_id'])) {
      $select->where("$sitereviewTableName.`subsubcategory_id` =?", $params['subsubcategory_id']);
    }

    if (isset($params['recommend']) && !empty($params['recommend'])) {
      $select->where("recommend =?", $params['recommend']);
    }

    if (isset($params['user_id']) && !empty($params['user_id'])) {
      $select->where("$reviewTableName.owner_id =?", $params['user_id']);
    }

    if (isset($params['featured'])) {
      $select->where("$reviewTableName.featured =?", 1);
    }

    if (isset($params['user_ids']) && !empty($params['user_ids'])) {
      $select->where("$reviewTableName.owner_id in (?)", (array) $params['user_ids']);
    }

    if ((isset($params['rating']) && !empty($params['rating'])) || $params['order'] == 'rating_highest' || $params['order'] == 'rating_lowest') {
      $tableRating = Engine_Api::_()->getDbtable('ratings', 'sitereview');
      $tableRatingName = $tableRating->info('name');
      $select
              ->join($tableRatingName, "$tableRatingName.review_id = $reviewTableName.review_id", array('rating'))
              ->where("ratingparam_id = ?", 0)
              ->group("$tableRatingName.review_id");
      if (isset($params['rating']) && !empty($params['rating']))
        $select->where("rating =?", $params['rating']);
    }

    if (isset($params['order'])) {
      if ($params['order'] == 'rating_highest') {
        $select->order("$tableRatingName.rating DESC");
      } else if ($params['order'] == 'rating_lowest') {
        $select->order("$tableRatingName.rating ASC");
      } else if ($params['order'] == 'view_most') {
        $select->order("$reviewTableName.view_count DESC");
      } else if ($params['order'] == 'like_most') {
        $select->order("$reviewTableName.like_count DESC");
      } else if ($params['order'] == 'helpfull_most') {
        $select->order("$reviewTableName.helpful_count DESC");
      } else if ($params['order'] == 'replay_most') {
        $select->order("$reviewTableName.reply_count DESC");
      } else if ($params['order'] == 'featured') {
        $select->order("$reviewTableName.featured DESC");
      }
    }
    $select->order("$reviewTableName.modified_date DESC");

    return $select;
  }

  /**
   * Return average recommendetion for list reviews
   *
   * @param Array $params
   * @return average recommendetion for list reviews
   */
  public function getAvgRecommendation($params = array()) {

    $reviewTableName = $this->info('name');
    //MAKE QUERY
    $select = $this->select()
            ->from($this->info('name'), array('*', 'AVG(recommend) AS avg_recommend'));

    if (isset($params['resource_id']) && !empty($params['resource_id'])) {
      $select->where("$reviewTableName.resource_id = ?", $params['resource_id']);
    }

    if (isset($params['resource_type']) && !empty($params['resource_type'])) {
      $select->where("$reviewTableName.resource_type = ?", $params['resource_type']);
    }

    $select->where('status = ?', 1)
            ->group('resource_id');

    $reviewTableName = $this->info('name');

    if (isset($params['type']) && $params['type'] == 'editor') {
      $select->where("$reviewTableName.type =?", $params['type']);
    } elseif (isset($params['type']) && ($params['type'] == 'user' || $params['type'] == 'visitor')) {
      $select->where("$reviewTableName.type in (?)", array($params['type'], 'visitor'));
    }

    //RETURN RESULTS
    return $this->fetchAll($select);
  }

  /**
   * Return review data for checking that viewer has been posted a review or not
   *
   * @param Int resource_id
   * @param Int viewer_id
   * @return Zend_Db_Table_Select
   */
  public function canPostReview($params = array()) {

    //MAKE QUERY
    $select = $this->select()
            ->from($this->info('name'), array('review_id'));

    $reviewTableName = $this->info('name');

    if (isset($params['resource_id']) && !empty($params['resource_id'])) {
      $select->where("$reviewTableName.resource_id = ?", $params['resource_id']);
    }

    if (isset($params['resource_type']) && !empty($params['resource_type'])) {
      $select->where("$reviewTableName.resource_type = ?", $params['resource_type']);
    }

    if (isset($params['type']) && $params['type'] == 'editor') {
      $select->where("$reviewTableName.type =?", $params['type']);
      if (!isset($params['notIncludeStatusCheck'])) {
        $select->where("$reviewTableName.status =?", 1);
      }
    } elseif (isset($params['type']) && ($params['type'] == 'user' || $params['type'] == 'visitor')) {
      $select->where("$reviewTableName.type in (?)", array($params['type'], 'visitor'));
    }

    if (isset($params['viewer_id']) && !empty($params['viewer_id'])) {
      $select->where('owner_id = ?', $params['viewer_id']);
    }

    $hasPosted = $select->query()->fetchColumn();

    //RETURN RESULTS
    return $hasPosted;
  }

  /**
   * Return total reviews for listing
   *
   * @param Array $params
   * @return total reviews for listing
   */
  public function totalReviews($params = array()) {

    $reviewTableName = $this->info('name');

    //MAKE QUERY
    $select = $this->select()
            ->from($this->info('name'), array('COUNT(*) AS count'));

    if (isset($params['resource_id']) && !empty($params['resource_id'])) {
      $select->where("$reviewTableName.resource_id = ?", $params['resource_id']);
    }

    if (isset($params['resource_type']) && !empty($params['resource_type'])) {
      $select->where("$reviewTableName.resource_type = ?", $params['resource_type']);
    }

    $reviewTableName = $this->info('name');
    if (isset($params['type']) && $params['type'] == 'editor') {
      $select->where("$reviewTableName.type =?", $params['type']);
    } elseif (isset($params['type']) && ($params['type'] == 'user' || $params['type'] == 'visitor')) {
      $select->where("$reviewTableName.type in (?)", array($params['type'], 'visitor'));
    }

    if (isset($params['owner_id']) && !empty($params['owner_id'])) {
      $select->where('owner_id = ?', $params['owner_id']);
    }

    $totalReviews = $select->where("status = ?", 1)
            ->query()
            ->fetchColumn();

    //RETURN RESULTS
    return $totalReviews;
  }

  /**
   * Return paginator
   *
   * @param Int $user_id
   * @param Varchar $type
   * @return paginator
   */
  public function getReviewComments($user_id = 0, $type = 'user') {

    $commentTable = Engine_Api::_()->getDbtable('comments', 'core');
    $commentTableName = $commentTable->info('name');

    $reviewTableName = $this->info('name');

    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($reviewTableName, array('review_id', 'resource_id', 'title', 'type'))
            ->join($commentTableName, "$commentTableName.resource_id = $reviewTableName.review_id", array('body AS comment', 'creation_date'))
            ->where("$commentTableName.poster_id = ?", $user_id);

    if ($type == 'editor') {
      $select->where("$reviewTableName.type =?", $type);
    } elseif ($type == 'user' || $type == 'visitor') {
      $select->where("$reviewTableName.type in (?)", array($type, 'visitor'));
    }

    return Zend_Paginator::factory($select);
  }

  /**
   * Return total comment
   *
   * @param Int $user_id
   * @return total comment
   */
  public function countReviewComments($user_id = 0) {

    $commentTable = Engine_Api::_()->getDbtable('comments', 'core');
    $commentTableName = $commentTable->info('name');

    $totalCommentCount = $commentTable->select()
            ->from($commentTableName, array('COUNT(comment_id) as total_comments'))
            ->where("$commentTableName.resource_type = ?", 'sitereview_review')
            ->where("$commentTableName.poster_id = ?", $user_id)
            ->query()
            ->fetchColumn();
    return $totalCommentCount;
  }

  /**
   * Return total review category
   *
   * @param Int $user_id
   * @param Varchar $resource_type
   * @return total review category
   */
  public function countReviewCategories($user_id = 0, $resource_type = null) {

    //GET LISTING TABLE NAME
    $listingTable = Engine_Api::_()->getItemtable($resource_type);
    $listingTableName = $listingTable->info('name');
    $primary = current($listingTable->info("primary"));
    $reviewTableName = $this->info('name');

    $countReviewCategories = $this->select()
            ->setIntegrityCheck(false)
            ->from($reviewTableName, array(''))
            ->joinInner($listingTableName, "$listingTableName.$primary = $reviewTableName.resource_id", array('COUNT(DISTINCT category_id) as total_categories'));

    if (!empty($resource_type)) {
      $countReviewCategories->where("$reviewTableName.resource_type = ?", $resource_type);
    }

    $result = $countReviewCategories->where("$reviewTableName.owner_id = ?", $user_id)
            ->where("$listingTableName.category_id != ?", 0)
            ->where("$reviewTableName.status = ?", 1)
            ->query()
            ->fetchColumn();

    return $result;
  }

  /**
   * Return top reviewers
   *
   * @param Array $params
   * @return top reviewers
   */
  public function topReviewers($params = array()) {

    //GET USER TABLE INFO
    $tableUser = Engine_Api::_()->getDbtable('users', 'user');
    $tableUserName = $tableUser->info('name');

    //GET REVIEW TABLE NAME
    $reviewTableName = $this->info('name');

    //MAKE QUERY
    $select = $tableUser->select()
            ->setIntegrityCheck(false)
            ->from($tableUserName, array('user_id', 'displayname', 'username', 'photo_id'))
            ->join($reviewTableName, "$tableUserName.user_id = $reviewTableName.owner_id", array('COUNT(engine4_sitereview_reviews.review_id) AS review_count', 'MAX(engine4_sitereview_reviews.review_id) as max_review_id'));

    if (isset($params['type']) && $params['type'] == 'editor') {
      $select->where("$reviewTableName.type =?", $params['type']);
    } elseif (isset($params['type']) && ($params['type'] == 'user' || $params['type'] == 'editor')) {
      $select->where("$reviewTableName.type in (?)", array($params['type'], 'visitor'));
    }

    if (isset($params['listingtype_id']) && !empty($params['listingtype_id']) && $params['listingtype_id'] != -1) {

      $sitereviewTable = Engine_Api::_()->getItemtable($params['resource_type']);
      $sitereviewTableName = $sitereviewTable->info('name');
      $primary = current($sitereviewTable->info("primary"));

      $select->join($sitereviewTableName, "$reviewTableName.resource_id = $sitereviewTableName.$primary", array(''))
              ->where($sitereviewTableName . '.listingtype_id = ?', $params['listingtype_id']);
    }

    if (isset($params['resource_type']) && !empty($params['resource_type'])) {
      $select->where("$reviewTableName.resource_type = ?", $params['resource_type']);
    }

    $select->where($reviewTableName . '.status = ?', 1)
            ->group($tableUserName . ".user_id")
            ->order('review_count DESC')
            ->order('user_id DESC')
            ->limit($params['limit']);

    //RETURN THE RESULTS
    return $tableUser->fetchAll($select);
  }

  /**
   * Return reviews
   *
   * @param Array $params
   * @return reviews
   */
  public function getReviews($params = array()) {
    $popularity = $params['popularity'];
    $interval = $params['interval'];

    //MAKE TIMING STRING
    $sqlTimeStr = '';
    $current_time = date("Y-m-d H:i:s");
    if ($interval == 'week') {
      $time_duration = date('Y-m-d H:i:s', strtotime('-7 days'));
      $sqlTimeStr = ".creation_date BETWEEN " . "'" . $time_duration . "'" . " AND " . "'" . $current_time . "'";
    } elseif ($interval == 'month') {
      $time_duration = date('Y-m-d H:i:s', strtotime('-1 months'));
      $sqlTimeStr = ".creation_date BETWEEN " . "'" . $time_duration . "'" . " AND " . "'" . $current_time . "'" . "";
    }

    //GET REVIEW TABLE NAME
    $reviewTableName = $this->info('name');

    $sitereviewTable = Engine_Api::_()->getItemtable($params['resource_type']);
    $sitereviewTableName = $sitereviewTable->info('name');
    $primary = current($sitereviewTable->info("primary"));

    $ratingTable = Engine_Api::_()->getDbTable('ratings', 'sitereview');
    $ratingTableName = $ratingTable->info('name');

    //MAKE QUERY
    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($reviewTableName);

    if ($params['resource_type'] == 'sitereview_listing') {
      $select->joinInner($sitereviewTableName, "$sitereviewTableName.$primary = $reviewTableName.resource_id", array('title AS listing_title', $primary, 'listingtype_id', 'category_id'));
    } else {
      $select->joinInner($sitereviewTableName, "$sitereviewTableName.$primary = $reviewTableName.resource_id", array('title AS listing_title', $primary, 'category_id'));
    }

    $select->joinInner($ratingTableName, "$ratingTableName.review_id = $reviewTableName.review_id", array('rating'))
            ->where($ratingTableName . '.ratingparam_id = ?', 0);

    if (isset($params['type']) && $params['type'] == 'editor') {
      $select->where("$reviewTableName.type =?", $params['type']);
    } elseif (isset($params['type']) && ($params['type'] == 'user' || $params['type'] == 'visitor')) {
      $select->where("$reviewTableName.type in (?)", array($params['type'], 'visitor'));
    }

    if (isset($params['owner_id']) && !empty($params['owner_id'])) {
      $select->where("$reviewTableName.owner_id =?", $params['owner_id']);
    }

    if (isset($params['listingtype_id']) && !empty($params['listingtype_id']) && $params['listingtype_id'] != -1) {
      $select->where($sitereviewTableName . '.listingtype_id = ?', $params['listingtype_id']);
    }

    if (isset($params['listingtype_ids']) && !empty($params['listingtype_ids']) && $params['listingtype_ids'] != -1) {
      $select->where($sitereviewTableName . '.listingtype_id in (?)', $params['listingtype_ids']);
    }

    if (isset($params['groupby']) && !empty($params['groupby'])) {
      $select->group($reviewTableName . ".review_id");
      
//    } else {
//      $select->group('user_id');
    }

    if (isset($params['status']) && !empty($params['status'])) {
      $select->where($reviewTableName . '.featured = ?', 1);
    }

    if($popularity == 'RAND()') {
        $select->order("RAND()");
    }
    elseif ($interval != 'overall' && $popularity == 'like_count') {
      $popularityTable = Engine_Api::_()->getDbtable('likes', 'core');
      $popularityTableName = $popularityTable->info('name');
      $select = $select->joinLeft($popularityTableName, $popularityTableName . '.resource_id = ' . $reviewTableName . '.review_id', array("COUNT(like_id) as total_count"))
              ->where($popularityTableName . "$sqlTimeStr  or " . $popularityTableName . '.creation_date is null')
              ->order("total_count DESC");
    } elseif ($interval != 'overall' && $popularity == 'comment_count') {
      $popularityTable = Engine_Api::_()->getDbtable('comments', 'core');
      $popularityTableName = $popularityTable->info('name');

      $select = $select->joinLeft($popularityTableName, $popularityTableName . '.resource_id = ' . $reviewTableName . '.review_id', array("COUNT(comment_id) as total_count"))
              ->where($popularityTableName . "$sqlTimeStr  or " . $popularityTableName . '.creation_date is null')
              ->order("total_count DESC");
    } elseif ($popularity == 'view_count' || $popularity == 'review_id' || $popularity == 'modified_date' || $popularity == 'creation_date' || $popularity == 'helpful_count') {
      $select->order("$reviewTableName.$popularity DESC");
    } elseif ($interval == 'overall' && ($popularity == 'like_count' || $popularity == 'comment_count')) {
      $select->order("$reviewTableName.$popularity DESC");
    }

    if (isset($params['popularity']) && !empty($params['popularity']) && $params['popularity'] != 'review_id' && $params['popularity'] != 'creation_date' && $params['popularity'] != 'RAND()') {
      $select->order($reviewTableName . ".review_id DESC");
    }

    $select->where($reviewTableName . '.status = ?', 1)
            ->limit($params['limit']);

    //RETURN THE RESULTS
    if (isset($params['pagination']) && !empty($params['pagination'])) {
      return Zend_Paginator::factory($select);
    } else {
      return $this->fetchAll($select);
    }
    
  }

  /**
   * Return column name
   *
   * @param Int $review_id
   * @param Varchar $column_name
   * @return column name
   */
  public function getColumnValue($review_id = 0, $column_name) {

    $column = $this->select()
            ->from($this->info('name'), array("$column_name"))
            ->where('review_id = ?', $review_id)
            ->limit(1)
            ->query()
            ->fetchColumn();

    return $column;
  }

  /**
   * Return mapped reviews
   *
   * @param Int $category_id
   * @param Varchar $resource_type
   * @return mapped reviews
   */
  public function getMappedReviews($category_id, $resource_type = null) {

    //RETURN IF CATEGORY ID IS NULL
    if (empty($category_id)) {
      return null;
    }

    //GET REVIEW TABLE NAME
    $reviewTableName = $this->info('name');

    //GET LISTING TABLE NAME
    $listingTable = Engine_Api::_()->getItemTable('sitereview_listing');
    $listingTableName = $listingTable->info('name');

    //MAKE QUERY
    $select = $this->select()
            ->from($reviewTableName, array('review_id'))
            ->joinLeft($listingTableName, "$listingTableName.listing_id = $reviewTableName.resource_id", array())
            ->where('resource_type = ?', $resource_type)
            ->where("category_id = $category_id OR subcategory_id = $category_id OR subsubcategory_id = $category_id");

    //GET DATA
    $categoryData = $this->fetchAll($select);

    if (!empty($categoryData)) {
      return $categoryData->toArray();
    }

    return null;
  }

}