<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Listingtypes.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Listingtypes extends Engine_Db_Table {

  protected $_rowClass = 'Sitereview_Model_Listingtype';
  protected $_serializedColumns = array('contact_detail', 'admin_expiry_duration', 'language_phrases');

  public function getListingTypeColumn($listingtype_id, $column_name = 'title_plural') {

    $column = $this->select()
            ->from($this->info('name'), "$column_name")
            ->where('listingtype_id = ?', $listingtype_id)
            ->query()
            ->fetchColumn();

    return $column;
  }
  
  public function getAllowReview($listingtype_id) {

   if (empty($listingtype_id)) {
      $count = $this->getListingTypeCount();
      if($count > 1){
      return true;
      }else{
        $listingtype_id = 1;
      }
    }
    $column = $this->select()
            ->from($this->info('name'), "allow_review")
            ->where('listingtype_id = ?', $listingtype_id)
            ->query()
            ->fetchColumn();

    return $column;
  }

  public function getProfileMappingColumn($listingtype_id) {

    $profile_mapping = $this->select()
            ->from($this->info('name'), "profile_mapping")
            ->where('listingtype_id = ?', $listingtype_id)
            ->query()
            ->fetchColumn();

    if ($profile_mapping == 3) {
      return 'subsubcategory_id';
    } elseif ($profile_mapping == 2) {
      return 'subcategory_id';
    } else {
      return 'category_id';
    }
  }

  public function getAllListingTypes() {

    $select = $this->select()->from($this->info('name'))->order("order ASC")->order("listingtype_id ASC");

    return $this->fetchAll($select);
  }

  public function getListingTypes($listingtype_id = 0, $params = array()) {

    if (isset($params['expiry']) && $params['expiry'] == 'nonZero') {
      $columnsArray = array('title_plural', 'listingtype_id', 'expiry','admin_expiry_duration', 'redirection');
    }
    else {
      $columnsArray = array('title_plural', 'listingtype_id', 'redirection');
    }
    
    $select = $this->select()
            ->from($this->info('name'), $columnsArray)
            ->order("order ASC")
            ->order("listingtype_id ASC")
    ;

    if ($listingtype_id > 0) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }
    
    if (isset($params['expiry']) && $params['expiry'] == 'nonZero') {
      $select->where('expiry != ?', 0);
    }

    if (isset($params['visible']) && !empty($params['visible'])) {
      $select->where('visible = ?', $params['visible']);
    }
    
    if(isset($params['member_level_allow']) && !empty($params['member_level_allow'])) {
      
      $selectListingTypes = $select;
      
      //GET VIEWER ID
      $viewer = Engine_Api::_()->user()->getViewer();
           
      foreach($this->fetchAll($selectListingTypes) as $listingType) {
        $listingTypeId = $listingType->listingtype_id;
        $can_view = Engine_Api::_()->authorization()->isAllowed('sitereview_listing', $viewer, "view_listtype_$listingTypeId");
        if(empty($can_view)) {
          $select->where('listingtype_id != ?', $listingTypeId);
        }
      }
    }

    return $this->fetchAll($select);
  }

  public function getListingRow($listingtype_id) {

    $select = $this->select()
            ->from($this->info('name'), array('title_plural', 'title_singular'))
            ->where('listingtype_id = ?', $listingtype_id);

    return $this->fetchRow($select);
  }

  public function getListingTypesArray($listingtype_id = 0, $add_all = 0, $params = array()) {

    $listingTypeTableName = $this->info('name');
    $select = $this->select()->from($listingTypeTableName, array('title_plural', 'listingtype_id'))->order("order ASC")->order("listingtype_id ASC");

    if (!empty($listingtype_id)) {
      $select->where('listingtype_id != ?', $listingtype_id);
    }

    if (isset($params['visible']) && !empty($params['visible'])) {
      $select->where('visible = ?', $params['visible']);
    }
    
    if (isset($params['wishlist']) && !empty($params['wishlist'])) {
      $select->where('wishlist = ?', $params['wishlist']);
    }    
    
    if (isset($params['allowUserReview']) && !empty($params['allowUserReview'])) {
      $select->where('reviews = 3 OR reviews = 2');
    }    

    $listingTypeDatas = $this->fetchAll($select)->toArray();
    $listingTypes = array();
    if ($add_all)
      $listingTypes[0] = '';
    foreach ($listingTypeDatas as $key) {
      $listingTypes[$key['listingtype_id']] = $key['title_plural'];
    }

    return $listingTypes;
  }

  public function checkListingSlug($slug = '') {

    $listingtype_id = $this->select()
            ->from($this->info('name'), "listingtype_id")
            ->where('slug_singular = ?', $slug)
            ->orwhere('slug_plural = ?', $slug)
            ->query()
            ->fetchColumn();

    return $listingtype_id;
  }

  public function getListingTypeCount() {

    $select = $this->select()->from($this->info('name'), array("COUNT(listingtype_id) as total_listingtypes"));

    $total_listingtypes = $select->query()->fetchColumn();

    return $total_listingtypes;
  }
  
  public function getPageListings($listingTypeId , $page_id){
      $table = Engine_Api::_()->getDbTable('contents', 'sitepageintegration');
      $select = $table->select()
                    ->from($table->info('name'), "resource_id")
                    ->where('resource_type = ?', 'sitereview_listing')
                    ->where('page_id = ?', $page_id);
      $listings = $table->fetchAll($select);
      foreach ($listings as $listing) {
               $listing_ids[] = $listing->resource_id;
            }
      if(empty($listing_ids))
          return ;
      $listing_table = Engine_Api::_()->getDbTable('listings', 'sitereview');
      $select = $listing_table->select()
              ->where('listingtype_id = ?',$listingTypeId)
              ->where('listing_id IN('. join(',', $listing_ids) .')')
              ->order('creation_date DESC')
              ->limit(10);
   
      return $listing_table->fetchAll($select);
     
  }
  
  public function getBusinessListings($listingTypeId , $business_id){
      $table = Engine_Api::_()->getDbTable('contents', 'sitebusinessintegration');
      $select = $table->select()
                    ->from($table->info('name'), "resource_id")
                    ->where('resource_type = ?', 'sitereview_listing')
                    ->where('business_id = ?', $business_id);
      $listings = $table->fetchAll($select);
      foreach ($listings as $listing) {
               $listing_ids[] = $listing->resource_id;
            }
            if(empty($listing_ids))
          return ;
      $listing_table = Engine_Api::_()->getDbTable('listings', 'sitereview');
      $select = $listing_table->select()
              ->where('listingtype_id = ?',$listingTypeId)
              ->where('listing_id IN('. join(',', $listing_ids) .')')
              ->order('creation_date DESC')
               ->limit(10);
      
      return $listing_table->fetchAll($select);
     
  }
  
  public function getGroupListings($listingTypeId , $group_id){
      $table = Engine_Api::_()->getDbTable('contents', 'sitegroupintegration');
      $select = $table->select()
                    ->from($table->info('name'), "resource_id")
                    ->where('resource_type = ?', 'sitereview_listing')
                    ->where('group_id = ?', $group_id);
      $listings = $table->fetchAll($select);
      foreach ($listings as $listing) {
               $listing_ids[] = $listing->resource_id;
            }
            if(empty($listing_ids))
          return ;
      $listing_table = Engine_Api::_()->getDbTable('listings', 'sitereview');
      $select = $listing_table->select()
              ->where('listingtype_id = ?',$listingTypeId)
              ->where('listing_id IN('. join(',', $listing_ids) .')')
              ->order('creation_date DESC')
               ->limit(10);
              
      return $listing_table->fetchAll($select);
     
  }

}
