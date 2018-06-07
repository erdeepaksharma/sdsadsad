<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2016-2017 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: MemberLevel.php 2017-02-08 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Form_Admin_MemberLevel extends Authorization_Form_Admin_Level_Abstract
{ 
  public function init()
  { 
    parent::init();
    $this
    ->setTitle('Member Level Settings')
    ->setDescription("These settings are applied on a per member level basis. Start by selecting the member level you want to modify, then adjust the settings for that level below.");

    $this->addElement('Radio', 'aaf_pinunpin_enable', array(
            'label' => 'Allow Pin the Post',
            'description' => 'Do you want to allow your users to pin / unpin the post on content profile page?',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => 1
        ));
     $this->addElement('Radio', 'aaf_schedule_post_enable', array(
          'label' => 'Allow Scheduled Post',
          'description' => 'Do you want to allow your users to schedule their post for future?',
          'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
          ),
          'value' => 1,
        ));
        
        $this->addElement('Radio', 'aaf_targeted_post_enable', array(
          'label' => 'Allow Targeted Post',
          'description' => 'Do you want to allow your users to specify their post for a particular gender or for users belonging to a certain range of age?',
          'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
          ),
          'value' => 1,
        ));
        $this->addElement('Radio', 'aaf_memories_enable', array(
            'label' => 'Allow ‘On This Day’ Post',
            'description' => 'Do you want to display user’s memory of ‘On this Day’, which he / she posted on same date in past?',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
           'value' => 1,
        ));
        $this->addElement('Radio', 'aaf_add_feeling_enable', array(
            'label' => 'Allow to Add Feelings',
            'description' => 'Do you want to allow your users to add feeling in their status updates?',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
           'value' => 1,
        ));
        $this->addElement('Radio', 'aaf_advertise_enable', array(
            'label' => 'Allow Product Advertisement',
            'description' => 'Do you want to allow your users to advertise product from status box? [Note: This product will only list down in feeds, there is no separate page where it will get redirected. Users can search these product from ‘Buy Sell’ filter in status box.]',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
           'value' => 1,
        ));
        $this->addElement('Radio', 'aaf_greeting_enable', array(
            'label' => 'Allow Greeting / Announcement',
            'description' => 'Do you want to display greeting / announcement to the users of this member level?',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
           'value' => 1,
        ));
        $this->addElement('Radio', 'aaf_feed_banner_enable', array(
            'label' => 'Allow Feed Banner',
            'description' => 'Do you want to allow your users to add banner in feed from status box?',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
           'value' => 1,
        ));
    
  }
  
}