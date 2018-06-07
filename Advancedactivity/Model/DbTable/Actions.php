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
class Advancedactivity_Model_DbTable_Actions extends Engine_Db_Table
{

  protected $_rowClass = 'Advancedactivity_Model_Action';
  protected $_name = 'activity_actions';
  protected $_serializedColumns = array('params');
  protected $_actionTypes;

  public function addActivity(Core_Model_Item_Abstract $subject, Core_Model_Item_Abstract $object, $type, $body = null, $privacy = null, array $params = null)
  {
    // Disabled or missing type
    $typeInfo = $this->getActionType($type);
    if( !$typeInfo || !$typeInfo->enabled ) {
      return;
    }
    if( !empty($params['schedule_time']) && $params['schedule_time'] != '0000-00-00' ) {
      $dbTime = Engine_Api::_()->advancedactivity()->userToDbDateTime(array('starttime' => $params['schedule_time']));
      $scheduleTime = $dbTime['starttime'];
    }
    unset($params['schedule_time']);
    // User disabled publishing of this type
    $actionSettingsTable = Engine_Api::_()->getDbtable('actionSettings', 'activity');
    if( $subject->getType() == "user" ) {
      if( !$actionSettingsTable->checkEnabledAction($subject, $type) ) {
        return;
      }
    } else {
      if( !$actionSettingsTable->checkEnabledAction($object, $type) ) {
        return;
      }
    }

    $category_id = 0;
    if( isset($params['aaf_post_category_id']) ) {
      $category_id = $params['aaf_post_category_id'];
      unset($params['aaf_post_category_id']);
    }

    $postByAJAX = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.post.byajax', 1);
    $socialDNApublish = 0;
    if( empty($postByAJAX) ) {
      $socialDNApublish = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('socialdnapublisher');
    }
    $activityPoints = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('activitypoints');
    if( (empty($postByAJAX) && $socialDNApublish) || ($activityPoints && empty($socialDNApublish)) ) {
      // To make compatebile with "Social DNA Publisher" Plugin And "Activity Points" PLugin
      $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onSemodsAddActivity', array(
        'subject' => $subject,
        'object' => $object,
        'type' => $type,
        'body' => $body,
        'params' => $params,
      ));
    }
    // Create action
    $action = $this->createRow();
    $action->setFromArray(array(
      'type' => $type,
      'subject_type' => $subject->getType(),
      'subject_id' => $subject->getIdentity(),
      'object_type' => $object->getType(),
      'object_id' => $object->getIdentity(),
      'body' => (string) $body,
      'params' => (array) $params,
      'date' => date('Y-m-d H:i:s'),
      'privacy' => $privacy,
      'commentable' => ($typeInfo->commentable > 0 ) ? 1 : 0,
      'shareable' => ($typeInfo->shareable > 0) ? 1 : 0,
      'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
      'publish_date' => !empty($scheduleTime) ? $scheduleTime : NULL
    ));
    if( isset($action->category_id) && Engine_Api::_()->hasModuleBootstrap('advancedactivitypost') && $category_id ) {
      $action->category_id = $category_id;
    }
    $action->save();
    if( !empty($scheduleTime) ) {
      return $action;
    }
    // Add bindings
    $this->addActivityBindings($action, $type, $subject, $object);

    // We want to update the subject
    if( isset($subject->modified_date) ) {
      $subject->modified_date = date('Y-m-d H:i:s');
      $subject->save();
    }

    if( empty($postByAJAX) && $socialDNApublish ) {
      // To make compatebile with "Social DNA Publisher" Plugin And "Activity Points" PLugin
      $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('onSemodsAddActivityAfter', array(
        'subject' => $subject,
        'object' => $object,
        'type' => $type,
        'body' => $body,
        'params' => $params,
        'action' => $action,
      ));
    }
    Engine_Api::_()->getApi('cache', 'advancedactivity')->flushAll();
    return $action;
  }

  public function getActivity(User_Model_User $user, array $params = array())
  {
    // Proc args
    extract($this->_getInfo($params)); // action_id, limit, min_id, max_id
    $getAafPostType = Engine_Api::_()->getApi('settings', 'core')->getSetting('getaaf.post.type', 0);
    if( in_array($actionTypeGroup, array('only_network')) ) {
      $networkTable = Engine_Api::_()->getDbtable('membership', 'network');
      $ids = $networkTable->getMembershipsOfIds($user);
      if( count($ids) <= 0 ) {
        return;
      }
    }
    $cacheApi = Engine_Api::_()->getApi('cache', 'advancedactivity');
    // For Hashtag Search
    $hashtag = '';
    if( isset($params['hashtag']) && !empty($params['hashtag']) ) {
      $hashtag = $params['hashtag'];
    }
    $hastTagActionIds = array();
    if( Engine_Api::_()->hasModuleBootstrap('sitehashtag') && $hashtag ) {
      $hastTagActionIds = $this->getHashTagActionsIds($hashtag);
      if( empty($hastTagActionIds) ) {
        return;
      }
    }
    $cacheParams['params'] = $params;
    // Prepare main query
    $streamTable = Engine_Api::_()->getDbtable('stream', 'activity');
    $db = $streamTable->getAdapter();
    $union = new Zend_Db_Select($db);

    // Prepare action types
    $masterActionTypes = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionTypes();
    $mainActionTypes = array();
    $birthday_post_enable = false;
    //CHECK IF SITEMOBILE PLUGIN IS ENABLED AND SITE IS IN MOBILE MODE:
    $enable_sitemobilemodule = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemobile');
    if( $enable_sitemobilemodule )
      $checkMobMode = Engine_Api::_()->sitemobile()->checkMode('mobile-mode') || Engine_Api::_()->sitemobile()->checkMode('tablet-mode');
    else
      $checkMobMode = 0;
    $cacheParams['checkMobMode'] = $checkMobMode;
    // Filter out types set as not displayable
    foreach( $masterActionTypes as $type ) {

      //IF SITE MOBILE MODULE IS ENABLED AND SITE IS IN MOBILE MODE THEN WE WILL ONLY SHOW THE MODULES FEED WHICH ARE COMPATIBLE WITH OUR SITE MOBILE PLUGIN.
      if( $checkMobMode && isset($type->module) ) {
        //SPECIAL CASE FOR SITEALBUM ADDED BY DINESH
        if( $type->module == 'sitealbum' )
          $type->module = 'album';
        if( $type->module == 'siteforum' )
          $type->module = 'forum';
        $supportedModules = Engine_Api::_()->sitemobile()->isSupportedModule($type->module);
        if( !$supportedModules )
          continue;
      }
//      if( $actionTypeGroup == 'advertise' && $type->type != 'post_self_sell' ) {
//        continue;
//      }
      if( !empty($hastTagActionIds) || (($type->displayable & 4) && ($type->type != 'birthday_post')) ) {
        $mainActionTypes[] = $type->type;
      } elseif( $type->type == 'birthday_post' ) {
        $birthday_post_enable = (bool) $type->enabled;
      }
    }
    $showPost = in_array("post", $mainActionTypes);
    // Filter types based on user request
    if( isset($showTypes) && is_array($showTypes) && !empty($showTypes) ) {
      $mainActionTypes = array_intersect($mainActionTypes, $showTypes);
    } else if( isset($hideTypes) && is_array($hideTypes) && !empty($hideTypes) ) {
      $mainActionTypes = array_diff($mainActionTypes, $hideTypes);
    }
    $mainActionTypesArray = $mainActionTypes;
    // Nothing to show
    if( empty($mainActionTypes) || empty($getAafPostType) ) {
      return null;
    }
    // Show everything
    else if( count($mainActionTypes) == count($masterActionTypes) ) {
      $mainActionTypes = true;
    }
    // Build where clause
    else {
      $mainActionTypes = "'" . join("', '", $mainActionTypes) . "'";
    }
    if( $actionTypeGroup == 'schedule_post' ) {
      $ids = $this->getAllShecduledPostActionIds($user);
    } else if( $actionTypeGroup == 'hidden_post' ) {
      $hiddenIds = Engine_Api::_()->getDbtable('hide', 'advancedactivity')->getHideItemByMember($user);
      $ids = !empty($hiddenIds['activity_action']) ? $hiddenIds['activity_action'] : array();
    } else {
      $cacheParams['mainActionTypes'] = $mainActionTypes;
      $cacheHookKey = __CLASS__ . '_getActivity_Hook_Response' . ($user ? $user->getGuid() : '');
      if( $cacheApi->test($cacheHookKey) ) {
        $responses = $cacheApi->load($cacheHookKey);
      } else {
        // Prepare sub queries
        $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('getActivity', array(
          'for' => $user,
        ));
        $responses = (array) $event->getResponses();
        $cacheApi->save($responses, $cacheHookKey);
      }

      if( empty($responses) ) {
        return null;
      }
      $friendsFlage = false;
      $action_ids = array();
      if( $actionTypeGroup == 'user_saved' ) {
        $action_ids = Engine_Api::_()->getDbtable('saveFeeds', 'advancedactivity')->getSaveFeeds($user, $mainActionTypesArray, array('limit' => $limit, 'max_id' => $max_id));
        if( empty($action_ids) )
          return null;
      }
      if( $actionTypeGroup == 'memories' ) {
        $action_ids = $this->getOnThisDayActivity($user, array('filter' => true));
        if( empty($action_ids) ) {
          return;
        }
      }
      $cacheActionKey = __CLASS__ . '_getActivity_' . serialize($cacheParams);
      if( $cacheApi->test($cacheActionKey) && $actionTypeGroup != 'user_saved' ) {
        $ids = $cacheApi->load($cacheActionKey);
      } else {
        foreach( $responses as $response ) {
          if( empty($response) )
            continue;

          if( $response['type'] == 'forum' && Engine_Api::_()->hasModuleBootstrap('siteforum') ) {
            $response['type'] = 'siteforum';
          }
          if( $membership && !in_array($response['type'], array('members', 'members_list', 'forum', 'siteforum')) ) {
            continue;
          }

          if( in_array($actionTypeGroup, array('membership', 'member_list')) && !in_array($response['type'], array('members', 'members_list', 'forum', 'siteforum')) ) {
            continue;
          } elseif( in_array($actionTypeGroup, array('membership', 'member_list')) ) {
            if( $response['type'] == 'members' ) {
              $friendsFlage = true;
            }
          }
          if( in_array($actionTypeGroup, array('only_network', 'network_list')) && !in_array($response['type'], array('network', 'forum', 'siteforum')) ) {
            continue;
          }

          if( $actionTypeGroup == 'network_list' && !empty($listTypeFilter) && in_array($response['type'], array('network')) ) {
            $response['data'] = $listTypeFilter;
          }
          if( $actionTypeGroup == 'member_list' && !empty($listTypeFilter) ) {
            if( $response['type'] == 'members' ) {
              $response['data'] = $listTypeFilter['member_list']['value'];
            } elseif( $response['type'] == 'members_list' ) {
              $response['data'] = $listTypeFilter['member_list']['list_ids'];
            }
          }

          $streamName = $streamTable->info('name');
          $actionTableName = $this->info('name');
          $select = $streamTable->select()
            ->from($streamTable->info('name'), 'action_id')
            ->where('target_type = ?', $response['type']);

          if( !empty($hastTagActionIds) ) {
            $select->where('action_id IN (?)', $hastTagActionIds);
          }

          if( $actionTypeGroup == 'category_list' && !empty($listTypeFilter) ) {
            $category_ids = isset($listTypeFilter['categories_id']) && $listTypeFilter['categories_id'] ? $listTypeFilter['categories_id'] : array(0);

            $select->setIntegrityCheck(false)
              ->join($actionTableName, "$actionTableName.action_id =$streamName.action_id", array())
              ->where("$actionTableName.category_id IN(?)", (array) $category_ids);
          }

          if( empty($response['data']) ) {
            // Simple
            $select->where('target_id = ?', 0);
          } else if( is_scalar($response['data']) || count($response['data']) === 1 ) {
            // Single
            if( is_array($response['data']) ) {
              list($response['data']) = $response['data'];
            }
            $select->where($streamName . '.target_id = ?', $response['data']);
          } else if( is_array($response['data']) ) {
            // Array
            $select->where($streamName . '.target_id IN(?)', (array) $response['data']);
          } else {
            // Unknown
            continue;
          }

          // Add action_id/max_id/min_id
          if( null !== $action_id ) {
            $select->where($streamName . '.action_id = ?', $action_id);
          } else {
            if( null !== $min_id ) {
              $select->where($streamName . '.action_id >= ?', $min_id);
            } else if( null !== $max_id ) {
              $select->where($streamName . '.action_id <= ?', $max_id);
            }
          }

          if( $mainActionTypes !== true ) {
            if( $showPost && !empty($actionTypeGroup) && !in_array($actionTypeGroup, array('all', 'members', 'members_list', 'category_list', 'sitepage', 'sitebusiness', 'sitegroup', 'sitestore', 'custom_list', 'like', 'posts', 'photo', 'music', 'video', 'sitevideo')) ) {
              if( $actionTypeGroup == "list" ) {
                $object_type = "list_listing";
              } else {
                $object_type = $actionTypeGroup;
              }
              $select->where('(' . $streamName . '.type IN(' . $mainActionTypes . ') OR (' . $streamName . '.type = "post" and ' . $streamName . '.object_type ="' . $object_type . '") )');
            } else {
              $select->where('' . $streamName . '.type IN(' . $mainActionTypes . ')');
            }
          }

          if( $actionTypeGroup == 'member_list' && !empty($listTypeFilter) && in_array($response['type'], array('members_list')) ) {
            $select->where('' . $streamName . '.type IN(' . $mainActionTypes . ')');
          }
          // Add order/limit
          $select
            ->order("action_id DESC")
            ->limit($limit);
          if( !empty($action_ids) ) {
            $select->where($streamName . '.action_id IN(?)', (array) $action_ids);
          }
          if( in_array($actionTypeGroup, array('membership', 'member_list')) && in_array($response['type'], array('members', 'forum', 'siteforum')) ) {
            $ids = $user->membership()->getMembershipsOfIds();
            if( !empty($ids) ) {
              $select
                ->where($streamName . '.subject_type = ?', 'user')
                ->where($streamName . '.subject_id IN (?)', (array) $ids);
            }
          }
          if( in_array($actionTypeGroup, array('only_network', 'network_list')) && in_array($response['type'], array('forum', 'siteforum')) ) {
            $ids = array();
            if( $actionTypeGroup == 'network_list' && !empty($listTypeFilter) ) {
              $ids = $listTypeFilter;
            } else {
              $networkTable = Engine_Api::_()->getDbtable('membership', 'network');
              $ids = $networkTable->getMembershipsOfIds($user);
            }
            $ids = array_unique($ids);
            $member_ids = array();
            foreach( Engine_Api::_()->getItemTable('network')->find($ids) as $network ) {
              $selectNetwork = $network->membership()->getMembersSelect();
              $members = Zend_Paginator::factory($selectNetwork);
              foreach( $members as $member ) {
                $member_ids[] = $member->user_id;
              }
            }
            if( !empty($member_ids) ) {
              $member_ids = array_unique($member_ids);
              $select
                ->where($streamName . '.subject_type = ?', 'user')
                ->where($streamName . '.subject_id IN (?)', (array) $member_ids);
            }
          }

          //Custom Filter Work
          if( !empty($custom_filter_type) && !empty($custom_type_ids) ) {

            $custom_ids = explode(',', $custom_type_ids);
            $select
              ->where($streamName . '.object_type = ?', $custom_filter_type)
              ->where($streamName . '.object_id IN (?)', (array) $custom_ids);
          }
          //Custom Filter Work

          if( $actionTypeGroup == 'custom_list' && !empty($listTypeFilter) ) {
            foreach( $listTypeFilter as $resource ) {
              $selectSubject = clone $select;
              // Add subject to main query
              $selectSubject
                ->where($streamName . '.subject_type = ?', $resource->child_type)
                ->where($streamName . '.subject_id = ?', $resource->child_id);
              $union->union(array('(' . $selectSubject->__toString() . ')')); // (string) not work before PHP 5.2.0
              // Add object to main query
              $selectObject = clone $select;

              $selectObject
                ->where($streamName . '.object_type = ?', $resource->child_type)
                ->where($streamName . '.object_id = ?', $resource->child_id);
              $union->union(array('(' . $selectObject->__toString() . ')')); // (string) not work before PHP 5.2.0
            }
          } else {
            // Add to main query
            $union->union(array('(' . $select->__toString() . ')')); // (string) not work before PHP 5.2.0
          }
        }
        // Finish main query
        $union
          ->order("action_id DESC")
          ->limit($limit);
        if( in_array($actionTypeGroup, array('membership', 'member_list')) && !$friendsFlage ) {
          return;
        }
        //echo $union;die;
        // Get actions
        $actions = $db->fetchAll($union);

        // Process ids
        $ids = array();
        if( empty($hastTagActionIds) ) {
          if( in_array($actionTypeGroup, array('all', 'posts')) ) {
            $ids = $this->getTaggedBaseActionIds($user, array('min' => $min_id, 'max' => $max_id));
          }
          $birthDayPluginEnable = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('birthday');
          if( $actionTypeGroup == 'all' && $birthday_post_enable && $birthDayPluginEnable && $user->getIdentity() ) {
            $ids = array_merge($ids, $this->getIncludeBirthdayWishFeed($min_id, $max_id));
          }
        }
        // No visible actions and ids
        if( empty($actions) && empty($ids) ) {
          return null;
        }

        foreach( $actions as $data ) {
          $ids[] = $data['action_id'];
        }
        $cacheApi->save($ids, $cacheActionKey);
      }
    }

    $ids = array_unique($ids);
    if( empty($ids) ) {
      return null;
    }
    // Finally get activity
    return $this->fetchAll(
        $this->select()
          ->where('action_id IN(' . join(',', $ids) . ')')
          ->order("action_id DESC")
          ->limit($limit)
    );
  }

  public function getAllPinedActionId($type = null)
  {

    $tablePin = Engine_Api::_()->getDbTable('pinsettings', 'advancedactivity');
    $select = $tablePin->select()->from($tablePin->info('name'), array('action_id'));
    if( !empty($type) ) {
      $select->where('object_type = ?', $type);
    }
    $select->where('reset_date >= ?', new Zend_Db_Expr('NOW()'));
    $results = $select->order("pinsetting_id DESC ")->query()->fetchAll();
    $pinedIds = array();
    foreach( $results as $result ) {
      $pinedIds[] = $result['action_id'];
    }

    return $pinedIds;
  }

  public function getTargetedActionIds()
  {
    $action_ids = array();
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !empty($viewer) && $viewer->isAdmin() ) {
      return $action_ids;
    }
    $fields = Engine_Api::_()->fields()->getFieldsValuesByAlias($viewer);
    $tableTarget = Engine_Api::_()->getDbTable('targets', 'advancedactivity');
    $select = $tableTarget->select()->from($tableTarget->info('name'), array('action_id'));
    if( !empty($fields['birthdate']) ) {
      $birthdate = explode('-', $fields['birthdate']);
      $value = mktime(0, 0, 0, $birthdate[2], $birthdate[1], $birthdate[0]);
      $date = new Zend_Date($value);
      $birth_duration = $date->sub(time());
      if( $birth_duration instanceof Zend_Date ) {
        $birth_duration = $birth_duration->toValue();
      }
      $age = (int) (- $birth_duration / 365 / 86400);
    }
    if( !empty($fields['gender']) ) {
      $optionTable = Engine_Api::_()->fields()->getTable('user', 'options');
      $gender = $optionTable->select()
        ->from($optionTable->info('name'), array('label'))
        ->where('option_id =?', $fields['gender'])
        ->query()
        ->fetchColumn();
    }
    if( !empty($gender) )
      $select->where('gender !=? and gender !=\'\'', $gender);
    if( !empty($age) )
      $select->orwhere('min_age > ? and min_age !=0 ', $age)
        ->orwhere('max_age < ? and max_age !=0 ', $age);
    $results = $tableTarget->fetchAll($select)->toArray();
    foreach( $results as $result ) {
      $action_ids[] = $result['action_id'];
    }

    return $action_ids;
  }

  public function getActivityAbout(Core_Model_Item_Abstract $about, User_Model_User $user, array $params = array())
  {
    // Proc args
    extract($this->_getInfo($params)); // action_id, limit, min_id, max_id
    $getAafMembershipType = Engine_Api::_()->getApi('settings', 'core')->getSetting('getaaf.membership.type', 0);
    // Prepare main query
    $streamTable = Engine_Api::_()->getDbtable('stream', 'activity');
    $db = $streamTable->getAdapter();
    $union = new Zend_Db_Select($db);
    $pinUnion = new Zend_Db_Select($db);
    // Prepare action types
    $masterActionTypes = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionTypes();
    $subjectActionTypes = array();
    $objectActionTypes = array();
    //CHECK IF SITEMOBILE PLUGIN IS ENABLED AND SITE IS IN MOBILE MODE:
    $enable_sitemobilemodule = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitemobile');
    if( $enable_sitemobilemodule )
      $checkMobMode = Engine_Api::_()->sitemobile()->checkMode('mobile-mode') || Engine_Api::_()->sitemobile()->checkMode('tablet-mode');
    else
      $checkMobMode = 0;
    // Filter types based on displayable
    foreach( $masterActionTypes as $type ) {
      //IF SITE MOBILE MODULE IS ENABLED AND SITE IS IN MOBILE MODE THEN WE WILL ONLY SHOW THE MODULES FEED WHICH ARE COMPATIBLE WITH OUR SITE MOBILE PLUGIN.
      if( $checkMobMode && isset($type->module) ) {
        //SPECIAL CASE FOR SITEALBUM ADDED BY DINESH
        if( $type->module == 'sitealbum' )
          $type->module = 'album';
        $supportedModules = Engine_Api::_()->sitemobile()->isSupportedModule($type->module);
        if( !$supportedModules )
          continue;
      }
      if( ($about->getType() == 'sitepage_page' && Engine_Api::_()->hasItemType('sitepage_page') && Engine_Api::_()->sitepage()->isFeedTypePageEnable()) || ($about->getType() == 'sitebusiness_business' && Engine_Api::_()->hasItemType('sitebusiness_business') && Engine_Api::_()->sitebusiness()->isFeedTypeBusinessEnable()) || ($about->getType() == 'sitegroup_group' && Engine_Api::_()->hasItemType('sitegroup_group') && Engine_Api::_()->sitegroup()->isFeedTypeGroupEnable()) || ($about->getType() == 'sitestore_store' && Engine_Api::_()->hasItemType('sitestore_store') && Engine_Api::_()->sitestore()->isFeedTypeStoreEnable()) ) {
        if( $actionTypeGroup == 'owner' && isset($type->is_object_thumb) && !$type->is_object_thumb )
          continue;
        if( $actionTypeGroup == 'membership' && isset($type->is_object_thumb) && $type->is_object_thumb )
          continue;
      }
      if( $actionTypeGroup == 'advertise' && !in_array($type->type, array( 'post_self_sell', 'post_sell')) ) {
        continue;
      }
      if( $type->displayable & 1 ) {
        $subjectActionTypes[] = $type->type;
      }
      if( $type->displayable & 2 ) {
        $objectActionTypes[] = $type->type;
      }
    }

    // Filter types based on user request
    if( isset($showTypes) && is_array($showTypes) && !empty($showTypes) ) {
      $subjectActionTypes = array_intersect($subjectActionTypes, $showTypes);
      $objectActionTypes = array_intersect($objectActionTypes, $showTypes);
    } else if( isset($hideTypes) && is_array($hideTypes) && !empty($hideTypes) ) {
      $subjectActionTypes = array_diff($subjectActionTypes, $hideTypes);
      $objectActionTypes = array_diff($objectActionTypes, $hideTypes);
    }

    // Nothing to show
    if( empty($getAafMembershipType) || (empty($subjectActionTypes) && empty($objectActionTypes)) ) {
      return null;
    }

    if( empty($subjectActionTypes) ) {
      $subjectActionTypes = null;
    } else if( count($subjectActionTypes) == count($masterActionTypes) ) {
      $subjectActionTypes = true;
    } else {
      $subjectActionTypes = "'" . join("', '", $subjectActionTypes) . "'";
    }

    if( empty($objectActionTypes) ) {
      $objectActionTypes = null;
    } else if( count($objectActionTypes) == count($masterActionTypes) ) {
      $objectActionTypes = true;
    } else {
      $objectActionTypes = "'" . join("', '", $objectActionTypes) . "'";
    }

    // Prepare sub queries
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('getActivity', array(
      'for' => $user,
      'about' => $about,
    ));
    $responses = (array) $event->getResponses();

    if( empty($responses) ) {
      return null;
    }
    $member_ids = array();
    $action_ids = array();
    $ids = array();
    if( $actionTypeGroup == 'schedule_post' && null === $max_id && null === $min_id && null === $action_id ) {
      $ids = $this->getAllShecduledPostActionIds($user, $about);
      if( empty($ids) ) {
        return;
      }
    } else {
      if( $actionTypeGroup == 'hidden_post' ) {
        $hiddenIds = Engine_Api::_()->getDbtable('hide', 'advancedactivity')->getHideItemByMember($user);
        $action_ids = !empty($hiddenIds['activity_action']) ? $hiddenIds['activity_action'] : array();
        if( empty($action_ids) ) {
          return;
        }
      } else if( $actionTypeGroup == 'memories' ) {
        $action_ids = $this->getOnThisDayActivity($user, array('filter' => true));
        if( empty($action_ids) ) {
          return;
        }
      } else if( $actionTypeGroup == 'owner' ) {
        if( $about instanceof Sitepage_Model_Page ) {
          if( Engine_Api::_()->hasItemType('sitepage_page') && !Engine_Api::_()->sitepage()->isFeedTypePageEnable() ) {
            $manageadminTable = Engine_Api::_()->getDbtable('manageadmins', 'sitepage');
            $manageadminTableName = $manageadminTable->info('name');
            $select = $manageadminTable->select()
              ->from($manageadminTableName, 'user_id')
              ->where('page_id = ?', $about->getIdentity());
            $member_ids = $select->query()->fetchAll(Zend_Db::FETCH_COLUMN);
          }
        } elseif( $about instanceof Sitebusiness_Model_Business ) {
          if( Engine_Api::_()->hasItemType('sitebusiness_business') && !Engine_Api::_()->sitebusiness()->isFeedTypeBusinessEnable() ) {
            $manageadminTable = Engine_Api::_()->getDbtable('manageadmins', 'sitebusiness');
            $manageadminTableName = $manageadminTable->info('name');
            $select = $manageadminTable->select()
              ->from($manageadminTableName, 'user_id')
              ->where('business_id = ?', $about->getIdentity());
            $member_ids = $select->query()->fetchAll(Zend_Db::FETCH_COLUMN);
          }
        } elseif( $about instanceof Sitegroup_Model_Group ) {
          if( Engine_Api::_()->hasItemType('sitegroup_group') && !Engine_Api::_()->sitegroup()->isFeedTypeGroupEnable() ) {
            $manageadminTable = Engine_Api::_()->getDbtable('manageadmins', 'sitegroup');
            $manageadminTableName = $manageadminTable->info('name');
            $select = $manageadminTable->select()
              ->from($manageadminTableName, 'user_id')
              ->where('group_id = ?', $about->getIdentity());
            $member_ids = $select->query()->fetchAll(Zend_Db::FETCH_COLUMN);
          }
        } elseif( $about instanceof Sitestore_Model_Store ) {
          if( Engine_Api::_()->hasItemType('sitestore_store') && !Engine_Api::_()->sitestore()->isFeedTypeStoreEnable() ) {
            $manageadminTable = Engine_Api::_()->getDbtable('manageadmins', 'sitestore');
            $manageadminTableName = $manageadminTable->info('name');
            $select = $manageadminTable->select()
              ->from($manageadminTableName, 'user_id')
              ->where('store_id = ?', $about->getIdentity());
            $member_ids = $select->query()->fetchAll(Zend_Db::FETCH_COLUMN);
          }
        } elseif( $about instanceof Siteevent_Model_Event ) {
          $member_ids = $about->getLeadersIds();
        } else {
          if( $about instanceof User_Model_User ) {
            $member_ids[] = $about->getIdentity();
          } elseif( $about instanceof Group_Model_Group ) {
            $objectParent = $about->getParent('user');
            if( $objectParent instanceof User_Model_User ) {
              $member_ids[] = $objectParent->getIdentity();
            }
            foreach( $about->getOfficerList()->getAll() as $value ) {
              $member_ids[] = $value->child_id;
            }
          } else {
            $objectParent = $about->getParent('user');
            if( $objectParent instanceof User_Model_User ) {
              $member_ids[] = $objectParent->getIdentity();
            }
          }
        }
      } elseif( $actionTypeGroup == 'membership' ) {
        if( $about->getType() === 'siteevent_event' ) {
          $member_ids = $about->membership()->getMembersIds();
        } else {
          $member_ids = $user->membership()->getMembershipsOfIds();
        }


        if( empty($member_ids) )
          return;
      } else if( $actionTypeGroup === 'pinfeed' ) {
        $action_ids = $this->getAllPinedActionId($about->getGuid());
        if( empty($action_ids) ) {
          return;
        }
      }
      $pinIdsList = array();
      if( $actionTypeGroup == 'all' && null === $max_id && null === $min_id && null === $action_id ) {
        $pinIdsList = $this->getAllPinedActionId($about->getGuid());
      }
      foreach( $responses as $response ) {
        if( empty($response) )
          continue;

        // Target info
        $select = $streamTable->select()
          ->from($streamTable->info('name'), 'action_id')
          ->where('target_type = ?', $response['type'])
        ;

        if( empty($response['data']) ) {
          // Simple
          $select->where('target_id = ?', 0);
        } else if( is_scalar($response['data']) || count($response['data']) === 1 ) {
          // Single
          if( is_array($response['data']) ) {
            list($response['data']) = $response['data'];
          }
          $select->where('target_id = ?', $response['data']);
        } else if( is_array($response['data']) ) {
          // Array
          $select->where('target_id IN(?)', (array) $response['data']);
        } else {
          // Unknown
          continue;
        }
        if( !empty($action_ids) ) {
          $select->where('action_id IN(?)', (array) $action_ids);
        }
        // Add action_id/max_id/min_id
        if( null !== $action_id ) {
          $select->where('action_id = ?', $action_id);
        } else {
          if( null !== $min_id ) {
            $select->where('action_id >= ?', $min_id);
          } else if( null !== $max_id ) {
            $select->where('action_id <= ?', $max_id);
          }
        }

        // Add order/limit
        $select
          ->order("action_id DESC")
          ->limit($limit);


        // Add subject to main query
        $selectSubject = clone $select;
        if( $subjectActionTypes !== null ) {
          if( $subjectActionTypes !== true ) {
            $selectSubject->where('type IN(' . $subjectActionTypes . ')');
          }
          $selectSubject
            ->where('subject_type = ?', $about->getType())
            ->where('subject_id = ?', $about->getIdentity());

          if( !empty($member_ids) ) {
            $selectSubject
              ->where('object_type = ?', 'user')
              ->where('object_id  In(?)', (array) $member_ids);
          }

          $union->union(array('(' . $selectSubject->__toString() . ')')); // (string) not work before PHP 5.2.0
        }

        // Add object to main query
        $selectObject = clone $select;
        if( $objectActionTypes !== null ) {
          if( $objectActionTypes !== true ) {
            $selectObject->where('type IN(' . $objectActionTypes . ')');
          }
          $selectObject
            ->where('object_type = ?', $about->getType())
            ->where('object_id = ?', $about->getIdentity());

          if( !empty($member_ids) ) {
            $selectObject
              ->where('subject_type = ?', 'user')
              ->where('subject_id   IN(?)', (array) $member_ids);
          }
          $union->union(array('(' . $selectObject->__toString() . ')')); // (string) not work before PHP 5.2.0
          if( $pinIdsList ) {
            $selectObject->where('action_id IN(?)', array($pinIdsList));
            $pinUnion->union(array('(' . $selectObject->__toString() . ')'));
          }
        }
      }

      // Finish main query
      $union
        ->order("action_id DESC")
        ->limit($limit);

      // Get actions
      $actions = $db->fetchAll($union);

      // Process ids
      $ids = array();
      $pinIds = array();
      if( $actionTypeGroup == 'all' ) {
        if( $pinIdsList ) {
          $pinUnion
            ->order("action_id DESC")
            ->limit($limit);

          // Get actions
          $pinActions = $db->fetchAll($pinUnion);
          foreach( $pinActions as $data ) {
            $pinIds[] = $data['action_id'];
          }
        }
        if( ($about->getType() == 'sitepageevent_event' ) ) {
          $ids = Engine_Api::_()->getApi('subCore', 'sitepage')->getEveryonePageProfileFeeds($about, $this->_getInfo($params));
        } else if( ($about->getType() == 'sitebusinessevent_event' ) ) {
          $ids = Engine_Api::_()->getApi('subCore', 'sitebusiness')->getEveryoneBusinessProfileFeeds($about, $this->_getInfo($params));
        } else if( ($about->getType() == 'sitegroupevent_event' ) ) {
          $ids = Engine_Api::_()->getApi('subCore', 'sitegroup')->getEveryoneGroupProfileFeeds($about, $this->_getInfo($params));
        } else if( ($about->getType() == 'sitestoreevent_event' ) ) {
          $ids = Engine_Api::_()->getApi('subCore', 'sitestore')->getEveryoneStoreProfileFeeds($about, $this->_getInfo($params));
        }
      }
    }
    $ids = array_unique($ids);
    // No visible actions and ids
    if( empty($actions) && empty($ids) && empty($pinIds) ) {
      return null;
    }

    foreach( $actions as $data ) {
      if( $pinIdsList && in_array($data['action_id'], $pinIdsList) ) {
        continue;
      }
      $ids[] = $data['action_id'];
    }
    if( empty($ids) && empty($pinIds) ) {
      return null;
    }
    $actions = array();
    if( $pinIds ) {
      $ids = array_diff($ids, $pinIds);
      if( null === $max_id && null === $min_id && null === $action_id ) {
        $results = $this->fetchAll(
          $this->select()
            ->where('action_id IN(?)', (array) $pinIds)
            ->order("action_id DESC")
            ->limit($limit)
        );
        foreach( $results as $action ) {
          $actions[] = $action;
        }
      }
    }
    // Finally get activity
    if( $ids ) {
      $results = $this->fetchAll(
        $this->select()
          ->where('action_id IN(' . join(',', $ids) . ')')
          ->order("action_id DESC")
          ->limit($limit)
      );
      foreach( $results as $action ) {
        $actions[] = $action;
      }
    }
    return $actions;
  }

  // Utility
  /**
   * Add an action-privacy binding
   *
   * @param int $action_id
   * @param string $type
   * @param Core_Model_Item_Abstract $subject
   * @param Core_Model_Item_Abstract $object
   * @return int The insert id
   */
  public function addActivityBindings($action)
  {
    // Get privacy bindings
    $privacy = $action->privacy;
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('addActivity', array(
      'subject' => $action->getSubject(),
      'object' => $action->getObject(),
      'type' => $action->type,
      'content' => $privacy,
      'aafPayload' => true
    ));

    $notInclude = false;
    if( !empty($privacy) && !in_array($privacy, array('everyone', 'networks', 'friends')) && !Engine_Api::_()->advancedactivity()->isNetworkBasePrivacy($privacy) ) {
      $notInclude = true;
    }

    $privacyNetworkIds = array();
    $item = $object = $action->getObject();
    if( !($object instanceof User_Model_User) && !isset($object->networks_privacy) ) {
      $item = $object->getParent();
    }
    $hasAddNetworkPrivacy = false;
    $viewNetworkPricavyEnable = (isset($item->networks_privacy) && $item->networks_privacy) ? Engine_Api::_()->getApi('settings', 'core')->getSetting(strtolower($item->getModuleName()) . '.networkprofile.privacy', 0) : 0;
    if( $viewNetworkPricavyEnable ) {
      $privacyNetworkIds = is_array($item->networks_privacy) ? $item->networks_privacy : explode(",", $item->networks_privacy);
    }
    // Add privacy bindings
    $streamTable = Engine_Api::_()->getDbtable('stream', 'activity');
    $include_type = array();
    foreach( (array) $event->getResponses() as $response ) {
      if( isset($response['target']) ) {
        $target_type = $response['target'];
        $target_id = 0;
      } else if( isset($response['type']) && isset($response['identity']) ) {
        $target_type = $response['type'];
        $target_id = $response['identity'];
      } else {
        continue;
      }

      if( !empty($privacy) && $privacy == 'friends' && !in_array($target_type, array('members_list', 'owner', 'parent', 'members')) ) {
        continue;
      }

      if( !empty($privacy) && $privacy == 'networks' && !in_array($target_type, array('members_list', 'owner', 'parent', 'members', 'network')) ) {
        continue;
      }

      if( Engine_Api::_()->advancedactivity()->isNetworkBasePrivacy($privacy) && !in_array($target_type, array('network_list', 'owner', 'parent')) ) {
        continue;
      } elseif( $target_type == 'network_list' ) {
        $target_type = 'network';
      }

      if( $notInclude && !in_array($target_type, array('members_list', 'owner', 'parent')) ) {
        continue;
      }

      if( count($privacyNetworkIds) && in_array($target_type, array('everyone', 'registered', 'network', 'members')) ) {
        $hasAddNetworkPrivacy = true;
        continue;
      }


//      if ($target_type == 'sitepage_page' && $action->type == 'like_sitepage_page') {
//        continue;
//      }

      if( isset($include_type[$target_type]) && in_array($target_id, $include_type[$target_type]) ) {
        continue;
      }
      $include_type[$target_type][] = $target_id;
      $streamTable->insert(array(
        'action_id' => $action->action_id,
        'type' => $action->type,
        'target_type' => (string) $target_type,
        'target_id' => (int) $target_id,
        'subject_type' => $action->subject_type,
        'subject_id' => $action->subject_id,
        'object_type' => $action->object_type,
        'object_id' => $action->object_id,
      ));
    }

    if( $hasAddNetworkPrivacy ) {
      $target_type = 'network';
      foreach( $privacyNetworkIds as $target_id ) {
        if( isset($include_type[$target_type]) && in_array($target_id, $include_type[$target_type]) ) {
          continue;
        }
        $include_type[$target_type][] = $target_id;
        $streamTable->insert(array(
          'action_id' => $action->action_id,
          'type' => $action->type,
          'target_type' => 'network',
          'target_id' => (int) $target_id,
          'subject_type' => $action->subject_type,
          'subject_id' => $action->subject_id,
          'object_type' => $action->object_type,
          'object_id' => $action->object_id,
        ));
      }
    }
    return $this;
  }

  public function attachActivity($action, Core_Model_Item_Abstract $attachment, $mode = 1, $attachmentData = null)
  {

    $attachmentTable = Engine_Api::_()->getDbtable('attachments', 'activity');

    if( is_numeric($action) ) {
      $action = $this->fetchRow($this->select()->where('action_id = ?', $action)->limit(1));
    }

    if( !($action instanceof Activity_Model_Action) ) {
      $eInfo = ( is_object($action) ? get_class($action) : $action );
      throw new Activity_Model_Exception(sprintf('Invalid action passed to attachActivity: %s', $eInfo));
    }

    if( isset($attachmentData) && $attachmentData['type'] == 'photo' && $attachmentData['photo_id'] ) {
      $typeExplode = explode(",", $attachmentData['photo_id']);
      foreach( $typeExplode as $photo_id ) {
        $attachmentRow = $attachmentTable->createRow();
        $attachmentRow->action_id = $action->action_id;
        $attachmentRow->type = $attachment->getType();
        $attachmentRow->id = $photo_id; //$attachment->getIdentity();
        $attachmentRow->mode = (int) $mode;
        $attachmentRow->save();

        $action->attachment_count++;
        $action->save();

        if( Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitealbum') ) {
          $photoItem = Engine_Api::_()->getItem('album_photo', $photo_id);
          if( $photoItem ) {
            $albumItem = Engine_Api::_()->getItem('album', $photoItem->album_id);
            if( $albumItem && isset($albumItem->photos_count) ) {
              $albumItem->photos_count++;
              $albumItem->save();
            }
          }
        }
      }
    } else {
      $attachmentRow = $attachmentTable->createRow();
      $attachmentRow->action_id = $action->action_id;
      $attachmentRow->type = $attachment->getType();
      $attachmentRow->id = $attachment->getIdentity();
      $attachmentRow->mode = (int) $mode;
      $attachmentRow->save();

      $action->attachment_count++;
      $action->save();

      if( Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitealbum') ) {
        $photoItem = Engine_Api::_()->getItem('album_photo', $attachment->getIdentity());
        if( $photoItem ) {
          $albumItem = Engine_Api::_()->getItem('album', $photoItem->album_id);
          if( isset($albumItem->photos_count) && $albumItem ) {
            $albumItem->photos_count++;
            $albumItem->save();
          }
        }
      }
    }
    return $this;
  }

  // Utility

  protected function _getInfo(array $params)
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $args = array(
      'limit' => $settings->getSetting('activity.length', 20),
      'action_id' => null,
      'max_id' => null,
      'min_id' => null,
      'showTypes' => null,
      'hideTypes' => null,
    );

    $newParams = array();
    foreach( $args as $arg => $default ) {
      if( !empty($params[$arg]) ) {
        $newParams[$arg] = $params[$arg];
      } else {
        $newParams[$arg] = $default;
      }
      if( isset($params[$arg]) )
        unset($params[$arg]);
    }
    $newParams = array_merge($newParams, $params);
    return $newParams;
  }

  //---------

  public function getActivityOfShare(User_Model_User $user, array $params = array())
  {
    // Proc args
    extract($this->_getInfo($params)); // action_id, limit, min_id, max_id
    // Prepare main query
    $streamTable = Engine_Api::_()->getDbtable('stream', 'activity');
    $db = $streamTable->getAdapter();
    $union = new Zend_Db_Select($db);

    // Prepare action types
    $masterActionTypes = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionTypes();
    $mainActionTypes = array();

    // Filter out types set as not displayable
    foreach( $masterActionTypes as $type ) {
      if( $type->displayable & 4 ) {
        $mainActionTypes[] = $type->type;
      }
    }

    // Filter types based on user request
    if( isset($showTypes) && is_array($showTypes) && !empty($showTypes) ) {
      $mainActionTypes = array_intersect($mainActionTypes, $showTypes);
    } else if( isset($hideTypes) && is_array($hideTypes) && !empty($hideTypes) ) {
      $mainActionTypes = array_diff($mainActionTypes, $hideTypes);
    }

    // Nothing to show
    if( empty($mainActionTypes) ) {
      return null;
    }
    // Show everything
    else if( count($mainActionTypes) == count($masterActionTypes) ) {
      $mainActionTypes = true;
    }
    // Build where clause
    else {
      $mainActionTypes = "'" . join("', '", $mainActionTypes) . "'";
    }

    // Prepare sub queries
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('getActivity', array(
      'for' => $user,
    ));
    $responses = (array) $event->getResponses();

    if( empty($responses) ) {
      return null;
    }

    foreach( $responses as $response ) {
      if( empty($response) )
        continue;

      $select = $streamTable->select()
        ->from($streamTable->info('name'), 'action_id')
        ->where('target_type = ?', $response['type'])
      ;

      if( empty($response['data']) ) {
        // Simple
        $select->where('target_id = ?', 0);
      } else if( is_scalar($response['data']) || count($response['data']) === 1 ) {
        // Single
        if( is_array($response['data']) ) {
          list($response['data']) = $response['data'];
        }
        $select->where('target_id = ?', $response['data']);
      } else if( is_array($response['data']) ) {
        // Array
        $select->where('target_id IN(?)', (array) $response['data']);
      } else {
        // Unknown
        continue;
      }

      // Add action_id/max_id/min_id
      if( null !== $action_ids ) {
        $select->where('action_id IN(?)', (array) $action_ids);
      }

      if( $mainActionTypes !== true ) {
        $select->where('type IN(' . $mainActionTypes . ')');
      }

      // Add order/limit
      $select
        ->order("action_id DESC")
        ->limit($limit);

      // Add to main query
      $union->union(array('(' . $select->__toString() . ')')); // (string) not work before PHP 5.2.0
    }

    // Finish main query
    $union
      ->order("action_id DESC")
      ->limit($limit);

    // Get actions
    $actions = $db->fetchAll($union);

    // No visible actions
    if( empty($actions) ) {
      return null;
    }

    // Process ids
    $ids = array();
    foreach( $actions as $data ) {
      $ids[] = $data['action_id'];
    }
    $ids = array_unique($ids);

    // Finally get activity
    return $this->fetchAll(
        $this->select()
          ->where('action_id IN(' . join(',', $ids) . ')')
          ->order("action_id DESC")
          ->limit($limit)
    );
  }

  // get friends base birthday wish action id
  public function getBirthdayWishActionIds($params = array())
  {

    $actionTableName = $this->info('name');

    $user = Engine_Api::_()->user()->getViewer();
    $memberIds = array();
    $memberIds = $user->membership()->getMembershipsOfIds();
    //  $memberIds[] = $user->getIdentity();
    $params['posterIds'] = array_merge($memberIds, array($user->getIdentity()));
    if( isset($params['memberIds']) ) {
      $memberIds = $params['memberIds'];
    }
    $select = $this->select()
      ->setIntegrityCheck(false)
      ->from($actionTableName, array('*', "DATE_FORMAT(" . "date, '%Y') AS year"))
      ->where('type = ?', 'birthday_post')
      ->where('object_type = ?', 'user')
      ->order('date DESC');

    if( isset($params['year']) && !empty($params['year']) ) {
      $select->where("DATE_FORMAT(date, '%Y') = ?", $params['year']);
    }

    if( !empty($memberIds) )
      $select->where('object_id in(?)', (array) $memberIds);

    $select->where('subject_id in(?)', (array) $params['posterIds'])
      ->where('subject_type = ?', 'user');
    return $this->fetchAll($select);
  }

  public function getIncludeBirthdayWishFeed($min = null, $max = null)
  {

    $results = $this->getBirthdayWishActionIds();

    $actionsIds = array();
    $includedMember = array();

    foreach( $results as $value ) {
      if( (!empty($min) && $min > $value->action_id ) || (!empty($max) && $max < $value->action_id ) ) {
        continue;
      }
      if( isset($includedMember[$value->year]) && in_array($value->object_id, $includedMember[$value->year]) )
        continue;

      $includedMember[$value->year][] = $value->object_id;
      $actionsIds[] = $value->action_id;
    }
    return $actionsIds;
  }

  public function getBirthdayFeedRelatedActions(Activity_Model_Action $action)
  {

    $posterIds = array();
    $year = date('Y', strtotime($action->date));
    return $this->getBirthdayWishActionIds(array('memberIds' => array($action->object_id), 'year' => $year));
  }

  public function getTaggedBaseActionIds($user, $params = array())
  {

    $memberIds = $user->membership()->getMembershipsOfIds();
    $memberIds[] = $user->getIdentity();
    $table = Engine_Api::_()->getDbtable('TagMaps', 'core');
    $select = $table->select()
      ->from($table->info('name'), "resource_id")
      ->where('resource_type = ?', 'activity_action')
      ->where('tag_type = ?', 'user')
      ->where('tag_id in(?)', (array) $memberIds)
      ->order('creation_date 	DESC');
    if( !empty($params['min']) ) {
      $select->where('resource_id >= ?', $params['min']);
    } else if( !empty($params['max']) ) {
      $select->where('resource_id <= ?', $params['max']);
    }

    return $select->query()
        ->fetchAll(Zend_Db::FETCH_COLUMN);
  }

  public function detachFromActivity(Core_Model_Item_Abstract $attachment)
  {
    $attachmentsTable = Engine_Api::_()->getDbtable('attachments', 'activity');
    $select = $attachmentsTable->select()
      ->where('`type` = ?', $attachment->getType())
      ->where('`id` = ?', $attachment->getIdentity())
    ;

    foreach( $attachmentsTable->fetchAll($select) as $row ) {
      $this->update(array(
        'attachment_count' => new Zend_Db_Expr('attachment_count - 1'),
        ), array(
        'action_id = ?' => $row->action_id,
      ));
      $row->delete();
    }

    return $this;
  }

