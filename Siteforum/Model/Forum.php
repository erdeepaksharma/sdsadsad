<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Forum.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Model_Forum extends Core_Model_Item_Collectible {

    protected $_children_types = array('forum_topic');
    protected $_parent_type = 'forum_category';
    protected $_owner_type = 'forum_category';
    protected $_collection_type = 'forum_category';
    protected $_collection_column_name = 'category_id';
    protected $_type = 'forum';
    protected $_collectible_type = 'forum_forum';

    //We use membership system to manage moderators
    public function membership() {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('membership', 'siteforum'));
    }

    public function getCollection() {
        return Engine_Api::_()->getItem($this->_collection_type, $this->category_id);
    }

    public function getHighestOrder($params = array()) {

        $table = Engine_Api::_()->getItemTable($this->_collectible_type);
        if (!in_array('order', $table->info('cols'))) {
            throw new Core_Model_Item_Exception('Unable to use order as order column doesn\'t exist');
        }

        $select = $table->select();
        if (!empty($params['category_id'])) {

            $select
                    ->from($table->info('name'), new Zend_Db_Expr('MAX(`order`) as max_order'))
                    ->where('category_id = ?', $params['category_id'])
                    ->where('subcategory_id = ?', 0);
        } else if (!empty($params['subcategory_id'])) {
            $select
                    ->from($table->info('name'), new Zend_Db_Expr('MAX(`order`) as max_order'))
                    ->where('subcategory_id = ?', $params['subcategory_id'])
            ;
        }


        $data = $select->query()->fetch();
        $next = (int) @$data['max_order'];
        return $next;
    }

    public function getHref($params = array()) {
        $params = array_merge(array(
            'route' => 'siteforum_forum',
            'reset' => true,
            'forum_id' => $this->getIdentity(),
            'slug' => $this->getSlug(),
            'action' => 'view',
                ), $params);
        $route = $params['route'];
        $reset = $params['reset'];
        unset($params['route']);
        unset($params['reset']);
        return Zend_Controller_Front::getInstance()->getRouter()
                        ->assemble($params, $route, $reset);
    }

    public function getSlug($str = null, $maxstrlen = 64) {
        $translate = Zend_Registry::get('Zend_Translate');
        $title = $translate->translate($this->getTitle());
        return parent::getSlug($title);
    }

    public function getLastCreatedPost() {
        return Engine_Api::_()->getItem('forum_post', $this->lastpost_id);
    }

    public function getLastUpdatedTopic() {
        $lastPost = Engine_Api::_()->getItem('forum_post', $this->lastpost_id);
        if (!$lastPost)
            return false;
        return Engine_Api::_()->getItem('forum_topic', $lastPost->topic_id);
        //return $this->getChildren('siteforum_topic', array('limit'=>1, 'order'=>'modified_date DESC'))->current();
    }

    public function getParent($recurseType = null) {
        return Engine_Api::_()->getItem('forum_category', $this->category_id);
    }

    public function getOwner($recurseType = null) {
        return $this->getParent();
    }

// Hooks

    protected function _insert() {
        if (empty($this->category_id)) {
            throw new Siteforum_Model_Exception('Cannot have a forum without a category');
        }

        // Increment parent siteforum count
        $category = $this->getParent();
        $category->forum_count = new Zend_Db_Expr('forum_count + 1');
        $category->save();

        parent::_insert();
    }

    protected function _update() {
        if (empty($this->category_id)) {
            throw new Siteforum_Model_Exception('Cannot have a forum without a category');
        }

        parent::_update();
    }

    protected function _delete() {
        // Decrement parent siteforum count


        $category = $this->getParent();
        if($category->forum_count)
        $category->forum_count = new Zend_Db_Expr('forum_count - 1');
        $category->save();

        // Delete all child topics
        $table = Engine_Api::_()->getItemTable('forum_topic');
        $select = $table->select()
                ->where('forum_id = ?', $this->getIdentity())
        ;
        foreach ($table->fetchAll($select) as $topic) {
            $topic->delete();
        }


        parent::_delete();
    }

    public function isModerator($user) {
        $list = $this->getModeratorList();
        return $list->has($user);
    }

    public function getModeratorList() {
        $table = Engine_Api::_()->getItemTable('forum_list');
        $select = $table->select()
                ->where('owner_id = ?', $this->getIdentity())
                ->limit(1);

        $list = $table->fetchRow($select);

        if (null === $list) {
            $list = $table->createRow();
            $list->setFromArray(array(
                'owner_id' => $this->getIdentity(),
            ));
            $list->save();
        }

        return $list;
    }

    public function getPrevSiteforum() {
        $table = $this->getTable();
        if (!in_array('order', $table->info('cols'))) {
            throw new Core_Model_Item_Exception('Unable to use order as order column doesn\'t exist');
        }

        $select = $table->select()
                ->where('`order` < ?', $this->order)
                // Should be confined to a category
                ->where('`category_id` = ?', $this->category_id)
                ->order('order DESC')
                ->limit(1);

        return $table->fetchRow($select);
    }

    public function moveUp() {
        $table = $this->getTable();
        $db = $table->getAdapter();
        $db->beginTransaction();
        try {

            if ($this->subcategory_id == 0) {
                $siteforums = $table->select()->where('category_id = ?', $this->category_id)->order('order ASC')->query()->fetchAll();
            } else {
                $siteforums = $table->select()->where('category_id = ?', $this->category_id)->where('subcategory_id = ?', $this->subcategory_id)->order('order ASC')->query()->fetchAll();
            }
            $newOrder = array();
            foreach ($siteforums as $siteforum) {
                if ($this->forum_id == $siteforum['forum_id']) {
                    $prevSiteforum = array_pop($newOrder);
                    array_push($newOrder, $siteforum['forum_id']);
                    if ($prevSiteforum) {
                        array_push($newOrder, $prevSiteforum);
                        unset($prevSiteforum);
                    }
                } else {
                    array_push($newOrder, $siteforum['forum_id']);
                }
            }

            if ($this->subcategory_id == 0) {
                $select = $table->select()->where('category_id = ?', $this->category_id);
                foreach ($table->fetchAll($select) as $row) {
                    $order = array_search($row->forum_id, $newOrder);
                    $row->order = $order + 1;
                    $row->save();
                }
            } else {
                $select = $table->select()->where('category_id = ?', $this->category_id)->where('subcategory_id = ?', $this->subcategory_id);
                foreach ($table->fetchAll($select) as $row) {
                    $order = array_search($row->forum_id, $newOrder);
                    $row->order = $order + 1;
                    $row->save();
                }
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
            'parent_id' => $this->forum_id,
            'parent_type' => "forum_forum",
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

}
