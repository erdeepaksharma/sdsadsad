<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Posts.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Model_DbTable_Posts extends Engine_Db_Table {

    protected $_name = 'forum_posts';
    protected $_rowClass = 'Siteforum_Model_Post';

    public function getChildrenSelectOfForumTopic($topic) {
        $select = $this->select()->where('topic_id = ?', $topic->topic_id);
        return $select;
    }

    public function getPaginator($params = array()) {

        $userTable = Engine_Api::_()->getItemTable('user');
        $thanksTable = Engine_Api::_()->getDbTable('thanks', 'siteforum');
        $userTableName = $userTable->info('name');
        $thanksTableName = $thanksTable->info('name');
        $select = $userTable->select();

        if (!empty($params['post_id'])) {

            $postItem = Engine_Api::_()->getItem('forum_post', $params['post_id']);
            $select->distinct()
                    ->setIntegrityCheck(false)
                    ->from($userTableName, array("user_id", "displayname", "username", 'photo_id'))
                    ->join($thanksTableName, "$thanksTableName.visitor_id = $userTableName.user_id", array(""))
                    ->where("$thanksTableName.user_id = ?", $postItem->user_id);
            //->where("$thanksTableName.post_id = ?", $this->getParam('topic_post_id'));
        }

        if (!empty($params['username']))
            $select->where('displayname LIKE ?', '%' . $params['username'] . '%');

        return Zend_Paginator::factory($select);
    }

    public function getPopularPosts($params = array()) {

        $select = $this->select();

        if (!empty($params['forum_ids']))
            $select->where('forum_id IN(?)', $params['forum_ids']);

        if (!empty($params['popular_criteria']))
            $select->order($params['popular_criteria'] . ' DESC');

        if (!empty($params['user_id'])) {
            $select->where('user_id = ?', $params['user_id']);
            return Zend_Paginator::factory($select);
        }

        if (!empty($params['limit']))
            $select->limit($params['limit']);

        return $this->fetchAll($select);
    }

}
