<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteapi
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    FriendController.php 2015-09-17 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_FriendsController extends Siteapi_Controller_Action_Standard {

    public function init() {
        // Try to set subject
        $user_id = $this->getRequestParam('user_id', null);
        if ($user_id && !Engine_Api::_()->core()->hasSubject()) {
            $user = Engine_Api::_()->getItem('user', $user_id);
            if ($user) {
                Engine_Api::_()->core()->setSubject($user);
            }
        }
        Engine_Api::_()->getApi('Core', 'siteapi')->setView();
    }

    public function suggestAction() {
        // Validate request methods
        $this->validateRequestMethod();

        $data = array();
        $subject_guid = $this->getRequestParam('subject', null);
        $viewer = Engine_Api::_()->user()->getViewer();
        if ($subject_guid && (stripos($subject_guid, 'event') !== false || stripos($subject_guid, 'group') !== false)) {
            $subject = Engine_Api::_()->getItemByGuid($subject_guid);
        } else {
            $subject = $viewer;
        }

        if ($viewer->getIdentity()) {
            $data = array();
            $table = Engine_Api::_()->getItemTable('user');
            $select = $subject->membership()->getMembersObjectSelect();

            if (0 < ($limit = (int) $this->getRequestParam('limit', 10))) {
                $select->limit($limit);
            }

            if (null !== ($text = $this->getRequestParam('search', $this->getRequestParam('value')))) {
                $select->where('`' . $table->info('name') . '`.`displayname` LIKE ?', '%' . $text . '%');
            }
            $select->where('`' . $table->info('name') . '`.`user_id` <> ?', $viewer->getIdentity());
            $select->order("{$table->info('name')}.displayname ASC");
            $ids = array();
            foreach ($select->getTable()->fetchAll($select) as $friend) {
                $tempData['type'] = 'user';
                $tempData['id'] = $friend->getIdentity();
                $tempData['guid'] = $friend->getGuid();
                $tempData['label'] = trim($friend->getTitle());

                // Add images
                $getContentImages = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($friend);
                $tempData = array_merge($tempData, $getContentImages);

                $data[] = $tempData;
            }
        }

        $this->respondWithSuccess($data);
    }

    public function suggestTagAction() {
        $viewer = Engine_Api::_()->user()->getViewer();

        if (!$this->_helper->requireUser()->isValid())
            $this->respondWithError('unauthorized');

        if (!$viewer->getIdentity()) {
            $data = null;
        } else {
            $subject = $viewer;
            $data = array();
            $enableContent = Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.tagging.module', array('friends', 'sitepage', 'sitebusiness', 'sitegroup', 'sitestore', 'list', 'group', 'event'));
            if (in_array('friends', $enableContent)) {
                $table = Engine_Api::_()->getItemTable('user');
                $select = $subject->membership()->getMembersObjectSelect();

                if ($this->_getParam('includeSelf', false) && stripos($viewer->getTitle(), $this->_getParam('search', $this->_getParam('value'))) !== false) {
                    $image = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($viewer);
                    $data[] = array(
                        'type' => 'user',
                        'id' => $viewer->getIdentity(),
                        'guid' => $viewer->getGuid(),
                        'label' => $viewer->getTitle() . ' ' . $this->translate('(you)'),
                        'photo' => $image['image_icon'],
                        'url' => $viewer->getHref(),
                    );
                }

                if (0 < ($limit = (int) $this->_getParam('limit', 10))) {
                    $select->limit($limit);
                }

                if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
                    $select->where('`' . $table->info('name') . '`.`displayname` LIKE ?', '%' . $text . '%');
                }
                $select->where('`' . $table->info('name') . '`.`user_id` <> ?', $viewer->getIdentity());
                $select->order("{$table->info('name')}.displayname ASC");
                $ids = array();
                foreach ($select->getTable()->fetchAll($select) as $friend) {
                    $image = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($friend);
                    $data[] = array(
                        'type' => 'user',
                        'id' => $friend->getIdentity(),
                        'guid' => $friend->getGuid(),
                        'label' => $friend->getTitle(),
                        'photo' => $image['image_icon'],
                        'url' => $friend->getHref(),
                    );
                    $ids[] = $friend->getIdentity();
                    $friend_data[$friend->getIdentity()] = $friend->getTitle();
                }
            }

            if (in_array('sitepage', $enableContent) && Engine_Api::_()->hasItemType('sitepage_page')) {
                $remaningLimit = $limit - @count($data);
                if ($remaningLimit > 0) {
                    $table = Engine_Api::_()->getItemTable('sitepage_page');
                    $tableName = $table->info('name');
                    $select = $table->getPagesSelectSql(array('limit' => $remaningLimit));
                    // $select = $table->getPagesSelectSql();
                    if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
                        $select->where('`' . $table->info('name') . '`.`title` LIKE ?', '%' . $text . '%');
                    }
                    $select->order("{$tableName}.title ASC");
                    foreach ($select->getTable()->fetchAll($select) as $page) {
                        $image = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($page);
                        $data[] = array(
                            'type' => ucfirst($this->translate('sitepage_page')),
                            'id' => $page->getIdentity(),
                            'guid' => $page->getGuid(),
                            'label' => $page->getTitle(),
                            'photo' => $image['image_icon'],
                            'url' => $page->getHref(),
                        );
                        $ids[] = $page->getIdentity();
                    }
                }
            }

            if (in_array('sitebusiness', $enableContent) && Engine_Api::_()->hasItemType('sitebusiness_business')) {
                $remaningLimit = $limit - @count($data);
                if ($remaningLimit > 0) {
                    $table = Engine_Api::_()->getItemTable('sitebusiness_business');
                    $tableName = $table->info('name');
                    $select = $table->getBusinessesSelectSql(array('limit' => $remaningLimit));
                    if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
                        $select->where('`' . $table->info('name') . '`.`title` LIKE ?', '%' . $text . '%');
                    }
                    $select->order("{$tableName}.title ASC");
                    foreach ($select->getTable()->fetchAll($select) as $business) {
                        $image = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($business);
                        $data[] = array(
                            'type' => ucfirst($this->translate('sitebusiness_business')),
                            'id' => $business->getIdentity(),
                            'guid' => $business->getGuid(),
                            'label' => $business->getTitle(),
                            'photo' => $image['image_icon'],
                            'url' => $business->getHref(),
                        );
                        $ids[] = $business->getIdentity();
                    }
                }
            }

            if (in_array('sitegroup', $enableContent) && Engine_Api::_()->hasItemType('sitegroup_group')) {
                $remaningLimit = $limit - @count($data);
                if ($remaningLimit > 0) {
                    $table = Engine_Api::_()->getItemTable('sitegroup_group');
                    $tableName = $table->info('name');
                    $select = $table->getGroupsSelectSql(array('limit' => $remaningLimit));
                    // $select = $table->getPagesSelectSql();
                    if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
                        $select->where('`' . $table->info('name') . '`.`title` LIKE ?', '%' . $text . '%');
                    }
                    $select->order("{$tableName}.title ASC");
                    foreach ($select->getTable()->fetchAll($select) as $group) {
                        $image = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($group);
                        $data[] = array(
                            'type' => ucfirst($this->translate('sitegroup_group')),
                            'id' => $group->getIdentity(),
                            'guid' => $group->getGuid(),
                            'label' => $group->getTitle(),
                            'photo' => $image['image_icon'],
                            'url' => $group->getHref(),
                        );
                        $ids[] = $group->getIdentity();
                    }
                }
            }
            if (in_array('sitestore', $enableContent) && Engine_Api::_()->hasItemType('sitestore_store')) {
                $remaningLimit = $limit - @count($data);
                if ($remaningLimit > 0) {
                    $table = Engine_Api::_()->getItemTable('sitestore_store');
                    $tableName = $table->info('name');
                    $select = $table->getStoresSelectSql(array('limit' => $remaningLimit));
                    // $select = $table->getPagesSelectSql();
                    if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
                        $select->where('`' . $table->info('name') . '`.`title` LIKE ?', '%' . $text . '%');
                    }
                    $select->order("{$tableName}.title ASC");
                    foreach ($select->getTable()->fetchAll($select) as $store) {
                        $image = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($store);
                        $data[] = array(
                            'type' => ucfirst($this->translate('sitestore_store')),
                            'id' => $store->getIdentity(),
                            'guid' => $store->getGuid(),
                            'label' => $store->getTitle(),
                            'photo' => $image['image_icon'],
                            'url' => $store->getHref(),
                        );
                        $ids[] = $store->getIdentity();
                    }
                }
            }


            if (in_array('list', $enableContent) && Engine_Api::_()->hasItemType('list_listing')) {
                $remaningLimit = $limit - @count($data);
                if ($remaningLimit > 0) {
                    $table = Engine_Api::_()->getItemTable('list_listing');
                    $tableName = $table->info('name');
                    $select = $table->getListingSelectSql(array('limit' => $remaningLimit));
                    if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
                        $select->where('`' . $table->info('name') . '`.`title` LIKE ?', '%' . $text . '%');
                    }
                    $select->order("{$tableName}.title ASC");
                    foreach ($select->getTable()->fetchAll($select) as $list) {
                        $image = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($list);
                        $data[] = array(
                            'type' => ucfirst($this->translate('list_listing')),
                            'id' => $list->getIdentity(),
                            'guid' => $list->getGuid(),
                            'label' => $list->getTitle(),
                            'photo' => $image['image_icon'],
                            'url' => $list->getHref(),
                        );
                        $ids[] = $list->getIdentity();
                    }
                }
            }
            if (in_array('group', $enableContent) && Engine_Api::_()->hasItemType('group')) {
                $remaningLimit = $limit - @count($data);
                if ($remaningLimit > 0) {
                    $table = Engine_Api::_()->getItemTable('group');
                    $tableName = $table->info('name');
                    $select = $table->select();
                    $select->where('search = ?', (bool) 1);
                    if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
                        $select->where('`' . $table->info('name') . '`.`title` LIKE ?', '%' . $text . '%');
                    }
                    $select->order("{$tableName}.title ASC");
                    foreach ($select->getTable()->fetchAll($select) as $group) {
                        $image = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($group);
                        $data[] = array(
                            'type' => $group->getShortType(true),
                            'id' => $group->getIdentity(),
                            'guid' => $group->getGuid(),
                            'label' => $group->getTitle(),
                            'photo' => $image['image_icon'],
                            'url' => $group->getHref(),
                        );
                        $ids[] = $group->getIdentity();
                    }
                }
            }
            if (in_array('event', $enableContent) && Engine_Api::_()->hasItemType('event')) {
                $remaningLimit = $limit - @count($data);
                if ($remaningLimit > 0) {
                    $table = Engine_Api::_()->getItemTable('event');
                    $tableName = $table->info('name');
                    $select = $table->select();
                    $select->where('search = ?', (bool) 1);
                    $select->where("endtime > FROM_UNIXTIME(?)", time());
                    if (null !== ($text = $this->_getParam('search', $this->_getParam('value')))) {
                        $select->where('`' . $table->info('name') . '`.`title` LIKE ?', '%' . $text . '%');
                    }
                    $select->order("{$tableName}.title ASC");
                    foreach ($select->getTable()->fetchAll($select) as $event) {
                        $image = Engine_Api::_()->getApi('Core', 'siteapi')->getContentImage($event);
                        $data[] = array(
                            'type' => $event->getShortType(true),
                            'id' => $event->getIdentity(),
                            'guid' => $event->getGuid(),
                            'label' => $event->getTitle(),
                            'photo' => $image['image_icon'],
                            'url' => $event->getHref(),
                        );
                        $ids[] = $event->getIdentity();
                    }
                }
            }
        }
        $this->respondWithSuccess($data);
    }

}
