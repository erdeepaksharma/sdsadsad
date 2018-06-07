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
class Advancedactivity_Api_Core extends Core_Api_Abstract {

    public function getFeelingContent($action) {

        $params = $action->params;
        if( empty($params['feelings']) ) {
            return;
        }
        $title = '';
        $photoUrl = '';
        $feelingtypeItem = Engine_Api::_()->getItem('advancedactivity_feelingtype', $params['feelings']['parent']);
        if(empty($feelingtypeItem)){
            return;
        }
        $feelingItem = null;
        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        if( !empty($feelingtypeItem->type) && !empty($params['feelings']['type']) ) {
          if (Engine_Api::_()->hasItemType($params['feelings']['type']))
          $feelingItem = Engine_Api::_()->getItem($params['feelings']['type'], $params['feelings']['child']);
        } elseif( empty($params['feelings']['type']) && $params['feelings']['child'] != 'custom_feeling' ) {
          $feelingItem = Engine_Api::_()->getItem('advancedactivity_feeling', $params['feelings']['child']);
        }
        if(empty($feelingItem) && $params['feelings']['child'] != 'custom_feeling'){
            return;
        }
        if( $params['feelings']['child'] != 'custom_feeling' ) {
          $title = $feelingItem->getTitle();
          $photoUrl = $feelingItem->getPhotoUrl();
          if( !($feelingItem instanceof Advancedactivity_Model_Feeling) ) {
            $title = '<a href="' . $feelingItem->getHref() . '">' . $title . '</a>';
          }
        } else {
          $title = $params['feelings']['childtitle'];
          $photoUrl = $feelingtypeItem->getPhotoUrl();
        }
        $includeSym = '<span class="seaocore_txt_light aaf_feeling_txt"> ';
        $includeSym .= '<img src="' . $photoUrl . '" /> ' .
        $view->translate($feelingtypeItem->getTitle()) . '  ' . $view->translate($title) . '</span>';

        return $includeSym;
    }

    public function getTagContent($action) {
//    if ($this->isMobile())
//      return;
    $tagContent = $this->getTag($action);
    $count = @count($tagContent);
    if (empty($count))
      return;

    $tagContent->toarray();
    $translate = Zend_Registry::get('Zend_Translate');
    $id = $tagContent['0']->tag_id;
    $type = $tagContent['0']->tag_type;
    $tagger = Engine_Api::_()->getItem($type, $id);

    $includeSym = '<span class="seaocore_txt_light"> &#151; ';
    if (empty($action->body) && $action->type == 'sitetagcheckin_checkin') {
      $includeSym = '';
    }

    $content = $includeSym .' '. $translate->translate('with') . '</span>' . ' '
            . '<a class="sea_add_tooltip_link" '
            . 'href="' . $tagger->getHref() . '" '
            . 'rel="' . $tagger->getType() . ' ' . $tagger->getIdentity() . '" >'
            . $translate->translate($tagger->getTitle())
            . '</a>';
    if ($count == 2) {
      $id = $tagContent['1']->tag_id;
      $type = $tagContent['1']->tag_type;
      $tagger = Engine_Api::_()->getItem($type, $id);
      $content = $content . ' ' . $translate->translate('and') . ' '
              . '<a class="sea_add_tooltip_link" '
              . 'rel="' . $tagger->getType() . ' ' . $tagger->getIdentity() . '" '
              . 'href="' . $tagger->getHref() . '">'
              . $translate->translate($tagger->getTitle())
              . '</a>';
    } else if ($count > 2) {
      $otherFriends = null;
      for ($i = 1; $i < $count; $i++):
        $id = $tagContent[$i]->tag_id;
        $type = $tagContent[$i]->tag_type;
        $tagger = Engine_Api::_()->getItem($type, $id);
        $otherFriends.= $translate->translate($tagger->getTitle()) . "<br />";
      endfor;
      $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
      $URL = $view->url(array('module' => 'advancedactivity', 'controller' => 'feed', 'action' =>
          'view-more-results', 'action_id' => $action->getIdentity()), 'default', true);
      $content = $content . ' ' . $translate->translate('and') . ' '
              . '<span class="aaf_feed_show_tooltip_wrapper">'
              . '<a href="' . $URL . '" class="smoothbox">'
              . ($count - 1) . ' ' . $translate->translate('others')
              . '</a>'
              . '<span class="aaf_feed_show_tooltip" style="margin-left:-8px;">'
              . '<img src="' . $view->layout()->staticBaseUrl . 'application/modules/Advancedactivity/externals/images/tooltip_arrow.png" />'
              . $otherFriends
              . '</span>'
              . '</span>';
    }
    return $content.=".";
  }

  public function getTag($resource) {
    $table = Engine_Api::_()->getDbtable('TagMaps', 'core');
    $select = $table->select()
                    ->where('resource_type = ?',$resource->getType())
                    ->where('resource_id = ?', $resource->getIdentity())
                    ->order('creation_date 	DESC')
    ;
    return $table->fetchAll($select);
  }

  public function hasFeedTag($resource) {
    $enableSettings = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.composer.options', array("withtags", "emotions", "userprivacy"));

    if (!in_array("withtags", $enableSettings))
      return false;

    $count = count($this->getTag($resource));

    if (empty($count) && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitetagcheckin') /* && $resource->object_type == 'user' */) {
      $params = (array) $resource->params;
      if (is_array($params) && isset($params['checkin']) && in_array($resource->getTypeInfo()->type, array("sitetagcheckin_post", "sitetagcheckin_post_self", "sitetagcheckin_status", "sitetagcheckin_checkin", "sitetagcheckin_content", "sitetagcheckin_add_to_map", "sitepage_post", "sitepage_post_self", 'sitebusiness_post', 'sitebusiness_post_self', "sitegroup_post", "sitegroup_post_self", "sitestore_post", "sitestore_post_self", 'sitetagcheckin_lct_add_to_map', 'siteevent_post', 'siteevent_post_parent'))) {
        $count = 1;
      }
    }

    return $count > 0 ? true : false;
  }

