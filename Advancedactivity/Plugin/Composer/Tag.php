<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Tag.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Plugin_Composer_Tag extends Core_Plugin_Composer {

  public function onAAFComposerTag($data, $params) {
    $action = (empty($params)) ? null : $params['action'];
    if (!$action || empty($data['tag'])) {
      return;
    }

    $tagsArray = array();
    parse_str($data['tag'], $tagsArray);
    if (empty($tagsArray)) {
      return;
    }

    $action->params = array_merge((array) $action->params, array('tags' => $tagsArray));
    $action->save();

    $viewer = Engine_Api::_()->_()->user()->getViewer();
    $type_name = Zend_Registry::get('Zend_Translate')->translate('post');
          
    if (is_array($type_name)) {
      $type_name = $type_name[0];
    } else {
      $type_name = 'post';  
    }
    $notificationAPi = Engine_Api::_()->getDbtable('notifications', 'activity');
    foreach ($tagsArray as $key => $tagStrValue) {
      $tag = Engine_Api::_()->getItemByGuid($key);
      if ($tag && ($tag instanceof User_Model_User) && !$tag->isSelf($viewer)) {
        $notificationAPi->addNotification($tag, $viewer, $action, 'tagged', array(
            'object_type_name' => $type_name,
            'label' => $type_name,
        ));
      } else if ($tag && ($tag instanceof Sitepage_Model_Page)) {
        $subject_title = $viewer->getTitle();
        $page_title = $tag->getTitle();
        foreach ($tag->getPageAdmins() as $owner) {
          if ($owner && ($owner instanceof User_Model_User) && !$owner->isSelf($viewer)) {
            $notificationAPi->addNotification($owner, $viewer, $action, 'sitepage_tagged', array(
                'subject_title' => $subject_title,
                'label' => $type_name,
                'object_type_name' => $type_name,
                'page_title' => $page_title
            ));
          }
        }
      } else if ($tag && ($tag instanceof Sitebusiness_Model_Business)) {
        $subject_title = $viewer->getTitle();
        $business_title = $tag->getTitle();
        foreach ($tag->getBusinessAdmins() as $owner) {
          if ($owner && ($owner instanceof User_Model_User) && !$owner->isSelf($viewer)) {
            $notificationAPi->addNotification($owner, $viewer, $action, 'sitebusiness_tagged', array(
                'subject_title' => $subject_title,
                'label' => $type_name,
                'object_type_name' => $type_name,
                'business_title' => $business_title
            ));
          }
        }
      } else if ($tag && ($tag instanceof Sitegroup_Model_Group)) {
        $subject_title = $viewer->getTitle();
        $group_title = $tag->getTitle();
        foreach ($tag->getGroupAdmins() as $owner) {
          if ($owner && ($owner instanceof User_Model_User) && !$owner->isSelf($viewer)) {
            $notificationAPi->addNotification($owner, $viewer, $action, 'sitegroup_tagged', array(
                'subject_title' => $subject_title,
                'label' => $type_name,
                'object_type_name' => $type_name,
                'group_title' => $group_title
            ));
          }
        }
      }else if ($tag && ($tag instanceof Sitestore_Model_Store)) {
        $subject_title = $viewer->getTitle();
        $store_title = $tag->getTitle();
        foreach ($tag->getStoreAdmins() as $owner) {
          if ($owner && ($owner instanceof User_Model_User) && !$owner->isSelf($viewer)) {
            $notificationAPi->addNotification($owner, $viewer, $action, 'sitestore_tagged', array(
                'subject_title' => $subject_title,
                'label' => $type_name,
                'object_type_name' => $type_name,
                'store_title' => $store_title
            ));
          }
        }
      } else if ($tag && ($tag instanceof Core_Model_Item_Abstract)) {
        $subject_title = $viewer->getTitle();
        $item_type = Zend_Registry::get('Zend_Translate')->translate($tag->getShortType());
        $item_title = $tag->getTitle();
        $owner = $tag->getOwner();
        if ($owner && ($owner instanceof User_Model_User) && !$owner->isSelf($viewer)) {
          $notificationAPi->addNotification($owner, $viewer, $action, 'aaf_tagged', array(
              'subject_title' => $subject_title,
              'label' => $type_name,
              'object_type_name' => $type_name,
              'item_title' => $item_title,
              'item_type' => $item_type
          ));
        }
        if (($tag instanceof Group_Model_Group)) {
          foreach ($tag->getOfficerList()->getAll() as $offices) {
            $owner = Engine_Api::_()->getItem('user', $offices->child_id);
            if ($owner && ($owner instanceof User_Model_User) && !$owner->isSelf($viewer)) {
              $notificationAPi->addNotification($owner, $viewer, $action, 'aaf_tagged', array(
                  'subject_title' => $subject_title,
                  'label' => $type_name,
                  'object_type_name' => $type_name,
                  'item_title' => $item_title,
                  'item_type' => $item_type
              ));
            }
          }
        }
      }
    }
  }

  public function onAAFComposerWithTag($data, $params) {

    $action = (empty($params)) ? null : $params['action'];
    if (!$action || empty($data['withTag'])) {
      return;
    }

    $users = array_values(array_unique(explode(",", $data['withTag'])));
    if (empty($users)) {
      return;
    }
    $actionTag = new Engine_ProxyObject($action, Engine_Api::_()->getDbtable('tags', 'core'));
    $notificationAPi = Engine_Api::_()->getDbtable('notifications', 'activity');
    $viewer = Engine_Api::_()->_()->user()->getViewer();
    $type_name = Zend_Registry::get('Zend_Translate')->translate('post');
    foreach (Engine_Api::_()->getItemMulti('user', $users) as $tag) {
      if (!$tag)
        continue;
      $actionTag->addTagMap($viewer, $tag, null);
      if (($tag instanceof User_Model_User) && !$tag->isSelf($viewer)) {
        // Add notification
        $notificationAPi->addNotification(
                $tag, $viewer, $action, 'tagged', array(
            'object_type_name' => $type_name,
            'label' => $type_name,
                )
        );
      }
    }
  }

}