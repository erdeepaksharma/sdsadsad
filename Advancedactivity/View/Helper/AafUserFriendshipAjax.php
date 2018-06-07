<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: UserFriendship.php 8835 2011-04-10 05:11:55Z jung $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Advancedactivity_View_Helper_AafUserFriendshipAjax extends Zend_View_Helper_Abstract {

  public function aafUserFriendshipAjax($action, $viewer = null) {
    $user = $action->getSubject();
    if (null === $viewer) {
      $viewer = Engine_Api::_()->user()->getViewer();
    }

    if (!$viewer || !$viewer->getIdentity() || $user->isSelf($viewer) || $user->getType() !== 'user') {
      return '';
    }

    $direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);

    // Get data
    if (!$direction) {
      $row = $user->membership()->getRow($viewer);
    }
    else
      $row = $viewer->membership()->getRow($user);

    // Render
    // Check if friendship is allowed in the network
    $eligible = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.eligible', 2);
    if ($eligible == 0) {
      return '';
    }

    // check admin level setting if you can befriend people in your network
    else if ($eligible == 1) {

      $networkMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
      $networkMembershipName = $networkMembershipTable->info('name');

      $select = new Zend_Db_Select($networkMembershipTable->getAdapter());
      $select
              ->from($networkMembershipName, 'user_id')
              ->join($networkMembershipName, "`{$networkMembershipName}`.`resource_id`=`{$networkMembershipName}_2`.resource_id", null)
              ->where("`{$networkMembershipName}`.user_id = ?", $viewer->getIdentity())
              ->where("`{$networkMembershipName}_2`.user_id = ?", $user->getIdentity())
      ;

      $data = $select->query()->fetch();

      if (empty($data)) {
        return '';
      }
    }

    if (!$direction) {
      // one-way mode
      if (null === $row || null === $row->resource_approved) {
        return "<a id='aaf_addfriend_" . $user->user_id . "' href='javascript:void(0);'
onclick='en4.advancedactivity.addfriend(" . $action->action_id . "," . $user->user_id . ")'>" .
                $this->view->translate('Follow') . "</a>";
      } else if ($row->resource_approved == 0) {
        return "<a id='aaf_addfriend_" . $user->user_id . "' href='javascript:void(0);'
onclick='en4.advancedactivity.cancelfriend(" . $action->action_id . "," . $user->user_id . ")'>" .
                $this->view->translate('Cancel Follow Request') . "</a>";
      }
    } else {
      // two-way mode
      if (null === $row || null === $row->user_approved) {
        return "<a class='icon_friend_add' id='aaf_addfriend_" . $user->user_id . "' href='javascript:void(0);'
onclick='en4.advancedactivity.addfriend(" . $action->action_id . "," . $user->user_id . ")'>" . $this->view->translate('Add Friend') .
                "</a>";
      } else if ($row->user_approved == 0) {
        return "<a class='icon_friend_remove' id='aaf_addfriend_" . $user->user_id . "' href='javascript:void(0);'
onclick='en4.advancedactivity.cancelfriend(" . $action->action_id . "," . $user->user_id . ")'>" .
                $this->view->translate('Cancel Request') . "</a>";
      }
    }

    return '';
  }

}