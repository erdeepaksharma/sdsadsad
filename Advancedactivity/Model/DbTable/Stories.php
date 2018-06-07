<?php

class Advancedactivity_Model_DbTable_Stories extends Engine_Db_Table {

    protected $_name = 'advancedactivity_stories';
    protected $_rowClass = 'Advancedactivity_Model_Story';

    public function getStoryPaginator($subject = array()) {
        return Zend_Paginator::factory($this->getStorySelect($subject));
    }

    public function getStorySelect($subject) {
        $storyTableName = $this->info('name');
        $select = $subject->membership()->getMembersOfSelect();
        $friends = $paginator = Zend_Paginator::factory($select);
        $paginator->setItemCountPerPage(1000);
        $paginator->setCurrentPageNumber(1);
        // Get stuff
        $currentDate = date('Y-m-d H:i:s');
        $days = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity_max_allowed_days', 1);
        $pastDate = date('Y-m-d H:i:s', strtotime('-' . $days . ' day', strtotime($currentDate)));
        $ids = array();
        //$ids[] = $subject->getIdentity();
        foreach ($friends as $friend) {
            $ids[] = $friend->resource_id;
        }
        if (count($ids) > 0) {
            $storyids = $this->getfriendStoryids($ids, $subject);
            
            $select = $this->select()
                    ->where('story_id IN(?)', $storyids)
                    ->where('create_date >= ?', $pastDate)
                    ->group("owner_id")
                    ->order("create_date DESC");
            return $select;
        } else {
            $select = $this->select()
                    ->where('create_date >= ?', $pastDate)
                    ->where("owner_id=?", $subject->getIdentity())
                    ->order("create_date DESC")
                    ->limit(1);
            return $select;
        }
    }

    public function getUserStory($user_id, $isSendMessage = 0) {
        if (empty($user_id))
            return;

        $currentDate = date('Y-m-d H:i:s');
        $days = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity_max_allowed_days', 1);
        $pastDate = date('Y-m-d H:i:s', strtotime('-' . $days . ' day', strtotime($currentDate)));
        if (!empty($isSendMessage)) {
            $select = $this->select()
                    ->where('owner_id =?', $user_id)
                    ->where('create_date >= ?', $pastDate)
                    ->order("create_date ASC");
        } else {
            $select = $this->select()
                    ->where('owner_id =?', $user_id)
                    ->where('create_date >= ?', $pastDate)
                    ->where('privacy = ?', 'everyone')
                    ->order("create_date ASC");
        }
        //$result = $this->fetchAll($select);
        //return $result;
        return Zend_Paginator::factory($select);
    }

    public function getyourStory($user_id) {
        if (empty($user_id))
            return;

        $currentDate = date('Y-m-d H:i:s');
        $days = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity_max_allowed_days', 1);
        $pastDate = date('Y-m-d H:i:s', strtotime('-' . $days . ' day', strtotime($currentDate)));
        $select = $this->select()
                ->where('owner_id =?', $user_id)
                ->where('create_date >= ?', $pastDate)
                ->order("create_date DESC");
        //->limit(1);
        return Zend_Paginator::factory($select);
    }

    public function getTotalStoryCount($user_id) {
        if (empty($user_id))
            return 0;

        $currentDate = date('Y-m-d H:i:s');
        $days = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity_max_allowed_days', 1);
        $pastDate = date('Y-m-d H:i:s', strtotime('-' . $days . ' day', strtotime($currentDate)));
        $select = $this->select()
                ->where('owner_id =?', $user_id)
                ->where('create_date >= ?', $pastDate)
                ->order("create_date DESC");
        $result = $this->fetchAll($select);
        return count($result);
    }