  public function hasMemberTagged($action, $user) {
    $actionTag = new Engine_ProxyObject($action, Engine_Api::_()->getDbtable('tags', 'core'));
    $tagMap = $actionTag->getTagMap($user);
    return empty($tagMap) ? false : true;
  }

  /* public function getTaggedFeedIds($member) {
    $table = Engine_Api::_()->getDbtable('TagMaps', 'core');

    return $table->select()
    ->from($table->info('name'), 'resource_id')
    ->where('resource_type = ?', 'activity_action')
    ->where('tag_type = ?', $member->getType())
    ->where('tag_id = ?', $member->getIdentity())
    ->order('creation_date 	DESC')
    ->query()
    ->fetchAll(Zend_Db::FETCH_COLUMN);
    } */

  public function editContentPrivacy($item, $user, $auth_view = null) {
    $search = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.post.searchable', 0);
    $type = null;
    switch ($auth_view) {
      case 'everyone':
        $auth_view = "everyone";
        break;
      case 'networks':
        $auth_view = "owner_network";
        $type = '_network';
        break;
      case 'friends':
        $auth_view = 'owner_member';
        $type = '_friend';
        break;
      case 'onlyme':
        $auth_view = 'owner';
        $type = '_onlyme';
        break;
    }
    if (empty($auth_view))
      $auth_view = "everyone";

    // Work For Album Plugin Start
    if ($item->getType() == 'album_photo') {
      $parent = $item->getParent();
      if ($auth_view != "everyone") {
        if (!empty($parent->search) && $parent->count() <= 1) {
          $parent->search = $search;
          $parent->photo_id = 0;
          $parent->save();
        }
        $type = 'wall' . $type;
        $album = $this->getSpecialAlbum($user, $type, $auth_view);
        if (isset($item->album_id))
          $item->album_id = $album->album_id;
        else
          $item->collection_id = $album->album_id;
        $item->save();
      } else if ($auth_view == "everyone" && empty($parent->search) && $parent->count() <= 1) {
        $parent->search = 1;
        $parent->save();
      }
    }
    // Work For Album Plugin End
    // Work For Music Plugin Start
    if ($item->getType() == 'music_playlist_song') {
      $parent = $item->getParent();
      if ($auth_view != "everyone") {
        if (!empty($parent->search) && count($parent->getSongs()) <= 1) {
          $parent->search = $search;
          $parent->save();
        }
        $type = 'wall' . $type;
        $playlist = $this->getSpecialPlaylist($user, $type, $auth_view);
        $item->playlist_id = $playlist->playlist_id;
        $item->save();
      } else if ($auth_view == "everyone" && empty($parent->search) && count($parent->getSongs()) <= 1) {
        $parent->search = 1;
        $parent->save();
      }
    }
    // Work For Music Plugin End

    $auth = Engine_Api::_()->authorization()->context;
    $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
    $viewMax = array_search($auth_view, $roles);
    foreach ($roles as $i => $role) {
      $auth->setAllowed($item, $role, 'view', ($i <= $viewMax));
    }
    // change search
    if ($auth_view != "everyone" && isset($item->search)) {
      $item->search = $search;
      $item->save();
    }
  }

  public function getSpecialAlbum(User_Model_User $user, $type, $auth_view) {
    if (!in_array($type, array('wall_friend', 'wall_network', 'wall_onlyme'))) {
      throw new Album_Model_Exception('Unknown special album type');
    }
    $table = Engine_Api::_()->getDbtable('albums', 'album');
    $select = $table->select()
            ->where('owner_type = ?', $user->getType())
            ->where('owner_id = ?', $user->getIdentity())
            ->where('type = ?', $type)
            ->order('album_id ASC')
            ->limit(1);

    $album = $table->fetchRow($select);

    // Create wall photos album if it doesn't exist yet
    if (null === $album) {
      $translate = Zend_Registry::get('Zend_Translate');
      $album = $table->createRow();
      $album->owner_type = 'user';
      $album->owner_id = $user->getIdentity();
      $album->title = $translate->_(ucfirst(str_replace("_", " ", $type)) . ' Photos');
      $album->type = $type;
      $album->search = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.post.searchable', 0) && Engine_Api::_()->getApi('settings', 'core')->getSetting('album.searchable', 0);
      $album->save();

      // Authorizations
      $auth = Engine_Api::_()->authorization()->context;
      $roles = array('owner', 'owner_member', 'owner_member_member', 'owner_network', 'registered', 'everyone');
      $viewMax = array_search($auth_view, $roles);
      foreach ($roles as $i => $role) {
        $auth->setAllowed($album, $role, 'view', ($i <= $viewMax));
        $auth->setAllowed($album, $role, 'comment', ($i <= $viewMax));
      }
    }

    return $album;
  }

  public function getSpecialPlaylist(User_Model_User $user, $type, $auth_view) {
    $allowedTypes = array('wall_friend', 'wall_network', 'wall_onlyme');
    if (!in_array($type, $allowedTypes)) {
      throw new Music_Model_Exception('Unknown special playlist type');
    }
    //$typeIndex = array_search($type, $allowedTypes);
    $playlistTable = Engine_Api::_()->getDbTable('playlists', 'music');
    $select = $playlistTable->select()
            ->where('owner_type = ?', $user->getType())
            ->where('owner_id = ?', $user->getIdentity())
            ->where('special = ?', $type)
            ->order('playlist_id ASC')
            ->limit(1);

    $playlist = $playlistTable->fetchRow($select);

    // Create if it doesn't exist yet
    if (null === $playlist) {
      $translate = Zend_Registry::get('Zend_Translate');

      $playlist = $playlistTable->createRow();
      $playlist->owner_type = 'user';
      $playlist->owner_id = $user->getIdentity();
      $playlist->special = $type;
      $playlist->title = $translate->_(ucfirst(str_replace("_", " ", $type)) . ' Playlist');
      $playlist->search = Engine_Api::_()->getApi('settings', 'core')->getSetting('advancedactivity.post.searchable', 0);
      $playlist->save();

      // Authorizations
      $auth = Engine_Api::_()->authorization()->context;
      $auth->setAllowed($playlist, 'everyone', 'view', true);
      $auth->setAllowed($playlist, 'everyone', 'comment', true);
    }

    return $playlist;
  }

