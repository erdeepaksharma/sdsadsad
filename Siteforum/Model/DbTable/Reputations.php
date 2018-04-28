<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Reputations.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Model_DbTable_Reputations extends Engine_Db_Table {

    protected $_name = 'forum_reputations';
    protected $_rowClass = "Siteforum_Model_Reputation";

    public function setReputation($user_id, $visitor_id, $topic_id, $reputation) {

        $user = Engine_Api::_()->getItem('user', $user_id);
        $viewer = Engine_Api::_()->getItem('user', $visitor_id);
        $post = Engine_Api::_()->getItem('forum_post', $topic_id);

        Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($user, $viewer, $post, 'siteforum_reputation');

        $this->insert(array(
            'visitor_id' => $visitor_id,
            'user_id' => $user_id,
            'post_id' => $topic_id,
            'reputation' => $reputation
        ));
    }

    public function checkReputation($user_id, $visitor_id, $post_id) {
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->where('post_id = ?', $post_id)
                ->where('user_id = ?', $user_id)
                ->where('visitor_id = ?', $visitor_id)
                ->limit(1);
        $row = $this->fetchAll($select);
        if (count($row) > 0) {
            return 1;
        } {
            return 0;
        }
    }

    public function reputationCount($user_id) {
        $reputation = array();

        $select = $this->select()
                ->where('user_id = ?', $user_id)
                ->where('reputation = ?', 1);
        $row = $this->fetchAll($select);
        $plus = count($row);

        $select = $this->select()
                ->where('user_id = ?', $user_id)
                ->where('reputation = ?', 0);
        $row = $this->fetchAll($select);
        $minus = count($row);

        $reputation[] = $plus;
        $reputation[] = $minus;

        return $reputation;
    }

    public function reputationgivenCount($user_id) {
        $select = $this->select()
                ->where('visitor_id = ?', $user_id)
                ->where('reputation = ?', 1);
        $row = $this->fetchAll($select);
        $plus = count($row);

        $select = $this->select()
                ->where('visitor_id = ?', $user_id)
                ->where('reputation = ?', 0);
        $row = $this->fetchAll($select);
        $minus = count($row);

        $total = $plus - $minus;
        return $total;
    }
    
    public function reputationCounts($reputation,$post_id){
      
      $post_table  = Engine_Api::_()->getDbTable('posts','siteforum');
      $user_id = $post_table->select()
                  ->from($post_table->info('name'), 'user_id')
                  ->where('post_id =?',$post_id)            
                  ->query()
                  ->fetchColumn(0)
                  ;
      return $this->select()
            ->from($this->info('name'), new Zend_Db_Expr('COUNT(*)'))
            ->where('reputation = ?', $reputation)
            ->where('user_id =?',$user_id)
            ->query()
            ->fetchColumn(0)
            ;
    }

}
