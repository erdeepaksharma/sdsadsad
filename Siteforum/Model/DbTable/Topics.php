<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Topics.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Model_DbTable_Topics extends Engine_Db_Table {

    protected $_name = 'forum_topics';
    protected $_rowClass = 'Siteforum_Model_Topic';

    public function getChildrenSelectOfForum($siteforum, $params) {

        $select = $this->select()->where('forum_id = ?', $siteforum->forum_id);

        return $select;
    }

    public function getForumTopics($params = array()) {

        $topicTableName = $this->info('name');

        $select = $this->select()->from($topicTableName);

        if (!empty($params['forum_id']))
            $select->where('forum_id = ?', $params['forum_id']);

        if (!empty($params['search']))
            $select->where('title LIKE ? OR description LIKE ?', '%' . $params['search'] . '%');

        if (!empty($params['tag_id'])) {
            $tagMapTableName = Engine_Api::_()->getDbtable('TagMaps', 'core')->info('name');
            $select
                    ->setIntegrityCheck(false)
                    ->joinLeft($tagMapTableName, "$tagMapTableName.resource_id = $topicTableName.topic_id", array('tagmap_id', 'resource_type', 'resource_id', $tagMapTableName . '.tag_id'))
                    ->where($tagMapTableName . '.resource_type = ?', 'forum_topic')
                    ->where($tagMapTableName . '.tag_id = ?', $params['tag_id']);
        }
        
        $select->order('sticky DESC');

        if (!empty($params['popular_criteria']))
            $select->order($params['popular_criteria'] . ' DESC');

        if (!empty($params['limit'])) {
            $select->limit($params['limit']);
        }

        return Zend_Paginator::factory($select);
    }

    public function getSubscribedTopics($user_id) {

        $tableTopicWatches = Engine_Api::_()->getDbtable('topicWatches', 'siteforum');
        $tableTopicWatchesName = $tableTopicWatches->info('name');

        $topicTableName = $this->info('name');

        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($tableTopicWatchesName, array())
                ->join($topicTableName, "$tableTopicWatchesName.topic_id = $topicTableName.topic_id")
                ->where($tableTopicWatchesName . '.user_id = ?', $user_id)
                ->where($tableTopicWatchesName . '.watch = ?', 1);

        return Zend_Paginator::factory($select);
    }
// Sticky Topic Work
    public function getBookmarkedTopics($user_id) {

        $select = $this->select();
        $user_item = Engine_Api::_()->getItem('user',$user_id);
        
        if(!empty($user_item) && $user_item->level_id != 1)
                  $select->where('user_id = ? ', $user_id);
        
        $select->where('sticky = 1');

        return Zend_Paginator::factory($select);
    }

    public function getViewedTopics($user_id) {

        $tableTopicViewes = Engine_Api::_()->getDbtable('topicviews', 'siteforum');
        $tableTopicViewesName = $tableTopicViewes->info('name');

        $topicTableName = $this->info('name');

        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($tableTopicViewesName, array())
                ->join($topicTableName, "$tableTopicViewesName.topic_id = $topicTableName.topic_id")
                ->where($tableTopicViewesName . '.user_id = ?', $user_id);

        return Zend_Paginator::factory($select);
    }

    public function getLikedTopics($user_id) {

        $tableLike = Engine_Api::_()->getDbtable('likes', 'core');
        $tableLikeName = $tableLike->info('name');

        $topicTableName = $this->info('name');

        $select = $this->select()
                ->setIntegrityCheck(false)
                ->from($tableLikeName, array())
                ->join($topicTableName, "$tableLikeName.resource_id = $topicTableName.topic_id")
                ->where($tableLikeName . '.resource_type = ?', 'forum_topic')
                ->where($tableLikeName . '.poster_type = ?', 'user')
                ->where($tableLikeName . '.poster_id = ?', $user_id)
                ->order($topicTableName . '.creation_date DESC');

        return Zend_Paginator::factory($select);
    }

    public function getPaginator($params = array()) {

        $userTable = Engine_Api::_()->getItemTable('user');

        $userTableName = $userTable->info('name');

        $select = $userTable->select();

        if (!empty($params['post_id'])) {
            $repTable = Engine_Api::_()->getDbTable('reputations', 'siteforum');
            $repTableName = $repTable->info('name');
            $postItem = Engine_Api::_()->getItem('forum_post', $params['post_id']);
            $select->distinct()
                    ->setIntegrityCheck(false)
                    ->from($userTableName, array("user_id", "displayname", "username", 'photo_id'))
                    ->join($repTableName, "$repTableName.visitor_id = $userTableName.user_id", array(""))
                    ->where("$repTableName.user_id = ?", $postItem->user_id);
        }
        
        if(isset($params['reputation']))
            $select->where('reputation = ?',$params['reputation']);
        
        if (!empty($params['username']))
            $select->where('displayname LIKE ?', '%' . $params['username'] . '%');

        return Zend_Paginator::factory($select);
    }

    public function getPopularTopics($params = array()) {

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

    public function getPopularUsers($params = array()) {

        $table = Engine_Api::_()->getDbTable($params['table'], 'siteforum');
        $userTable = Engine_Api::_()->getDbTable('users', 'user');
        $userTableName = $userTable->info('name');
        $tableName = $table->info('name');
        $select = $userTable->select()
                ->setIntegrityCheck(false)
                ->from($userTableName)
                ->join($tableName, "$tableName.user_id = $userTableName.user_id", array("user_id", "count(" . $tableName . ".user_id) as total_result"));


        if (!empty($params['show_online_user'])) {

            $onlineTable = Engine_Api::_()->getDbtable('online', 'user');
            $onlineTableName = $onlineTable->info('name');

            $select->join($onlineTableName, "$tableName.user_id = $onlineTableName.user_id")
                    ->where($onlineTableName . '.active > ?', new Zend_Db_Expr('DATE_SUB(NOW(),INTERVAL 20 MINUTE)'));
        }

        $select->group($tableName . '.user_id')->order('total_result DESC');

        if (!empty($params['limit'])) {
            $select->limit($params['limit']);
        }

        return $userTable->fetchAll($select);
    }

}
