<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Core.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Siteforum_Plugin_Core {

    public function onStatistics($event) {
        $table = Engine_Api::_()->getDbTable('topics', 'siteforum');
        $select = new Zend_Db_Select($table->getAdapter());
        $select->from($table->info('name'), 'COUNT(*) AS count');
        $event->addResponse($select->query()->fetchColumn(0), 'forum topic');
    }

    public function onUserDeleteAfter($event) {
        $payload = $event->getPayload();
        $user_id = $payload['identity'];

        // Signatures
        $table = Engine_Api::_()->getDbTable('signatures', 'siteforum');
        $table->delete(array(
            'user_id = ?' => $user_id,
        ));

        // Moderators
        $table = Engine_Api::_()->getDbTable('listItems', 'siteforum');
        $select = $table->select()->where('child_id = ?', $user_id);
        $rows = $table->fetchAll($select);
        foreach ($rows as $row) {
            $row->delete();
        }

        // Topics
        $table = Engine_Api::_()->getDbTable('topics', 'siteforum');
        $select = $table->select()->where('user_id = ?', $user_id);
        $rows = $table->fetchAll($select);
        foreach ($rows as $row) {
            //$row->delete();
        }

        // Posts
        $table = Engine_Api::_()->getDbTable('posts', 'siteforum');
        $select = $table->select()->where('user_id = ?', $user_id);
        $rows = $table->fetchAll($select);
        foreach ($rows as $row) {
            //$row->delete();
        }

        // Topic views
        $table = Engine_Api::_()->getDbTable('topicviews', 'siteforum');
        $table->delete(array(
            'user_id = ?' => $user_id,
        ));
    }

    public function addActivity($event) {
        $payload = $event->getPayload();
        $object = $payload['object'];

        // Only for object=siteforum
        $innerObject = null;
        if ($object instanceof Siteforum_Model_Siteforum) {
            $innerObject = $object;
        } else if ($object instanceof Siteforum_Model_Topic) {
            $innerObject = $object->getParent();
        } else if ($object instanceof Siteforum_Model_Post) {
            $innerObject = $object->getParent()->getParent();
        }

        if ($innerObject) {
            $content = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.content', 'everyone');
            $allowTable = Engine_Api::_()->getDbtable('allow', 'authorization');

            // Siteforum
            $event->addResponse(array(
                'type' => 'siteforum',
                'identity' => $object->forum_id
            ));

            // Everyone
            $module = Engine_Api::_()->getDbTable('modules','core');
            $select = $module->select()->where('name = ?','forum');
            $forum = $module->fetchRow($select); 
            $select = $module->select()->where('name = ?','advancedactivity');
            $advancedactivity = $module->fetchRow($select); 
            
            if (empty($forum) && empty($advancedactivity) && $content == 'everyone' && $allowTable->isAllowed($object->getAuthorizationItem(), 'everyone', 'view')) {
                $event->addResponse(array(
                    'type' => 'everyone',
                    'identity' => 0,
                ));
            }
        }
    }

    public function getActivity($event) {
        // Detect viewer and subject
        $payload = $event->getPayload();
        $user = null;
        $subject = null;
        if ($payload instanceof User_Model_User) {
            $user = $payload;
        } else if (is_array($payload)) {
            if (isset($payload['for']) && $payload['for'] instanceof User_Model_User) {
                $user = $payload['for'];
            }
            if (isset($payload['about']) && $payload['about'] instanceof Core_Model_Item_Abstract) {
                $subject = $payload['about'];
            }
        }
        if (null === $user) {
            $viewer = Engine_Api::_()->user()->getViewer();
            if ($viewer->getIdentity()) {
                $user = $viewer;
            }
        }
        if (null === $subject && Engine_Api::_()->core()->hasSubject()) {
            $subject = Engine_Api::_()->core()->getSubject();
        }

        // Get siteforum
        if ($user) {
            $authTable = Engine_Api::_()->getDbtable('allow', 'authorization');
            $perms = $authTable->select()
                    ->where('resource_type = ?', 'siteforum')
                    ->where('action = ?', 'view')
                    ->query()
                    ->fetchAll();
            $siteforumIds = array();
            foreach ($perms as $perm) {
                if ($perm['role'] == 'everyone') {
                    $siteforumIds[] = $perm['resource_id'];
                } else if ($user &&
                        $user->getIdentity() &&
                        $perm['role'] == 'authorization_level' &&
                        $perm['role_id'] == $user->level_id) {
                    $siteforumIds[] = $perm['resource_id'];
                }
            }
            if (!empty($siteforumIds)) {
                $event->addResponse(array(
                    'type' => 'siteforum',
                    'data' => $siteforumIds,
                ));
            }
        } else {
            $authTable = Engine_Api::_()->getDbtable('allow', 'authorization');
            $perms = $authTable->select()
                    ->where('resource_type = ?', 'siteforum')
                    ->where('action = ?', 'view')
                    ->query()
                    ->fetchAll();
            $siteforumIds = array();
            foreach ($perms as $perm) {
                if ($perm['role'] == 'everyone') {
                    $siteforumIds[] = $perm['resource_id'];
                }
            }
            if (!empty($siteforumIds)) {
                $event->addResponse(array(
                    'type' => 'siteforum',
                    'data' => $siteforumIds,
                ));
            }
        }
    }

}
