<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Topic.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Model_Topic extends Core_Model_Item_Abstract {

    protected $_parent_type = 'forum';
    protected $_owner_type = 'user';
    protected $_children_types = array('forum_post');
    protected $_type = 'forum_topic';

    // Generic content methods

    public function getDescription() {
        if (!isset($this->store()->firstPost)) {
            $postTable = Engine_Api::_()->getDbtable('posts', 'siteforum');
            $postSelect = $postTable->select()
                    ->where('topic_id = ?', $this->getIdentity())
                    ->order('post_id ASC')
                    ->limit(1);
            $this->store()->firstPost = $postTable->fetchRow($postSelect);
        }
        if (isset($this->store()->firstPost)) {
            // strip HTML and BBcode
            $content = $this->store()->firstPost->body;
            $content = strip_tags($content);
            $content = preg_replace('|[[\/\!]*?[^\[\]]*?]|si', '', $content);
            return $content;
        }
        return '';
    }

    public function getHref($params = array()) {
        $params = array_merge(array(
            'route' => 'siteforum_topic',
            'reset' => true,
            'topic_id' => $this->getIdentity(),
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

    // hooks

    protected function _insert() {
        if (empty($this->forum_id)) {
            throw new Siteforum_Model_Exception('Cannot have a topic without a forum');
        }

        if (empty($this->user_id)) {
            throw new Siteforum_Model_Exception('Cannot have a topic without a user');
        }

        // Increment parent topic count
        $siteforum = $this->getParent();
        $siteforum->topic_count = new Zend_Db_Expr('topic_count + 1');
        $siteforum->modified_date = date('Y-m-d H:i:s');
        $siteforum->save();

        parent::_insert();
    }

    protected function _update() {
        if (empty($this->forum_id)) {
            throw new Siteforum_Model_Exception('Cannot have a topic without a forum');
        }

        if (empty($this->user_id)) {
            throw new Siteforum_Model_Exception('Cannot have a topic without a user');
        }

        if (!empty($this->_modifiedFields['forum_id'])) {
            $originalSiteforumIdentity = $this->getTable()->select()
                    ->from($this->getTable()->info('name'), 'forum_id')
                    ->where('topic_id = ?', $this->getIdentity())
                    ->limit(1)
                    ->query()
                    ->fetchColumn(0)
            ;
            if ($originalSiteforumIdentity != $this->forum_id) {
                $postsTable = Engine_Api::_()->getItemTable('forum_post');

                $topicLastPost = $this->getLastCreatedPost();

                $oldSiteforum = Engine_Api::_()->getItem('forum', $originalSiteforumIdentity);
                $newSiteforum = Engine_Api::_()->getItem('forum', $this->forum_id);

                $oldSiteforumLastPost = $oldSiteforum->getLastCreatedPost();
                $newSiteforumLastPost = $newSiteforum->getLastCreatedPost();

                // Update old siteforum
                $oldSiteforum->topic_count = new Zend_Db_Expr('topic_count - 1');
                $oldSiteforum->post_count = new Zend_Db_Expr(sprintf('post_count - %d', $this->post_count));
                if (!$oldSiteforumLastPost || $oldSiteforumLastPost->topic_id == $this->getIdentity()) {
                    // Update old siteforum last post
                    $oldSiteforumNewLastPost = $postsTable->select()
                            ->from($postsTable->info('name'), array('post_id', 'user_id'))
                            ->where('forum_id = ?', $originalSiteforumIdentity)
                            ->where('topic_id != ?', $this->getIdentity())
                            ->order('post_id DESC')
                            ->limit(1)
                            ->query()
                            ->fetch();
                    if ($oldSiteforumNewLastPost) {
                        $oldSiteforum->lastpost_id = $oldSiteforumNewLastPost['post_id'];
                        $oldSiteforum->lastposter_id = $oldSiteforumNewLastPost['user_id'];
                    } else {
                        $oldSiteforum->lastpost_id = 0;
                        $oldSiteforum->lastposter_id = 0;
                    }
                }
                $oldSiteforum->save();

                // Update new siteforum
                $newSiteforum->topic_count = new Zend_Db_Expr('topic_count + 1');
                $newSiteforum->post_count = new Zend_Db_Expr(sprintf('post_count + %d', $this->post_count));
                if (!$newSiteforumLastPost || strtotime($topicLastPost->creation_date) > strtotime($newSiteforumLastPost->creation_date)) {
                    // Update new siteforum last post
                    $newSiteforum->lastpost_id = $topicLastPost->post_id;
                    $newSiteforum->lastposter_id = $topicLastPost->user_id;
                }
                if (strtotime($topicLastPost->creation_date) > strtotime($newSiteforum->modified_date)) {
                    $newSiteforum->modified_date = $topicLastPost->creation_date;
                }
                $newSiteforum->save();

                // Update posts
                $postsTable->update(array(
                    'forum_id' => $this->forum_id,
                        ), array(
                    'topic_id = ?' => $this->getIdentity(),
                ));
            }
        }

        parent::_update();
    }

    protected function _delete() {

        $siteforum = $this->getParent();

        // Decrement siteforum topic and post count
        $siteforum->topic_count = new Zend_Db_Expr('topic_count - 1');
        $siteforum->post_count = new Zend_Db_Expr(sprintf('post_count - %s', $this->post_count));

        // Update siteforum last post
        $olderSiteforumLastPost = Engine_Api::_()->getDbtable('posts', 'siteforum')->select()
                ->where('forum_id = ?', $this->forum_id)
                ->where('topic_id != ?', $this->topic_id)
                ->order('post_id DESC')
                ->limit(1)
                ->query()
                ->fetch();

        if ($olderSiteforumLastPost['post_id'] != $siteforum->lastpost_id) {
            if ($olderSiteforumLastPost) {
                $siteforum->lastpost_id = $olderSiteforumLastPost['post_id'];
                $siteforum->lastposter_id = $olderSiteforumLastPost['user_id'];
            } else {
                $siteforum->lastpost_id = null;
                $siteforum->lastposter_id = null;
            }
        }

        $siteforum->save();

        // Delete all posts
        $table = Engine_Api::_()->getItemTable('forum_post');
        $select = $table->select()
                ->where('topic_id = ?', $this->getIdentity())
        ;

        foreach ($table->fetchAll($select) as $post) {
            $post->deletingTopic = true;
            $post->delete();
        }

        Engine_Api::_()->getDbTable('ratings', 'siteforum')->delete(array(
            'topic_id = ?' => $this->topic_id,
        ));

        // remove topic views
        Engine_Api::_()->getDbTable('topicviews', 'siteforum')->delete(array(
            'topic_id = ?' => $this->topic_id,
        ));

        // remove topic watches
        Engine_Api::_()->getDbTable('topicWatches', 'siteforum')->delete(array(
            'resource_id = ?' => $this->forum_id,
            'topic_id = ?' => $this->topic_id,
        ));

        parent::_delete();
    }

    public function getLastCreatedPost() {
        $post = Engine_Api::_()->getItem('forum_post', $this->lastpost_id);
        if (!$post) {
            // this can happen if the last post was deleted
            $table = Engine_Api::_()->getDbTable('posts', 'siteforum');
            $post = $table->fetchRow(array('topic_id = ?' => $this->getIdentity()), 'creation_date DESC');
            if ($post) {
                // update topic table with valid information
                $db = $table->getAdapter();
                $db->beginTransaction();
                try {
                    $row = Engine_Api::_()->getItem('forum_topic', $this->getIdentity());
                    $row->lastpost_id = $post->getIdentity();
                    $row->lastposter_id = $post->getOwner('user')->getIdentity();
                    $row->save();
                    $db->commit();
                } catch (Exception $e) {
                    $db->rollback();
                    // @todo silence error?
                }
            }
        }
        return $post;
    }

    public function registerView($user) {
        $table = Engine_Api::_()->getDbTable('topicviews', 'siteforum');
        $table->delete(array('topic_id = ?' => $this->getIdentity(), 'user_id = ?' => $user->getIdentity()));
        $row = $table->createRow();
        $row->user_id = $user->user_id;
        $row->topic_id = $this->topic_id;
        $row->last_view_date = date('Y-m-d H:i:s');
        $row->save();
    }

    public function isViewed($user) {
        $table = Engine_Api::_()->getDbTable('topicviews', 'siteforum');
        $row = $table->fetchRow($table->select()->where('user_id = ?', $user->getIdentity())->where('last_view_date > ?', $this->modified_date)->where("topic_id = ?", $this->getIdentity()));
        return $row != null;
    }

    public function getLastPage($per_page) {
        return $per_page > 0 ? ceil($this->post_count / $per_page) : 0;
    }

    public function getAuthorizationItem() {
        return $this->getParent();
    }

    public function tags() {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('tags', 'core'));
    }

    /**
     * Gets a proxy object for the comment handler
     *
     * @return Engine_ProxyObject
     * */
    public function comments() {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('comments', 'core'));
    }

    /**
     * Gets a proxy object for the like handler
     *
     * @return Engine_ProxyObject
     * */
    public function likes() {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'core'));
    }

}
