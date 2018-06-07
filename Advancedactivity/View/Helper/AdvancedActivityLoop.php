<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: AdvancedActivityLoop.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_View_Helper_AdvancedActivityLoop extends Activity_View_Helper_Activity
{
  public function advancedActivityLoop($actions = null, array $data = array())
  {
    if( null == $actions || (!is_array($actions) && !($actions instanceof Zend_Db_Table_Rowset_Abstract)) ) {
      return '';
    }
    if( isset($data['isShort']) && $data['isShort'] ) {
      $data = array_merge($data, array(
        'actions' => $actions,
      ));

      return $this->view->partial(
          '_activityShortText.tpl', 'advancedactivitypost', $data
      );
    } else {

      $coreSettingsApi = Engine_Api::_()->getApi('settings', 'core');
      $allowEdit = 0;
      $allowEditCategory = false;
      $privacyDropdownList = null;
      $form = new Advancedactivity_Form_Comment();
      $viewer = Engine_Api::_()->user()->getViewer();
      $activity_moderate = "";
      $add_saved_feed = false;
      $is_owner = false;
      if( $viewer->getIdentity() ) {
        $activity_moderate = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');
        if( Engine_Api::_()->core()->hasSubject() && $viewer->isSelf(Engine_Api::_()->core()->getSubject()) ) {
          $allowEdit = $coreSettingsApi->getSetting('advancedactivity.post.canedit', 1);
//          if( $allowEdit )
//            $privacyDropdownList = $this->getPrivacyDropdownList();
          if( Engine_Api::_()->hasModuleBootstrap('advancedactivitypost') ) {
            $tableCategories = Engine_Api::_()->getDbtable('categories', 'advancedactivitypost');
            $categoriesList = $tableCategories->getCategories();
            $allowEditCategory = count($categoriesList);
          }
        }
        if( !Engine_Api::_()->core()->hasSubject() ) {
          $add_saved_feed_row = Engine_Api::_()->getDbtable('contents', 'advancedactivity')->getContentList(array('content_tab' => 1, 'filter_type' => 'user_saved'));
          $add_saved_feed = !empty($add_saved_feed_row) ? true : false;
        } else {
          $subject = Engine_Api::_()->core()->getSubject();

          if( $subject->getType() == 'siteevent_event' && ($subject->getParent()->getType() == 'sitepage_page' || $subject->getParent()->getType() == 'sitbusiness_business' || $subject->getParent()->getType() == 'sitegroup_group' || $subject->getParent()->getType() == 'sitestore_store') ) {
            $subject = Engine_Api::_()->getItem($subject->getParent()->getType(), $subject->getParent()->getIdentity());
          }
          switch( $subject->getType() ) {
            case 'user':
              $is_owner = $viewer->isSelf($subject);
              break;
            case 'sitepage_page':
            case 'sitebusiness_business':
            case 'sitegroup_group':
            case 'sitestore_store':
              $is_owner = $subject->isOwner($viewer);
              break;
            case 'sitepageevent_event':
            case 'sitebusinessevent_event':
            case 'sitegroupevent_event':
            case 'sitestorevent_event':
              $is_owner = $viewer->isSelf($subject);
              if( empty($is_owner) ) {
                $is_owner = $subject->getParent()->isOwner($viewer);
              }
              break;
            default :
              $is_owner = $viewer->isSelf($subject->getOwner());
              break;
          }
        }
      }
      $settings = Engine_Api::_()->getApi('settings', 'core');
      $composerOptions = $settings->getSetting('advancedactivity.composer.options', array("emotions", "withtags"));
      $allowReaction = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereaction') && $settings->getSetting('sitereaction.reaction.active', 1);
      $data = array_merge($data, array(
        'actions' => $actions,
        'commentForm' => $form,
        'user_limit' => $coreSettingsApi->getSetting('activity_userlength'),
        'allow_delete' => $coreSettingsApi->getSetting('activity_userdelete'),
        'commentShowBottomPost' => $coreSettingsApi->getSetting('advancedactivity.comment.show.bottom.post', 1),
        'isMobile' => Engine_Api::_()->advancedactivity()->isMobile(),
        'activity_moderate' => $activity_moderate,
        'allowEdit' => $allowEdit,
        'allowEditCategory' => $allowEditCategory,
        'privacyDropdownList' => $privacyDropdownList,
        'allowEmotionsIcon' => in_array("emotions", $composerOptions),
        'allowReaction' => $allowReaction,
        'allowSaveFeed' => $add_saved_feed,
        'is_owner' => $is_owner,
        //'showLargePhoto' => $coreSettingsApi->getSetting('aaf.largephoto.enable', 1)
      ));

      return $this->view->partial(
          '_activityText.tpl', 'advancedactivity', $data
      );
    }
  }

  protected function getPrivacyDropdownList()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $viewer = Engine_Api::_()->user()->getViewer();
    $showPrivacyDropdown = in_array('userprivacy', $settings->getSetting('advancedactivity.composer.options', array("withtags", "emotions", "userprivacy")));
    if( !$showPrivacyDropdown )
      return;

    $availableLabels = array('everyone' => 'Everyone', 'networks' => 'Friends &amp; Networks', 'friends' => 'Friends Only', 'onlyme' => 'Only Me');

    $userFriendListEnable = $settings->getSetting('user.friends.lists');
    if( $userFriendListEnable ) {
      $listTable = Engine_Api::_()->getItemTable('user_list');
      $lists = $listTable->fetchAll($listTable->select()->where('owner_id = ?', $viewer->getIdentity()));
      $countList = count($lists);
      if( $countList > 0 ) {
        $availableLabels[] = "separator";
        foreach( $lists as $list ) {
          $availableLabels[$list->list_id] = $list->title;
        }
      }
    }

    $enableNetworkList = $settings->getSetting('advancedactivity.networklist.privacy', 0);
    if( $enableNetworkList ) {
      $this->view->network_lists = $networkLists = Engine_Api::_()->advancedactivity()->getNetworks($enableNetworkList, $viewer);
      $this->view->enableNetworkList = count($networkLists);

      if( $this->view->enableNetworkList ) {
        $availableLabels[] = "separator";
        foreach( $networkLists as $network ) {
          $availableLabels["network_" . $network->getIdentity()] = $network->getTitle();
        }
      }
    }
    if( $this->view->enableNetworkList > 1 ) {
      $availableLabels[] = "separator";
      $availableLabels["network_custom"] = "Multiple Networks";
    }
    if( $userFriendListEnable ) {
      if( $this->view->enableNetworkList <= 1 )
        $availableLabels[] = "separator";
      $lable = $this->view->enableNetworkList <= 1 ? "Custom" : "Multiple Friend Lists";
      if( $countList == 1 )
        $availableLabels["custom_1"] = $lable;
      else if( $countList > 1 )
        $availableLabels["custom_2"] = $lable;
      else
        $availableLabels["custom_0"] = $lable;
    }

    return $availableLabels;
  }

}
