<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: TopicWatches.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Model_DbTable_TopicWatches extends Engine_Db_Table {

    protected $_name = 'forum_topicwatches';

    public function isWatching($params = array()) {
        $select = $this->select();
        $select
                ->from($this->info('name'), 'watch')
                ->where('resource_id = ?', $params['resource_id'])
                ->where('topic_id = ?', $params['topic_id'])
                ->where('user_id = ?', $params['user_id'])
                ->limit(1);
        return $select->query()->fetchColumn(0);
    }

}
