<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Level.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_Settings_Level extends Authorization_Form_Admin_Level_Abstract {

  public function init() {

    parent::init();

    $this->setTitle('General - Member Level Settings')
            ->setDescription("These settings are applied on a per member level basis. Start by selecting the member level you want to modify, then adjust the settings for that level below.");

    $this->addElement('Radio', "wishlist", array(
        'label' => 'Allow Viewing of Wishlists?',
        'description' => 'Do you want to let members view wishlists?',
        'multiOptions' => array(
            2 => 'Yes, allow members to view all wishlists, even private ones.',
            1 => 'Yes, allow viewing of wishlists.',
            0 => 'No, do not allow wishlists to be viewed.',
        ),
        'value' => ( $this->isModerator() ? 2 : 1 ),
    ));

    if (!$this->isModerator()) {
      unset($this->wishlist->options[2]);
    }

    if (!$this->isPublic()) {

      $this->addElement('MultiCheckbox', "auth_wishlist", array(
          'label' => 'Wishlists View Privacy',
          'description' => 'Do you want to let members view wishlists? This is useful if you want members to be able to view wishlists, but only certain levels to be able to view wishlists.',
          'multiOptions' => array(
              'everyone' => 'Everyone',
              'registered' => 'All Registered Members',
              'owner_network' => 'Friends and Networks',
              'owner_member_member' => 'Friends of Friends',
              'owner_member' => 'Friends Only',
              'owner' => 'Just Me'
          )
      ));
    }
  }

}