    public function getEveryoneStory($subject) {
        if (empty($subject))
            return;
        $storyTableName = $this->info('name');
        $select = $subject->membership()->getMembersOfSelect();
        $friends = $paginator = Zend_Paginator::factory($select);
        $paginator->setItemCountPerPage(1000);
        $paginator->setCurrentPageNumber(1);
        // Get stuff
        $currentDate = date('Y-m-d H:i:s');
        $days = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity_max_allowed_days', 1);
        $pastDate = date('Y-m-d H:i:s', strtotime('-' . $days . ' day', strtotime($currentDate)));
        $ids = array();
        //$ids[] = $subject->getIdentity();
        foreach ($friends as $friend) {
            $ids[] = $friend->resource_id;
        }
        if (count($ids) > 0) {
            $storyids = $this->getEveryoneStoryids($ids, $subject);
            $select1 = $this->select()
                    ->where('story_id IN(?)', $storyids)
                    ->where('create_date >= ?', $pastDate)
                    ->where('privacy = ?', 'everyone')
                    ->order("create_date DESC");
        } else {
            $storyids = $this->getEveryoneStoryids($ids, $subject);
            $select1 = $this->select()
                    ->where('story_id IN(?)', $storyids)
                    ->where('create_date >= ?', $pastDate)
                    ->where('privacy = ?', 'everyone')
                    ->order("create_date DESC");
        }
        return Zend_Paginator::factory($select1);
    }

    public function getEveryoneStoryids($ids = array(), $subject = array()) {


        $currentDate = date('Y-m-d H:i:s');
        $days = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity_max_allowed_days', 1);
        $pastDate = date('Y-m-d H:i:s', strtotime('-' . $days . ' day', strtotime($currentDate)));

        $table = Engine_Api::_()->getDbtable('stories', 'advancedactivity');
        if (count($ids) > 0) {
            $result = $table->select()
                            ->from($table->info('name'), array('story_id', 'owner_id'))
                            ->where('owner_id NOT IN(?)', $ids)
                            ->where('create_date >= ?', $pastDate)
                            ->where('privacy = ?', 'everyone')
                            ->order("create_date DESC")
                            ->query()->fetchAll();
        } else {
            $result = $this->select()
                            ->from($table->info('name'), array('story_id', 'owner_id'))
                            ->where('owner_id !=?', $subject->getIdentity())
                            ->where('create_date >= ?', $pastDate)
                            ->where('privacy = ?', 'everyone')
                            ->order("create_date DESC")
                            ->query()->fetchAll();
        }
        $ownerids = array();
        $storyids = array();
        $storyids[] = 0;
        foreach ($result as $value) {
            if (!in_array($value['owner_id'], $ownerids)) {
                $storyids[] = $value['story_id'];
                $ownerids[] = $value['owner_id'];
            }
        }
        return $storyids;
    }

    public function getfriendStoryids($ids = array(), $subject = array()) {


        $currentDate = date('Y-m-d H:i:s');
        $days = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity_max_allowed_days', 1);
        $pastDate = date('Y-m-d H:i:s', strtotime('-' . $days . ' day', strtotime($currentDate)));

        $table = Engine_Api::_()->getDbtable('stories', 'advancedactivity');
        if (count($ids) > 0) {
            $result = $table->select()
                            ->from($table->info('name'), array('story_id', 'owner_id'))
                            ->where('owner_id IN(?)', $ids)
                            ->where('create_date >= ?', $pastDate)
                            ->order("create_date DESC")
                            ->query()->fetchAll();
        } else {
            $result = $this->select()
                            ->from($table->info('name'), array('story_id', 'owner_id'))
                            ->where('owner_id =?', $subject->getIdentity())
                            ->where('create_date >= ?', $pastDate)
                            ->order("create_date DESC")
                            ->query()->fetchAll();
        }
        $ownerids = array();
        $storyids = array();
        $storyids[] = 0;
        foreach ($result as $value) {
            if (!in_array($value['owner_id'], $ownerids)) {
                $storyids[] = $value['story_id'];
                $ownerids[] = $value['owner_id'];
            }
        }
        return $storyids;
    }

}

?>
