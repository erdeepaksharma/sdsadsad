<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Listings.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Listings extends Engine_Db_Table {

  protected $_rowClass = "Sitereview_Model_Listing";
  protected $_serializedColumns = array('main_video', 'networks_privacy');

  public function expirySQL($select, $listingtype_id, $showExpiry=0) {
    if (empty($select))
      return;
    if (Engine_Api::_()->sitereview()->hasPackageEnable())
    $select->where("{$this->info('name')}.expiration_date  > ?", date("Y-m-d H:i:s"));
    $reviewApi = Engine_Api::_()->sitereview();
    if ($listingtype_id > 0) {
      $reviewApi->setListingTypeInRegistry($listingtype_id);
      $expirySettings = $reviewApi->expirySettings($listingtype_id);

      if ($expirySettings == 2) {
        $approveDate = $reviewApi->adminExpiryDuration($listingtype_id);
        if (empty($showExpiry))
          $select->where($this->info('name') . ".`approved_date` >= ?", $approveDate);
        else
          $select->where($this->info('name') . ".`approved_date` < ?", $approveDate);
      } elseif ($expirySettings == 1) {
        $current_date = date("Y-m-d i:s:m", time());
        if (empty($showExpiry))
          $select->where("(" .$this->info('name') . ".`end_date` = '0000-00-00 00:00:00' or " . $this->info('name') . ".`end_date` IS NULL or " . $this->info('name') . ".`end_date` >= ?)", $current_date);
        else{
        $select->where("(" .$this->info('name') . ".`end_date` != '0000-00-00 00:00:00' and " . $this->info('name') . ".`end_date` < ?)", $current_date);
        }
      }
      return $select; 
    }else {
      $reviewApi->setListingTypeInRegistry(-1);
      $expiryBaseListingTypeIds = Zend_Registry::get('expiryBaseListingTypeIds');
      $adminExpiryIds = (isset($expiryBaseListingTypeIds[2])) ? $expiryBaseListingTypeIds[2] : array();
      $endDateExpiryIds = (isset($expiryBaseListingTypeIds[1])) ? $expiryBaseListingTypeIds[1] : array();
      $noExpiryIds = (isset($expiryBaseListingTypeIds[0])) ? $expiryBaseListingTypeIds[0] : array();
      if (empty($adminExpiryIds) && empty($endDateExpiryIds))
        return $select;

      $sql = array();
      foreach ($adminExpiryIds as $listingtype_id) {
        if (!Zend_Registry::isRegistered('listingtypeApprovedDate' . $listingtype_id)) {
          $approveDate = $reviewApi->adminExpiryDuration($listingtype_id);
          Zend_Registry::set('listingtypeApprovedDate' . $listingtype_id, $approveDate);
        } else {
          $approveDate = Zend_Registry::get('listingtypeApprovedDate' . $listingtype_id);
        }

        if (empty($showExpiry))
          $sql[] = "(" . $this->info('name') . ".`approved_date` >= '$approveDate' AND " . $this->info('name') . ".`listingtype_id` = '$listingtype_id')";
        else
          $sql[] = "(" . $this->info('name') . ".`approved_date` < '$approveDate' AND " . $this->info('name') . ".`listingtype_id` = '$listingtype_id')";
      }
      if (!empty($endDateExpiryIds)) {
        $current_date = date("Y-m-d i:s:m", time());
        if (empty($showExpiry))
          $sql[] = "( (" . $this->info('name') . ".`end_date` = '0000-00-00 00:00:00' or " .$this->info('name') . ".`end_date` IS NULL or " . $this->info('name') . ".`end_date` >= '$current_date' ) AND " . $this->info('name') . ".`listingtype_id` IN('" . join("','", $endDateExpiryIds) . "'))";
        else
          $sql[] = "( (" . $this->info('name') . ".`end_date` != '0000-00-00 00:00:00' and  " .$this->info('name') . ".`end_date` < '$current_date') AND " . $this->info('name') . ".`listingtype_id` IN('" . join("','", $endDateExpiryIds) . "'))";
      }

      if ($noExpiryIds) {
        $sql[] = "(" . $this->info('name') . ".`listingtype_id` IN('" . join("','", $noExpiryIds) . "'))";
      }

      if (!empty($sql)) {
        $select->where("(" . join(" OR ", $sql) . ")");
      }
      return $select;
    }
  }

  public function getOnlyViewableListingsId() {
        $viewer = Engine_Api::_()->user()->getViewer();
        $listings_ids = array();
        $cache = Zend_Registry::get('Zend_Cache');
        $cacheName = 'sitereview_ids_user_id_' . $viewer->getIdentity();
        $data = APPLICATION_ENV == 'development' ? ( Zend_Registry::isRegistered($cacheName) ? Zend_Registry::get($cacheName) : null ) : $cache->load($cacheName);
        if ($data && is_array($data)) {  
            $listings_ids = $data;
        } else { 
            set_time_limit(0);
            $tableName = $this->info('name');
            $listing_select = $this->select()
                    ->from($this->info('name'), array('listing_id', 'owner_id', 'title', 'photo_id', 'networks_privacy'))
                    ->where("{$tableName}.search = ?", 1)
                    ->where("{$tableName}.closed = ?", 0)
                    ->where("{$tableName}.approved = ?", 1)
                    ->where("{$tableName}.draft = ?", 0)
                    ->where('creation_date <=  ?', date('Y-m-d H:i:s')); 
            // Create new array filtering out private listings
            $i = 0;
            foreach ($this->fetchAll($listing_select) as $listing) {  
                if ($listing->isOwner($viewer) || Engine_Api::_()->authorization()->isAllowed($listing, $viewer, 'view')) {
                    $listings_ids[$i++] = $listing->listing_id;
                }
            } 
            // Try to save to cache
            if (empty($listings_ids))
                $listings_ids = array(0);
            if (APPLICATION_ENV == 'development') {
                Zend_Registry::set($cacheName, $listings_ids);
            } else {
                $cache->save($listings_ids, $cacheName);
            }
        }
        return $listings_ids;
    }

  public function getSitereviewsPaginator($params = array(), $customParams = null) {

    $paginator = Zend_Paginator::factory($this->getSitereviewsSelect($params, $customParams));
    if (!empty($params['page'])) {
      $paginator->setCurrentPageNumber($params['page']);
    }

    if (!empty($params['limit'])) {
      $paginator->setItemCountPerPage($params['limit']);
    }

    return $paginator;
  }

  //GET SITEREVIEW SELECT QUERY
  public function getSitereviewsSelect($params = array(), $customParams = null) {

    //GET LISTING TABLE NAME
    $sitereviewTableName = $this->info('name');
    $tempSelect = array();
    global $sitereviewSelectQuery;

    //GET TAGMAP TABLE NAME
    $tagMapTableName = Engine_Api::_()->getDbtable('TagMaps', 'core')->info('name');

    //GET SEARCH TABLE
    $searchTable = Engine_Api::_()->fields()->getTable('sitereview_listing', 'search')->info('name');

    //GET LOCATION TABLE
    $locationTable = Engine_Api::_()->getDbtable('locations', 'sitereview');
    $locationTableName = $locationTable->info('name');

    //GET API
    $settings = Engine_Api::_()->getApi('settings', 'core');

    //MAKE QUERY
    $select = $this->select();

    $select = $select
            ->setIntegrityCheck(false)
            ->from($sitereviewTableName)
            //->joinLeft($locationTableName, "$sitereviewTableName.listing_id = $locationTableName.listing_id   ", array())
            ->group($sitereviewTableName . '.listing_id');

    if (isset($params['type']) && !empty($params['type'])) {
      $listingtype_id = (isset($params['listingtype_id']) && !empty($params['listingtype_id']) && $params['listingtype_id'] != -1) ? $params['listingtype_id'] : -1;
      if ($params['type'] == 'browse' || $params['type'] == 'home') {
        $select = $select
                ->where($sitereviewTableName . '.approved = ?', '1')
                ->where($sitereviewTableName . '.draft = ?', '0')
                ->where($sitereviewTableName . '.creation_date <= ?', date('Y-m-d H:i:s'));
        $showExpiry = (isset($params['show']) && $params['show'] == 'only_expiry') ? 1 : 0;
        $select = $this->expirySQL($select, $listingtype_id, $showExpiry);

        if ($params['type'] == 'browse' && isset($params['showClosed']) && !$params['showClosed']) {
          $select = $select->where($sitereviewTableName . '.closed = ?', '0');
        }
      } elseif ($params['type'] == 'browse_home_zero') {
        $select = $select
                ->where($sitereviewTableName . '.closed = ?', '0')
                ->where($sitereviewTableName . '.approved = ?', '1')
                ->where($sitereviewTableName . '.draft = ?', '0')
                ->where($sitereviewTableName . '.creation_date <= ?', date('Y-m-d H:i:s'));

        $select = $this->expirySQL($select, $listingtype_id);
      }
      if ($params['type'] != 'manage') {
        $select->where($sitereviewTableName . ".search = ?", 1);
      }
    }

    if (isset($customParams) && !empty($customParams)) {

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

      $searchParts = Engine_Api::_()->fields()->getSearchQuery('sitereview_listing', $customParams);
      foreach ($searchParts as $k => $v) {
        $select->where("`{$searchTable}`.{$k}", $v);
      }
    }

    $addGroupBy = 1;
    if (!isset($params['location']) && (isset($params['detactLocation']) && $params['detactLocation'] && isset($params['latitude']) && $params['latitude'] && isset($params['longitude']) && $params['longitude'] && isset($params['defaultLocationDistance']) && $params['defaultLocationDistance'])) {

      $locationsTable = Engine_Api::_()->getDbtable('locations', 'sitereview');
      $locationTableName = $locationsTable->info('name');
      $radius = $params['defaultLocationDistance']; //in miles
      $latitude = $params['latitude'];
      $longitude = $params['longitude'];
      $flage = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proximity.search.kilometer', 0);
      if (!empty($flage)) {
        $radius = $radius * (0.621371192);
      }
    //  $latitudeRadians = deg2rad($latitude);
      $latitudeSin = "sin(radians($latitude))"; //sin($latitudeRadians);
      $latitudeCos = "cos(radians($latitude))";// cos($latitudeRadians);

      $select->join($locationTableName, "$sitereviewTableName.listing_id = $locationTableName.listing_id", array("(degrees(acos($latitudeSin * sin(radians($locationTableName.latitude)) + $latitudeCos * cos(radians($locationTableName.latitude)) * cos(radians($longitude - $locationTableName.longitude)))) * 69.172) AS distance", $locationTableName.'.location AS locationName'));
      $sqlstring = "(degrees(acos($latitudeSin * sin(radians($locationTableName.latitude)) + $latitudeCos * cos(radians($locationTableName.latitude)) * cos(radians($longitude - $locationTableName.longitude)))) * 69.172 <= " . "'" . $radius . "'";
      $sqlstring .= ")";
      $select->where($sqlstring);
      $select->order("distance");
      $select->group("$sitereviewTableName.listing_id");
      $addGroupBy = 0;
    }    

    if (isset($params['sitereview_street']) && !empty($params['sitereview_street']) || isset($params['sitereview_city']) && !empty($params['sitereview_city']) || isset($params['sitereview_state']) && !empty($params['sitereview_state']) || isset($params['sitereview_country']) && !empty($params['sitereview_country'])) {
      $select->join($locationTableName, "$sitereviewTableName.listing_id = $locationTableName.listing_id   ", null);
    }

    if (isset($params['sitereview_street']) && !empty($params['sitereview_street'])) {
      $select->where($locationTableName . '.address   LIKE ? ', '%' . $params['sitereview_street'] . '%');
    } if (isset($params['sitereview_city']) && !empty($params['sitereview_city'])) {
      $select->where($locationTableName . '.city = ?', $params['sitereview_city']);
    } if (isset($params['sitereview_state']) && !empty($params['sitereview_state'])) {
      $select->where($locationTableName . '.state = ?', $params['sitereview_state']);
    } if (isset($params['sitereview_country']) && !empty($params['sitereview_country'])) {
      $select->where($locationTableName . '.country = ?', $params['sitereview_country']);
    }

    if ((isset($params['location']) && !empty($params['location'])) || (!empty($params['Latitude']) && !empty($params['Longitude']))) {
      $enable = $settings->getSetting('sitereview.proximitysearch', 1);
      if (isset($params['locationmiles']) && (!empty($params['locationmiles']) && !empty($enable))) {
        $longitude = 0;
        $latitude = 0;
        $selectLocQuery = $locationTable->select()->where('location = ?', $params['location']);
        $locationValue = $locationTable->fetchRow($selectLocQuery);

        //check for zip code in location search.
        if (empty($params['Latitude']) && empty($params['Longitude'])) {
          if (empty($locationValue)) {
            $locationResults = Engine_Api::_()->getApi('geoLocation', 'seaocore')->getLatLong(array('location' => $params['location'], 'module' => 'Multiple Listing Types'));
            if(!empty($locationResults['latitude']) && !empty($locationResults['longitude'])) {
                $latitude = $locationResults['latitude'];
                $longitude = $locationResults['longitude'];
            }
          } else {
            $latitude = (float) $locationValue->latitude;
            $longitude = (float) $locationValue->longitude;
          }
        } else {
          $latitude = (float) $params['Latitude'];
          $longitude = (float) $params['Longitude'];
        }

        $radius = $params['locationmiles'];

        $flage = $settings->getSetting('sitereview.proximity.search.kilometer', 0);
        if (!empty($flage)) {
          $radius = $radius * (0.621371192);
        }
        //  $latitudeRadians = deg2rad($latitude);
      $latitudeSin = "sin(radians($latitude))"; //sin($latitudeRadians);
      $latitudeCos = "cos(radians($latitude))";// cos($latitudeRadians);
        $select->join($locationTableName, "$sitereviewTableName.listing_id = $locationTableName.listing_id", array("(degrees(acos($latitudeSin * sin(radians($locationTableName.latitude)) + $latitudeCos * cos(radians($locationTableName.latitude)) * cos(radians($longitude - $locationTableName.longitude)))) * 69.172) AS distance"));
        $sqlstring = "(degrees(acos($latitudeSin * sin(radians($locationTableName.latitude)) + $latitudeCos * cos(radians($locationTableName.latitude)) * cos(radians($longitude - $locationTableName.longitude)))) * 69.172 <= " . "'" . $radius . "'";
        $sqlstring .= ")";
        $select->where($sqlstring);
        $select->order("distance");
      } else {
        $select->join($locationTableName, "$sitereviewTableName.listing_id = $locationTableName.listing_id", null);
        $select->where("`{$locationTableName}`.formatted_address LIKE ? or `{$locationTableName}`.location LIKE ? or `{$locationTableName}`.city LIKE ? or `{$locationTableName}`.state LIKE ?", "%" . $params['location'] . "%");
      }
    }

    if (isset($params['type']) && !empty($params['type']) && ($params['type'] == 'browse' || $params['type'] == 'home')) { 
      if($addGroupBy) { 
        $select = $this->getNetworkBaseSql($select, array('show' => $params['show']));
      }
      else {
        $select = $this->getNetworkBaseSql($select, array('not_groupBy' => 1, 'show' => $params['show']));
      } 
    }
    $api = Engine_Api::_()->sitereview();
    if (isset($params['price']['min']) && !empty($params['price']['min'])) {
      $select->where($sitereviewTableName . '.price >= ?', $api->getPriceWithCurrency($params['price']['min'], 1, 1));
    }

    if (isset($params['price']['max']) && !empty($params['price']['max'])) {
      $select->where($sitereviewTableName . '.price <= ?', $api->getPriceWithCurrency($params['price']['max'], 1, 1));
    }

    if (!empty($params['user_id']) && is_numeric($params['user_id'])) {
      $select->where($sitereviewTableName . '.owner_id = ?', $params['user_id']);
    }

    if (!empty($params['user']) && $params['user'] instanceof User_Model_User) {
      $select->where($sitereviewTableName . '.owner_id = ?', $params['user_id']->getIdentity());
    }

    if (!empty($params['users'])) {
      $str = (string) ( is_array($params['users']) ? "'" . join("', '", $params['users']) . "'" : $params['users'] );
      $select->where($sitereviewTableName . '.owner_id in (?)', new Zend_Db_Expr($str));
    }

    if (empty($params['users']) && isset($params['show']) && $params['show'] == '2') {
      $select->where($sitereviewTableName . '.owner_id = ?', '0');
    }

    if ((isset($params['show']) && $params['show'] == "4")) {
      $likeTableName = Engine_Api::_()->getDbtable('likes', 'core')->info('name');
      $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
      $select->setIntegrityCheck(false)
              ->join($likeTableName, "$likeTableName.resource_id = $sitereviewTableName.listing_id")
              ->where($likeTableName . '.poster_type = ?', 'user')
              ->where($likeTableName . '.poster_id = ?', $viewer_id)
              ->where($likeTableName . '.resource_type = ?', 'sitereview_listing');
    }

    if (!empty($params['tag_id'])) {
      $select
              ->setIntegrityCheck(false)
              ->joinLeft($tagMapTableName, "$tagMapTableName.resource_id = $sitereviewTableName.listing_id", array('tagmap_id', 'resource_type', 'resource_id', 'tag_id'))
              ->where($tagMapTableName . '.resource_type = ?', 'sitereview_listing')
              ->where($tagMapTableName . '.tag_id = ?', $params['tag_id']);
    }

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

    if (isset($params['closed']) && $params['closed'] != "") {
      $select->where($sitereviewTableName . '.closed = ?', $params['closed']);
    }

    // Could we use the search indexer for this?
    if (!empty($params['search'])) {

      $tagName = Engine_Api::_()->getDbtable('Tags', 'core')->info('name');
      $select
              ->setIntegrityCheck(false)
              ->joinLeft($tagMapTableName, "$tagMapTableName.resource_id = $sitereviewTableName.listing_id and " . $tagMapTableName . ".resource_type = 'sitereview_listing'", array('tagmap_id', 'resource_type', 'resource_id', 'tag_id'))
              ->joinLeft($tagName, "$tagName.tag_id = $tagMapTableName.tag_id");

      $select->where($sitereviewTableName . ".title LIKE ? OR " . $sitereviewTableName . ".body LIKE ? OR " . $tagName . ".text LIKE ? ", '%' . $params['search'] . '%');
    }

    if (!empty($params['start_date'])) {
      $select->where($sitereviewTableName . ".creation_date > ?", date('Y-m-d', $params['start_date']));
    }

    if (!empty($params['end_date'])) {
      $select->where($sitereviewTableName . ".creation_date < ?", date('Y-m-d', $params['end_date']));
    }

    if (!empty($params['has_photo'])) {
      $select->where($sitereviewTableName . ".photo_id > ?", 0);
    }

    if (!empty($params['has_review'])) {
      $has_review = $params['has_review'];
      $select->where($sitereviewTableName . ".$has_review > ?", 0);
    }

    if(isset($params['most_rated'])) {
      $select->order($sitereviewTableName . '.' . 'rating_avg'. ' DESC');
    }
      
    if (!empty($params['orderby']) && $params['orderby'] == "title") {
      $select->order($sitereviewTableName . '.' . $params['orderby']);
    } else if (!empty($params['orderby']) && $params['orderby'] == "fespfe") {
      $select->order($sitereviewTableName . '.sponsored' . ' DESC')
              ->order($sitereviewTableName . '.featured' . ' DESC');
    } else if (!empty($params['orderby']) && $params['orderby'] == "spfesp") {
      $select->order($sitereviewTableName . '.featured' . ' DESC')
              ->order($sitereviewTableName . '.sponsored' . ' DESC');
    } else if (!empty($params['orderby']) && $params['orderby'] != 'creation_date') {
      $select->order($sitereviewTableName . '.' . $params['orderby'] . ' DESC');
    }
    $select->order($sitereviewTableName . '.creation_date DESC');
    $getResult = !empty($sitereviewSelectQuery)? $select: $tempSelect;
    return $getResult;
  }

  /**
   * Get listing count based on category
   *
   * @param int $id
   * @param string $column_name
   * @param int $authorization
   * @return listing count
   */
  public function getListingsCount($id, $column_name, $listingtype_id = 0, $foruser = null) {

    //MAKE QUERY
    $select = $this->select()
            ->from($this->info('name'), array('COUNT(*) AS count'));

    if (!empty($foruser)) {
      $select->where('closed = ?', 0)
              ->where('approved = ?', 1)
              ->where('draft = ?', 0)
              ->where('creation_date <=  ?', date('Y-m-d H:i:s'))
              ->where('search = ?', 1);
      $select = $this->expirySQL($select, $listingtype_id);
      $select = $this->getNetworkBaseSql($select, array('not_groupBy' => 1));
    }

    if (!empty($column_name) && !empty($id)) {
      $select->where("$column_name = ?", $id);
    }

    if (!empty($listingtype_id) && $listingtype_id != -1) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }

    $totalListings = $select->query()->fetchColumn();

    //RETURN LISTINGS COUNT
    return $totalListings;
  }

  /**
   * Has Listings 
   * @param int $listingtype_id : Listing Type Id
   */
  public function hasListings($listingtype_id =0) {

    $select = $this->select()
            ->from($this->info('name'), 'listing_id')
            ->where('closed = ?', 0)
            ->where('approved = ?', 1)
            ->where('draft = ?', 0)
            ->where('creation_date <=  ?', date('Y-m-d H:i:s'))
            ->where('search = ?', 1);
    $select = $this->expirySQL($select, $listingtype_id);

    if (!empty($listingtype_id) && $listingtype_id != -1) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }

    return $select->query()->fetchColumn();
  }

  /**
   * Get listings based on category
   * @param string $title : search text
   * @param int $category_id : category id
   * @param char $popularity : result sorting based on views, reviews, likes, comments
   * @param char $interval : time interval
   * @param string $sqlTimeStr : Time durating string for where clause 
   */
  public function listingsBySettings($params = array()) {

    $groupBy = 1;
    $listingTableName = $this->info('name');

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
    
    $select = $this->select()->setIntegrityCheck(false);

    if($popularity != 'end_date') {
      if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
        $select->from($listingTableName, array('listing_id', 'listingtype_id', 'title', 'photo_id', 'owner_id', 'category_id', 'view_count', 'review_count', 'like_count', 'comment_count', 'rating_avg', 'rating_editor', 'rating_users', 'sponsored', 'featured', 'newlabel', 'creation_date', 'body', 'end_date', 'location', 'price','package_id'));
      }
      else {
        $select->from($listingTableName, array('listing_id', 'listingtype_id', 'title', 'photo_id', 'owner_id', 'category_id', 'view_count', 'review_count', 'like_count', 'comment_count', 'rating_avg', 'rating_editor', 'rating_users', 'sponsored', 'featured', 'newlabel', 'creation_date', 'body', 'end_date', 'location', 'price'));
      }
    }

    if($popularity == 'end_date') {
      
      $end_date = ' CASE ';
      $where = ' CASE ';
      $listingTypeIdsArray = array();
      
      $listingTypeId = -1;
      if(isset($params['listingtype_id'])) {
        $listingTypeId = $params['listingtype_id'];
      }
      
      $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes($listingTypeId, array('expiry' => 'nonZero'));
      foreach($listingTypes as $listingType) {
        if($listingType->expiry == 1) {
          $end_date .= " WHEN $listingTableName.listingtype_id = $listingType->listingtype_id THEN end_date ";
          
          $where .= " WHEN $listingTableName.listingtype_id = $listingType->listingtype_id THEN $listingTableName.end_date >= '$current_time'";
        }
        elseif($listingType->expiry == 2) {
          $duration = $listingType->admin_expiry_duration;
          $interval_type = $duration[1];
          $interval_type = empty($interval_type) ? 1 : $interval_type;
          $interval_value = $duration[0];
          $interval_value = empty($interval_value) ? 1 : $interval_value;
          
          $approveDate = Engine_Api::_()->sitereview()->adminExpiryDuration($listingType->listingtype_id);

          $end_date .= " WHEN $listingTableName.listingtype_id = $listingType->listingtype_id THEN  DATE_ADD(approved_date, INTERVAL $interval_value $interval_type) ";        
          
          $where .= " WHEN $listingTableName.listingtype_id = $listingType->listingtype_id THEN  DATE_ADD(approved_date, INTERVAL $interval_value $interval_type) >= '$current_time'";  
          
        }
        $listingTypeIdsArray[] = $listingType->listingtype_id;
      }
      
      if(Count($listingTypeIdsArray) > 0) {
        $select->where("$listingTableName.listingtype_id IN (?)", (array) $listingTypeIdsArray);
      }
      
      $end_date .= ' END  ';
      $where .= ' END ';

      $end_date = new Zend_Db_Expr($end_date);
      $where = new Zend_Db_Expr($where);
      
      if(Count($listingTypes) > 0) {
        $columnArray = array('listing_id', 'listingtype_id', 'title', 'photo_id', 'owner_id', 'category_id', 'view_count', 'review_count', 'like_count', 'comment_count', 'rating_avg', 'rating_editor', 'rating_users', 'sponsored', 'featured', 'newlabel', 'creation_date', 'body', 'approved_date','end_date' => $end_date);
        $select->from($listingTableName, $columnArray);      

        $select->where($where);
        $select->order("end_date ASC");
      }
    }
    elseif ($interval != 'overall' && $popularity == 'review_count') {

      $popularityTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
      $popularityTableName = $popularityTable->info('name');
      $select = $select->joinLeft($popularityTableName, "($popularityTableName.resource_id = $listingTableName.listing_id and $reviewTableName .resource_type ='sitereview_listing')", array("COUNT(review_id) as total_count"))
              ->where($popularityTableName . "$sqlTimeStr  or " . $popularityTableName . '.creation_date is null')
              ->order("total_count DESC");
    } elseif ($interval != 'overall' && ($popularity == 'rating_avg' || $popularity == 'rating_editor' || $popularity == 'rating_users')) {

      if ($interval == 'week') {
        $time_duration = date('Y-m-d H:i:s', strtotime('-7 days'));
        $sqlTimeStr = ".modified_date BETWEEN " . "'" . $time_duration . "'" . " AND " . "'" . $current_time . "'";
      } elseif ($interval == 'month') {
        $time_duration = date('Y-m-d H:i:s', strtotime('-1 months'));
        $sqlTimeStr = ".modified_date BETWEEN " . "'" . $time_duration . "'" . " AND " . "'" . $current_time . "'" . "";
      }

      $ratingTable = Engine_Api::_()->getDbTable('ratings', 'sitereview');
      $ratingTableName = $ratingTable->info('name');

      $popularityTable = Engine_Api::_()->getDbtable('reviews', 'sitereview');
      $popularityTableName = $popularityTable->info('name');
      $select = $select->joinLeft($popularityTableName, $popularityTableName . '.resource_id = ' . $listingTableName . '.listing_id', array(""))
              ->join($ratingTableName, $ratingTableName . '.review_id = ' . $popularityTableName . '.review_id')
              ->where($popularityTableName . '.resource_type = ?', 'sitereview_listing')
              ->where($ratingTableName . '.ratingparam_id = ?', 0);

      if ($popularity == 'rating_editor') {
        $select->where("$popularityTableName.type = ?", 'editor');
      } elseif ($popularity == 'rating_users') {
        $select->where("$popularityTableName.type = ?", 'user')
                ->orWhere("$popularityTableName.type = ?", 'visitor');
      }

      $select->where($popularityTableName . "$sqlTimeStr  or " . $popularityTableName . '.modified_date is null');
      $select->order("$listingTableName.$popularity DESC");
    } elseif ($interval != 'overall' && $popularity == 'like_count') {

      $popularityTable = Engine_Api::_()->getDbtable('likes', 'core');
      $popularityTableName = $popularityTable->info('name');

      $select = $select->joinLeft($popularityTableName, $popularityTableName . '.resource_id = ' . $listingTableName . '.listing_id', array("COUNT(like_id) as total_count"))
              ->where($popularityTableName . "$sqlTimeStr  or " . $popularityTableName . '.creation_date is null')
              ->order("total_count DESC");
    } elseif ($interval != 'overall' && $popularity == 'comment_count') {

      $popularityTable = Engine_Api::_()->getDbtable('comments', 'core');
      $popularityTableName = $popularityTable->info('name');

      $select = $select->joinLeft($popularityTableName, $popularityTableName . '.resource_id = ' . $listingTableName . '.listing_id', array("COUNT(comment_id) as total_count"))
              ->where($popularityTableName . "$sqlTimeStr  or " . $popularityTableName . '.creation_date is null')
              ->order("total_count DESC");
    } elseif ($popularity == 'most_discussed') {

      $popularityTable = Engine_Api::_()->getDbtable('posts', 'sitereview');
      $popularityTableName = $popularityTable->info('name');
      $select = $select->joinLeft($popularityTableName, $popularityTableName . '.listing_id = ' . $listingTableName . '.listing_id', array("COUNT(post_id) as total_count"))
              ->order("total_count DESC");

      if ($interval != 'overall') {
        $select->where($popularityTableName . "$sqlTimeStr  or " . $popularityTableName . '.creation_date is null');
      }
    } elseif ($popularity == 'view_count' || $popularity == 'listing_id' || $popularity == 'modified_date' || $popularity == 'creation_date') {
      $select->order("$listingTableName.$popularity DESC");
    } elseif ($interval == 'overall' && ($popularity == 'review_count' || $popularity == 'like_count' || $popularity == 'comment_count' || $popularity == 'rating_avg' || $popularity == 'rating_editor' || $popularity == 'rating_users')) {
      $select->order("$listingTableName.$popularity DESC");
    }

    $listingtype_id = (isset($params['listingtype_id']) && !empty($params['listingtype_id']) && $params['listingtype_id'] != -1) ? $params['listingtype_id'] : -1;
    $select = $this->expirySQL($select, $listingtype_id);
    $select->group($listingTableName . '.listing_id');
    $select->where($listingTableName . '.closed = ?', '0')
            ->where($listingTableName . '.approved = ?', '1')
            ->where($listingTableName . '.search = ?', '1')
            ->where($listingTableName . '.draft = ?', '0')
            ->where($listingTableName . '.creation_date <= ?', date('Y-m-d H:i:s'));
    
    if (isset($params['detactLocation']) && $params['detactLocation'] && isset($params['latitude']) && $params['latitude'] && isset($params['longitude']) && $params['longitude'] && isset($params['defaultLocationDistance']) && $params['defaultLocationDistance']) {
      $locationsTable = Engine_Api::_()->getDbtable('locations', 'sitereview');
      $locationTableName = $locationsTable->info('name');
      $radius = $params['defaultLocationDistance']; //in miles
      $latitude = $params['latitude'];
      $longitude = $params['longitude'];
      $flage = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proximity.search.kilometer', 0);
      if (!empty($flage)) {
        $radius = $radius * (0.621371192);
      }
     //  $latitudeRadians = deg2rad($latitude);
      $latitudeSin = "sin(radians($latitude))"; //sin($latitudeRadians);
      $latitudeCos = "cos(radians($latitude))";// cos($latitudeRadians);

      $select->join($locationTableName, "$listingTableName.listing_id = $locationTableName.listing_id", array("(degrees(acos($latitudeSin * sin(radians($locationTableName.latitude)) + $latitudeCos * cos(radians($locationTableName.latitude)) * cos(radians($longitude - $locationTableName.longitude)))) * 69.172) AS distance", $locationTableName.'.location AS locationName'));
      $sqlstring = "(degrees(acos($latitudeSin * sin(radians($locationTableName.latitude)) + $latitudeCos * cos(radians($locationTableName.latitude)) * cos(radians($longitude - $locationTableName.longitude)))) * 69.172 <= " . "'" . $radius . "'";
      $sqlstring .= ")";
      $select->where($sqlstring);
      $select->order("distance");
      //$select->group("$listingTableName.listing_id");
    }        

    if (isset($params['featured']) && !empty($params['featured'])) {
      $select->where('featured = ?', 1);
    }

    if (isset($params['sponsored']) && !empty($params['sponsored'])) {
      $select->where('sponsored = ?', 1);
    }

    if (isset($params['newlabel']) && !empty($params['newlabel'])) {
      $select->where('newlabel = ?', 1);
    }
    
    if (isset($params['sponsored_or_featured']) && !empty($params['sponsored_or_featured'])) {
      $select->where("$listingTableName.featured = 1 OR $listingTableName.sponsored = 1");
    }      


    if ( isset($params['createdbyfriends']) && !empty($params['createdbyfriends']) && !empty($params['users'])) {
      $str = (string) ( is_array($params['users']) ? "'" . join("', '", $params['users']) . "'" : $params['users'] );
      $select->where($listingTableName . '.owner_id in (?)', new Zend_Db_Expr($str));
    }  

    if (isset($params['createdbyfriends']) && $params['createdbyfriends'] == '2' && empty($params['users'])) {
      $select->where($listingTableName . '.owner_id = ?', '0');
    }
 
    if (isset($params['listingtype_id']) && !empty($params['listingtype_id']) && $params['listingtype_id'] != -1) {
      $select->where($listingTableName . '.listingtype_id = ?', $params['listingtype_id']);
    }

    if (isset($params['category_id']) && !empty($params['category_id'])) {
      $select->where($listingTableName . '.category_id = ?', $params['category_id']);
    }

    if (isset($params['subcategory_id']) && !empty($params['subcategory_id'])) {
      $select->where($listingTableName . '.subcategory_id = ?', $params['subcategory_id']);
    }

    if (isset($params['subsubcategory_id']) && !empty($params['subsubcategory_id'])) {
      $select->where($listingTableName . '.subsubcategory_id = ?', $params['subsubcategory_id']);
    }

    if (isset($params['popularity']) && !empty($params['popularity']) && $params['popularity'] != 'creation_date' && $params['popularity'] != 'creation_date' && $params['popularity'] != 'random') {
      $select->order($listingTableName . ".creation_date DESC");
    }

    if (isset($params['popularity']) && $params['popularity'] == 'random') {
      $select->order('RAND() DESC ');
    }

    //Start Network work
    $select = $this->getNetworkBaseSql($select, array('not_groupBy' => $groupBy));

    //End Network work
    if (isset($params['paginator']) && !empty($params['paginator'])) {
      $paginator = Zend_Paginator::factory($select);
      if (isset($params['page']) && !empty($params['page'])) {
        $paginator->setCurrentPageNumber($params['page']);
      }

      if (isset($params['limit']) && !empty($params['limit'])) {
        $paginator->setItemCountPerPage($params['limit']);
      } 
      return $paginator;
    }
    if (isset($params['limit']) && !empty($params['limit'])) {
      $select->limit($params['limit']);
    }

    return $this->fetchAll($select);
  }

  /**
   * Get pages to add as item of the day
   * @param string $title : search text
   * @param int $limit : result limit
   */
  public function getDayItems($title, $limit = 10, $listingtype_id = 0) {

    //MAKE QUERY
    $select = $this->select()
            ->from($this->info('name'), array('listing_id', 'listingtype_id', 'owner_id', 'title', 'photo_id'))
            ->where('title  LIKE ? ', '%' . $title . '%')
            ->where('closed = ?', '0')
            ->where('approved = ?', '1')
            ->where('draft = ?', '0')
            ->where('creation_date <= ?', date('Y-m-d H:i:s'))
            ->where('search = ?', '1')
            ->order('title ASC')
            ->limit($limit);
    $select = $this->expirySQL($select, $listingtype_id);
    if ($listingtype_id > 0) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }

    //RETURN RESULTS
    return $this->fetchAll($select);
  }

  /**
   * Return listing data
   *
   * @param array params
   * @return Zend_Db_Table_Select
   */
  public function widgetListingsData($params = array()) {

    //GET TABLE NAME
    $tableListingName = $this->info('name');

    //MAKE QUERY
    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
        $select = $this->select()->from($tableListingName, array("listing_id", 'listingtype_id', "title", "category_id", "subcategory_id", "subsubcategory_id", "view_count", "comment_count", "like_count", "rating_avg", "rating_editor", "rating_users", "review_count", "owner_id", "photo_id", "price", 'featured', 'sponsored','newlabel', 'package_id'));
    }
    else {
        $select = $this->select()->from($tableListingName, array("listing_id", 'listingtype_id', "title", "category_id", "subcategory_id", "subsubcategory_id", "view_count", "comment_count", "like_count", "rating_avg", "rating_editor", "rating_users", "review_count", "owner_id", "photo_id", "price", 'featured', 'sponsored','newlabel'));
    }

    //SELECT ONLY AUTHENTICATE LISTINGS
    $select = $select->where('approved = ?', 1)
            ->where('draft = ?', 0)
            ->where($tableListingName.'.creation_date <= ?', date('Y-m-d H:i:s'))
            ->where('closed = ?', 0)
            ->where('search = ?', 1);
    $listingtype_id = (isset($params['listingtype_id']) && !empty($params['listingtype_id']) && $params['listingtype_id'] != -1) ? $params['listingtype_id'] : -1;
    $select = $this->expirySQL($select, $listingtype_id);

    if (isset($params['zero_count']) && !empty($params['zero_count'])) {
      $select->where($params['zero_count'] . ' != ?', 0);
    }

    if (isset($params['owner_id']) && !empty($params['owner_id'])) {
      $select->where('owner_id = ?', $params['owner_id']);
    }

    if (isset($params['listing_id']) && !empty($params['listing_id'])) {
      $select->where('listing_id != ?', $params['listing_id']);
    }

    if (isset($params['listingtype_id']) && !empty($params['listingtype_id']) && $params['listingtype_id'] != -1) {
      $select->where('listingtype_id = ?', $params['listingtype_id']);
    }

    if (isset($params['featured']) && !empty($params['featured'])) {
      $select->where('featured = ?', 1);
    }

    if ((isset($params['category_id']) && !empty($params['category_id']))) {
      $select->where('category_id = ?', $params['category_id']);
    }

    if (isset($params['tags']) && !empty($params['tags'])) {

      //GET TAG MAPS TABLE NAME
      $tableTagmapsName = Engine_Api::_()->getDbtable('TagMaps', 'core')->info('name');

      $select->setIntegrityCheck(false)
              ->joinLeft($tableTagmapsName, "$tableTagmapsName.resource_id = $tableListingName.listing_id", array(''))
              ->where($tableTagmapsName . '.resource_type = ?', 'sitereview_listing');

      foreach ($params['tags'] as $tag_id) {
        $tagSqlArray[] = "$tableTagmapsName.tag_id = $tag_id";
      }
      $select->where("(" . join(") or (", $tagSqlArray) . ")");
    }

    if (isset($params['orderby']) && !empty($params['orderby'])) {
      $select->order($params['orderby']);
    }

    $select->order($tableListingName.'.creation_date DESC');

    if (isset($params['limit']) && !empty($params['limit'])) {
      $select->limit($params['limit']);
    }
    
    //Start Network work
    $select = $this->getNetworkBaseSql($select, array('not_groupBy' => 1));
    //End Network work
    $select->group('listing_id');

    if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
      return Zend_Paginator::factory($select);
    }

    return $this->fetchAll($select);
  }

  public function getMappedSitereview($category_id) {

    //RETURN IF CATEGORY ID IS NULL
    if (empty($category_id)) {
      return null;
    }

    //MAKE QUERY
    $select = $this->select()
            ->from($this->info('name'), 'listing_id')
            ->where("category_id = $category_id OR subcategory_id = $category_id OR subsubcategory_id = $category_id");

    //GET DATA
    $categoryData = $this->fetchAll($select);

    if (!empty($categoryData)) {
      return $categoryData->toArray();
    }

    return null;
  }

  /**
   * Get Popular location base on city and state
   *
   */
  public function getPopularLocation($params = null) {

    //GET SITEREVIEW TABLE NAME
    $sitereviewTableName = $this->info('name');

    //GET LOCATION TABLE
    $locationTable = Engine_Api::_()->getDbtable('locations', 'sitereview');
    $locationTableName = $locationTable->info('name');

    //MAKE QUERY
    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from($sitereviewTableName, null)
            ->join($locationTableName, "$sitereviewTableName.listing_id = $locationTableName.listing_id", array("city", "count(city) as count_location", "state", "count(state) as count_location_state"))
            ->where($sitereviewTableName . '.approved = ?', '1')
            ->where($sitereviewTableName . '.draft = ?', '0')
            ->where($sitereviewTableName . '.creation_date <= ?', date('Y-m-d H:i:s'))
            ->where($sitereviewTableName . ".search = ?", 1)
            ->where($sitereviewTableName . '.closed = ?', '0')
            ->group("city")
            ->group("state")
            ->order("count_location DESC");
    $listingtype_id = (isset($params['listingtype_id']) && !empty($params['listingtype_id']) && $params['listingtype_id'] != -1) ? $params['listingtype_id'] : -1;
    $select = $this->expirySQL($select, $listingtype_id);
    if (isset($params['limit']) && !empty($params['limit'])) {
      $select->limit($params['limit']);
    }

    if (isset($params['category_id']) && !empty($params['category_id'])) {
      $select->where($sitereviewTableName . 'category_id = ?', $params['category_id']);
    }

    if (isset($params['subcategory_id']) && !empty($params['subcategory_id'])) {
      $select->where($sitereviewTableName . 'subcategory_id = ?', $params['subcategory_id']);
    }

    if (isset($params['subsubcategory_id']) && !empty($params['subsubcategory_id'])) {
      $select->where($sitereviewTableName . 'subsubcategory_id = ?', $params['subsubcategory_id']);
    }

    if (!empty($params['listingtype_id']) && $params['listingtype_id'] != -1) {
      $select->where($sitereviewTableName . '.listingtype_id = ?', $params['listingtype_id']);
    }

    $select = $this->getNetworkBaseSql($select, array('not_groupBy' => 1));

    //RETURN RESULTS
    return $this->fetchAll($select);
  }

  public function getNetworkBaseSql($select, $params = array()) {

    if (empty($select))
      return;

    //GET SITEREVIEW TABLE NAME
    $sitereviewTableName = $this->info('name');

    //START NETWORK WORK
    $enableNetwork = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.network', 0);
    if (!empty($enableNetwork) || (isset($params['browse_network']) && !empty($params['browse_network'])) || (isset($params['show']) && $params['show'] == "3")) {
      $viewer = Engine_Api::_()->user()->getViewer();
      $networkMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
      if (!Zend_Registry::isRegistered('viewerNetworksIdsSR')) {
        $viewerNetworkIds = $networkMembershipTable->getMembershipsOfIds($viewer);
        Zend_Registry::set('viewerNetworksIdsSR', $viewerNetworkIds);
      } else {
        $viewerNetworkIds = Zend_Registry::get('viewerNetworksIdsSR');
      }

      if (!Engine_Api::_()->sitereview()->listBaseNetworkEnable()) {
        if (!empty($viewerNetworkIds)) {
          if (isset($params['setIntegrity']) && !empty($params['setIntegrity'])) {
            $select->setIntegrityCheck(false)
                    ->from($sitereviewTableName);
          }
          $networkMembershipName = $networkMembershipTable->info('name');
          $select
                  ->join($networkMembershipName, "`{$sitereviewTableName}`.owner_id = `{$networkMembershipName}`.user_id  ", null)
                  ->where("`{$networkMembershipName}`.`resource_id`  IN (?) ", (array) $viewerNetworkIds);
          if (!isset($params['not_groupBy']) || empty($params['not_groupBy'])) {
            $select->group($sitereviewTableName . ".listing_id");
          }
        }
      } else {
        // $viewerNetwork = $networkMembershipTable->getMembershipsOfInfo($viewer);

        $str = array();
        $columnName = "`{$sitereviewTableName}`.networks_privacy";
        foreach ($viewerNetworkIds as $networkId) {
          $str[] = '\'%"' . $networkId . '"%\'';
        }
        if (!empty($str)) {
          $likeNetworkVale = (string) ( join(" or $columnName  LIKE ", $str) );
          $select->where($columnName . ' LIKE ' . $likeNetworkVale . ' or ' . $columnName . " IS NULL");
        } else {
          $select->where($columnName . " IS NULL");
        }
      }
      //END NETWORK WORK
    } else { 
      $select = Engine_Api::_()->sitereview()->addPrivacyListingsSQl($select, $this->info('name')); 
    } 
    //RETURN QUERY
    return $select;
  }

  public function recentlyViewed($params = array()) {

    //GET VIEWER ID
    $viewer_id = $params['viewer_id'];

    //GET VIEWER TABLE
    $viewTable = Engine_Api::_()->getDbtable('vieweds', 'sitereview');
    $viewTableName = $viewTable->info('name');

    //GET SITEREVIEW TABLE NAME
    $sitereviewTableName = $this->info('name');
    
    // QUERY TO FETCH THE RESULTS ACCRODING TO THE MAX DATE
    $subQuery = $viewTable->select()
                ->from($viewTableName, array('listing_id', 'max(date) as date'))
                ->group($viewTableName . '.listing_id');


    $subSubQuery = $viewTable->select()
                ->from(array('t1' => $viewTableName), array('listing_id', 'date', 'viewer_id'))
                ->joinInner(array('t2' => $subQuery), 't1.listing_id = t2.listing_id and t1.date = t2.date', array(''))
                ->group('t1.listing_id');
    
    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
        $fetchColumns = array('listing_id', 'listingtype_id', 'title', 'owner_id', 'photo_id', 'review_count', 'view_count', 'like_count', 'comment_count', 'category_id', 'rating_avg', 'rating_editor', 'rating_users', 'featured', 'sponsored','newlabel', 'package_id');
    }
    else {
        $fetchColumns = array('listing_id', 'listingtype_id', 'title', 'owner_id', 'photo_id', 'review_count', 'view_count', 'like_count', 'comment_count', 'category_id', 'rating_avg', 'rating_editor', 'rating_users', 'featured', 'sponsored','newlabel');
    }

    //MAKE QUERY
    $select = $this->select()
            ->setIntegrityCheck(false)
            ->from(array($sitereviewTableName => $sitereviewTableName), $fetchColumns)
            ->joinInner(array($viewTableName => $subSubQuery), "$sitereviewTableName . listing_id = $viewTableName . listing_id", array('viewer_id'))
            ->group($viewTableName. '.listing_id');
    
    $select = $this->getNetworkBaseSql($select);
    if ($params['show'] == 1) {

      //GET MEMBERSHIP TABLE
      $membership_table = Engine_Api::_()->getDbtable('membership', 'user');
      $ids = $membership_table->getMembershipsOfIds(Engine_Api::_()->user()->getViewer());
      if (empty($ids))
        $ids[] = -1;
      $select->where($viewTableName . '.viewer_id  In(?)', (array) $ids);
//      $select->joinInner($member_name, "$member_name . user_id = $viewTableName . viewer_id", NULL)
//              ->where($member_name . '.resource_id = ?', $viewer_id)
//              ->where($viewTableName . '.viewer_id <> ?', $viewer_id)
//              ->where($member_name . '.active = ?', 1);
    } else if($params['show'] == 0){
      $select->where($viewTableName . '.viewer_id = ?', $viewer_id);
    }

    $select->order('date DESC');

    if (isset($params['featured']) && !empty($params['featured'])) {
      $select->where($sitereviewTableName . '.featured = ?', 1);
    }

    if (isset($params['sponsored']) && !empty($params['sponsored'])) {
      $select->where($sitereviewTableName . '.sponsored = ?', 1);
    }

    if (isset($params['newlabel']) && !empty($params['newlabel'])) {
      $select->where($sitereviewTableName . '.newlabel = ?', 1);
    }
    
    if (isset($params['sponsored_or_featured']) && !empty($params['sponsored_or_featured'])) {
      $select->where("$sitereviewTableName.featured = 1 OR $sitereviewTableName.sponsored = 1");
    }        

    if (isset($params['listingtype_id']) && !empty($params['listingtype_id']) && $params['listingtype_id'] != -1) {
      $select->where($sitereviewTableName . '.listingtype_id = ?', $params['listingtype_id']);
    }

    $select->where($sitereviewTableName . '.closed = ?', '0')
            ->where($sitereviewTableName . '.approved = ?', '1')
            ->where($sitereviewTableName . '.draft = ?', '0')
            ->where($sitereviewTableName . '.creation_date <= ?', date('Y-m-d H:i:s'))
            ->where($sitereviewTableName . ".search = ?", '1');
    $listingtype_id = (isset($params['listingtype_id']) && !empty($params['listingtype_id']) && $params['listingtype_id'] != -1) ? $params['listingtype_id'] : -1;
    $select = $this->expirySQL($select, $listingtype_id);

    if (isset($params['paginator']) && !empty($params['paginator'])) {
      return $paginator = Zend_Paginator::factory($select);
    }

    if (isset($params['limit']) && !empty($params['limit'])) {
      $select->limit($params['limit']);
    }

    if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
      return Zend_Paginator::factory($select);
    }

    //RETURN RESULTS
    return $this->fetchAll($select);
  }

  // get lising according to requerment
  public function getListing($sitereviewtype = '', $params = array()) {

    $limit = 10;
    $table = Engine_Api::_()->getDbtable('listings', 'sitereview');
    $sitereviewTableName = $table->info('name');

    $select = $table->select()
            ->where($sitereviewTableName . '.closed = ?', '0')
            ->where($sitereviewTableName . '.approved = ?', '1')
            ->where($sitereviewTableName . '.draft = ?', '0')
            ->where($sitereviewTableName . '.creation_date <= ?', date('Y-m-d H:i:s'))
            ->where($sitereviewTableName . ".search = ?", 1);
    $listingtype_id = (isset($params['listingtype_id']) && !empty($params['listingtype_id']) && $params['listingtype_id'] != -1) ? $params['listingtype_id'] : -1;
    $select = $this->expirySQL($select, $listingtype_id);
    
    if (isset($params['expiring_soon']) && !empty($params['expiring_soon']) || $sitereviewtype == 'expiring_soon') {
      
      $current_time = date("Y-m-d H:i:s");

      $end_date = ' CASE ';
      $where = ' CASE ';
      $listingTypeIdsArray = array();
      
      $listingTypeId = -1;
      if(isset($params['listingtype_id'])) {
        $listingTypeId = $params['listingtype_id'];
      }
      
      $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes($listingTypeId, array('expiry' => 'nonZero'));
      foreach($listingTypes as $listingType) {
        if($listingType->expiry == 1) {
          $end_date .= " WHEN $sitereviewTableName.listingtype_id = $listingType->listingtype_id THEN $sitereviewTableName.end_date ";
          
          $where .= " WHEN $sitereviewTableName.listingtype_id = $listingType->listingtype_id THEN $sitereviewTableName.end_date >= '$current_time'";
        }
        elseif($listingType->expiry == 2) {
          $duration = $listingType->admin_expiry_duration;
          $interval_type = $duration[1];
          $interval_type = empty($interval_type) ? 1 : $interval_type;
          $interval_value = $duration[0];
          $interval_value = empty($interval_value) ? 1 : $interval_value;
          
          $approveDate = Engine_Api::_()->sitereview()->adminExpiryDuration($listingType->listingtype_id);

          $end_date .= " WHEN $sitereviewTableName.listingtype_id = $listingType->listingtype_id THEN  DATE_ADD(approved_date, INTERVAL $interval_value $interval_type) ";        
          
          $where .= " WHEN $sitereviewTableName.listingtype_id = $listingType->listingtype_id THEN  DATE_ADD(approved_date, INTERVAL $interval_value $interval_type) >= '$current_time'";  
          
        }
        $listingTypeIdsArray[] = $listingType->listingtype_id;
      }
      
      if(Count($listingTypeIdsArray) > 0) {
        $select->where("$sitereviewTableName.listingtype_id IN (?)", (array) $listingTypeIdsArray);
      }
      
      $end_date .= ' END  ';
      $where .= ' END ';

      $end_date = new Zend_Db_Expr($end_date);
      $where = new Zend_Db_Expr($where);
      
      if(Count($listingTypes) > 0) {
        $columnArray = array('listing_id', 'listingtype_id', 'title', 'photo_id', 'owner_id', 'category_id', 'view_count', 'review_count', 'like_count', 'comment_count', 'rating_avg', 'rating_editor', 'rating_users', 'sponsored', 'featured', 'newlabel', 'creation_date', 'body','approved_date', 'location', 'price', 'end_date' => $end_date);
        $select->from($sitereviewTableName, $columnArray);      

        $select->where($where);
        $select->order("end_date ASC");
      }
    }
    else {
      $select->from($sitereviewTableName);
    }
    
    if (isset($params['detactLocation']) && $params['detactLocation'] && isset($params['latitude']) && $params['latitude'] && isset($params['longitude']) && $params['longitude'] && isset($params['defaultLocationDistance']) && $params['defaultLocationDistance']) {
      $locationsTable = Engine_Api::_()->getDbtable('locations', 'sitereview');
      $locationTableName = $locationsTable->info('name');
      $radius = $params['defaultLocationDistance']; //in miles
      $latitude = $params['latitude'];
      $longitude = $params['longitude'];
      $flage = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proximity.search.kilometer', 0);
      if (!empty($flage)) {
        $radius = $radius * (0.621371192);
      }
      //  $latitudeRadians = deg2rad($latitude);
      $latitudeSin = "sin(radians($latitude))"; //sin($latitudeRadians);
      $latitudeCos = "cos(radians($latitude))";// cos($latitudeRadians);

      $select->setIntegrityCheck(false)->join($locationTableName, "$sitereviewTableName.listing_id = $locationTableName.listing_id", array("(degrees(acos($latitudeSin * sin(radians($locationTableName.latitude)) + $latitudeCos * cos(radians($locationTableName.latitude)) * cos(radians($longitude - $locationTableName.longitude)))) * 69.172) AS distance", $locationTableName.'.location AS locationName'));
      $sqlstring = "(degrees(acos($latitudeSin * sin(radians($locationTableName.latitude)) + $latitudeCos * cos(radians($locationTableName.latitude)) * cos(radians($longitude - $locationTableName.longitude)))) * 69.172 <= " . "'" . $radius . "'";
      $sqlstring .= ")";
      $select->where($sqlstring);
      $select->order("distance");
      //$select->group("$sitereviewTableName.listing_id");
    }        

    if (isset($params['listingtype_id']) && $params['listingtype_id'] > 0) {
      $select->where($sitereviewTableName . '.listingtype_id = ?', $params['listingtype_id']);
    }

    if (isset($params['listing_id']) && !empty($params['listing_id'])) {
      $select->where($sitereviewTableName . '.listing_id != ?', $params['listing_id']);
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

    if (isset($params['popularity']) && !empty($params['popularity'])) {
      $select->order($params['popularity'] . " DESC");
    }

    if (isset($params['featured']) && !empty($params['featured']) || $sitereviewtype == 'featured') {
      $select->where("$sitereviewTableName.featured = ?", 1);
    }

    if (isset($params['sponsored']) && !empty($params['sponsored']) || $sitereviewtype == 'sponsored') {
      $select->where($sitereviewTableName . '.sponsored = ?', '1');
    }

    if (isset($params['newlabel']) && !empty($params['newlabel']) || $sitereviewtype == 'newlabel') {
      $select->where($sitereviewTableName . '.newlabel = ?', '1');
    }
    
    if (isset($params['sponsored_or_featured']) && !empty($params['sponsored_or_featured'])) {
      $select->where("$sitereviewTableName.featured = 1 OR $sitereviewTableName.sponsored = 1");
    }    

    if (isset($params['similarItems']) && !empty($params['similarItems'])) {
      $select->where("$sitereviewTableName.listing_id IN (?)", (array) $params['similarItems']);
    }

    //Start Network work
    $select = $table->getNetworkBaseSql($select, array('setIntegrity' => 1, 'not_groupBy' => 1));
    //End Network work

    if ($sitereviewtype == 'most_popular') {
      $select = $select->order($sitereviewTableName . '.view_count DESC');
    }

    if ($sitereviewtype == 'most_reviews' || $sitereviewtype == 'most_reviewed') {
      $select = $select->order($sitereviewTableName . '.review_count DESC');
    }
    
    if(isset($params['similar_items_order']) && !empty($params['similar_items_order'])) {
      if(isset($params['ratingType']) && !empty($params['ratingType']) && $params['ratingType'] != 'rating_both') {
        $ratingType = $params['ratingType'];
        $select->order($sitereviewTableName . ".$ratingType DESC");
      }
      else {
        $select->order($sitereviewTableName . '.rating_avg DESC');
      }
      $select->order('RAND()');
      
    }else {
      $select->order($sitereviewTableName . '.creation_date DESC');
    }

    if (isset($params['limit']) && !empty($params['limit'])) {
      $limit = $params['limit'];
    }

    $select->group($sitereviewTableName . '.listing_id');

    if (!Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
      return Zend_Paginator::factory($select);
    }

    if (isset($params['start_index']) && $params['start_index'] >= 0) {
      $select = $select->limit($limit, $params['start_index']);
      return $table->fetchAll($select);
    } else {

      $paginator = Zend_Paginator::factory($select);
      if (!empty($params['page'])) {
        $paginator->setCurrentPageNumber($params['page']);
      }

      if (!empty($params['limit'])) {
        $paginator->setItemCountPerPage($limit);
      }

      return $paginator;
    }
  }

  //GET DISCUSSED LISTINGS
  public function getDiscussedListing($params = array()) {

    //GET SITEREVIEW TABLE NAME
    $sitereviewTableName = $this->info('name');

    //GET TOPIC TABLE
    $topictable = Engine_Api::_()->getDbTable('topics', 'sitereview');
    $topic_tableName = $topictable->info('name');

    //MAKE QUERY
    $select = $this->select()->setIntegrityCheck(false)
            ->from($sitereviewTableName, array('listing_id', 'listingtype_id', 'title', 'photo_id', 'owner_id', 'category_id', 'subcategory_id', 'subsubcategory_id', 'rating_avg', 'rating_users', 'rating_editor'))
            ->join($topic_tableName, $topic_tableName . '.listing_id = ' . $sitereviewTableName . '.listing_id', array('count(*) as counttopics', '(sum(post_count) - count(*) ) as total_count'))
            ->where($sitereviewTableName . '.closed = ?', '0')
            ->where($sitereviewTableName . '.approved = ?', '1')
            ->where($sitereviewTableName . '.draft = ?', '0')
            ->where($sitereviewTableName . '.creation_date <= ?', date('Y-m-d H:i:s'))
            ->where($sitereviewTableName . ".search = ?", 1)
            ->where($topic_tableName . '.post_count > ?', '1')
            ->group($topic_tableName . '.listing_id')
            ->order('total_count DESC')
            ->order('counttopics DESC')
            ->limit($params['limit']);
    
    $listingtype_id = (isset($params['listingtype_id']) && !empty($params['listingtype_id']) && $params['listingtype_id'] != -1) ? $params['listingtype_id'] : -1;
    
    $select = $this->expirySQL($select, $listingtype_id);
    
    if (isset($params['detactLocation']) && $params['detactLocation'] && isset($params['latitude']) && $params['latitude'] && isset($params['longitude']) && $params['longitude'] && isset($params['defaultLocationDistance']) && $params['defaultLocationDistance']) {
      $locationsTable = Engine_Api::_()->getDbtable('locations', 'sitereview');
      $locationTableName = $locationsTable->info('name');
      $radius = $params['defaultLocationDistance']; //in miles
      $latitude = $params['latitude'];
      $longitude = $params['longitude'];
      $flage = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proximity.search.kilometer', 0);
      if (!empty($flage)) {
        $radius = $radius * (0.621371192);
      }
     //  $latitudeRadians = deg2rad($latitude);
      $latitudeSin = "sin(radians($latitude))"; //sin($latitudeRadians);
      $latitudeCos = "cos(radians($latitude))";// cos($latitudeRadians);

      $select->join($locationTableName, "$sitereviewTableName.listing_id = $locationTableName.listing_id", array("(degrees(acos($latitudeSin * sin(radians($locationTableName.latitude)) + $latitudeCos * cos(radians($locationTableName.latitude)) * cos(radians($longitude - $locationTableName.longitude)))) * 69.172) AS distance", $locationTableName.'.location AS locationName'));
      $sqlstring = "(degrees(acos($latitudeSin * sin(radians($locationTableName.latitude)) + $latitudeCos * cos(radians($locationTableName.latitude)) * cos(radians($longitude - $locationTableName.longitude)))) * 69.172 <= " . "'" . $radius . "'";
      $sqlstring .= ")";
      $select->where($sqlstring);
      $select->order("distance");
      //$select->group("$sitereviewTableName.listing_id");
    }        
    
    if (isset($params['featured']) && !empty($params['featured'])) {
      $select->where($sitereviewTableName . '.featured = ?', 1);
    }
    
    if (isset($params['sponsored_or_featured']) && !empty($params['sponsored_or_featured'])) {
      $select->where("$sitereviewTableName.featured = 1 OR $sitereviewTableName.sponsored = 1");
    }        

    if (isset($params['listingtype_id']) && !empty($params['listingtype_id']) && $params['listingtype_id'] != -1) {
      $select->where($sitereviewTableName . '.listingtype_id = ?', $params['listingtype_id']);
    }

    if (isset($params['category_id']) && (!empty($params['category_id']) && $params['category_id'] != -1)) {
      $select->where($sitereviewTableName . '.category_id = ?', $params['category_id']);
    }

    if (isset($params['subcategory_id']) && (!empty($params['subcategory_id']) && $params['subcategory_id'] != -1)) {
      $select->where($sitereviewTableName . '.subcategory_id = ?', $params['subcategory_id']);
    }

    if (isset($params['subsubcategory_id']) && (!empty($params['subsubcategory_id']) && $params['subsubcategory_id'] != -1)) {
      $select->where($sitereviewTableName . '.subsubcategory_id = ?', $params['subsubcategory_id']);
    }

    //START NETWORK WORK
    $select = $this->getNetworkBaseSql($select, array('not_groupBy' => 1));
    //END NETWORK WORK

    //FETCH RESULTS
    return $this->fetchAll($select);
  }

  // get sitereview sitereview relative to sitereview owner
  public function userListing($owner_id, $listing_id, $listingtype_id = 0, $limit = 3) {

    //GET SITEREVIEW TABLE NAME
    $sitereviewTableName = $this->info('name');
    
    $fetchColumns = array("listing_id", 'listingtype_id', "title", "category_id", "subcategory_id", "subsubcategory_id", "view_count", "comment_count", "like_count", "rating_avg", "rating_editor", "rating_users", "review_count", "owner_id", "photo_id", "price", 'featured', 'sponsored','newlabel');
    
    if (Engine_Api::_()->sitereview()->hasPackageEnable()) {
        $fetchColumns = array("listing_id", 'listingtype_id', "title", "category_id", "subcategory_id", "subsubcategory_id", "view_count", "comment_count", "like_count", "rating_avg", "rating_editor", "rating_users", "review_count", "owner_id", "photo_id", "price", 'featured', 'sponsored','newlabel', 'package_id');
    }    

    //MAKE QUERY
    $select = $this->select()
            ->from($sitereviewTableName, $fetchColumns)
            ->where($sitereviewTableName . '.closed = ?', '0')
            ->where($sitereviewTableName . '.approved = ?', '1')
            ->where($sitereviewTableName . '.draft = ?', '0')
            ->where($sitereviewTableName . '.creation_date <= ?', date('Y-m-d H:i:s'))
            ->where($sitereviewTableName . ".search = ?", 1)
            ->where($sitereviewTableName . '.listing_id <> ?', $listing_id)
            ->where($sitereviewTableName . '.owner_id = ?', $owner_id)
            ->limit($limit);

    $select = $this->expirySQL($select, $listingtype_id);
    if (!empty($listingtype_id) && $listingtype_id != -1) {
      $select->where($sitereviewTableName . '.listingtype_id = ?', $listingtype_id);
    }

    //Start Network work
    $select = $this->getNetworkBaseSql($select, array('setIntegrity' => 1));
    //End Network work

    //RETURN RESULTS
    if (Engine_API::_()->seaocore()->checkSitemobileMode('fullsite-mode')) {
      return $this->fetchAll($select);
    } else {
      return Zend_Paginator::factory($select);
    }  
  }

  /**
   * Handle archive sitereview
   * @param array $results : document owner archive sitereview array
   * @return sitereview with detail.
   */
  public function getArchiveSitereview($spec, $listingtype_id = 0) {
    if (!($spec instanceof User_Model_User)) {
      return null;
    }

    $localeObject = Zend_Registry::get('Locale');
    if (!$localeObject) {
      $localeObject = new Zend_Locale();
    }

    $dates = $this->select()
            ->from($this->info('name'), 'creation_date')
            ->where('owner_id = ?', $spec->getIdentity())
            ->where('closed = ?', '0')
            ->where('approved = ?', '1')
            ->where('draft = ?', '0')
            ->where('creation_date <= ?', date('Y-m-d H:i:s'))
            ->where("search = ?", 1)
            ->order('creation_date DESC');
    $select = $this->expirySQL($dates, $listingtype_id);
    if (!empty($listingtype_id) && $listingtype_id != -1) {
      $dates->where('listingtype_id = ?', $listingtype_id);
    }

    $dates = $dates
            ->query()
            ->fetchAll(Zend_Db::FETCH_COLUMN);

    $time = time();

    $archive_sitereview = array();
    foreach ($dates as $date) {

      $date = strtotime($date);
      $ltime = localtime($date, true);
      $ltime["tm_mon"] = $ltime["tm_mon"] + 1;
      $ltime["tm_year"] = $ltime["tm_year"] + 1900;

      // LESS THAN A YEAR AGO - MONTHS
      if ($date + 31536000 > $time) {
        $date_start = mktime(0, 0, 0, $ltime["tm_mon"], 1, $ltime["tm_year"]);
        $date_end = mktime(0, 0, 0, $ltime["tm_mon"] + 1, 1, $ltime["tm_year"]);
        $type = 'month';

        $dateObject = new Zend_Date($date);
        $format = $localeObject->getTranslation('yMMMM', 'dateitem', $localeObject);
        $label = $dateObject->toString($format, $localeObject);
      }
      // MORE THAN A YEAR AGO - YEARS
      else {
        $date_start = mktime(0, 0, 0, 1, 1, $ltime["tm_year"]);
        $date_end = mktime(0, 0, 0, 1, 1, $ltime["tm_year"] + 1);
        $type = 'year';

        $dateObject = new Zend_Date($date);
        $format = $localeObject->getTranslation('yyyy', 'dateitem', $localeObject);
        if (!$format) {
          $format = $localeObject->getTranslation('y', 'dateitem', $localeObject);
        }
        $label = $dateObject->toString($format, $localeObject);
      }

      if (!isset($archive_sitereview[$date_start])) {
        $archive_sitereview[$date_start] = array(
            'type' => $type,
            'label' => $label,
            'date' => $date,
            'date_start' => $date_start,
            'date_end' => $date_end,
            'user_id' => $spec->getIdentity(),
            'count' => 1
        );
      } else {
        $archive_sitereview[$date_start]['count']++;
      }
    }

    return $archive_sitereview;
  }

  public function getListingTypeId($listing_id) {

    $listingtype_id = $this->select()
            ->from($this->info('name'), 'listingtype_id')
            ->where('listing_id = ?', $listing_id)
            ->query()
            ->fetchColumn();

    return $listingtype_id;
  }

  /**
   * Get listings 
   *
   * @param int $id
   * @param string $column_name
   * @param int $authorization
   * @return listing count
   */
  public function getListings($params = array()) {

    //MAKE QUERY
    $select = $this->select()
            //->from($this->info('name'), array('COUNT(*) AS count'))
            ->where('closed = ?', 0)
            ->where('approved = ?', 1)
            ->where('draft = ?', 0)
            ->where('creation_date <= ?', date('Y-m-d H:i:s'))
            ->where('search = ?', 1);
    $listingtype_id = (isset($params['listingtype_id']) && !empty($params['listingtype_id']) && $params['listingtype_id'] != -1) ? $params['listingtype_id'] : -1;
    $select = $this->expirySQL($select, $listingtype_id);
    if (isset($params['list_ids']) && !empty($params['list_ids'])) {
      $select->where('listing_id  IN(?)', (array) $params['list_ids']);
    }
    if (isset($params['category_id']) && !empty($params['category_id'])) {
      $select->where('category_id  = ?', $params['category_id']);
    }

    return $this->fetchAll($select);
  }

  /**
   * Get FAQs
   *
   * @param int $category_id
   * @param string $column_name
   * @param int $authorization
   * @param int $no_subcategory
   * @return FAQs
   */
  public function getLists($category_id, $column_name, $authorization, $no_subcategory, $limit, $faq_limit, $count_only, $listingtype_id = 0) {

    //GET FAQ TABLE NAME
    $tableListingName = $this->info('name');

    //RETURN IF ID IS EMPTY
    if (empty($column_name) || empty($category_id)) {
      return;
    }

    //MAKE QUERY
    $select = $this->select();

    if (empty($count_only)) {
      $select = $select->from($tableListingName, array('listing_id', 'title', 'category_id', 'subcategory_id', 'subsubcategory_id'));
    } else {
      $select = $select->from($tableListingName, array('COUNT(*) AS count'));
    }

    $select->where("$column_name = ?", $category_id);

    if (!empty($listingtype_id) && $listingtype_id != -1) {
      $select->where("listingtype_id = ?", $listingtype_id);
    }

    if (!empty($no_subcategory)) {

      //MAKE QUERY
      $categoryTable = Engine_Api::_()->getDbtable('categories', 'sitereview');
      $categoryTableName = $categoryTable->info('name');
      $selectSubcategories = $categoryTable->select()
              ->from($categoryTableName, array('category_id'))
              ->where('cat_dependency = ?', $category_id);
      $subcategories = $selectSubcategories->query()->fetchAll(Zend_Db::FETCH_COLUMN);
      if (Count($subcategories) > 0) {
        $str_arr = array();
        foreach ($subcategories as $value) {
          $select = $select->where("subcategory_id  != ?", $value);
        }
      }
      $select = $select->where("subcategory_id = ?", 0);
    }

    //AUTHORIZATION CHECK
    if (!empty($authorization)) {
      $select = $select->where('closed = ?', 0)
              ->where('approved = ?', 1)
              ->where('draft = ?', 0)
              ->where('creation_date <= ?', date('Y-m-d H:i:s'))
              ->where('search = ?', 1);
      $select = $this->expirySQL($select, $listingtype_id);
    }

    //LIMIT CHECK
    if (!empty($faq_limit)) {
      $select = $select->limit($faq_limit);
    }

    if (empty($count_only)) {
      $select = $select->order("$tableListingName.creation_date DESC");
      return $this->fetchAll($select);
    } else {
      return $select->query()->fetchColumn();
    }
  }

  public function getSimilarItems($params = NULL) {

    $sitereviewTableName = $this->info('name');
    $select = $this->select()
            ->from($sitereviewTableName, array('listing_id', 'photo_id', 'title', 'listingtype_id'));

    if (isset($params['listingtype_id']) && !empty($params['listingtype_id'])) {
      $select->where($sitereviewTableName . '.listingtype_id = ?', $params['listingtype_id']);
    }

    if (isset($params['textSearch']) && !empty($params['textSearch'])) {
      $select->where("$sitereviewTableName.title LIKE ? ", "%" . $params['textSearch'] . "%");
    }

    if (isset($params['listing_id']) && !empty($params['listing_id'])) {
      $select->where($sitereviewTableName . '.listing_id != ?', $params['listing_id']);
    }

    if (isset($params['category_id']) && !empty($params['category_id']) && $params['category_id'] != -1) {
      $select->where($sitereviewTableName . '.category_id = ?', $params['category_id']);
    }

    if (isset($params['subcategory_id']) && !empty($params['subcategory_id']) && $params['subcategory_id'] != -1) {
      $select->where($sitereviewTableName . '.subcategory_id = ?', $params['subcategory_id']);
    }

    if (isset($params['limit']) && !empty($params['limit'])) {
      $select->limit($params['limit']);
    }

    if (isset($params['listingIds']) && !empty($params['listingIds'])) {
      $listingIds = join(',', $params['listingIds']);
      $select->where("listing_id IN ($listingIds)");
    }

    if (isset($params['notListingIds']) && !empty($params['notListingIds'])) {
      $notListingIds = join(',', $params['notListingIds']);
      $select->where("listing_id NOT IN ($notListingIds)");
    }

    return Zend_Paginator::factory($select);
  }

  public function updateListingsListingtypes($previous_listingtype_id, $current_listingtype_id) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    $db->query("UPDATE `engine4_sitereview_listings` SET `listingtype_id` = $current_listingtype_id WHERE `listingtype_id` = $previous_listingtype_id");
  }

  /**
   * Return listings which have this category and this mapping
   *
   * @param int category_id
   * @return Zend_Db_Table_Select
   */
  public function getCategoryList($category_id, $categoryType) {

    //RETURN IF CATEGORY ID IS NULL
    if (empty($category_id)) {
      return null;
    }

    //MAKE QUERY
    $select = $this->select()
            ->from($this->info('name'), 'listing_id')
            ->where("$categoryType = ?", $category_id);

    //GET DATA
    return $this->fetchAll($select);
  }
  
  /**
   * Return top reviewers
   *
   * @param Array $params
   * @return top reviewers
   */
  public function topPosters($params = array()) {

    //GET USER TABLE INFO
    $tableUser = Engine_Api::_()->getDbtable('users', 'user');
    $tableUserName = $tableUser->info('name');

    //GET REVIEW TABLE NAME
    $sitereviewTableName = $this->info('name');

    //MAKE QUERY
    $select = $tableUser->select()
            ->setIntegrityCheck(false)
            ->from($tableUserName, array('user_id', 'displayname', 'username', 'photo_id'))
            ->join($sitereviewTableName, "$tableUserName.user_id = $sitereviewTableName.owner_id", array('COUNT(engine4_sitereview_listings.listing_id) AS listing_count'));

    if (isset($params['listingtype_id']) && !empty($params['listingtype_id']) && $params['listingtype_id'] != -1) {
      $select->where($sitereviewTableName . '.listingtype_id = ?', $params['listingtype_id']);
    } 

    $select = $select->where($sitereviewTableName . '.draft = ?', 0)
            ->where($sitereviewTableName . '.creation_date <= ?', date('Y-m-d H:i:s'))
            ->where($sitereviewTableName . '.search = ?', 1)
            ->where($sitereviewTableName . '.closed = ?', 0)
            ->where($sitereviewTableName . '.approved = ?', 1)
            ->group($tableUserName . ".user_id")
            ->order('listing_count DESC')
            ->order('user_id DESC')
            ->limit($params['limit']);
    
    $select = $this->expirySQL($select, $params['listingtype_id']);

    //RETURN THE RESULTS
    return $tableUser->fetchAll($select);
  }  
  
  /**
   * Return listings which can user have to choice to claim
   *
   * @param array params
   * @return Zend_Db_Table_Select
   */
  public function getSuggestClaimListing($params) {
    //SELECT
    $select = $this->select()
            ->from($this->info('name'), array('listing_id', 'title', 'photo_id', 'owner_id', 'listingtype_id'))
            ->where('listingtype_id = ?', $params['listingtype_id'])
            ->where('approved = ?', '1')
            ->where('closed = ?', '0')
            ->where('draft = ?', '0')
            ->where('creation_date <= ?', date('Y-m-d H:i:s'));

    if (isset($params['listing_id']) && !empty($params['listing_id'])) {
      $select = $select->where('listing_id = ?', $params['listing_id']);
    }

    if (isset($params['viewer_id']) && !empty($params['viewer_id'])) {
      $select = $select->where('owner_id != ?', $params['viewer_id']);
    }

    if (isset($params['title']) && !empty($params['title'])) {
      $select = $select->where('title LIKE ? ', '%' . $params['title'] . '%');
    }

    if (isset($params['limit']) && !empty($params['limit'])) {
      $select = $select->limit($params['limit']);
    }

    if (isset($params['orderby']) && !empty($params['orderby'])) {
      $select = $select->order($params['orderby']);
    }
    if (Engine_Api::_()->sitereview()->hasPackageEnable())
      $select->where('expiration_date  > ?', date("Y-m-d H:i:s"));

    //FETCH
    return $this->fetchAll($select);
  }
  
  public function getListingColumn($params = array()) {

    $select = $this->select()->from($this->info('name'), 'listing_id');
    
    if(!empty($params['listingtype_id'])) {
        $select->where('listingtype_id = ?', $params['listingtype_id']);
    }
    
    if(!empty($params['title'])) {
        $select->where('title = ?', $params['title']);
    }    

    return $select->query()->fetchColumn();
  }  

}