// Actions

  public function getActionById($action_id)
  {
    return $this->find($action_id)->current();
  }

  public function getActionsByObject(Core_Model_Item_Abstract $object)
  {
    $select = $this->select()->where('object_type = ?', $object->getType())
      ->where('object_id = ?', $object->getIdentity());
    return $this->fetchAll($select);
  }

  public function getActionsBySubject(Core_Model_Item_Abstract $subject)
  {
    $select = $this->select()
      ->where('subject_type = ?', $subject->getType())
      ->where('subject_id = ?', $subject->getIdentity())
    ;

    return $this->fetchAll($select);
  }

  public function getActionsByAttachment(Core_Model_Item_Abstract $attachment)
  {
    // Get all action ids from attachments
    $attachmentTable = Engine_Api::_()->getDbtable('attachments', 'activity');
    $select = $attachmentTable->select()
      ->where('type = ?', $attachment->getType())
      ->where('id = ?', $attachment->getIdentity())
    ;

    $actions = array();
    foreach( $attachmentTable->fetchAll($select) as $attachmentRow ) {
      $actions[] = $attachmentRow->action_id;
    }

    // Get all actions
    $select = $this->select()
      ->where('action_id IN(\'' . join("','", $ids) . '\')')
    ;

    return $this->fetchAll($select);
  }

  public function clearActivityBindings($action)
  {
    $streamTable = Engine_Api::_()->getDbtable('stream', 'activity');
    $streamTable->delete(array(
      'action_id = ?' => $action->getIdentity(),
    ));
  }

  public function resetActivityBindings($action)
  {
    if( $action->getObject() ) {
      $this->clearActivityBindings($action);
      $this->addActivityBindings($action);
    }
    return $this;
  }

  /**
   * Gets action type meta info
   *
   * @param string $type
   * @return Engine_Db_Row
   */
  public function getActionType($type)
  {
    return $this->getActionTypes()->getRowMatching('type', $type);
  }

  /**
   * Gets all action type meta info
   *
   * @param string|null $type
   * @return Engine_Db_Rowset
   */
  public function getActionTypes()
  {
    if( null === $this->_actionTypes ) {
      $table = Engine_Api::_()->getDbtable('actionTypes', 'activity');
      $this->_actionTypes = $table->fetchAll();
    }

    return $this->_actionTypes;
  }

  private function getHashTagActionsIds($hashtag_search)
  {

    $hashtagIds = $actionIds = array();
    $hashtags = explode(' ', $hashtag_search);
    $tagTable = Engine_Api::_()->getDbtable('tags', 'sitehashtag');
    $tagmapTable = Engine_Api::_()->getDbtable('tagmaps', 'sitehashtag');
    foreach( $hashtags as $hashtag ) {
      $tagTableRow = $tagTable->fetchRow(
        $tagTable->select()
          ->where('text = ?', $hashtag)
      );
      if( $tagTableRow ) {
        $hashtagIds[] = $tagTableRow->tag_id;
      }
    }

    foreach( $hashtagIds as $hashtagId ) {
      $rowsets = $tagmapTable->fetchAll($tagmapTable->select()->where('tag_id = ?', $hashtagId));

      foreach( $rowsets as $row ) {
        $actionIds[] = $row->action_id;
      }
    }

    return $actionIds;
  }

  public function getOnThisDayActivity($viewer, $params = array())
  {
    $oldTz = date_default_timezone_get();
    date_default_timezone_set($viewer->timezone);
    $date = date('Y-m-d H:i:s');
    date_default_timezone_set($oldTz);
    $select = $this->select()->from($this->info('name'), array('action_id'))
      ->where('(subject_type = "user" AND subject_id =?) OR (object_type="user" AND object_id =? )', $viewer->getIdentity())
      ->where('type LIKE "%post_self%" OR type LIKE "%status%"');
    if( !empty($params['filter']) ) {
      $actionIds = $select->where("DAY(date) = DAY('$date') and MONTH(date) = MONTH('$date') and YEAR(date) != YEAR('$date')")
        ->order('comment_count DESC')
        ->order('like_count DESC')
        ->query()
        ->fetchAll();

      return array_column($actionIds, 'action_id');
    }
    $action_id = $select->where("DAY(date) = DAY('$date') and MONTH(date) = MONTH('$date') and YEAR(date) != YEAR('$date')")
      ->order('comment_count DESC')
      ->order('like_count DESC')
      ->limit(1)
      ->query()
      ->fetchColumn()
    ;
    return $this->getActionById($action_id);
  }

 public function getAllShecduledPostActions()
  {
    $select = $this->select()
      ->where('publish_date <= ?', date('Y-m-d H:i:s'))
    ;
    return $this->fetchAll($select);
  }

  public function getAllShecduledPostActionIds(User_Model_User $user, $item = null)
  {
    $action_ids = array();
    $select = $this->select()
      ->where('publish_date != ?', '')
      ->where('subject_type = ?', 'user')
      ->where('subject_id = ?', $user->getIdentity());
    if( $item ) {
      $select->where('object_type = ?', $item->getType())
        ->where('subject_id = ?', $item->getIdentity());
      ;
    }
    $actions = $this->fetchAll($select);
    foreach( $actions as $action ) {
      $action_ids[] = $action->action_id;
    }
    return $action_ids;
  }

  public function getAllBuySellActionIdsByUserId($viewer, $about = null)
  {
    $attachmentTable = Engine_Api::_()->getDbTable('attachments', 'activity');
    $select = $attachmentTable->select()->from($attachmentTable->info('name'), array('action_id'))
      ->where('type = ? ', 'advancedactivity_sell');
    $action_ids = array();
    $attachments = $this->fetchAll($select);
    foreach( $attachments as $attachment ) {
      $action = $this->getActionById($attachment->action_id);

      if( !empty($about) ) {
        if( $action->getObject()->getIdentity() == $about->getIdentity() && $action->getObject()->getType() == $about->getType() ) {
          $action_ids[] = $attachment->action_id;
        }
      } else {
        $action_ids[] = $attachment->action_id;
      }
    }
    return $action_ids;
  }

}
