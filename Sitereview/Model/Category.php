<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Category.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Model_Category extends Core_Model_Item_Abstract {

  protected $_searchTriggers = false;

  public function getTitle($inflect = false) {
    if ($inflect) {
      return ucwords($this->category_name);
    } else {
      return $this->category_name;
    }
  }

  public function getHref($params = array()) {

    if ($this->subcat_dependency) {
      $type = 'subsubcategory';
      $params['subsubcategory_id'] = $this->category_id;
      $params['subsubcategoryname'] = $this->getCategorySlug();
      $cat =  $this->getTable()->getCategory($this->cat_dependency);
      $params['subcategory_id'] = $cat->category_id;
      $params['subcategoryname'] = $cat->getCategorySlug();
      $cat = $this->getTable()->getCategory( $cat->cat_dependency);
      $params['category_id'] = $cat->category_id;
      $params['categoryname'] = $cat->getCategorySlug();
    } else if ($this->cat_dependency) {
      $type = 'subcategory';
      $params['subcategory_id'] = $this->category_id;
      $params['subcategoryname'] = $this->getCategorySlug();
      $cat = $this->getTable()->getCategory($this->cat_dependency);
      $params['category_id'] = $cat->category_id;
      $params['categoryname'] = $cat->getCategorySlug();
    } else {
      $type = 'category';
      $params['category_id'] = $this->category_id;
      $params['categoryname'] = $this->getCategorySlug();
    }
    $params = array_merge(array(
        'route' => "sitereview_general_" . $type . "_listtype_$this->listingtype_id",
        'reset' => true,
            ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
                    ->assemble($params, $route, $reset);
  }

  /**
   * Return slug corrosponding to category name
   *
   * @return categoryname
   */
  public function getCategorySlug() {

    if(!empty($this->category_slug)) {
      $slug = $this->category_slug;
    } else {
      $slug = Engine_Api::_()->seaocore()->getSlug($this->category_name, 225);  
    }
      
    return $slug;
  }

  /**
   * Set category icon
   *
   */
  public function setPhoto($photo) {

    if ($photo instanceof Zend_Form_Element_File) {
      $file = $photo->getFileName();
    } else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
    } else if (is_string($photo) && file_exists($photo)) {
      $file = $photo;
    } else {
      return;
    }

    if (empty($file))
      return;

    //GET PHOTO DETAILS
    $name = basename($file);
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $mainName = $path . '/' . $name;

    //GET VIEWER ID
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    $photo_params = array(
        'parent_id' => $this->category_id,
        'parent_type' => "sitereview_category",
    );

    //RESIZE IMAGE WORK
    $image = Engine_Image::factory();
    $image->open($file);
    $image->open($file)
            ->resample(0, 0, $image->width, $image->height, $image->width, $image->height)
            ->write($mainName)
            ->destroy();

    try {
      $photoFile = Engine_Api::_()->storage()->create($mainName, $photo_params);
    } catch (Exception $e) {
      if ($e->getCode() == Storage_Api_Storage::SPACE_LIMIT_REACHED_CODE) {
        echo $e->getMessage();
        exit();
      }
    }

    return $photoFile;
  }

  public function hasChild() {
    $table = $this->getTable();
    //RETURN RESULTS
    return $table->select()
                    ->from($table, new Zend_Db_Expr('COUNT(cat_dependency)'))
                    ->where('cat_dependency = ?', $this->category_id)
                    ->limit(1)
                    ->query()
                    ->fetchColumn();
  }

  public function applyCompare() {
    
    $categories_ids = array();
    $table = $this->getTable();
    // 3rdlevel case
    $parent_compare_category_id = 0;
    if (!empty($this->cat_dependency) && !empty($this->subcat_dependency)) {
      $cat_levle = 3;
      $sub_cat = $table->getCategory($this->cat_dependency);
      if ($sub_cat->apply_compare) {
        $parent_compare_category_id = $sub_cat->category_id;
      } else {
        $parent_compare_category_id = $sub_cat->cat_dependency;
      }
    } elseif (!empty($this->cat_dependency)) { // subcategory Case
      $cat_levle = 2;
      $cat = $table->getCategory($this->cat_dependency);
      if ($cat->apply_compare) {
        $parent_compare_category_id = $cat->category_id;
      }
    } else { // category case
      $cat_levle = 1;
      $parent_compare_category_id = 0;
    }

    if ($parent_compare_category_id) {
      $table->update(array('apply_compare' => 1), array('cat_dependency = ?' => $parent_compare_category_id));
      $parent_cat = $table->getCategory($parent_compare_category_id);
      $parent_cat->apply_compare = 0;
      $parent_cat->save();
      if ($cat_levle == 3) {
        $table->update(array('apply_compare' => 1), array('cat_dependency = ?' => $this->cat_dependency));
        $parent_cat = $table->getCategory($this->cat_dependency);
        $parent_cat->apply_compare = 0;
        $parent_cat->save();
      }
    }

    if ($cat_levle <= 2) {
      $table->update(array('apply_compare' => 0), array('cat_dependency = ?' => $this->category_id));
      if ($cat_levle == 1) {
        $subCategories = $table->getSubCategories($this->category_id, array('category_id'));
        foreach ($subCategories as $subcategory) {
          $table->update(array('apply_compare' => 0), array('cat_dependency = ?' => $subcategory->category_id));
        }
      }
    }

    $this->apply_compare = 1;
    $this->save();
  }

  public function afterCreate() {
    
    $table = $this->getTable();
    $compareFlage = true;
    if (!empty($this->cat_dependency) && !empty($this->subcat_dependency)) {
      $subCat = $table->getCategory($this->cat_dependency);
      if ($subCat->apply_compare) {
        $compareFlage = false;
      } else {
        $cat = $table->getCategory($subCat->cat_dependency);
        if ($cat->apply_compare) {
          $compareFlage = false;
        }
      }
    } elseif (!empty($this->cat_dependency) && empty($this->subcat_dependency)) {
      $firstlevelCat = $table->getCategory($this->cat_dependency);
      if ($firstlevelCat->apply_compare) {
        $compareFlage = false;
      }
    }
    if ($compareFlage) {
      $this->apply_compare = 1;
      $this->save();
    }
    $compareSettingsTable = Engine_Api::_()->getDbtable('compareSettings', 'sitereview');
    $compareSettingsTable->insert(array('category_id' => $this->category_id));
  }

  protected function _delete() {

    $compareSettingsTable = Engine_Api::_()->getDbtable('compareSettings', 'sitereview');
    $compareSettingsTable->delete(array('category_id = ?' => $this->category_id));

    $ratingParamsTable = Engine_Api::_()->getDbtable('ratingparams', 'sitereview');
    $select = $ratingParamsTable->select()
            ->from($ratingParamsTable->info('name'), 'ratingparam_id')
            ->where('category_id = ?', $this->category_id)
            ->where('resource_type = ?', 'sitereview_listing');

    $ratingParams = $ratingParamsTable->fetchAll($select);
    foreach ($ratingParams as $ratingParam) {
      Engine_Api::_()->getItem('sitereview_ratingparam', $ratingParam->ratingparam_id)->delete();
    }

    //FIRST SAVE PAGE ID'S CORROSPONDING TO CATEGORY ID FOR UPDATION AFTER DELETE FROM RATING TABLE
    $tableRating = Engine_Api::_()->getDbtable('ratings', 'sitereview');

    $tableRating->delete(array('ratingparam_id != ?' => 0, 'category_id = ?' => $this->category_id, 'resource_type =?' => 'sitereview_listing'));

    $tableRating->update(array('category_id' => 0), array('category_id = ?' => $this->category_id, 'resource_type =?' => 'sitereview_listing'));

    parent::_delete();
  }

}
