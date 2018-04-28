<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Thanks.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Model_DbTable_Thanks extends Engine_Db_Table {

    protected $_name = 'forum_thanks';
    protected $_rowClass = "Siteforum_Model_Thank";

    public function getPostIds() {
        $select = $this->select()
                ->order('count(post_id) desc')
                ->group('post_id');
        $postIds = $this->fetchAll($select);
        $postIdsArray = array();
        foreach ($postIds as $postId) {
            $postIdsArray[] = $postId->post_id;
        }

        return $postIdsArray;
    }

    public function countThanked($user_id) {

        $select = $this->select()
                ->where('visitor_id = ?', $user_id);
        $row = $this->fetchAll($select);
        $total = count($row);
        return $total;
    }

    public function countThanks($user_id) {

        $select = $this->select()
                ->where('user_id = ?', $user_id);
        $row = $this->fetchAll($select);
        $total = count($row);
        return $total;
    }

    //function to set thanks
    public function setThanks($user_id, $visitor_id, $topic_id) {

        $user = Engine_Api::_()->getItem('user', $user_id);
        $viewer = Engine_Api::_()->getItem('user', $visitor_id);
        $post = Engine_Api::_()->getItem('forum_post', $topic_id);

        $post->thanks_count = new Zend_Db_Expr('thanks_count + 1');
        $post->save();

        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $post, 'siteforum_thanks');

        $select = $this->select()
                ->where('post_id = ?', $topic_id)
                ->where('user_id = ?', $user_id)
                ->where('visitor_id = ?', $visitor_id);
        $row = $this->fetchRow($select);
        if (empty($row)) {
            // create rating
            Engine_Api::_()->getDbTable('thanks', 'siteforum')->insert(array(
                'post_id' => $topic_id,
                'user_id' => $user_id,
                'visitor_id' => $visitor_id,
            ));
        }
    }

    public function checkThanked($post_id, $user_id, $visitor_id) {
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->where('post_id = ?', $post_id)
                ->where('user_id = ?', $user_id)
                ->where('visitor_id = ?', $visitor_id)
                ->limit(1);
        $row = $this->fetchAll($select);
        if (count($row) > 0) {
            return 1;
        } else {
            return 0;
        }
    }

}
