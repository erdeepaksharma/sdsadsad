<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Categories.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_DbTable_Categories extends Engine_Db_Table {

  protected $_rowClass = 'Sitereview_Model_Category';
  protected $_categories = array();

  /**
   * Return subcaregories
   *
   * @param int category_id
   * @return all sub categories
   */
  public function getSubCategories($category_id, $fetchColumns = array()) {

    //RETURN IF CATEGORY ID IS EMPTY
    if (empty($category_id)) {
      return;
    }
    
    //MAKE QUERY
    $select = $this->select();

    if (!empty($fetchColumns)) {
        $select->from($this->info('name'), $fetchColumns);
    }

    $select->where('cat_dependency = ?', $category_id)
            ->order('cat_order');    

    //RETURN RESULTS
    return $this->fetchAll($select);
  }

  /**
   * Get category object
   * @param int $category_id : category id
   * @return category object
   */
  public function getCategory($category_id) {
    if (empty($category_id))
      return;
    if (!array_key_exists($category_id, $this->_categories)) {
      $this->_categories[$category_id] = $this->find($category_id)->current();
    }
    return $this->_categories[$category_id];
  }

  public function getCategoriesList($listingtype_id = 0, $cat_depandancy = -1, $fetchColumns = array()) {

    $select = $this->select()->order('cat_order');
    
    if (!empty($fetchColumns)) {
        $select->from($this->info('name'), $fetchColumns);
    }    

    if ($cat_depandancy != -1) {
      $select->where('cat_dependency = ?', $cat_depandancy);
    }

    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }
    //RETURN DATA
    return $this->fetchAll($select);
  }

  /**
   * Return categories
   *
   * @param array $category_ids
   * @return all categories
   */
  public function getCategories($category_ids = null, $count_only = 0, $listingtype_id = 0, $sponsored = 0, $cat_depandancy = 0, $limit = 0, $orderBy = 'cat_order', $visibility = 0, $fetchColumns = array()) {

    //MAKE QUERY
    $select = $this->select();

    //GET CATEGORY TABLE NAME
    $categoryTableName = $this->info('name');

    //GET LISTING TYPE TABLE 
    $listingTypeTable = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');
    $listingTypeTableName = $listingTypeTable->info('name');

    if ($orderBy == 'category_name') {
      $select->order('category_name');
    } else {
      $select->order('cat_order');
    }

    if (!empty($cat_depandancy)) {
      $select->where('cat_dependency = ?', 0);
    }

    if (!empty($listingtype_id) && $listingtype_id != -1) {
      $select->where($categoryTableName . '.listingtype_id = ?', $listingtype_id);
    }
    
    if(!empty($count_only)) {
        $select->from($this->info('name'), 'category_id');
    }
    elseif(!empty ($fetchColumns)) {
        $select->from($categoryTableName, $fetchColumns);
    }

    if (!empty($visibility)) {

      $select->setIntegrityCheck(false)
              ->join($listingTypeTableName, "$listingTypeTableName.listingtype_id = $categoryTableName.listingtype_id", array(''))
              ->where("$listingTypeTableName.visible = 1")
      ;
    }

    if (!empty($sponsored)) {
      $select->where('sponsored = ?', 1);
    }

    if (!empty($category_ids)) {
      foreach ($category_ids as $ids) {
        $categoryIdsArray[] = "category_id = $ids";
      }
      $select->where("(" . join(") or (", $categoryIdsArray) . ")");
    }

    if (!empty($count_only)) {
      return $select->query()->fetchColumn();
    }

    if (!empty($limit)) {
      $select->limit($limit);
    }

    //RETURN DATA
    return $this->fetchAll($select);
  }

  public function similarItemsCategories($element_value, $element_type) {

    $categoriesTable = Engine_Api::_()->getDbTable('categories', 'sitereview');
    $select = $this->select()
            ->from($this->info('name'), array('category_id', 'category_name'))
            ->where("$element_type = ?", $element_value);

    if ($element_type == 'listingtype_id') {
      $select->where('cat_dependency = ?', 0)->where('subcat_dependency = ?', 0);
    } elseif ($element_type == 'cat_dependency') {
      $select->where('subcat_dependency = ?', 0);
    } elseif ($element_type == 'subcat_dependency') {
      $select->where('cat_dependency = ?', $element_value);
    }

    $categoriesData = $this->fetchAll($select);

    $categories = array();
    if (Count($categoriesData) > 0) {
      foreach ($categoriesData as $category) {
        $data = array();
        $data['category_name'] = $category->category_name;
        $data['category_id'] = $category->category_id;
        $categories[] = $data;
      }
    }

    return $categories;
  }

  public function updateCategoryListingtypes($previous_listingtype_id, $current_listingtype_id) {

    //GET DATABASE
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    $db->query("UPDATE `engine4_sitereview_categories` SET `listingtype_id` = $current_listingtype_id WHERE `listingtype_id` = $previous_listingtype_id");
  }

  /**
   * Get Mapping array
   *
   */
  public function getMapping($listingtype_id = 0, $profileTypeName = 'profile_type') {

    //MAKE QUERY
    $select = $this->select()->from($this->info('name'), array('category_id', "$profileTypeName"));

    if (!empty($listingtype_id)) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }

    //FETCH DATA
    $mapping = $this->fetchAll($select);

    //RETURN DATA
    if (!empty($mapping)) {
      return $mapping->toArray();
    }

    return null;
  }

  public function getChildMapping($category_id, $profileTypeName = 'profile_type') {

    //GET CATEGORY TABLE NAME
    $categoryTableName = $this->info('name');

    $category = Engine_Api::_()->getItem('sitereview_category', $category_id);

    $select = $this->select()
            ->from($categoryTableName, 'category_id')
            ->where('listingtype_id = ?', $category->listingtype_id)
            ->where("$profileTypeName != ?", 0)
            ->where("cat_dependency = $category->category_id OR subcat_dependency = $category->category_id");

    return $this->fetchAll($select);
  }

  public function getChilds($category_id, $fetchColumns = array()) {

    //GET CATEGORY TABLE NAME
    $categoryTableName = $this->info('name');

    $category = Engine_Api::_()->getItem('sitereview_category', $category_id);

    $select = $this->select()->where("cat_dependency = ?", $category_id);
    
    if(!empty($fetchColumns)) {
        $select->from($categoryTableName, $fetchColumns);
    }
    else {
        $select->from($categoryTableName);
    }
            
    //IF SUBCATEGORY THEN FETCH 3RD LEVEL CATEGORY
    if ($category->cat_dependency != 0 && $category->subcat_dependency == 0) {
      $select->where("subcat_dependency = ?", $category_id);
    }
    //IF CATEGORY THEN FETCH SUB-CATEGORY
    elseif ($category->cat_dependency == 0 && $category->subcat_dependency == 0) {
      $select->where("subcat_dependency = ?", 0);
    }
    //IF 3RD LEVEL CATEGORY
    else {
      return array();
    }

    return $this->fetchAll($select);
  }

  /**
   * Get profile_type corresponding to category_id
   *
   * @param int category_id
   */
  public function getProfileType($categoryIds = array(), $categoryId = 0, $profileTypeName = 'profile_type') {

    if (!empty($categoryIds)) {
      $profile_type = 0;
      foreach ($categoryIds as $value) {
        $profile_type = $this->select()
                ->from($this->info('name'), array("$profileTypeName"))
                ->where("category_id = ?", $value)
                ->query()
                ->fetchColumn();

        if (!empty($profile_type)) {
          return $profile_type;
        }
      }

      return $profile_type;
    } elseif (!empty($categoryId)) {

      //FETCH DATA
      $profile_type = $this->select()
              ->from($this->info('name'), array("$profileTypeName"))
              ->where("category_id = ?", $categoryId)
              ->query()
              ->fetchColumn();

      return $profile_type;
    }

    return 0;
  }

  public function getAllProfileTypes($categoryIds = array(), $also_find_nested = 0) {

    $levelOfCategory = count($categoryIds);
    if (empty($levelOfCategory))
      return;
    $categoryIdsFinal = $categoryIds;
    if ($also_find_nested) {
      if ($levelOfCategory < 3) {
        $subCategoryIds = array();
        if ($levelOfCategory == 1) {
          $categories = $this->getChilds($categoryIds[0], array('category_id'));
          foreach ($categories as $category) {
            $categoryIdsFinal[] = $subCategoryIds[] = $category->category_id;
          }
        } else {
          $subCategoryIds[] = $categoryIds[1];
        }

        foreach ($subCategoryIds as $cateory_id) {
          $categories = $this->getChilds($cateory_id, array('category_id'));
          foreach ($categories as $category) {
            $categoryIdsFinal[] = $category->category_id;
          }
        }
      }
    }
    return $this->select()
                    ->from($this->info('name'), array('profile_type'))
                    ->where("category_id In(?)", $categoryIdsFinal)
                    ->query()
                    ->fetchAll(Zend_Db::FETCH_COLUMN);
  }

  /**
   * Gets all categories and subcategories
   *
   * @param string $category_id
   * @param string $fieldname
   * @param int $sitereviewCondition
   * @param string $sitereview
   * @param  all categories and subcategories
   */
  public function getCategorieshaslistings($listingtype_id, $category_id = null, $fieldname, $limit = null, $params = array(), $fetchColumns = array()) {

    //GET CATEGORY TABLE NAME
    $tableCategoriesName = $this->info('name');

    //GET LISTINGS TABLE
    $tableListing = Engine_Api::_()->getDbtable('listings', 'sitereview');
    $tableListingName = $tableListing->info('name');

    //MAKE QUERY
    $select = $this->select()->setIntegrityCheck(false);
    
    if (!empty($fetchColumns)) {
        $select->from($tableCategoriesName, $fetchColumns);
    }
    else {
        $select->from($tableCategoriesName);
    }

    $select = $select->join($tableListingName, $tableListingName . '.' . $fieldname . '=' . $tableCategoriesName . '.category_id', null);

    if (!empty($order)) {
      $select->order("$order");
    }

    $select = $select->where($tableCategoriesName . '.cat_dependency = ' . $category_id)
            ->group($tableCategoriesName . '.category_id')
            ->order('cat_order');

    if (!empty($limit)) {
      $select = $select->limit($limit);
    }

    $select->where($tableListingName . '.approved = ?', 1)->where($tableListingName . '.draft = ?', 0)->where($tableListingName . '.creation_date <= ?', date('Y-m-d H:i:s'))->where($tableListingName . '.search = ?', 1)->where($tableListingName . '.closed = ?', 0);
    $select = $tableListing->expirySQL($select, $listingtype_id);
    $select = $tableListing->getNetworkBaseSql($select, array('not_groupBy' => 1));

    if (!empty($listingtype_id) && $listingtype_id != -1) {
      $select->where($tableCategoriesName . '.listingtype_id = ?', $listingtype_id);
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
     // $latitudeRadians = deg2rad($latitude);
    $latitudeSin = "sin(radians($latitude))";
    $latitudeCos = "cos(radians($latitude))";

      $select->join($locationTableName, "$tableListingName.listing_id = $locationTableName.listing_id", array("(degrees(acos($latitudeSin * sin(radians($locationTableName.latitude)) + $latitudeCos * cos(radians($locationTableName.latitude)) * cos(radians($longitude - $locationTableName.longitude)))) * 69.172) AS distance", $locationTableName . '.location AS locationName'));
      $sqlstring = "(degrees(acos($latitudeSin * sin(radians($locationTableName.latitude)) + $latitudeCos * cos(radians($locationTableName.latitude)) * cos(radians($longitude - $locationTableName.longitude)))) * 69.172 <= " . "'" . $radius . "'";
      $sqlstring .= ")";
      $select->where($sqlstring);
      $select->order("distance");
      // $select->group("$tableListingName.listing_id");
    }

    //RETURN DATA
    return $this->fetchAll($select);
  }

  public function getCatDependancyArray() {

    $cat_dependency = $this->select()->from($this->info('name'), 'cat_dependency')->where('cat_dependency <>?', 0)->group('cat_dependency')->query()->fetchAll(Zend_Db::FETCH_COLUMN);

    return $cat_dependency;
  }

  /**
   * Return categories
   *
   * @param int $home_page_display
   * @return categories
   */
  public function getCategoriesByLevel($listingtype_id = 0, $level = null, $fetchColumns = array()) {

    $select = $this->select()->order('cat_order');
    
    if(!empty($fetchColumns)) {
        $select->from($this->info('name'), $fetchColumns);
    }
    
    switch ($level) {
      case 'category':
        $select->where('cat_dependency =?', 0);
        break;
      case 'subcategory':
        $select->where('cat_dependency !=?', 0);
        $select->where('subcat_dependency =?', 0);
        break;
      case 'subsubcategory':
        $select->where('cat_dependency !=?', 0);
        $select->where('subcat_dependency !=?', 0);
        break;
    }

    if ($listingtype_id > 0) {
      $select->where('listingtype_id = ?', $listingtype_id);
    }

    return $this->fetchAll($select);
  }

    /**
   * Return column name
   *
   * @param Int $review_id
   * @param Varchar $column_name
   * @return column name
   */
  public function getColumnValue($listingtype_id = 0, $column_name) {

    $column = $this->select()
            ->from($this->info('name'), "$column_name")
            ->where('listingtype_id = ?', $listingtype_id)
            ->where("$column_name <>?", 0)
            ->group("$column_name")
            ->query()
            ->fetchAll(Zend_Db::FETCH_COLUMN);

    return $column;
  }
}