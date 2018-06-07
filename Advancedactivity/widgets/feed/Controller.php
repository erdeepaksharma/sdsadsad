<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Controller.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Widget_FeedController extends Engine_Content_Widget_Abstract
{

  private $_blockedUserIds = array();
  private $isForCategoryPage = false;
  private $settings;
  private $viewer_id;
  private $_cacheApi;

  public function indexAction()
  {

    // Don't render this if not authorized
    $this->_cacheApi = Engine_Api::_()->getApi('cache', 'advancedactivity');
    $this->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->integrateCommunityAdv = $this->_getParam('integrateCommunityAdv', 1);
    $this->view->feedSettings = $this->_getParam('feedSettings', array());
    $this->view->viewer_id = $this->viewer_id = $viewer_id = $viewer->getIdentity();
    $this->view->settingsApi = $this->settings = Engine_Api::_()->getApi('settings', 'core');
    $this->subject = $subject = Engine_Api::_()->core()->hasSubject() ? Engine_Api::_()->core()->getSubject() : null;
    // Don't render this if not authorized
    $cacheId = __CLASS__ . 'shouldNoRender';
    if( $this->_cacheApi->test($cacheId) ) {
      $shouldNoRender = $this->shouldNoRender();
      $this->_cacheApi->save($shouldNoRender, $cacheId);
    } else {
      $shouldNoRender = $this->_cacheApi->load($cacheId);
    }
    if( $shouldNoRender ) {
      return $this->setNoRender();
    }
    $this->setSettings();
    $listLimit = 0;
    $composerLimit = 1;
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $this->view->onthisday = $request->getParam('onthisday', 0);
    $this->view->registerHelper(new Advancedactivity_View_Helper_GetRichContent(), 'getRichContent');;
    // Get some options
    $length = $request->getParam('limit', $this->settings->getSetting('activity.length', 15));
    if( $length > 50 ) {
      $length = 50;
    }
    $itemActionLimit = $this->settings->getSetting('activity.userlength', 5);
    $getComposerValue = $this->settings->getSetting('aaf.composer.value', $listLimit);

    $actionTable = Engine_Api::_()->getDbtable('actions', 'advancedactivity');
    $getListViewValue = Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.list.view.value', $composerLimit);
    $getPublishValue = Engine_Api::_()->getApi('settings', 'core')->getSetting('aaf.publish.str.value', $composerLimit);
    $this->view->action_id = (int) $request->getParam('action_id');
    if( !$this->view->action_id ) {
      $this->view->action_id = $this->_getParam('action_id');
    }
    $seaocoreVersion = Engine_Api::_()->seaocore()->getCurrentVersion('4.9.0', 'user');
    if( $viewer && !$viewer->isAdmin() && !empty($seaocoreVersion) ) {
      $this->_blockedUserIds = $viewer->getAllBlockedUserIds();
    }
    // Get all activity feed types for custom view?
    $actionTypeFilters = array();
    $listTypeFilter = array();
    if( !$this->view->feedOnly && empty($subject) && !$this->isForCategoryPage ) {
      $this->setFilterList();
    }
    $actionTypeGroup = $this->view->actionFilter;
    if( $actionTypeGroup && !in_array($actionTypeGroup, array('membership', 'owner', 'all', 'network_list', 'member_list', 'custom_list', 'category_list', 'activity_category')) ) {
      $actionTypesTable = Engine_Api::_()->getDbtable('actionTypes', 'advancedactivity');
      $this->view->groupedActionTypes = $groupedActionTypes = $actionTypesTable->getEnabledGroupedActionTypes();
      if( isset($groupedActionTypes[$actionTypeGroup]) ) {
        $actionTypeFilters = $groupedActionTypes[$actionTypeGroup];
        if( in_array($actionTypeGroup, array('sitepage', 'sitebusiness', 'sitegroup', 'sitestore')) ) {
          $actionTypeGroupSubModules = Engine_Api::_()->advancedactivity()->getSubModules($actionTypeGroup);
          foreach( $actionTypeGroupSubModules as $actionTypeGroupSubModule ) {
            if( isset($groupedActionTypes[$actionTypeGroupSubModule]) ) {
              $actionTypeFilters = array_merge($actionTypeFilters, $groupedActionTypes[$actionTypeGroupSubModule]);
            }
          }
        }
      }
    } else if( in_array($actionTypeGroup, array('member_list', 'custom_list', 'category_list')) && ($list_id = $this->_getParam('list_id')) != null ) {
      $listTypeFilter = Engine_Api::_()->advancedactivity()->getListBaseContent($actionTypeGroup, array('list_id' => $list_id));
    } else if( $actionTypeGroup == 'activity_category' && ($list_id = $this->_getParam('list_id')) != null ) {
      $listTypeFilter['categories_id'][] = $list_id;
      $actionTypeGroup = 'category_list';
      $this->view->list_id = $list_id = $this->_getParam('list_id');
    } else if( $actionTypeGroup == 'network_list' && ($list_id = $this->_getParam('list_id') != null) ) {
      $this->view->list_id = $list_id = $this->_getParam('list_id');
      $listTypeFilter = array($list_id);
    }

    // Get config options for activity
    $config = array(
      'action_id' => (int) $this->view->action_id,
      'max_id' => (int) $request->getParam('maxid'),
      'min_id' => (int) $request->getParam('minid'),
      'limit' => (int) $length,
      'showTypes' => $actionTypeFilters,
      'membership' => $actionTypeGroup == 'membership' ? true : false,
      'listTypeFilter' => $listTypeFilter,
      'actionTypeGroup' => $actionTypeGroup
    );

    if( Engine_Api::_()->hasModuleBootstrap('sitehashtag') && $this->view->search ) {
      $config['hashtag'] = $this->view->search;
    }


    // Pre-process feed items
    $selectCount = 0;
    $nextid = null;
    $firstid = $actionTable->select()->from($actionTable, 'action_id')->order('action_id DESC')->limit(1)->query()->fetchColumn();
    $tmpConfig = $config;
    $activity = array();
    $endOfFeed = false;

    $friendRequests = array();
    $itemActionCounts = array();

    $similarActivities = array();

    $hideItems = array();
    if( empty($subject) && $actionTypeGroup != 'hidden_post' && $viewer->getIdentity() ) {
      $hideItems = Engine_Api::_()->getDbtable('hide', 'advancedactivity')->getHideItemByMember($viewer);
//            if ($default_firstid) {
//                $firstid = $default_firstid;
//            }
    }
    $hideTargetedActionsIds = $actionTable->getTargetedActionIds();
    $grouped_actions = array();

    do {
      // Get current batch
      $actions = null;
      if( !empty($subject) ) {
        $actions = $actionTable->getActivityAbout($subject, $viewer, $tmpConfig);
      } else {
        $actions = $actionTable->getActivity($viewer, $tmpConfig);
      }
      $selectCount++;

      // Are we at the end?
      if( count($actions) < $length || count($actions) <= 0 ) {
        $endOfFeed = true;
      }

      // Pre-process
      if( count($actions) > 0 ) {
        try {
          foreach( $actions as $action ) {
            // get next id
            if( null === $nextid || $action->action_id <= $nextid ) {
              $nextid = $action->action_id - 1;
            }
            // get first id
            if( null === $firstid || $action->action_id > $firstid ) {
              $firstid = $action->action_id;
            }

            // skip disabled actions
            if( !$action->getTypeInfo() || !$action->getTypeInfo()->enabled )
              continue;
            // skip items with missing items
            if( !$action->getSubject() || !$action->getSubject()->getIdentity() )
              continue;
            if( !$action->getObject() || !$action->getObject()->getIdentity() )
              continue;

            // skip the hide actions and content
            if( !empty($hideItems) ) {
              if( isset($hideItems[$action->getType()]) && in_array($action->getIdentity(), $hideItems[$action->getType()]) ) {
                continue;
              }
              if( !$action->getTypeInfo()->is_object_thumb && isset($hideItems[$action->getSubject()->getType()]) && in_array($action->getSubject()->getIdentity(), $hideItems[$action->getSubject()->getType()]) ) {
                continue;
              }
              if( ($action->getTypeInfo()->is_object_thumb || $action->getObject()->getType() == 'user' ) && isset($hideItems[$action->getObject()->getType()]) && in_array($action->getObject()->getIdentity(), $hideItems[$action->getObject()->getType()]) ) {
                continue;
              }
            }

            $actionObject = $action->getObject();

            if( in_array($action->action_id, $hideTargetedActionsIds) && $viewer->getIdentity() !== $action->getOwner()->getIdentity() )
              continue;

            // track/remove users who do too much (but only in the main feed)
            if( empty($subject) ) {
              $actionSubject = $action->getSubject();
              if( isset($action->getTypeInfo()->is_object_thumb) && $action->getTypeInfo()->is_object_thumb ) {
                $itemAction = $action->getObject();
              } else {
                $itemAction = $action->getSubject();
              }
              if( !isset($itemActionCounts[$itemAction->getGuid()]) ) {
                $itemActionCounts[$itemAction->getGuid()] = 1;
              } else if( $itemActionCounts[$itemAction->getGuid()] >= $itemActionLimit ) {
                continue;
              } else {
                $itemActionCounts[$itemAction->getGuid()] ++;
              }
            }
            if( $this->isBlocked($action) ) {
              continue;
            }
            // remove duplicate friend requests
            if( $action->type == 'friends' ) {
              $id = $action->subject_id . '_' . $action->object_id;
              $rev_id = $action->object_id . '_' . $action->subject_id;
              if( in_array($id, $friendRequests) || in_array($rev_id, $friendRequests) ) {
                continue;
              } else {
                $friendRequests[] = $id;
                $friendRequests[] = $rev_id;
              }
            }
            $total_guid = '';
            /* Start Working group feed. */
            if( isset($action->getTypeInfo()->is_grouped) && !empty($action->getTypeInfo()->is_grouped) ) {
              if( $action->type == 'friends' ) {
                $object_guid = $action->getSubject()->getGuid();
                $total_guid = $action->type . '_' . $object_guid;

                if( !isset($grouped_actions[$total_guid]) ) {
                  $grouped_actions[$total_guid] = array();
                }
                $grouped_actions[$total_guid][] = $action->getObject();
              } elseif( $action && $action->type == 'tagged' ) {
                foreach( $action->getAttachments() as $attachment ) {
                  $object_guid = $attachment->item->getGuid();
                  $Subject_guid = $action->getSubject()->getGuid();
                  $total_guid = $action->type . '_' . $object_guid . '_' . $Subject_guid;
                }
                if( !isset($grouped_actions[$total_guid]) ) {
                  $grouped_actions[$total_guid] = array();
                }
                $grouped_actions[$total_guid][$action->getObject()->getGuid()] = $action->getObject();
              } else {
                $object_guid = $action->getObject()->getGuid();
                $total_guid = $action->type . '_' . $object_guid;

                if( !isset($grouped_actions[$total_guid]) ) {
                  $grouped_actions[$total_guid] = array();
                }
                $grouped_actions[$total_guid][] = $action->getSubject();
              }

              if( count($grouped_actions[$total_guid]) > 1 ) {
                continue;
              }
            }
            /* End Working group feed. */

            // remove items with disabled module attachments
            try {
              $attachments = $action->getAttachments();
            } catch( Exception $e ) {
              // if a module is disabled, getAttachments() will throw an Engine_Api_Exception; catch and continue
              continue;
            }
            $similarFeedType = $action->type . '_' . $actionObject->getGuid();
            if( $action->canMakeSimilar() ) {
              $similarActivities[$similarFeedType][] = $action;
            }
            if( isset($similarActivities[$similarFeedType]) && count($similarActivities[$similarFeedType]) > 1 ) {
              continue;
            }
            // add to list
            if( count($activity) < $length ) {
              $activity[] = $action;
              if( count($activity) == $length ) {
                $actions = array();
                break;
              }
            }
          }
        } catch( Exception $ex ) {
          continue;
        }
      }
      // Set next tmp max_id
      if( $nextid ) {
        $tmpConfig['max_id'] = $nextid;
      }
      if( !empty($tmpConfig['action_id']) ) {
        $actions = array();
      }
    } while( count($activity) < $length && $selectCount <= 5 && !$endOfFeed );

//    if( !empty($pinActivity) ) {
//      $activity = array_merge($pinActivity, $activity);
//    }
    if( count($activity) < $length || count($activity) <= 0 ) {
      $endOfFeed = true;
    }


    $this->view->groupedFeeds = $grouped_actions;
    $this->view->activity = $activity;
    $this->view->activityCount = count($activity);
    $this->view->nextid = $nextid;
    $this->view->firstid = $firstid;
    $this->view->endOfFeed = $endOfFeed;
    $this->view->similarActivities = $similarActivities;

    if( Engine_Api::_()->hasModuleBootstrap('sitehashtag') ) {
      $this->view->hashtag = $this->getHashtagNames($activity);
    }


    // Get some other info
    if( !empty($subject) ) {
      $this->view->subjectGuid = $subject->getGuid(false);
    }


    $this->view->addHelperPath(APPLICATION_PATH . '/application/modules/Advancedactivity/View/Helper', 'Advancedactivity_View_Helper');
    if( ($getListViewValue + $getPublishValue) != $getComposerValue ) {
      Engine_Api::_()->getApi('settings', 'core')->setSetting('advancedactivity.post.active', $composerLimit);
    }
  }

  protected function getHashtagNames($activity)
  {

    $hashTagMapTable = Engine_Api::_()->getDbtable('tagmaps', 'sitehashtag');
    $hashtagNames = array();
    foreach( $activity as $action ) {
      $cacheId = __CLASS__ . 'hashtag_' . $action['action_id'];
      if( $this->_cacheApi->test($cacheId) ) {
        $hashtagNames[$action['action_id']] = $this->_cacheApi->load($cacheId);
      } else {
        $hashtags = Engine_Api::_()->sitehashtag()->getHashTags($action->body);
        $hashtagName = array();
        $hashtagmaps = $hashTagMapTable->getActionTagMaps($action->action_id);
        foreach( $hashtagmaps as $hashtagmap ) {
          $tag = Engine_Api::_()->getItem('sitehashtag_tag', $hashtagmap->tag_id);
          if( $tag && !in_array($tag->text, $hashtags[0]) ) {
            $hashtagName[] = $tag->text;
          }
        }
        $hashtagNames[$action['action_id']] = $hashtagName;
        $this->_cacheApi->save($hashtagName, $cacheId);
      }
    }

    return $hashtagNames;
  }

  private function isBlocked($action)
  {

    if( empty($this->_blockedUserIds) ) {
      return false;
    }
    $actionObjectOwner = $action->getObject()->getOwner();
    $actionSubjectOwner = $action->getSubject()->getOwner();
    if( $actionSubjectOwner instanceof User_Model_User && in_array($actionSubjectOwner->getIdentity(), $this->_blockedUserIds) ) {
      return true;
    }
    if( $actionObjectOwner instanceof User_Model_User && in_array($actionObjectOwner->getIdentity(), $this->_blockedUserIds) ) {
      return true;
    }
    return false;
  }

  private function shouldNoRender()
  {
    if( $this->subject ) {
      $this->viewer = Engine_Api::_()->user()->getViewer();
      // Get subject
      $parentSubject = $subject = $this->subject;
      if( $subject->getType() == 'siteevent_event' ) {
        $parentSubject = $subject->getParent();
      }
      if( in_array($subject->getType(), array('sitepage_page', 'sitepageevent_event')) || ($parentSubject->getType() == 'sitepage_page') ) {
        $pageSubject = $subject->getType() == 'sitepageevent_event' ? $subject->getParentPage() : $parentSubject;
        $isManageAdmin = Engine_Api::_()->sitepage()->isManageAdmin($pageSubject, 'view');
        return empty($isManageAdmin);
      }
      if( in_array($subject->getType(), array('sitebusiness_business', 'sitebusinessevent_event')) || ($parentSubject->getType() == 'sitebusiness_business') ) {
        $businessSubject = $subject->getType() != 'sitebusinessevent_event' ? $parentSubject : Engine_Api::_()->getItem('sitebusiness_business', $subject->business_id);
        $isManageAdmin = Engine_Api::_()->sitebusiness()->isManageAdmin($businessSubject, 'view');
        return empty($isManageAdmin);
      }
      if( in_array($subject->getType(), array('sitegroup_group', 'sitegroupevent_event')) || ($parentSubject->getType() == 'sitegroup_group') ) {
        $groupSubject = $subject->getType() != 'sitegroupevent_event' ? $parentSubject : Engine_Api::_()->getItem('sitegroup_group', $subject->group_id);
        $isManageAdmin = Engine_Api::_()->sitegroup()->isManageAdmin($groupSubject, 'view');
        return empty($isManageAdmin);
      }
      if( in_array($subject->getType(), array('sitestore_store')) || ($parentSubject->getType() == 'sitestore_store') ) {
        $isManageAdmin = Engine_Api::_()->sitestore()->isManageAdmin($parentSubject, 'view');
        return empty($isManageAdmin);
      }
      return !$subject->authorization()->isAllowed($this->viewer, 'view') && !$parentSubject->authorization()->isAllowed($this->viewer, 'view');
    }
  }

  private function setSettings()
  {
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $this->view->integrateCommunityAdv = $this->_getParam('integrateCommunityAdv', 1);
    $this->isForCategoryPage = $this->_getParam('isForCategoryPage', false);
    $this->view->homefeed = $request->getParam('homefeed', false);
    $this->view->getUpdate = $request->getParam('getUpdate');
    $this->view->checkUpdate = $request->getParam('checkUpdate');
    $this->view->post_failed = (int) $request->getParam('pf');
    $this->view->viewAllLikes = $request->getParam('viewAllLikes', $request->getParam('show_likes', false));
    if( !$this->view->viewAllLikes ) {
      $this->view->viewAllLikes = $this->_getParam('viewAllLikes');
    }
    $viewAllComments = $request->getParam('viewAllComments', $request->getParam('show_comments', false));
    if( !$viewAllComments ) {
      $viewAllComments = $this->_getParam('viewAllComments');
    }
    $this->view->viewAllComments = $viewAllComments;
    $this->view->tabtype = $this->settings->getSetting('advancedactivity.tabtype', 3);
    $this->view->showPosts = $this->_getParam('showPosts', 1);
    $this->view->search = $this->_getParam('search');
    $this->view->hide = $this->_getParam('hide');
    $this->getElement()->removeDecorator('Title');
    $this->getElement()->removeDecorator('Container');
    $this->view->feedOnly = $request->getParam('feedOnly', false);
    $this->view->onViewPage = $this->_getParam('onViewPage');
    if( !$this->view->feedOnly ) {
      $this->view->feedOnly = $this->_getParam('feedOnly');
    }
    $this->view->module_name = $request->getModuleName();
    $this->view->action_name = $request->getActionName();
    $this->defaultcontentTab = $defaultcontentTab = $request->getParam('actionFilter');
    $this->view->actionFilter = $actionTypeGroup = $this->isForCategoryPage ? $request->getParam('actionFilter', 'all') : $this->_getParam('actionFilter', 'all');
    $this->view->isFromTab = $request->getParam('isFromTab', false);
    if( !$this->subject && $request->getParam('actionFilter') && $this->view->isFromTab && $this->viewer_id && $this->settings->getSetting('advancedactivity.save.filter', 0) ) {
      $contentTabs = Engine_Api::_()->getDbtable('contents', 'advancedactivity')->getContentList(array('content_tab' => 1));
      foreach( $contentTabs as $v ) {
        if( $actionTypeGroup == $v->filter_type ) {
          Engine_Api::_()->getDbtable('userSettings', 'seaocore')->setSetting($this->viewer, "aaf_filter", $actionTypeGroup);
          break;
        }
      }
    }
    $this->view->updateSettings = $this->settings->getSetting('activity.liveupdate');
    $this->view->showHashtags = $this->settings->getSetting('sitehashtag.showHashtags', 1);
    if( Engine_Api::_()->hasModuleBootstrap('sitehashtag') && !empty($this->view->hide) ) {
      $this->view->updateSettings = 0;
    }
    $this->view->curr_url = $request->getRequestUri(); // Return the current URL.
  }

  private function setFilterList()
  {
    if( $this->view->onViewPage ) {
      return;
    }
    $cacheId = 'aaf.feed.filter.list';
    if( $this->_cacheApi->test($cacheId) ) {
      $data = $this->_cacheApi->load($cacheId);
      $this->view->canCreateCategroyList = isset($data['canCreateCategroyList']) ? $data['canCreateCategroyList'] : '';
      $this->view->contentTabMax = isset($data['contentTabMax']) ? $data['contentTabMax'] : '';
      $this->view->enableContentTabs = isset($data['enableContentTabs']) ? $data['enableContentTabs'] : '';
      $this->view->filterTabs = isset($data['filterTabs']) ? $data['filterTabs'] : '';
      $this->view->contentTabs = isset($data['contentTabs']) ? $data['contentTabs'] : '';
      $this->view->actionFilter = isset($data['actionFilter']) ? $data['actionFilter'] : '';
      $this->view->canCreateCustomList = isset($data['canCreateCustomList']) ? $data['canCreateCustomList'] : '';
    } else {
      $this->view->contentTabs = $contentTabs = Engine_Api::_()->getDbtable('contents', 'advancedactivity')->getContentList(array('content_tab' => 1));
      $this->view->contentTabMax = $this->settings->getSetting('advancedactivity.defaultvisible', 7);
      $countContentTabs = @count($contentTabs);
      $this->view->enableContentTabs = !empty($countContentTabs);
      $filterTabs = array();
      $i = 0;
      $defaultUsercontentTab = $this->viewer_id && $this->settings->getSetting('advancedactivity.save.filter', 0) ? Engine_Api::_()->getDbtable('settings', 'user')->getSetting($this->viewer, "aaf_filter") : '';
      $feedTabs = array('memories' , 'advertise' , 'schedule_post');
      $authorizationApi = Engine_Api::_()->authorization();
      $statusBoxOptions = $this->settings->getSetting('advancedactivity.composer.options', array("withtags", "emotions", "userprivacy", "webcam", "postTarget","schedulePost"));
      foreach( $contentTabs as $value ) {
        if( empty($this->viewer_id) && in_array($value->filter_type, array('membership', 'only_network')) )
          continue;
        if((empty($this->viewer_id) && in_array($value->filter_type, array_merge($feedTabs,array('hidden_post')))) || !empty($this->viewer_id) && in_array($value->filter_type, $feedTabs) && !$authorizationApi->isAllowed('advancedactivity_feed', $this->viewer, 'aaf_' . $value->filter_type . '_enable') ) {
          continue;
        }
        if(in_array($value->filter_type, array('schedule_post')) && !in_array('schedulePost',$statusBoxOptions)){
            continue;  
        }
        $filterTabs[$i]['filter_type'] = $value->filter_type;
        $filterTabs[$i]['tab_title'] = $value->resource_title;
        $filterTabs[$i]['list_id'] = $value->content_id;
        $i++;
        if( empty($this->defaultcontentTab) || $defaultUsercontentTab == $value->filter_type ) {
          $this->defaultcontentTab = $value->filter_type;
          $this->view->actionFilter = $actionTypeGroup = $this->defaultcontentTab;
        }
      }
      $enableNetworkListFilter = $this->settings->getSetting('advancedactivity.networklist.filtering', 0);
      if( $this->viewer_id && $enableNetworkListFilter ) {
        $networkLists = Engine_Api::_()->advancedactivity()->getNetworks($enableNetworkListFilter, $this->viewer);
        $countNetworkLists = count($networkLists);
        if( $countNetworkLists ) {
          if( count($filterTabs) > $this->view->contentTabMax )
            $filterTabs[$i]['filter_type'] = "separator";
          $i++;
          foreach( $networkLists as $value ) {
            $filterTabs[$i]['filter_type'] = "network_list";
            $filterTabs[$i]['tab_title'] = $value->getTitle();
            $filterTabs[$i]['list_id'] = $value->getIdentity();
            $i++;
          }
        }
      }
      $enableFriendListFilter = !empty($this->viewer_id) && $this->settings->getSetting('advancedactivity.friendlist.filtering', 1) && $this->settings->getSetting('user.friends.lists') && (empty($subject) || $this->viewer->isSelf($subject));
      if( $enableFriendListFilter ) {
        $listTable = Engine_Api::_()->getItemTable('user_list');
        $lists = $listTable->fetchAll($listTable->select()->where('owner_id = ?', $this->viewer->getIdentity()));
        $countlistsLists = count($lists);
        if( $countlistsLists ) {
          if( count($filterTabs) > $this->view->contentTabMax )
            $filterTabs[$i]['filter_type'] = "separator";
          $i++;
          foreach( $lists as $value ) {
            $filterTabs[$i]['filter_type'] = "member_list";
            $filterTabs[$i]['tab_title'] = $value->title;
            $filterTabs[$i]['list_id'] = $value->list_id;
            $i++;
          }
        }
      }
      $this->view->canCreateCategroyList = 0;
      $categoryFilter = $this->settings->getSetting('aaf.category.filtering', 1);
      if( $categoryFilter && Engine_Api::_()->hasModuleBootstrap('advancedactivitypost') ) {
        $tableCategories = Engine_Api::_()->getDbtable('categories', 'advancedactivitypost');
        $categoriesList = $tableCategories->getCategories();
        if( count($categoriesList) ) {
          if( count($filterTabs) > $this->view->contentTabMax ) {
            $filterTabs[$i]['filter_type'] = "separator";
            $i++;
          }
          foreach( $categoriesList as $value ) {
            $filterTabs[$i]['filter_type'] = "activity_category";
            $filterTabs[$i]['tab_title'] = $value->getTitle();
            $filterTabs[$i]['list_id'] = $value->category_id;
            $i++;
          }
        }
      }
      $this->view->canCreateCustomList = 0;
      if( $this->viewer_id ) {
        $customTypeLists = Engine_Api::_()->getDbtable('customtypes', 'advancedactivity')->getCustomTypeList(array('enabled' => 1));
        $countCustomTypeLists = count($customTypeLists);
        $customListFiltering =  $this->settings->getSetting('advancedactivity.customlist.filtering', 1);
        $this->view->canCreateCustomList = !empty($countCustomTypeLists) && $customListFiltering;
        if( $this->view->canCreateCustomList ) {
          $customLists = Engine_Api::_()->getDbtable('lists', 'advancedactivity')->getMemberOfList($this->viewer, 'default');
          $countCustomLists = count($customLists);
          if( $countCustomLists ) {
            if( count($filterTabs) > $this->view->contentTabMax ) {
              $filterTabs[$i]['filter_type'] = "separator";
              $i++;
            }
            foreach( $customLists as $value ) {
              $filterTabs[$i]['filter_type'] = "custom_list";
              $filterTabs[$i]['tab_title'] = $value->title;
              $filterTabs[$i]['list_id'] = $value->list_id;
              $i++;
            }
          }
        }
        if( Engine_Api::_()->hasModuleBootstrap('advancedactivitypost') ) {
          $tableCategories = Engine_Api::_()->getDbtable('categories', 'advancedactivitypost');
          $categoriesList = $tableCategories->getCategories();
          if( count($categoriesList) ) {
            $this->view->canCreateCategroyList = $this->settings->getSetting('aaf.categorylist.filtering', 1);
            if( $this->view->canCreateCategroyList ) {
              $customLists = Engine_Api::_()->getDbtable('lists', 'advancedactivity')->getMemberOfList($this->viewer, 'category');
              $countCustomLists = count($customLists);
              if( $countCustomLists ) {
                if( count($filterTabs) > $this->view->contentTabMax ) {
                  $filterTabs[$i]['filter_type'] = "separator";
                  $i++;
                }
                foreach( $customLists as $value ) {
                  $filterTabs[$i]['filter_type'] = "category_list";
                  $filterTabs[$i]['tab_title'] = $value->title;
                  $filterTabs[$i]['list_id'] = $value->list_id;
                  $i++;
                }
              }
            }
          }
        }
      }
      $this->view->filterTabs = $filterTabs;
      $this->_cacheApi->save(
        array(
        'canCreateCategroyList' => $this->view->canCreateCategroyList,
        'contentTabMax' => $this->view->contentTabMax,
        'enableContentTabs' => $this->view->enableContentTabs,
        'filterTabs' => $this->view->filterTabs,
        'contentTabs ' => $this->view->contentTabs,
        'actionFilter' => $this->view->actionFilter,
        'canCreateCustomList' => $this->view->canCreateCustomList,
        ), $cacheId);
    }
  }

}
