<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Category.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Model_Category extends Core_Model_Item_Collection {

    protected $_children_types = array('forum_forum');
    protected $_collectible_type = "forum_forum";
    protected $_collection_column_name = "category_id";
    protected $_type = 'forum_category';

    public function getHref($params = array()) {
        $params = array_merge(array(
            'route' => 'siteforum_general',
            'reset' => true,
                ), $params);
        $route = $params['route'];
        $reset = $params['reset'];
        unset($params['route']);
        unset($params['reset']);
        return Zend_Controller_Front::getInstance()->getRouter()
                        ->assemble($params, $route, $reset);
    }

    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = Engine_Api::_()->getDbtable('categories', 'siteforum');
        }
        return $this->_table;
    }

    protected function getPrevCategory() {
        $table = Engine_Api::_()->getItemTable('forum_category');
        if (!in_array('order', $table->info('cols'))) {
            throw new Core_Model_Item_Exception('Unable to use order as order column doesn\'t exist');
        }

        $select = $table->select()->setIntegrityCheck(false)
                ->from($table->info('name'), 'MAX(`order`) AS max_order')
                ->where('`order` < ?', $this->order);

        $row = $select->query()->fetch();
        return $table->fetchAll($table->select()->where('`order` = ?', $row['max_order']))->current();
    }

    public function moveUp() {

        $table = $this->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $categories = $table->select()->where('cat_dependency = 0')->order('order ASC')->query()->fetchAll();
            $new_cats = array();
            foreach ($categories as $category) {
                if ($this->category_id == $category['category_id']) {
                    $prev_cat = array_pop($new_cats);
                    array_push($new_cats, $category['category_id']);
                    if ($prev_cat) {
                        array_push($new_cats, $prev_cat);
                        unset($prev_cat);
                    }
                } else {
                    array_push($new_cats, $category['category_id']);
                }
            }
            foreach ($table->fetchAll($table->select()->where('cat_dependency = 0')) as $row) {

                $order = array_search($row->category_id, $new_cats);
                $row->order = $order + 1;
                $row->save();
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

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

        $photo_params = array(
            'parent_id' => $this->category_id,
            'parent_type' => "forum_category",
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

    public function moveSubCategoryUp($category_id) {

        $table = $this->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {
            $categories = $table->select()->where('cat_dependency = ?', $category_id)->order('order ASC')->query()->fetchAll();

            $new_cats = array();
            foreach ($categories as $category) {
                if ($this->category_id == $category['category_id']) {
                    $prev_cat = array_pop($new_cats);
                    array_push($new_cats, $category['category_id']);
                    if ($prev_cat) {
                        array_push($new_cats, $prev_cat);
                        unset($prev_cat);
                    }
                } else {
                    array_push($new_cats, $category['category_id']);
                }
            }
            foreach ($table->fetchAll($table->select()->where('cat_dependency = ?', $category_id)) as $row) {
                $order = array_search($row->category_id, $new_cats);
                $row->order = $order + 1;
                $row->save();
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

}
