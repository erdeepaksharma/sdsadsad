<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Categories.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Model_DbTable_Categories extends Engine_Db_Table {

    protected $_name = 'forum_categories';
    protected $_rowClass = 'Siteforum_Model_Category';

    public function getCategories() {

        $table = Engine_Api::_()->getDbTable('categories', 'siteforum');
        $rName = $table->info('name');
        $select = $table->select()
                ->from($rName,array('category_id','title','photo_id'))
                ->where($rName . '.cat_dependency = ?', 0)
                ->order('order');
        $row = $table->fetchAll($select);
        return $row;
    }

    public function getCatDependancyArray() {

        $cat_dependency = $this->select()->from($this->info('name'), 'cat_dependency')->where('cat_dependency <>?', 0)->group('cat_dependency')->query()->fetchAll(Zend_Db::FETCH_COLUMN);

        return $cat_dependency;
    }

    public function getSubCategories($category_id, $fetchColumns = array()) {

        //RETURN IF CATEGORY ID IS EMPTY
        if (empty($category_id)) {
            return;
        }
        $rName = $this->info('name');
        //MAKE QUERY
        $select = $this->select()->from($rName,array('category_id','title','photo_id'));

        if (!empty($fetchColumns)) {
            $select->from($this->info('name'), $fetchColumns);
        }

        $select->where('cat_dependency = ?', $category_id)
                ->order('order');

        //RETURN RESULTS
        return $this->fetchAll($select);
    }

    public function getParentCategory($category_id) {
        $table = Engine_Api::_()->getDbTable('categories', 'siteforum');
        $rName = $table->info('name');
        $select = $table->select()
                ->where('category_id = ?', $category_id);
        $row = $table->fetchRow($select);

        return $table->fetchRow($table->select()
                                ->where('category_id = ?', $row->cat_dependency));
    }

    public function getSubCategory($category_id) {

        $select = $this->select()
                ->where('cat_dependency != ?', 0)
                ->where('cat_dependency = ?', $category_id)
                ->order('order ASC');

        return $this->fetchAll($select);
    }

    public function getMaxCategoryOrder() {

        $select = $this->select()->from($this->info('name'), new Zend_Db_Expr('MAX(`order`) as max_order'));
        $data = $select->query()->fetch();
        $order = (int) @$data['max_order'];

        return $order;
    }

    public function getMaxSubCategoryOrder($category_id) {
        $select = $this->select()->from($this->info('name'), new Zend_Db_Expr('MAX(`order`) as max_order'))->where('cat_dependency = ?', $category_id);
        $data = $select->query()->fetch();
        $order = (int) @$data['max_order'];
        return $order;
    }

    public function category($category_id) {

        $select = $this->select()
                ->where('category_id = ?', $category_id);

        return $this->fetchAll($select);
    }
    
    public function iconUpload() {

    //MAKE DIRECTORY IN PUBLIC FOLDER
    @mkdir(APPLICATION_PATH . "/temporary/siteforum_icons", 0777);

    //COPY THE ICONS IN NEWLY CREATED FOLDER
    $dir = APPLICATION_PATH . "/application/modules/Siteforum/externals/images/icons";
    $public_dir = APPLICATION_PATH . "/temporary/siteforum_icons";

    if (is_dir($dir) && is_dir($public_dir)) {
      $files = scandir($dir);
      foreach ($files as $file) {
        if (strstr($file, '.png')) {
          @copy(APPLICATION_PATH . "/application/modules/Siteforum/externals/images/icons/$file", APPLICATION_PATH . "/temporary/siteforum_icons/$file");
        }
      }
      @chmod(APPLICATION_PATH . '/temporary/siteforum_icons', 0777);
    }

    //MAKE QUERY
    $select = $this->select()->from($this->info('name'), array('category_id', 'title', 'photo_id'));
    $categories = $this->fetchAll($select);

    //UPLOAD DEFAULT ICONS
    foreach ($categories as $category) {
      $categoryName = $category->title;
      
      $iconName = Engine_Api::_()->seaocore()->getSlug($categoryName, 225) . '.png';

      @chmod(APPLICATION_PATH . '/temporary/siteforum_icons', 0777);

      $file = array();
      $file['tmp_name'] = APPLICATION_PATH . "/temporary/siteforum_icons/$iconName";
      $file['name'] = $iconName;

      if (file_exists($file['tmp_name'])) {
        $name = basename($file['tmp_name']);
        $path = dirname($file['tmp_name']);
        $mainName = $path . '/' . $file['name'];

        @chmod($mainName, 0777);

        $photo_params = array(
            'parent_id' => $category->category_id,
            'parent_type' => "siteforum_category",
        );

        //RESIZE IMAGE WORK
        $image = Engine_Image::factory();
        $image->open($file['tmp_name']);
        $image->open($file['tmp_name']);
        $size = min($image->height, $image->width);
        $x = ($image->width - $size) / 2;
        $y = ($image->height - $size) / 2;

        $image->resample($x, $y, $size, $size, 16, 16)
          ->write($mainName)
          ->destroy();


        $photoFile = Engine_Api::_()->storage()->create($mainName, $photo_params);

        //UPDATE FILE ID IN CATEGORY TABLE
        if (!empty($photoFile->file_id)) {
          $contentType = Engine_Api::_()->getItem('forum_category', $category->category_id);
          $contentType->photo_id = $photoFile->file_id;
          $contentType->save();
        }
      }
    }
    
    $forumTable = Engine_Api::_()->getDbTable('forums', 'siteforum');
    
    $select = $forumTable->select()->from($forumTable->info('name'), array('forum_id', 'title', 'photo_id'));
    $forums = $this->fetchAll($select);

    //UPLOAD DEFAULT ICONS
    foreach ($forums as $forum) {
      $forumName = $forum->title;
      $iconName = Engine_Api::_()->seaocore()->getSlug($forumName, 225) . '.png';
      @chmod(APPLICATION_PATH . '/temporary/siteforum_icons', 0777);

      $file = array();
      $file['tmp_name'] = APPLICATION_PATH . "/temporary/siteforum_icons/$iconName";
      $file['name'] = $iconName;

      if (file_exists($file['tmp_name'])) {
        $name = basename($file['tmp_name']);
        $path = dirname($file['tmp_name']);
        $mainName = $path . '/' . $file['name'];

        @chmod($mainName, 0777);

        $photo_params = array(
            'parent_id' => $forum->forum_id,
            'parent_type' => "siteforum_forum",
        );

        //RESIZE IMAGE WORK
        $image = Engine_Image::factory();
        $image->open($file['tmp_name']);
        $image->open($file['tmp_name']);
        $size = min($image->height, $image->width);
        $x = ($image->width - $size) / 2;
        $y = ($image->height - $size) / 2;

        $image->resample($x, $y, $size, $size, 48, 48)
          ->write($mainName)
          ->destroy();


        $photoFile = Engine_Api::_()->storage()->create($mainName, $photo_params);

        //UPDATE FILE ID IN CATEGORY TABLE
        if (!empty($photoFile->file_id)) {
          $contentType = Engine_Api::_()->getItem('forum_forum', $forum->forum_id);
          $contentType->photo_id = $photoFile->file_id;
          $contentType->save();
        }
      }
    }

    //REMOVE THE CREATED PUBLIC DIRECTORY
    if (is_dir(APPLICATION_PATH . '/temporary/siteforum_icons')) {
      $files = scandir(APPLICATION_PATH . '/temporary/siteforum_icons');
      foreach ($files as $file) {
        $is_exist = file_exists(APPLICATION_PATH . "/temporary/siteforum_icons/$file");
        if ($is_exist) {
          @unlink(APPLICATION_PATH . "/temporary/siteforum_icons/$file");
        }
      }
      @rmdir(APPLICATION_PATH . '/temporary/siteforum_icons');
    }
  }


}
