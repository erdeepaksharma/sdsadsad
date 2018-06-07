<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: GetContent.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_View_Helper_EditPost extends Zend_View_Helper_Abstract
{

  protected $_composePartials = null;
  protected $_privacyDropdownList = null;

  public function editPost($action, $options = array())
  {
    if( !$action->canEdit() ) {
      return;
    }
    $this->_cacheApi = Engine_Api::_()->getApi('cache', 'advancedactivity');
    $form = new Advancedactivity_Form_EditPost();
    $form->body->setAttrib('id', 'edit-body-' . $action->getIdentity());
    $form->populate($action->toArray());
    return $this->view->partial(
        '_editPost.tpl', 'advancedactivity', array(
        'action' => $action,
        'form' => $form,
        'composePartials' => $this->getComposePartials(),
        'options' => $options,
        'privacyDropdownList' => $this->getPrivacyDropdownList()
        )
    );
  }

  protected function getPrivacyDropdownList()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $viewer = Engine_Api::_()->user()->getViewer();
    $showPrivacyDropdown = in_array('userprivacy', $settings->getSetting('advancedactivity.composer.options', array("withtags", "emotions", "userprivacy")));
    if( !$showPrivacyDropdown )
      return;

    if( $this->_privacyDropdownList !== null ) {
      return $this->_privacyDropdownList;
    }
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

    $this->_privacyDropdownList = $availableLabels;
    return $availableLabels;
  }

  private function getComposePartials()
  {
    if( $this->_composePartials !== null ) {
      return $this->_composePartials;
    }
    $composePartials = array(
      array('_composeTag.tpl', 'advancedactivity')
    );
    $seaocoreVersion = Engine_Api::_()->seaocore()->getCurrentVersion('4.9.0', 'core');
    // Assign the composing values
    if( !empty($seaocoreVersion) ) {
      foreach( Zend_Registry::get('Engine_Manifest') as $data ) {
        if( empty($data['composer']) ) {
          continue;
        }
        foreach( $data['composer'] as $config ) {
          if( empty($config['allowEdit']) ) {
            continue;
          }
          if( !empty($config['auth']) && !Engine_Api::_()->authorization()->isAllowed($config['auth'][0], null, $config['auth'][1]) ) {
            continue;
          }
          $composePartials[] = $config['script'];
        }
      }
    }
    if( Engine_Api::_()->hasModuleBootstrap('sitehashtag') ) {
      $composePartials[] = array('_composerHashtag.tpl', 'sitehashtag');
    }

    $this->_composePartials = $composePartials;
    return $this->_composePartials;
  }

}
