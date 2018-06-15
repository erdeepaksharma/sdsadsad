<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Wishlistmaps.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Wishlistmaps extends Engine_Db_Table {

  protected $_rowClass = 'Sitereview_Model_Wishlistmap';

  public function wishlistListings($wishlist_id, $params = null) {
    //RETURN IF WISHLIST ID IS EMPTY
    if (empty($wishlist_id)) {
      return;
    }

    //GET WISHLIST PAGE TABLE NAME
    $wishlistListingTableName = $this->info('name');

    //GET LISTING TABLE
    $sitereviewTable = Engine_Api::_()->getDbTable('listings', 'sitereview');
    $sitereviewTableName = $sitereviewTable->info('name');
    
    if(Engine_Api::_()->sitereview()->hasPackageEnable()) {
      $arrayOfColumn = array('title', 'listing_id', 'listingtype_id', 'owner_id', 'photo_id', 'rating_avg', 'rating_users', 'rating_editor', 'body', 'price', 'category_id','featured','newlabel','sponsored','like_count','view_count','comment_count','review_count','creation_date','package_id');
    }
    else {
      $arrayOfColumn = array('title', 'listing_id', 'listingtype_id', 'owner_id', 'photo_id', 'rating_avg', 'rating_users', 'rating_editor', 'body', 'price', 'category_id','featured','newlabel','sponsored','like_count','view_count','comment_count','review_count','creation_date');
    }

    //MAKE QUERY
    $select = $sitereviewTable->select()
            ->setIntegrityCheck(false)
            ->from($sitereviewTableName, $arrayOfColumn)
            ->join($wishlistListingTableName, "$wishlistListingTableName.listing_id = $sitereviewTableName.listing_id", array('date', 'listing_id'))
            ->where($wishlistListingTableName . '.wishlist_id = ?', $wishlist_id);

    if (isset($params['listingtype_id']) && !empty($params['listingtype_id']) && $params['listingtype_id'] != -1) {
      $select->where($sitereviewTableName . '.listingtype_id = ?', $params['listingtype_id']);
    }

    if (isset($params['category_id']) && !empty($params['category_id'])) {
      $select->where($sitereviewTableName . '.category_id = ?', $params['category_id']);
    }

    if (isset($params['subcategory_id']) && !empty($params['subcategory_id'])) {
      $select->where($sitereviewTableName . '.subcategory_id = ?', $params['subcategory_id']);
    }

    if (isset($params['subsubcategory_id']) && !empty($params['subsubcategory_id'])) {
      $select->where($sitereviewTableName . '.subsubcategory_id = ?', $params['subsubcategory_id']);
    }

    if (isset($params['category']) && !empty($params['category'])) {
      $select->where($sitereviewTableName . '.category_id = ?', $params['category']);
    }

    if (isset($params['subcategory']) && !empty($params['subcategory'])) {
      $select->where($sitereviewTableName . '.subcategory_id = ?', $params['subcategory']);
    }

    if (isset($params['subsubcategory']) && !empty($params['subsubcategory'])) {
      $select->where($sitereviewTableName . '.subsubcategory_id = ?', $params['subsubcategory']);
    }

    if (isset($params['search']) && !empty($params['search'])) {
      $select->where($sitereviewTableName . ".title LIKE ? OR " . $sitereviewTableName . ".body LIKE ? ", '%' . $params['search'] . '%');
    }
    if (isset($params['orderby']) && $params['orderby'] == 'random') {
      $select->order('RAND()');
    } else if (isset($params['orderby']) && !empty($params['orderby'])) {
      if($params['orderby'] == 'date') {
        $select->order("$wishlistListingTableName." . $params['orderby'] . " DESC");
      }
      else {
        $select->order("$sitereviewTableName." . $params['orderby'] . " DESC");
      }
    } else {
      $select->order($sitereviewTableName . '.creation_date' . " DESC");
    }

    //RETURN RESULTS
    return Zend_Paginator::factory($select);
  }

  public function pageWishlists($listing_id, $owner_id = 0) {

    //RETURN IF PAGE ID IS EMPTY
    if (empty($listing_id)) {
      return;
    }

    //GET WISHLIST PAGE TABLE NAME
    $wishlistTable = Engine_Api::_()->getDbTable('wishlists', 'sitereview');
    $wishlistTableName = $wishlistTable->info('name');

    //GET WISHLIST PAGE TABLE NAME
    $wishlistListingTableName = $this->info('name');

    //MAKE QUERY
    $select = $wishlistTable->select()
            ->setIntegrityCheck(false)
            ->from($wishlistTableName)
            ->join($wishlistListingTableName, "$wishlistListingTableName.wishlist_id = $wishlistTableName.wishlist_id")
            ->where($wishlistTableName . '.owner_id = ?', $owner_id)
            ->where($wishlistListingTableName.'.listing_id = ?', $listing_id);
    
    //RETURN RESULTS
    return $wishlistTable->fetchAll($select);
  }

  /**
   * Return wishlists count
   *
   * @param int $wishlist_id 
   * @return wishlists count
   * */
  public function itemCount($wishlist_id) {

    $wishlistCount = $this->select()
            ->from($this->info('name'), array('COUNT(*) AS count'))
            ->where('wishlist_id = ?', $wishlist_id)
            ->query()
            ->fetchColumn();

    //RETURN WISHLIST COUNT
    return $wishlistCount;
  }

  /**
   * Return wishlists count
   *
   * @param int $wishlist_id 
   * @return wishlists count
   * */
  public function getWishlistsListingCount($listing_id) {

    $wishlistCount = $this->select()
            ->from($this->info('name'), array('COUNT(*) AS count'))
            ->where('listing_id = ?', $listing_id)
            ->query()
            ->fetchColumn();

    //RETURN WISHLIST COUNT
    return $wishlistCount;
  }
  
  public function performWishlistMapAction($wishlist_id, $listing_id, $action) {
      
    $wishlistId = $this->select()
            ->from($this->info('name'), array('wishlist_id'))
            ->where('listing_id = ?', $listing_id)
            ->where('wishlist_id = ?', $wishlist_id)
            ->query()
            ->fetchColumn();
    
    if($action == 'add' && empty($wishlistId)) {
        $this->insert(array('wishlist_id' => $wishlist_id, 'listing_id' => $listing_id, 'date' => new Zend_Db_Expr('NOW()')));
    }
    elseif($action == 'remove' && !empty($wishlistId)) {
        $this->delete(array('wishlist_id = ?' => $wishlist_id, 'listing_id = ?' => $listing_id));
    }
  }
  
  public function isItemAdded($listing_id, $owner_id, $wishlist_id) {
      
    //GET WISHLIST PAGE TABLE NAME
    $wishlistTable = Engine_Api::_()->getDbTable('wishlists', 'sitereview');
    $wishlistTableName = $wishlistTable->info('name');

    //GET WISHLIST PAGE TABLE NAME
    $wishlistListingTableName = $this->info('name');

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity(); 
    $recent_wishlist_id = Engine_Api::_()->getDbTable('wishlists', 'sitereview')->recentWishlistId($viewer_id); 
    
    //MAKE QUERY
    $wishlist_id = $wishlistTable->select()
            ->setIntegrityCheck(false)
            ->from($wishlistTableName, 'wishlist_id')
            ->join($wishlistListingTableName, "$wishlistListingTableName.wishlist_id = $wishlistTableName.wishlist_id", null)
            ->where($wishlistTableName . '.owner_id = ?', $owner_id)
            ->where($wishlistListingTableName.'.listing_id = ?', $listing_id)
            ->where($wishlistListingTableName.'.wishlist_id = ?', $recent_wishlist_id)
            ->limit(1)
            ->query()
            ->fetchColumn();
    
    //RETURN RESULTS
    return $wishlist_id;      
  }
}
