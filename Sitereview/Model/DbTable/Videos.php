<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Videos.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Videos extends Engine_Db_Table {

  protected $_rowClass = "Sitereview_Model_Video";

  public function getVideosPaginator($params = array()) {
    
    $paginator = Zend_Paginator::factory($this->getVideosSelect($params));
    if (!empty($params['page'])) {
      $paginator->setCurrentPageNumber($params['page']);
    }
    if (!empty($params['limit'])) {
      $paginator->setItemCountPerPage($params['limit']);
    }
    return $paginator;
  }

  public function getVideosSelect($params = array()) {

    $rName = $this->info('name');
    $tmTable = Engine_Api::_()->getDbtable('TagMaps', 'core');
    $tmName = $tmTable->info('name');

    $select = $this->select()
            ->from($rName)
            ->order(!empty($params['orderby']) ? $params['orderby'] . ' DESC' : "$rName.creation_date DESC" );

    if (!empty($params['text'])) {
      $searchTable = Engine_Api::_()->getDbtable('search', 'core');
      $db = $searchTable->getAdapter();
      $sName = $searchTable->info('name');
      $select
              ->joinRight($sName, $sName . '.id=' . $rName . '.video_id', null)
              ->where($sName . '.type = ?', 'sitereview_video')
              ->where(new Zend_Db_Expr($db->quoteInto('MATCH(' . $sName . '.`title`, ' . $sName . '.`description`, ' . $sName . '.`keywords`, ' . $sName . '.`hidden`) AGAINST (? IN BOOLEAN MODE)', $params['text'])))
              ->order(new Zend_Db_Expr($db->quoteInto('MATCH(' . $sName . '.`title`, ' . $sName . '.`description`, ' . $sName . '.`keywords`, ' . $sName . '.`hidden`) AGAINST (?) DESC', $params['text'])));
    }

    if (!empty($params['status']) && is_numeric($params['status'])) {
      $select->where($rName . '.status = ?', $params['status']);
    }
    if (!empty($params['search']) && is_numeric($params['search'])) {
      $select->where($rName . '.search = ?', $params['search']);
    }
    if (!empty($params['user_id']) && is_numeric($params['user_id'])) {
      $select->where($rName . '.owner_id = ?', $params['user_id']);
    }

    if (!empty($params['user']) && $params['user'] instanceof User_Model_User) {
      $select->where($rName . '.owner_id = ?', $params['user_id']->getIdentity());
    }

    if (isset($params['listingtype_id']) && !empty($params['listingtype_id'])) {
      $listingsTable = Engine_Api::_()->getDbtable('listings', 'sitereview');
      $listingsName = $listingsTable->info('name');
      $select
              ->joinLeft($listingsName, "$listingsName. listing_id = $rName. listing_id", NULL)
              ->where($listingsName . '.listingtype_id = ?', $params['listingtype_id']);
    }

    if (!empty($params['tag'])) {
      $select
              ->joinLeft($tmName, "$tmName.resource_id = $rName.video_id", NULL)
              ->where($tmName . '.resource_type = ?', 'sitereview_video')
              ->where($tmName . '.tag_id = ?', $params['tag']);
    }

    return $select;
  }

  /**
   * Return video count
   *
   * @param int $listing_id
   * @return video count
   */
  public function getListingVideoCount($listing_id) {

    $count = $this->select()
            ->from($this->info('name'), 'count(*) as count')
            ->where('listing_id = ?', $listing_id)
            ->query()
            ->fetchColumn();
    return $count;
  }

  /**
   * Return video data
   *
   * @param array params
   * @return Zend_Db_Table_Select
   */
  public function widgetVideosData($params = array(), $videoType = null, $widgetType = null) {

    $tableVideoName = $this->info('name');

    if (isset($params['view_action']) && !empty($params['view_action'])) {
      $select = $this->select()->limit($params['limit']);
      if ($widgetType == 'sameposter') {
        $select = $this->select()
                ->where($tableVideoName . '.listing_id = ?', $params['listing_id'])
                ->where($tableVideoName . '.video_id != ?', $params['video_id'])
                ->order($tableVideoName . '.creation_date DESC');
      } elseif ($widgetType == 'showalsolike') {
        $likesTable = Engine_Api::_()->getDbtable('likes', 'core');
        $likesTableName = $likesTable->info('name');
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($tableVideoName)
                ->joinLeft($likesTableName, $likesTableName . '.resource_id=video_id', null)
                ->joinLeft($likesTableName . ' as l2', $likesTableName . '.poster_id=l2.poster_id', null)
                ->where($likesTableName . '.poster_type = ?', 'user')
                ->where('l2.poster_type = ?', 'user')
                ->where($likesTableName . '.resource_type = ?', $params['resource_type'])
                ->where('l2.resource_type = ?', $params['resource_type'])
                ->where($likesTableName . '.resource_id != ?', $params['resource_id'])
                ->where('l2.resource_id = ?', $params['resource_id'])
                ->where($tableVideoName . '.video_id != ?', $params['video_id'])
                ->group("$tableVideoName.video_id")
                ->order($tableVideoName . '.like_count DESC');
      } elseif ($widgetType == 'showsametag') {
        // Get tags for this video
        $tagMapsTable = Engine_Api::_()->getDbtable('tagMaps', 'core');
        $tagsTable = Engine_Api::_()->getDbtable('tags', 'core');

        // Get tags
        $tags = $tagMapsTable->select()
                ->from($tagMapsTable, 'tag_id')
                ->where('resource_type = ?', $params['resource_type'])
                ->where('resource_id = ?', $params['resource_id'])
                ->query()
                ->fetchAll(Zend_Db::FETCH_COLUMN);

        // No tags
        if (!empty($tags)) {
          // Get other with same tags
          $select = $this->select()
                  ->setIntegrityCheck(false)
                  ->from($tableVideoName)
                  ->joinLeft($tagMapsTable->info('name'), 'resource_id=video_id', null)
                  ->where('resource_type = ?', $params['resource_type'])
                  ->where('resource_id != ?', $params['resource_id'])
                  ->where('tag_id IN(?)', $tags)
                  ->order($tableVideoName . '.creation_date DESC')
                  ->group("$tableVideoName.video_id");
        }
      }
    }

    //End Network work
    return $this->fetchAll($select);
  }

  public function getTagCloud($limit=100) {

    $tableTagmaps = 'engine4_core_tagmaps';
    $tableTags = 'engine4_core_tags';

    $tableSitereviews = $this->info('name');
    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($tableSitereviews, 'title')
            ->joinInner($tableTagmaps, "$tableSitereviews.video_id = $tableTagmaps.resource_id", array('COUNT(engine4_core_tagmaps.resource_id) AS Frequency'))
            ->joinInner($tableTags, "$tableTags.tag_id = $tableTagmaps.tag_id", array('text', 'tag_id'));

    $select->where($tableSitereviews . ".search = ?", 1);
    $select = $select
            ->where($tableTagmaps . '.resource_type = ?', 'sitereview_video')
            ->group("$tableTags.text")
            ->order("Frequency DESC")
            ->limit($limit);

    return $select->query()->fetchAll();
  }

}