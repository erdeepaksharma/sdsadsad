<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Advancedactivity_Plugin_Task_Notification extends Core_Plugin_Task_Abstract {

    protected $_max;
    protected $_count;
    protected $_break;
    protected $_offset;

    public function getTotal() {
        $notificationTable = Engine_Api::_()->getDbtable('notificationQueues', 'advancedactivity');
        return $notificationTable->select()
                        ->from($notificationTable->info('name'), new Zend_Db_Expr('COUNT(*)'))
                        ->query()
                        ->fetchColumn(0)
        ;
    }

    public function execute() {
        
        $this->_count = $this->getTotal();
        $this->_break = false;
        $this->_offset = 0;

        // Loop until no notification left or count is reached
        while ($this->_offset <= $this->_count && !$this->_break) {
            $this->_processOne();
            $this->_offset++;
        }

        // We didn't do anything
        if ($this->_count <= 0) {
            $this->_setWasIdle();
        }
    }

    protected function _processOne() {
        $notificationTable = Engine_Api::_()->getDbtable('notificationQueues', 'advancedactivity');
        $db = $notificationTable->getAdapter();
        
        // Select a single notification item
        $notificationSelect = $notificationTable->select()->limit(1);
        $notificationRow = $notificationTable->fetchRow($notificationSelect);
        if (null === $notificationRow) {
            $this->_break = true;
            return;
        }
        
        
        
        $db->beginTransaction();
        try {
            
            $user = Engine_Api::_()->getItem('user', $notificationRow->user_id);
            $subject = Engine_Api::_()->getItem($notificationRow->subject_type, $notificationRow->subject_id);
            $object = Engine_Api::_()->getItem($notificationRow->object_type, $notificationRow->object_id);
            $params = $notificationRow->params;
            $type = $notificationRow->type;
            
            if(empty($user) || empty($subject) || empty($object)){
                $notificationRow->delete();
                $db->commit();
                return;
            }
            
            $table = Engine_Api::_()->getDbtable('notifications', 'activity');
            // We may want to check later if a request exists of the same type already
            $row = $table->createRow();
            $row->user_id = $user->getIdentity();
            $row->subject_type = $subject->getType();
            $row->subject_id = $subject->getIdentity();
            $row->object_type = $object->getType();
            $row->object_id = $object->getIdentity();
            $row->type = $type;
            $row->params = $params;
            $row->date = $notificationRow->date;
            $row->save();

            // Try to add row to caching
            if (Zend_Registry::isRegistered('Zend_Cache')) {
                $cache = Zend_Registry::get('Zend_Cache');
                $id = __CLASS__ . '_new_' . $user->getIdentity();
                $cache->save(true, $id);
            }

            // Try to send an email
            $notificationSettingsTable = Engine_Api::_()->getDbtable('notificationSettings', 'activity');
            if ($notificationSettingsTable->checkEnabledNotification($user, $type) && !empty($user->email)) {
                $view = Zend_Registry::get('Zend_View');

                $sender_photo = $subject->getPhotoUrl('thumb.icon');
                if (!$sender_photo) {
                    $sender_photo = '/' . $view->getHelper('itemPhoto')->getNoPhoto($subject, 'thumb.icon');
                }

                $recipient_photo = $user->getPhotoUrl('thumb.icon');
                if (!$recipient_photo) {
                    $recipient_photo = '/' . $view->getHelper('itemPhoto')->getNoPhoto($user, 'thumb.icon');
                }

                // Main params
                $defaultParams = array(
                    'host' => $_SERVER['HTTP_HOST'],
                    'email' => $user->email,
                    'date' => time(),
                    'recipient_title' => $user->getTitle(),
                    'recipient_link' => $user->getHref(),
                    'recipient_photo' => $recipient_photo,
                    'sender_title' => $subject->getTitle(),
                    'sender_link' => $subject->getHref(),
                    'sender_photo' => $sender_photo,
                    'object_title' => $object->getTitle(),
                    'object_link' => $object->getHref(),
                    'object_photo' => $object->getPhotoUrl('thumb.icon'),
                    'object_description' => $object->getDescription(),
                );
                // Extra params
                try {
                    $objectParent = $object->getParent();
                    if ($objectParent && !$objectParent->isSelf($object)) {
                        $defaultParams['object_parent_title'] = $objectParent->getTitle();
                        $defaultParams['object_parent_link'] = $objectParent->getHref();
                        $defaultParams['object_parent_photo'] = $objectParent->getPhotoUrl('thumb.icon');
                        $defaultParams['object_parent_description'] = $objectParent->getDescription();
                    }
                } catch (Exception $e) {
                    
                }
                try {
                    $objectOwner = $object->getParent();
                    if ($objectOwner && !$objectOwner->isSelf($object)) {
                        $defaultParams['object_owner_title'] = $objectOwner->getTitle();
                        $defaultParams['object_owner_link'] = $objectOwner->getHref();
                        $defaultParams['object_owner_photo'] = $objectOwner->getPhotoUrl('thumb.icon');
                        $defaultParams['object_owner_description'] = $objectOwner->getDescription();
                    }
                } catch (Exception $e) {
                    
                }
                // Send
                try {
                    Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'notify_' . $type, array_merge($defaultParams, (array) $params));
                } catch (Exception $e) {
                    // Silence exception
                }
            }

            $notificationRow->delete();
            $db->commit();
        } catch (Exception $e) { 
            $notificationRow->delete();
            $db->commit();
             
        }
    }

}