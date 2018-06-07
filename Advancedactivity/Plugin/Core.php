<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Core.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Plugin_Core extends Zend_Controller_Plugin_Abstract {
    
    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
        
        if ((Engine_Api::_()->hasModuleBootstrap('siteiosapp') || Engine_Api::_()->hasModuleBootstrap('siteandroidapp') || Engine_Api::_()->hasModuleBootstrap('sitepushnotification')) && Engine_Api::_()->getApi('settings', 'core')->getSetting('notification.queueing', 1)) {
            $loader = Engine_Loader::getInstance();
            $className = 'Advancedactivity_Model_DbTable_Notifications';
            Engine_Loader::loadClass($className);
            if ( method_exists($loader, 'setComponent')) {
              $loader->setComponent('Activity_Model_DbTable_Notifications', new $className());
            }
        }
    }

  public function onAlbumPhotoUpdateAfter($event) {
    //
    if (Engine_Api::_()->hasModuleBootstrap('sitealbum')) {
       $sitealbumVersion = Engine_Api::_()->getDbtable('modules', 'core')->getModule('sitealbum')->version;
       $versionCheck = Engine_Api::_()->advancedactivity()->checkVersion($sitealbumVersion, '4.8.4');
       if ($versionCheck) {
         return;
       }
    }
    $photo = $event->getPayload();
    if ($photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id)) {
      $table = Engine_Api::_()->getItemTable('storage_file');
      $select = $table->select()
              ->where('type = ?', 'thumb.medium')
              ->where('parent_file_id = ?', $photo->file_id);
      $file = $table->fetchRow($select);
      if (empty($file)) {

        $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
        $file = $tmpRow->temporary();
        $fileName = $tmpRow->name;

        if (!$fileName) {
          $fileName = $file;
        }

        $name = basename($file);
        $extension = ltrim(strrchr($fileName, '.'), '.');
        $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
        $params = array(
            'parent_type' => $photo->getType(),
            'parent_id' => $photo->getIdentity(),
            'user_id' => $photo->owner_id,
            'name' => $fileName,
        );

        // Save
        $filesTable = Engine_Api::_()->getDbtable('files', 'storage');

        // Resize image (main)
        $feedPath = $path . DIRECTORY_SEPARATOR . $base . '_medium.' . $extension;
        $image = Engine_Image::factory();
        $image->open($file)
                ->resize(375, 375)
                ->write($feedPath)
                ->destroy();


        // Store
        try {
          $iMain = $tmpRow;
          $iFeedNormal = $filesTable->createFile($feedPath, $params);

          $iMain->bridge($iFeedNormal, 'thumb.medium');
        } catch (Exception $e) {
          // Remove temp files

          @unlink($feedPath);
          // Throw
          if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE) {
            throw new Album_Model_Exception($e->getMessage(), $e->getCode());
          } else {
            throw $e;
          }
        }

        // Remove temp files
        @unlink($feedPath);
      }
    }
  }

  public function onItemDeleteBefore($event) {
    $item = $event->getPayload();
    // delete rows when item is deleted
    Engine_Api::_()->getDbtable('hide', 'advancedactivity')->delete(array(
        'hide_resource_type = ?' => $item->getType(),
        'hide_resource_id  = ?' => $item->getIdentity(),
    ));

    Engine_Api::_()->getDbtable('shares', 'advancedactivity')->delete(array(
        'resource_type = ?' => $item->getType(),
        'resource_id  = ?' => $item->getIdentity(),
    ));
    Engine_Api::_()->getItemTable('advancedactivity_list_item')->delete(array(
        'child_type = ?' => $item->getType(),
        'child_id  = ?' => $item->getIdentity()
    ));
    // User Delete
    if ($item instanceof User_Model_User) {
      $user_id = $item->getIdentity();
      // delete the all hide entry belong to user 
      Engine_Api::_()->getDbtable('hide', 'advancedactivity')->delete(array(
          'user_id 	 = ?' => $user_id
      ));
      // delete the all list entry belong to user 
      $lists = Engine_Api::_()->getDbtable('lists', 'advancedactivity')->getMemberOfList($item);

      foreach ($lists as $list) {
        $list->getListItemTable()->delete(array("list_id = ? " => $list->list_id));
        $list->delete();
      }
    } else if ($item instanceof Activity_Model_Action) {
      // Activity Delete
      $id = $item->getIdentity();
      Engine_Api::_()->getDbtable('shares', 'advancedactivity')->delete(array(
          'action_id  = ?' => $item->getIdentity(),
      ));
      Engine_Api::_()->getDbtable('shares', 'advancedactivity')->delete(array(
          'parent_action_id  = ?' => $item->getIdentity(),
      ));
      Engine_Api::_()->getDbtable('saveFeeds', 'advancedactivity')->delete(array(
          'action_id  = ?' => $item->getIdentity(),
      ));
      Engine_Api::_()->getDbtable('reports', 'advancedactivity')->delete(array(
          'action_id  = ?' => $item->getIdentity(),
      ));
    }
    
    // Delete Activity comments
    if($item->getType() == 'user') {
        $commentTable = Engine_Api::_()->getDbtable('comments', 'activity');

        // Delete comments by poster
        $commentSelect = $commentTable->select()
          ->where('poster_type = ?', $item->getType())
          ->where('poster_id = ?', $item->getIdentity());
        foreach( $commentTable->fetchAll($commentSelect) as $comment ) {
          $comment->delete();
        }
    }
  }

  /* public function onItemDeleteBefore($event) {
    $item = $event->getPayload();
    if ($item instanceof User_Model_User) {
    $user_id = $item->getIdentity();
    }
    } */

  public function addActivity($event) {
    $payload = $event->getPayload();
    $aafPayload = isset($payload['aafPayload']) && $payload['aafPayload'];
    if (isset($payload['content']) && !empty($payload['content']) && $aafPayload) {
      $subject = $payload['subject'];
      $object = $payload['object'];
      $content = $payload['content'];
      // Get object parent
      $objectParent = null;
      if ($object instanceof User_Model_User) {
        $objectParent = $object;
      } else {
        try {
          $objectParent = $object->getParent('user');
        } catch (Exception $e) {
          
        }
      }

      $checkview = "view";
      if ($object instanceof Sitereview_Model_Listing) {
        $checkview = "view_listtype_" . $object->listingtype_id;
      } else if (!$object instanceof User_Model_User) {
        $objectParentListing=null;
        try {
          $objectParentListing = $object->getParent();
        } catch (Exception $e) {
          
        }
        if ($objectParentListing instanceof Sitereview_Model_Listing) {
          $checkview = "view_listtype_" . $objectParentListing->listingtype_id;
        }
      }
      // Network
      if (in_array($content, array('everyone', 'networks'))) {
        if ($object instanceof User_Model_User && Engine_Api::_()->authorization()->isAllowed($object, 'network', 'view')) {
          $networkTable = Engine_Api::_()->getDbtable('membership', 'network');
          $ids = $networkTable->getMembershipsOfIds($object);
          $ids = array_unique($ids);
          foreach ($ids as $id) {
            $event->addResponse(array(
                'type' => 'network',
                'identity' => $id,
            ));
          }
        } elseif ($objectParent instanceof User_Model_User && Engine_Api::_()->authorization()->isAllowed($object, 'owner_network', $checkview)) {
          $networkTable = Engine_Api::_()->getDbtable('membership', 'network');
          $ids = $networkTable->getMembershipsOfIds($objectParent);
          $ids = array_unique($ids);
          foreach ($ids as $id) {
            $event->addResponse(array(
                'type' => 'network',
                'identity' => $id,
            ));
          }
        }
      }
      // Registered
      if ($content == 'everyone' &&
              Engine_Api::_()->authorization()->isAllowed($object, 'registered', $checkview)) {
        $event->addResponse(array(
            'type' => 'registered',
            'identity' => 0
        ));
      }


      // Everyone
      if ($content == 'everyone' &&
              Engine_Api::_()->authorization()->isAllowed($object, 'everyone', $checkview)) {
        $event->addResponse(array(
            'type' => 'everyone',
            'identity' => 0
        ));
      }
      // Members List
      if (!in_array($content, array('everyone', 'networks', 'friends', 'onlyme'))) {
        if (Engine_Api::_()->advancedactivity()->isNetworkBasePrivacy($content)) {
            $ids = Engine_Api::_()->advancedactivity()->isNetworkBasePrivacyIds($content);
            $ids = array_unique($ids);
            foreach ($ids as $id) {
              $event->addResponse(array(
                  'type' => 'network_list',
                  'identity' => $id,
              ));
            }
        } else {
          $owner_member_view = false;
          if ($object instanceof User_Model_User) {

            $owner_member_view = Engine_Api::_()->authorization()->isAllowed($object, 'member', 'view') || Engine_Api::_()->authorization()->context->isAllowed($object, 'member', 'view');
          } else if ($objectParent instanceof User_Model_User) {
            // Note: technically we shouldn't do owner_member, however some things are using it

            $owner_member_view = Engine_Api::_()->authorization()->isAllowed($object, 'owner_member', $checkview) ||
                    Engine_Api::_()->authorization()->isAllowed($object, 'parent_member', $checkview) || Engine_Api::_()->authorization()->context->isAllowed($object, 'owner_member', $checkview) ||
                    Engine_Api::_()->authorization()->context->isAllowed($object, 'parent_member', $checkview);
          }

          if ($owner_member_view) {
            $ids = explode(',', $content);
            foreach ($ids as $id) {
              $event->addResponse(array(
                  'type' => 'members_list',
                  'identity' => $id,
              ));
            }
          }
        }
      }
    } else { // Work For Fix the Core Activity Isuess related to getAuthorizationItem
      $subject = $payload['subject'];
      $object = $payload['object'];
      if( $object instanceof Forum_Model_Topic || $object instanceof Ynforum_Model_Topic) {
        return;
      }
      $content = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.content', 'everyone');

      // Get subject owner
      $subjectOwner = null;
      if ($subject instanceof User_Model_User) {
        $subjectOwner = $subject;
      } else {
        try {
          $subjectOwner = $subject->getOwner('user');
        } catch (Exception $e) {
          
        }
      }

      // Get object parent
      $objectParent = null;
      $objectParentListing = null;
      if ($object instanceof User_Model_User) {
        $objectParent = $object;
      } else {
        try {
          $objectParent = $object->getParent('user');
        } catch (Exception $e) {
          
        }
      }

      $checkview = "view";
      if ($object instanceof Sitereview_Model_Listing) {
        $checkview = "view_listtype_" . $object->listingtype_id;
      } else if (!$object instanceof User_Model_User) {
        try {
          $objectParentListing = $object->getParent();
        } catch (Exception $e) {
          
        }
        if ($objectParentListing instanceof Sitereview_Model_Listing) {
          $checkview = "view_listtype_" . $objectParentListing->listingtype_id;
        }
      }
      // Network
      if (in_array($content, array('everyone', 'networks'))) {
        if ($object instanceof User_Model_User && !Engine_Api::_()->authorization()->context->isAllowed($object, 'network', 'view') && Engine_Api::_()->authorization()->isAllowed($object, 'network', 'view')) {
          $networkTable = Engine_Api::_()->getDbtable('membership', 'network');
          $ids = $networkTable->getMembershipsOfIds($object);
          $ids = array_unique($ids);
          foreach ($ids as $id) {
            $event->addResponse(array(
                'type' => 'network',
                'identity' => $id,
            ));
          }
        } elseif ($objectParent instanceof User_Model_User && !Engine_Api::_()->authorization()->context->isAllowed($object, 'owner_network', 'view') && Engine_Api::_()->authorization()->isAllowed($object, 'owner_network', $checkview)) {
          $networkTable = Engine_Api::_()->getDbtable('membership', 'network');
          $ids = $networkTable->getMembershipsOfIds($objectParent);
          $ids = array_unique($ids);
          foreach ($ids as $id) {
            $event->addResponse(array(
                'type' => 'network',
                'identity' => $id,
            ));
          }
        }
      }

      // Members
      if ($object instanceof User_Model_User) {
        $memberView = Engine_Api::_()->authorization()->context->isAllowed($object, 'member', 'view');
        if (!$memberView && Engine_Api::_()->authorization()->isAllowed($object, 'member', 'view')) {
          $event->addResponse(array(
              'type' => 'members',
              'identity' => $object->getIdentity()
          ));
        }
      } else if ($objectParent instanceof User_Model_User) {
        // Note: technically we shouldn't do owner_member, however some things are using it
        if ((!Engine_Api::_()->authorization()->context->isAllowed($object, 'owner_member', 'view') && Engine_Api::_()->authorization()->isAllowed($object, 'owner_member', $checkview)) || (
                !Engine_Api::_()->authorization()->context->isAllowed($object, 'parent_member', 'view') && Engine_Api::_()->authorization()->isAllowed($object, 'parent_member', $checkview))) {
          $event->addResponse(array(
              'type' => 'members',
              'identity' => $objectParent->getIdentity()
          ));
        }
      }

      // Registered
      if ($content == 'everyone' &&
              !Engine_Api::_()->authorization()->context->isAllowed($object, 'registered', 'view') && Engine_Api::_()->authorization()->isAllowed($object, 'registered', $checkview)) {
        $event->addResponse(array(
            'type' => 'registered',
            'identity' => 0
        ));
      }


      // Everyone
      if ($content == 'everyone' &&
              !Engine_Api::_()->authorization()->context->isAllowed($object, 'everyone', 'view') && Engine_Api::_()->authorization()->isAllowed($object, 'everyone', $checkview)) {
        $event->addResponse(array(
            'type' => 'everyone',
            'identity' => 0
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

    // Get feed settings
    //  $content = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.content', 'everyone');
    // Member List
    if ($user) {
      $data = array();
      $data = Engine_Api::_()->advancedactivity()->getMemberBelongFriendList();
      if (!empty($data)) {
        $event->addResponse(array(
            'type' => 'members_list',
            'data' => $data,
        ));
      }
    }
    $networkFilterType = Engine_Api::_()->getApi("settings", "core")->getSetting('advancedactivity.networklist.filtering', 0);
    if( $networkFilterType == 2 ) {
      $event->addResponse(array(
        'type' => 'network',
        'data' => 0,
      ));
    }

    // Members (Followers)
    $hasSitemember = Engine_Api::_()->hasModuleBootstrap('sitemember');
    $followEnabled = $hasSitemember && Engine_Api::_()->getApi("settings", "core")->getSetting('user.friends.direction', 1) && Engine_Api::_()->getApi("settings", "core")->getSetting('sitemember.user.follow.enable', 1);
    if( $user && $followEnabled) {
      $data = array();
      $data = Engine_Api::_()->getDbtable('follows','seaocore')->getFollowingIds($user);
      if( !empty($data) ) {
        $event->addResponse(array(
          'type' => 'members',
          'data' => $data,
        ));
      }
    }
}

  // Hook for new user signup
  public function onUserCreateAfter($event) {
    $isSignupEnabled = Engine_Api::_()->getApi('settings', 'core')->getSetting('welcomeTab.isSignup', 0);
    if (!empty($isSignupEnabled)) {
      $session = new Zend_Session_Namespace();
      $session->isUserSignup = 1;
    }
  }

}