  public function getTwitterDescription($Tweet_description) {

    $chars = preg_split('/ /', $Tweet_description, -1, PREG_SPLIT_OFFSET_CAPTURE);
    $Tweet_description = '';
    foreach ($chars as $char) :
      $pos = explode('@', $char[0]);
      if (empty($pos[0]) && !empty($pos[1])) :
        $chars = preg_match_all('/[a-zA-Z0-9_]/', $pos[1], $matches);
        $string_destweet = implode($matches[0]);
        if (!empty($string_destweet))
          $pos_temp = explode($string_destweet, $pos[1]);
        if (empty($pos_temp[1])) {
          $pos_temp[1] = ' ';
        }
        $Tweet_description = $Tweet_description . '<a href="https://twitter.com/' . $string_destweet . '" target="_blank">@' . $string_destweet . '</a>' . $pos_temp[1];
      else:
        $Tweet_description = $Tweet_description . $char[0] . ' ';

      endif;
    endforeach;
    return $Tweet_description;
  }

  public function getMemberBelongFriendList($params = array()) {
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    if (empty($viewer_id))
      return null;

    $listTable = Engine_Api::_()->getItemTable('user_list');
    $listTableName = $listTable->info('name');

    $listItemTable = Engine_Api::_()->getItemTable('user_list_item');
    $listItemTableName = $listItemTable->info('name');
    $select = $listItemTable->select()
            ->setIntegrityCheck(false)
            ->from($listItemTableName, "$listItemTableName.list_id")
            ->join($listTableName, "$listTableName.list_id = $listItemTableName.list_id", null)
            ->where('child_id = ?', $viewer_id);
    if (isset($params['owner_ids']) && !empty($params['owner_ids']))
      $select->where('owner_id  IN(?)', (array) $params['owner_ids']);
    return $select->query()
                    ->fetchAll(Zend_Db::FETCH_COLUMN);
  }

  public function getListFriendIds($listId) {
    $listTable = Engine_Api::_()->getItemTable('user_list');
    $listTableName = $listTable->info('name');

    $listItemTable = Engine_Api::_()->getItemTable('user_list_item');
    $listItemTableName = $listItemTable->info('name');
    return $listItemTable->select()
                    ->setIntegrityCheck(false)
                    ->from($listItemTableName, "$listItemTableName.child_id")
                    ->join($listTableName, "$listTableName.list_id = $listItemTableName.list_id", null)
                    ->where($listTableName . '.list_id = ?', $listId)
                    ->query()
                    ->fetchAll(Zend_Db::FETCH_COLUMN);
  }

  public function getListBaseContent($type, $params = array()) {
    $list = array();
    if (!isset($params['list_id']) || empty($params['list_id']))
      return;

    if ($type == 'member_list') {
      $list['member_list']['value'] = $listFirendIds = $this->getListFriendIds($params['list_id']);
      $list['member_list']['list_ids'] = !empty($listFirendIds) ? $this->getMemberBelongFriendList(array("owner_ids" => $listFirendIds)) : 0;
    } else if ($type == 'custom_list') {
      $custom_list = Engine_Api::_()->getItem('advancedactivity_list', $params['list_id']);
      if ($custom_list->count()) {
        $list = $custom_list->getListItems();
      }
    } else if ($type == 'category_list') {
      $custom_list = Engine_Api::_()->getItem('advancedactivity_list', $params['list_id']);
      if ($custom_list->count() && $custom_list->type == 'category') {
        $listChilds = $custom_list->getListItems();
        foreach ($listChilds as $child) {
          if ($child->child_type == 'advancedactivity_category')
            $list['categories_id'][] = $child->child_id;
        }
      }
    }
    return $list;
  }

  public function getSubModules($startingFrom = null) {
    if (empty($startingFrom))
      return;
    $moduleTable = Engine_Api::_()->getDbtable('modules', 'core');
    $select = $moduleTable->select()
            ->from($moduleTable->info('name'), "name")
            ->where('name LIKE ?', $startingFrom . '%');

    if ($startingFrom == 'sitestore')
      $select->where('name <> ?', $startingFrom . 'product');
    return $select
                    ->query()
                    ->fetchAll(Zend_Db::FETCH_COLUMN);
  }

