<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Actions.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Model_DbTable_Notifications extends Activity_Model_DbTable_Notifications {
    
    protected $_name = 'activity_notifications';

    public function addNotification(User_Model_User $user, Core_Model_Item_Abstract $subject,
          Core_Model_Item_Abstract $object, $type, array $params = null)
  {
        $view = Zend_Registry::get('Zend_View');
        $sender_photo = $subject->getPhotoUrl('thumb.icon');
        if (!$sender_photo) {
            $sender_photo = '/' . $view->getHelper('itemPhoto')->getNoPhoto($subject, 'thumb.icon');
        }

        $recipient_photo = $user->getPhotoUrl('thumb.icon');
        if (!$recipient_photo) {
            $recipient_photo = '/' . $view->getHelper('itemPhoto')->getNoPhoto($user, 'thumb.icon');
        }

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
       $notificationTable = Engine_Api::_()->getDbtable('notificationQueues', 'advancedactivity');
       $notification_id = $notificationTable->insert(array(
            'type' => $type,
            'user_id' => $user->getIdentity(),
            'subject_id' => $subject->getIdentity(),
            'subject_type' => $subject->getType(),
            'object_id' => $object->getIdentity(),
            'object_type' => $object->getType(),
            'date' => date('Y-m-d H:i:s'),
            'params' => array_merge($defaultParams, (array) $params)
        ));
    }
}
