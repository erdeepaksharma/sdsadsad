<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Forums.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Model_DbTable_Forums extends Engine_Db_Table {

    protected $_name = 'forum_forums';
    protected $_rowClass = 'Siteforum_Model_Forum';

    public function getChildrenSelectOfForumCategory($category) {
        return $this->select()->where('subcategory_id = ?', $category->category_id);
    }

    public function getForums($params = array()) {

        if (!empty($params['category_id'])) {
            $category_id = $params['category_id'];
            $category = Engine_Api::_()->getItem('forum_category', $category_id);
            if (empty($category->cat_dependency)) {
                $select = $this->select()
                        ->where('category_id = ?', $category_id)
                        ->where('subcategory_id = ?', 0)
                        ->order('order');

                $row = $this->fetchAll($select);
                return $row;
            } else {
                $select = $this->select()
                        ->where('subcategory_id = ?', $category_id)
                        ->order('order');

                $row = $this->fetchAll($select);
                return $row;
            }
        }
    }

    public function getForumStatistics($params = array()) {

        $statistics = array();

        $postTable = Engine_Api::_()->getDbTable('posts', 'siteforum');
        $userTable = Engine_Api::_()->getDbTable('users', 'user');

        $select = $this->select();
        $activeUserSelect = $postTable->select();
        $totalUserSelect = $userTable->select();

        if (!empty($params['forum_id'])) {
            $select->where('forum_id = ?', $params['forum_id']);
            $activeUserSelect
                    ->from($postTable->info('name'), array("user_id"))
                    ->where('forum_id = ?', $params['forum_id'])
                    ->group('user_id');
            $totalUserSelect
                    ->from($userTable->info('name'), array("COUNT(*) as user_count"))
                    ->where('approved = ?', 1);
            $statistics['active_user'] = count($postTable->fetchAll($activeUserSelect)->toArray());
            $statistics['forum'] = $this->fetchRow($select)->toArray();
            $statistics['total_user'] = $userTable->fetchRow($totalUserSelect)->toArray();

            return $statistics;
        } else {
            $select->from($this->info('name'), array("SUM(post_count) as post_count", "SUM(topic_count) as topic_count", "COUNT(*) as forum_count"));
            $activeUserSelect
                    ->from($postTable->info('name'), array("user_id"))
                    ->group('user_id');
            $totalUserSelect
                    ->from($userTable->info('name'), array("COUNT(*) as user_count"))
                    ->where('approved = ?', 1);

            $statistics['active_user'] = count($postTable->fetchAll($activeUserSelect)->toArray());
            $statistics['forum'] = $this->fetchRow($select)->toArray();
            $statistics['total_user'] = $userTable->fetchRow($totalUserSelect)->toArray();

            return $statistics;
        }
    }

    public function getCategoryForum($category) {
        $select = $this->select()
                ->where('category_id = ?', $category->getIdentity())
                ->where('subcategory_id = ?', 0)
                ->order('order ASC');

        $row = $this->fetchAll($select);
        return $row;
    }

    public function getForumsAssoc() {
        $stmt = $this->select()
                ->from($this, array('forum_id', 'title'))
                ->order('title ASC')
                ->query();

        $data = array();
        foreach ($stmt->fetchAll() as $forum) {
            $data[$forum['forum_id']] = $forum['title'];
        }

        return $data;
    }

}