  public function getCustomBlock($limit = null) {
    $viewer = Engine_Api::_()->user()->getViewer();
    $customBlockIds = array();

    $customBlockObj = Engine_Api::_()->getDbtable('customblocks', 'advancedactivity')->getCustomBlock();

    foreach ($customBlockObj as $customBlock) {
      if (!empty($customBlock['limitation'])) {
        if ($customBlock['limitation'] == 1) {
          // Number of Friend.
          if (!empty($customBlock['limitation_value']) && ($customBlock['limitation_value'] < $viewer->member_count)) {
            continue;
          }
        } else if (!empty($customBlock['limitation_value']) && $customBlock['limitation'] == 2) {
          // Number of days since signup.
          $startDateTimestamp = strtotime(date('Y-m-d'));
          $viewerDateTimestamp = strtotime($viewer->creation_date);
          $lastDate = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $viewerDateTimestamp), date("d", $viewerDateTimestamp) + $customBlock['limitation_value'], date("Y", $viewerDateTimestamp)));
          $lastDateTimestamp = strtotime($lastDate);
          if ($lastDateTimestamp < $startDateTimestamp) {
            continue;
          }
        }
      }
      $customBlockIds[] = $customBlock['customblock_id'];

      if (!empty($limit)) {
        $getTempLimit = @COUNT($customBlockIds);
        if ($getTempLimit >= $limit) {
          break;
        }
      }
    }
    return $customBlockIds;
  }

  public function isWelcomePageCorrect() {
    $contentTable = Engine_Api::_()->getDbtable('content', 'core');
    $contentTableName = $contentTable->info('name');

    $pageTable = Engine_Api::_()->getDbtable('pages', 'core');
    $pageTableName = $pageTable->info('name');

    $selectPage = $pageTable->select()
            ->from($pageTableName, array('page_id'))
            ->where('name =?', 'advancedactivity_index_welcometab')
            ->limit(1);
    $pageId = $selectPage->query()->fetch();

    if (!empty($pageId)) {
      $select = $contentTable->select()
              ->from($contentTableName, array('name'))
              ->where('page_id =?', $pageId['page_id'])
              ->where('type =?', 'container')
              ->limit(10);
      $fetch = $select->query()->fetchAll();
      if (!empty($fetch)) {
        foreach ($fetch as $value) {
          $getNameArray[] = $value['name'];
        }

        if (in_array('left', $getNameArray) || in_array('right', $getNameArray) || in_array('top', $getNameArray) || in_array('bottom', $getNameArray)) {
          return false;
        }
      }
    }
    return true;
  }

  // If parameter not pass in the function then it will return the "Page Id" else It will return the widgets info.
  public function getWidgetPlacement($widgetName = null, $pageId = null) {
    $contentTable = Engine_Api::_()->getDbtable('content', 'core');
    $contentTableName = $contentTable->info('name');

    if (empty($pageId)) {
      $pageTable = Engine_Api::_()->getDbtable('pages', 'core');
      $pageTableName = $pageTable->info('name');

      $selectPage = $pageTable->select()
              ->from($pageTableName, array('page_id'))
              ->where('name =?', 'advancedactivity_index_welcometab')
              ->limit(1);
      $pageId = $selectPage->query()->fetch();
    }

    if (!empty($pageId) && !empty($widgetName)) {
      $select = $contentTable->select()
              ->from($contentTableName, array('content_id'))
              ->where('page_id =?', $pageId['page_id'])
              ->where('name =?', $widgetName)
              ->limit(1);
      $fetch = $select->query()->fetch();
      if (!empty($fetch)) {
        return $fetch;
      }
    }
    return $pageId;
  }

  public function getWidgetSettings($widgetName = null) {
    $viewer = Engine_Api::_()->user()->getViewer();
    global $welcomeTab_post;
    $widgetSettings = false;
    if (empty($welcomeTab_post)) {
      return false;
    }

    switch ($widgetName) {
      case 'advancedactivity.search-for-people':
        $welcomeTabFriendLimit = Engine_Api::_()->getApi('settings', 'core')->getSetting('welcome.search.people', 30);
        $viewerFriendLimit = $viewer->member_count;
        if (!empty($welcomeTabFriendLimit) && ($viewerFriendLimit <= $welcomeTabFriendLimit)) {
          return true;
        }
        break;

      case 'advancedactivity.profile-photo':
        // If member does not have any profile photo.
        $welcomeTabProfilePhoto = Engine_Api::_()->getApi('settings', 'core')->getSetting('welcome.profile.photo', 1);
        if (!empty($welcomeTabProfilePhoto) && empty($viewer->photo_id)) {
          return true;
        }
        break;

      case 'advancedactivity.custom-block':
        $getCustomBlockArray = Engine_Api::_()->advancedactivity()->getCustomBlock(1);
        $getCount = @COUNT($getCustomBlockArray);
        if (!empty($getCount)) {
          return true;
        }
        break;

      case 'suggestion-invites':
        $welcomeTabFriendLimit = Engine_Api::_()->getApi('settings', 'core')->getSetting('welcome.invite.friend.limit', 30);
        $viewerFriendLimit = $viewer->member_count;
        if (!empty($welcomeTabFriendLimit) && ($viewerFriendLimit <= $welcomeTabFriendLimit)) {
          return true;
        }

        break;

      case 'suggestion.suggestion-friend':
        $welcomeTabFriendLimit = Engine_Api::_()->getApi('settings', 'core')->getSetting('welcome.pymk.limit', 30);
        $viewerFriendLimit = $viewer->member_count;
        if (!empty($welcomeTabFriendLimit) && ($viewerFriendLimit <= $welcomeTabFriendLimit)) {
          return true;
        }
        break;

      case 'suggestion.explore-friend':
        $welcomeSuggestion = Engine_Api::_()->getApi('settings', 'core')->getSetting('welcome.suggestion', 50);
        $startDateTimestamp = strtotime(date('Y-m-d'));
        $viewerDateTimestamp = strtotime($viewer->creation_date);
        $lastDate = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $viewerDateTimestamp), date("d", $viewerDateTimestamp) + $welcomeSuggestion, date("Y", $viewerDateTimestamp)));
        $lastDateTimestamp = strtotime($lastDate);
        if ($lastDateTimestamp > $startDateTimestamp) {
          return true;
        }
        break;

      case 'sitelike.welcomemix-like':
        $welcomeTabFriendLimit = Engine_Api::_()->getApi('settings', 'core')->getSetting('welcomeTab.isLike', 5);
        $viewerFriendLimit = $viewer->member_count;
        if (!empty($welcomeTabFriendLimit) && ($viewerFriendLimit <= $welcomeTabFriendLimit)) {
          return true;
        }
        break;

      default:

        break;

        return $widgetSettings;
    }
  }

  public function getCustomBlockSettings($getWidgetsName) {
    // IF user upload the profile photo from the "Welcome Tab" then widget should be show whenever, I will not hard refresh page. Widget should be show when i am selecting "Thumb Nel" of change the "Image".
    if (!empty($_POST) && array_key_exists('coordinates', $_POST)) {
      if (empty($getWidgetsName)) {
        return true;
      }
      if (!empty($getWidgetsName) && in_array('advancedactivity.profile-photo', $getWidgetsName)) {
        return true;
      }
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    if (empty($viewer_id)) {
      return;
    }

    if (empty($getWidgetsName)) {
      $getWidgetsName = array('advancedactivity.search-for-people', 'suggestion-invites', 'advancedactivity.profile-photo', 'suggestion.suggestion-friend', 'advancedactivity.custom-block', 'suggestion.explore-friend', 'sitelike.welcomemix-like');
    }

    $pageShowFlag = false;
    $getPageId = $this->getWidgetPlacement();
    if (!empty($getPageId)) {
      foreach ($getWidgetsName as $widgetName) {
        // Get "Content Id" from the 
        $isWidgetsPlace = $this->getWidgetPlacement($widgetName, $getPageId);
        if (!empty($isWidgetsPlace)) {
          $getWidgetsSettings = $this->getWidgetSettings($widgetName);
          if (!empty($getWidgetsSettings)) {
            $pageShowFlag = true;
            break;
          }
        }
      }
    }
    return $pageShowFlag;
  }

  public function customBlockAuth($object) {
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();
    if (empty($viewer_id)) {
      return;
    }
    $is_valid = false;

    // Check Networks
    $tableNetwork = Engine_Api::_()->getDbTable('membership', 'network');
    $tableNetworkName = $tableNetwork->info('name');
    $selectId = $tableNetwork->select()
            ->from($tableNetworkName, 'resource_id')
            ->where('user_id = ?', $viewer_id);
    $networkIdArray = $tableNetwork->fetchAll($selectId)->toArray();
    if (!empty($networkIdArray)) {
      foreach ($networkIdArray as $key => $image_ids) {
        $id = strval($image_ids['resource_id']);
        $network_id = '"' . $id . '"';
        if (strstr($object->networks, $network_id)) {
          $is_valid = true;
          break;
        }
      }
    }

    // Check Levels
    if (!empty($viewer_id)) {
      $level_id = '"' . $viewer->level_id . '"';
      if (strstr($object->levels, $level_id)) {
        $is_valid = true;
      }
    }

    // Check Limitation
    if ($object->limitation == 1) {
      // Number of Friend.
      if (!empty($object->limitation_value) && ($object->limitation_value < $viewer->member_count)) {
        $is_valid = false;
      }
    } else if (!empty($object->limitation_value) && $object->limitation == 2) {
      // Number of days since signup.
      $startDateTimestamp = strtotime(date('Y-m-d'));
      $viewerDateTimestamp = strtotime($viewer->creation_date);
      $lastDate = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m', $viewerDateTimestamp), date("d", $viewerDateTimestamp) + $object->limitation_value, date("Y", $viewerDateTimestamp)));
      $lastDateTimestamp = strtotime($lastDate);
      if ($lastDateTimestamp < $startDateTimestamp) {
        $is_valid = false;
      }
    }

    return $is_valid;
  }

  public function getURLString($message) {
    if (empty($message))
      return;
    $message_temparray = preg_split("/[\s,]+/", $message);
    foreach ($message_temparray as $key => $value) :
      if ((stripos($value, "http://") === 0 || stripos($value, "https://") === 0 || stripos($value, "WWW") === 0) && ((stristr($value, "http://") != false) || (stristr($value, "https://") != false) || (stristr($value, "WWW") != false))):
        if (stripos($value, "www.") === 0 && stristr($value, "WWW.") !== false) {
          $URL_String = '<a href="http://' . $value . '" target="_blank" >' . $value . '</a>';
        } else
          $URL_String = '<a href="' . $value . '" target="_blank" >' . $value . '</a>';
        $message = str_replace($value, $URL_String, $message);
      endif;
    endforeach;

    return $message;
  }

  public function isBaseOnContentOwner(User_Model_User $viewer, Core_Model_Item_Abstract $item) {
    $type_setting = "aaf_" . $item->getType() . '_content_feed';
    $settingsCoreApi = Engine_Api::_()->getApi('settings', 'core');
    if ($item->getType() == 'sitepage_page') {
      if ($settingsCoreApi->sitepage_feed_type && $item->isOwner($viewer)) {
        return true;
      }
    } elseif ($item->getType() == 'sitebusiness_business') {
      if ($settingsCoreApi->sitebusiness_feed_type && $item->isOwner($viewer)) {
        return true;
      }
    } elseif ($item->getType() == 'sitegroup_group') {
      if ($settingsCoreApi->sitegroup_feed_type && $item->isOwner($viewer)) {
        return true;
      }
    } elseif ($item->getType() == 'sitestore_store') {
      if ($settingsCoreApi->sitestore_feed_type && $item->isOwner($viewer)) {
        return true;
      }
    } elseif ($item->getType() == 'siteevent_event') {

      $settingsCoreApi = Engine_Api::_()->getApi('settings', 'core');
      $parent = $item->getParent();
      $name = 'siteevent_event_leader_owner_' . $parent->getType();
      if ($settingsCoreApi->$name && $parent->isOwner($viewer))
        return true;
    } else if ($settingsCoreApi->$type_setting && $item->isOwner($viewer)) {
      return true;
    }

    return false;
  }

  /**
   * Plugin which return the error, if Siteadmin not using correct version for the plugin.
   *
   */
  public function isModulesSupport() {
    $modArray = array(
        'sitepage' => '4.2.0',
        'sitelike' => '4.2.0',
        'sitealbum' => '4.2.0',
        'suggestion' => '4.2.0',
        'peopleyoumayknow' => '4.2.0',
        'poke' => '4.2.0',
        'list' => '4.2.0',
        'recipe' => '4.2.0',
        'birthday' => '4.2.0'
    );
    $finalModules = array();
    foreach ($modArray as $key => $value) {
      $isModEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled($key);
      if (!empty($isModEnabled)) {
        $getModVersion = Engine_Api::_()->getDbtable('modules', 'core')->getModule($key);
        $isModSupport = $this->checkVersion($getModVersion->version, $value);
        if (empty($isModSupport)) {
          $finalModules[] = $getModVersion->title;
        }
      }
    }
    return $finalModules;
  }

  public function checkVersion($databaseVersion, $checkDependancyVersion) {
    $f = $databaseVersion;
    $s = $checkDependancyVersion;
    if (strcasecmp($f, $s) == 0)
      return -1;

    $fArr = explode(".", $f);
    $sArr = explode('.', $s);
    if (count($fArr) <= count($sArr))
      $count = count($fArr);
    else
      $count = count($sArr);

    for ($i = 0; $i < $count; $i++) {
      $fValue = $fArr[$i];
      $sValue = $sArr[$i];
      if (is_numeric($fValue) && is_numeric($sValue)) {
        if ($fValue > $sValue)
          return 1;
        elseif ($fValue < $sValue)
          return 0;
        else {
          if (($i + 1) == $count) {
            return -1;
          } else
            continue;
        }
      }
      elseif (is_string($fValue) && is_numeric($sValue)) {
        $fsArr = explode("p", $fValue);

        if ($fsArr[0] > $sValue)
          return 1;
        elseif ($fsArr[0] < $sValue)
          return 0;
        else {
          return 1;
        }
      } elseif (is_numeric($fValue) && is_string($sValue)) {
        $ssArr = explode("p", $sValue);

        if ($fValue > $ssArr[0])
          return 1;
        elseif ($fValue < $ssArr[0])
          return 0;
        else {
          return 0;
        }
      } elseif (is_string($fValue) && is_string($sValue)) {
        $fsArr = explode("p", $fValue);
        $ssArr = explode("p", $sValue);
        if ($fsArr[0] > $ssArr[0])
          return 1;
        elseif ($fsArr[0] < $ssArr[0])
          return 0;
        else {
          if ($fsArr[1] > $ssArr[1])
            return 1;
          elseif ($fsArr[1] < $ssArr[1])
            return 0;
          else {
            return -1;
          }
        }
      }
    }
  }

  public function isMobile() {
    $mobileEnable = false;
    $request = Zend_Controller_Front::getInstance()->getRequest();
    // Code for Mobile Compatibilty Plugins. We are not excuting the our plugin code in case of mode='mobile' or mode === 'touch'.
    $session = new Zend_Session_Namespace('standard-mobile-mode');
    if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
      // Reference from "Detect mobile browser (smartphone)" and URL : http://www.serveradminblog.com/2011/01/detect-mobile-browser-smartphone/
      $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
      if
      (
              preg_match('/imageuploader|android|blackberry|compal|fennec|hiptop|iemobile/i', $useragent) ||
              preg_match('/ip(hone|od)|kindle|lge|maemo|midp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\//i', $useragent) ||
              preg_match('/pocket|psp|symbian|treo|up\.(browser|link)|vodafone|windows (ce|phone)|xda/i', $useragent)
      )
        $mobileEnable = true;
      if (preg_match('/imageuploader|android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4)))
        $mobileEnable = true;
    }
    $mobile = false;
    if (!$mobileEnable && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('mobi')) {
      $mobile = $request->getParam("mobile");
      $session = new Zend_Session_Namespace('mobile');

      if ($mobile == "1") {
        $mobileEnable = true;
        $session->mobile = true;
      } elseif ($mobile == "0") {
        $mobileEnable = false;
        $session->mobile = false;
      } else {
        if (isset($session->mobile)) {
          $mobileEnable = (bool) $session->mobile;
        } else {
          // CHECK TO SEE IF MOBILE
          if (Engine_Api::_()->mobi()->isMobile()) {
            $mobileEnable = true;
            $session->mobile = true;
          } else {
            $mobileEnable = false;
            $session->mobile = false;
          }
        }
      }
    }
    return $mobileEnable;
  }

  public function getPageObj($widgetId, $widgetPageName) {

    if (!empty($widgetId) && !empty($widgetPageName)) {
      $contentTable = Engine_Api::_()->getDbtable('content', 'core');
      $contentTableName = $contentTable->info('name');

      $pageTable = Engine_Api::_()->getDbtable('pages', 'core');
      $pageTableName = $pageTable->info('name');

      $select = $contentTable->select()
              ->from($contentTableName, array('page_id'))
              ->where($contentTableName . '.content_id = ?', $widgetId)
              ->limit(1);
      $pageId = $select->query()->fetch();
      if (!empty($pageId) && !empty($pageId['page_id'])) {
        $select = $pageTable->select()
                ->from($pageTableName, array('name'))
                ->where($pageTableName . '.page_id = ?', $pageId['page_id'])
                ->limit(1);
        $pageName = $select->query()->fetch();
        if (!empty($pageName) && !empty($pageName['name'])) {
          if (strstr($pageName['name'], $widgetPageName)) {
            return true;
          }
        }
      }
    }
    return;
  }

  public function getNetworks($type, $viewer) {
    $ids = array();
    $viewer_id = $viewer->getIdentity();
    if (empty($type) || empty($viewer_id)) {
      return;
    }
    if( $type == 1 ) {
      $networkTable = Engine_Api::_()->getDbtable('membership', 'network');
      $ids = $networkTable->getMembershipsOfIds($viewer);
      $count = count($ids);
      if( empty($count) ) {
        return;
      }
      $ids = array_unique($ids);
    }

    $table = Engine_Api::_()->getItemTable('network');
    $select = $table->select()
            //->where('assignment = ?', 0)
            ->order('title ASC');
    if ($type == 1 && !empty($ids)) {
      $select->where('network_id IN(?)', $ids);
    }
    return $table->fetchAll($select);
  }

  public function isNetworkBasePrivacy($string) {
    if (empty($string))
      return;
    $arr = explode(',', $string);
    return preg_match("/network_/", $arr[0]);
  }

  public function isNetworkBasePrivacyIds($string) {
    if (empty($string))
      return;
    $arr = explode(',', $string);
    $ids = array();

    foreach ($arr as $val) {
      $ids[] = str_replace('network_', '', $val);
    }
    return $ids;
  }

  public function getNetworkTitleBasePrivacyIds($string)
  {
    if( empty($string) )
      return;
    $ids = $this->isNetworkBasePrivacyIds($string);
    $table = Engine_Api::_()->getItemTable('network');
    $select = $table->select()
      ->from($table->info('name'), 'title')
      ->order('title ASC');
    $select->where('network_id IN(?)', $ids);
    return $select->query()->fetchAll(Zend_Db::FETCH_COLUMN);
  }

  public function contentTabSettings($filter_type = null, $mode = null, $params = array()) {
    // Process
    if (empty($filter_type))
      return;
    $contentTable = Engine_Api::_()->getItemTable('advancedactivity_content');
    if ($mode == 'add') {
      $values = array(
          'module_name' => $params['module_name'],
          'filter_type' => $filter_type,
          'resource_title' => $params['resource_title'],
          'default' => 1
      );
      $content = $contentTable->fetchRow(array('filter_type = ?' => $filter_type));
      if (empty($content)) {
        $content = $contentTable->createRow();
      }
      $content->setFromArray($values);
      $content->save();
    } elseif ($mode == 'delete') {
      $content = $contentTable->fetchRow(array('filter_type = ?' => $filter_type));
      if ($content) {
        $content->delete();
      }
    }
  }

  public function customListSettings($resource_type = null, $mode = null, $params = array()) {
    // Process
    if (empty($resource_type))
      return;
    $customtypeTable = Engine_Api::_()->getItemTable('advancedactivity_customtype');
    if ($mode == 'add') {
      $values = array(
          'module_name' => $params['module_name'],
          'resource_type' => $resource_type,
          'resource_title' => $params['resource_title'],
          'default' => 1
      );
      $customtype = $customtypeTable->fetchRow(array('resource_type = ?' => $resource_type));
      if (empty($customtype)) {
        $customtype = $customtypeTable->createRow();
      }
      $customtype->setFromArray($values);
      $customtype->save();
    } elseif ($mode == 'delete') {
      $customtype = $customtypeTable->fetchRow(array('resource_type = ?' => $resource_type));
      if (!empty($customtype))
        $customtype->delete();
    }
  }

  public function getComposerMenuList($onlyKey = true) {
    $list = array();
    foreach (Zend_Registry::get('Engine_Manifest') as $data) {
      if (empty($data['composer'])) {
        continue;
      }
      foreach ($data['composer'] as $type => $config) {
        if (!isset($config['script'][1]) || in_array($type, array('facebook', 'twitter','tag', 'advanced_facebook', 'advanced_twitter', 'advanced_linkedin', 'hashtag')))
          continue;
        $key = $type . 'XXX' . $config['script'][1];
        $list[$key] = $onlyKey ? $key : $this->getComposerMenuListTitle($type, $config['script'][1]);
      }
    }
    if (in_array('videoXXXvideo', array_keys($list)) && in_array('videoXXXsitevideo', array_keys($list))) {
      unset($list['videoXXXvideo']);
    }
      
    return $list;
  }

  public function getComposerMenuListTitle($type, $module) {
    $title = array('photo' => 'Add Photo', 'link' => 'Add Link', 'video' => 'Add Video', 'music' => 'Add Music', 'event' => 'Create Event', 'checkin' => 'Share Location', 'hashtag' => 'Hashtag','feeling'=>'Feeling / Activity','sell'=>'Sell Something','sticker'=>'Post a Sticker','banner' => 'Add Banner','feed-tags' => 'Tag Friends','sitecrowdfunding' => 'Start a Project', 'gifPhoto' => 'Add GIF Image');
    $type = str_replace('sitepage', '', $type);
    $type = str_replace('sitegroup', '', $type);
    $type = str_replace('sitestore', '', $type);
    $type = str_replace('sitebusiness', '', $type);

    $moduleInfo = Engine_Api::_()->getDbtable('modules', 'core')->getModule($module);
    $module = $moduleInfo->title;
    if($moduleInfo->name == 'sitecrowdfunding'){
        $type = 'sitecrowdfunding';
    }
    if (isset($title[$type]))
      $text = (!in_array($moduleInfo->name,array('advancedactivity','sitetagcheckin','sitereaction'))) ?  $title[$type]. " ($module) " : $title[$type];
    else {
      $text = $type. " ($module)";
    }
    return $text;
  }

      

  /*
   * Returns array of active Core Ads
   * 
   */

  public function getCoreAddMultioptions($key = 0) {
    $coreAddArray = array();
    $table = Engine_Api::_()->getDbtable('adcampaigns', 'core');
    $select = $table->select()
            ->order('adcampaign_id DESC');
    $ads = $table->fetchAll($select);
    // Build a list of statuses for each ad
    $status = array();
    foreach ($ads as $item) {
      if (!$item->status) {
        continue;
      }
      if (!$item->hasStarted()) {
        continue;
      }
      if ($item->hasExpired()) {
        continue;
      }
      if ($item->hasReachedViewLimit()) {
        continue;
      }
      if ($item->hasReachedClickLimit()) {
        continue;
      }
      if ($item->hasReachedCtrLimit()) {
        continue;
      }
      if (empty($status[$item->adcampaign_id])) {
        $table = Engine_Api::_()->getDbtable('ads', 'core');
        $select = $table->select()->where('ad_campaign = ?', $item->adcampaign_id);
        $paginator = Zend_Paginator::factory($select);
        foreach ($paginator as $add) {
          if ($key)
            $coreAddArray[] = $add->ad_id;
          else
            $coreAddArray[$add->ad_id] = $add->name . ' [ ' . $item->name . ' ] ';
        }
      }
    }
    return $coreAddArray;
  }

  /*
   * Returns array of active Communtity Ads
   * 
   */

  public function getCommunityAddsMultioptions($key = 0) {
    // user table
    $tableUser = Engine_Api::_()->getItemTable('user')->info('name');
    // advertiesment table
    $table = Engine_Api::_()->getDbtable('userads', 'communityad');
    $rName = $table->info('name');
    $campTable = Engine_Api::_()->getDbtable('adcampaigns', 'communityad');
    $campName = $campTable->info('name');

    $packTable = Engine_Api::_()->getDbtable('packages', 'communityad');
    $packName = $packTable->info('name');
    $select = $table->select()
            ->setIntegrityCheck(false)
            ->from($rName, array('*', '(case when SUM(count_view) <> 0 and  SUM(count_click) <>0  then  ROUND((SUM(count_click) / SUM(count_view)), 7)  else 0 end) AS CTR'))
//                ->joinLeft($tableUser, "$rName.owner_id = $tableUser.user_id", 'username')
            ->join($packName, $packName . '.package_id = ' . $rName . '.package_id', array('title as package_name', 'price'))
            ->join($campName, $campName . '.adcampaign_id = ' . $rName . '.campaign_id', array('name'))
            ->group($rName . '.userad_id');

    $values = array(
        'order' => 'userad_id',
        'order_direction' => 'DESC',
    );

    $select->order((!empty($values['order']) ? $values['order'] : 'userad_id' ) . ' ' . (!empty($values['order_direction']) ? $values['order_direction'] : 'DESC' ));

    $paginator = $table->fetchAll($select);
    foreach ($paginator as $add) {
      if ($add->ad_type != 'default')
                continue;  
      if ($add->status == '1' && $add->approved == '1') {
        if ($key == 1)
          $tempArray[] = $add->userad_id;
        else
          $tempArray[$add->userad_id] = $add->cads_title;
      }
    }

    return $tempArray;
  }

  public function getCancelAdvs() {
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    $adClancelIds = array();
    if ($viewer_id) {
      $communityadAdcancel_ids = Engine_Api::_()->communityad()->getAdCancelIds($viewer_id, 'default');
      $adClancelIds = array();
      foreach ($communityadAdcancel_ids as $id)
        $adClancelIds[] = $id->ad_id;
    }

    return $adClancelIds;
  }
 //RETURNS DATE OR TIME DEPEND ON THE $DATETIME PARAMTER DATABASE TO CURRENT USER
    public function dbToUserDateTime($dateparams = array(), $dateTime = 'date') {

        $viewer = Engine_Api::_()->user()->getViewer();
        $timezone = Engine_Api::_()->getApi('settings', 'core')->core_locale_timezone;
        if ($viewer->getIdentity()) {
            $timezone = $viewer->timezone;
        }

        if (isset($dateparams['starttime']))
            $dateparams['starttime'] = strtotime($dateparams['starttime']);
        if (isset($dateparams['endtime']))
            $dateparams['endtime'] = strtotime($dateparams['endtime']);
        $oldTz = date_default_timezone_get();
        date_default_timezone_set($timezone);
        if (isset($dateparams['starttime']))
            $dateparams['starttime'] = date("Y-m-d H:i:s", $dateparams['starttime']);
        if (isset($dateparams['endtime']))
            $dateparams['endtime'] = date("Y-m-d H:i:s", $dateparams['endtime']);
        date_default_timezone_set($oldTz);
        if ($dateTime == 'time') {
            isset($dateparams['starttime']) ? $dateparams['starttime'] = strtotime($dateparams['starttime']) : '';
            isset($dateparams['endtime']) ? $dateparams['endtime'] = strtotime($dateparams['endtime']) : '';
        }
        return $dateparams;
    }
    
      //RETURNS DATE OR TIME DEPEND ON THE $DATETIME PARAMTER DATABASE TO CURRENT USER
    public function userToDbDateTime($dateparams = array(), $dateTime = 'date') {
        $viewer = Engine_Api::_()->user()->getViewer();
        $timezone = Engine_Api::_()->getApi('settings', 'core')->core_locale_timezone;
        if ($viewer->getIdentity()) {
            $timezone = $viewer->timezone;
        }
        $oldTz = date_default_timezone_get();
        date_default_timezone_set($timezone);
        if (isset($dateparams['starttime']))
            $dateparams['starttime'] = strtotime($dateparams['starttime']);
        if (isset($dateparams['endtime']))
            $dateparams['endtime'] = strtotime($dateparams['endtime']);
        date_default_timezone_set($oldTz);
        if (isset($dateparams['starttime']))
            $dateparams['starttime'] = date("Y-m-d H:i:s", $dateparams['starttime']);
        if (isset($dateparams['endtime']))
            $dateparams['endtime'] = date("Y-m-d H:i:s", $dateparams['endtime']);
        return $dateparams;
    }
}
