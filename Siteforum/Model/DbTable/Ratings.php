<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Ratings.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Model_DbTable_Ratings extends Engine_Db_Table {

    protected $_name = 'forum_ratings';
    protected $_rowClass = "Siteforum_Model_Rating";

    public function getRating($topic_id) {
        $rating_sum = $this->select()
                ->from($this->info('name'), new Zend_Db_Expr('SUM(rating)'))
                ->group('topic_id')
                ->where('topic_id = ?', $topic_id)
                ->query()
                ->fetchColumn(0)
        ;


        $total = $this->ratingCount($topic_id);
        if ($total)
            $rating = $rating_sum / $this->ratingCount($topic_id);
        else
            $rating = 0;
        return $rating;
    }

    public function getRatings($topic_id) {
        $select = $this->select()
                ->where('topic_id = ?', $topic_id);
        $row = $this->fetchAll($select);
        return $row;
    }

    public function setRating($topic_id, $user_id, $rating) {



        $select = $this->select()
                ->where('topic_id = ?', $topic_id)
                ->where('user_id = ?', $user_id);
        $row = $this->fetchRow($select);
        if (empty($row)) {
            // create rating
            Engine_Api::_()->getDbTable('ratings', 'siteforum')->insert(array(
                'topic_id' => $topic_id,
                'user_id' => $user_id,
                'rating' => $rating
            ));
        }
    }

    public function totalRating($topic_id) {
        $rating_sum = $this->select()
                ->from($this->info('name'), new Zend_Db_Expr('SUM(rating)'))
                ->where('topic_id = ?', $topic_id)
                ->query()
                ->fetchColumn(0);
        return $rating_sum;
    }

    public function checkRated($topic_id, $user_id) {
        $select = $this->select()
                ->setIntegrityCheck(false)
                ->where('topic_id = ?', $topic_id)
                ->where('user_id = ?', $user_id)
                ->limit(1);
        $row = $this->fetchAll($select);

        if (count($row) > 0)
            return 1;
        return 0;
    }

    public function ratingCount($topic_id) {

        $select = $this->select()
                ->where('topic_id = ?', $topic_id);
        $row = $this->fetchAll($select);
        $total = count($row);
        return $total;
    }

}
