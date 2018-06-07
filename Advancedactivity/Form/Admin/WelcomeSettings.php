<?php
 /**
* SocialEngine
*
* @category   Application_Extensions
* @package    Advancedactivity
* @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
* @license    http://www.socialengineaddons.com/license/
* @version    $Id: WelcomeSettings.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
* @author     SocialEngineAddOns
*/

class Advancedactivity_Form_Admin_WelcomeSettings extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->addElement('Radio', 'welcomeTab_is_default', array(
        'label' => 'Default Welcome Tab Display',
        'description' => 'If the Welcome Tab is displayed to a member in Advanced Activity Feeds widget, then should it be the default tab (appear as the opened tab on page load)? (Note: For a position of the Advanced Activity Feeds widget, you can choose whether Welcome Tab should be available in it or not from the Edit Settings popup of the widget.)',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('welcomeTab.is.default', 0),
    ));

    $this->addElement('Radio', 'welcomeTab_isSignup', array(
        'label' => 'For Newly Signed-up Users',
        'description' => 'For newly signed-up users, do you want the Welcome Tab to be shown as the default selected tab on Member Home Page on their first visit to that page? (This can be useful to give new users a quick overview of your website. This setting will only work for the first visit of new user on Member Home Page. Note that for this, you should have placed the Advanced Activity Feeds widget on Member Home Page with the Welcome Tab enabled.)', 
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('welcomeTab.isSignup', 0),
    ));

    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('suggestion') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('peopleyoumayknow')) {
      $this->addElement('Text', 'welcome_invite_friend_limit', array(
          'label' => 'Invite Friends',
          'description' => "Enter the number of friends till which users should be shown the Invite Friends block in the Welcome Tab (This block enables users to invite their contacts to become members of the site and their friends on it, by importing contacts from various services or manually entering email IDs. Those who are already site members get friend request. Users can thus quickly and easily grow their network on your site. Note that for this to be visible, you must place the 'Invite Friends' widget in this page from the Layout Editor. If you do not want this widget to be available in the Welcome Tab, then enter 0 below. This block will no more be visible to users in the Welcome Tab after their number of friends reaches the limit set by you.)",           'value' => $settings->getSetting('welcome.invite.friend.limit', 30),
          'maxlength' => '10',
          'validators' => array(
              array('Int', true),
          // array('GreaterThan', true, array(0)),
          ),
      ));
    } else {
      $suggestionLink1 = '<a
href="http://www.socialengineaddons.com/socialengine-suggestions-recommendations-plugin"
target="blank">Suggestions / Recommendations / People you may know & Inviter</a>';
$peopleLink1 = '<a
href="http://www.socialengineaddons.com/socialengine-people-you-may-know-friend-suggestions-inviter"
target="blank">People you may know / Friend Suggestions & Inviter</a>';
      $suggestionLink = sprintf('The Invite Friends block in the Welcome Tab requires either the "%s" or the "%s" plugin installed or enabled on your site. This block enables users to invite their contacts to become members of the site and their friends on it, by importing contacts from various services or manually entering email IDs. Those who are already site members get friend request. Users can thus quickly and easily grow their network on your site.', $suggestionLink1, $peopleLink1);

      $this->addElement('Dummy', 'welcome_invite_friend_limit', array(
          'label' => 'Invite Friends',
          'description' => $suggestionLink,
          'ignore' => true
      ));
      $this->welcome_invite_friend_limit->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    }


    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('suggestion') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('peopleyoumayknow')) {
      $this->addElement('Text', 'welcome_pymk_limit', array(
          'label' => 'People You May Know',
          'description' => 'Enter the number of friends till which users should be shown the People You May Know block in the Welcome Tab (This block shows to users the people they may know and enables them to easily and quickly add them as friends to grow their network on your site. Note that for this to be visible, you must place the "People you may know" widget in this page from the Layout Editor. If you do not want this widget to be available in the Welcome Tab, then enter 0 below. This block will no more be visible to users in the Welcome Tab after their number of friends reaches the limit set by you.).', 
          'value' => $settings->getSetting('welcome.pymk.limit', 30),
          'maxlength' => '10',
          'validators' => array(
              array('Int', true),
          // array('GreaterThan', true, array(0)),
          ),
      ));

      $this->addElement('Text', 'welcome_dis_pymk_limit', array(
          'label' => 'People You May Know Limit',
          'description' => 'How many suggestions do you want to display in the People You May Know block in the Welcome Tab? (Note: For this to be visible, please place the "Welcome: Friends Suggestions" widget in this Welcome page from the Layout Editor.)',
          'value' => $settings->getSetting('welcome.dis.pymk.limit', 20),
          'maxlength' => '10',
          'validators' => array(
              array('Int', true),
          // array('GreaterThan', true, array(0)),
          ),
      ));
    } else {
    $suggestionlink = '<a href="http://www.socialengineaddons.com/socialengine-suggestions-recommendations-plugin" target="blank">Suggestions / Recommendations / People you may know & Inviter</a>';
$peoplelink = '<a href="http://www.socialengineaddons.com/socialengine-people-you-may-know-friend-suggestions-inviter" target="blank">People you may know / Friend Suggestions & Inviter</a>';
      $suggestionLink = sprintf('The People You May Know block in the Welcome Tab requires either the "%s" or the "%s" plugin installed or enabled on your site. This block shows to users the people they may know and enables them to easily and quickly add them as friends to grow their network on your site.', $suggestionlink, $peoplelink);

      $this->addElement('Dummy', 'welcome_pymk_limit', array(
          'label' => 'People You May Know',
          'description' => $suggestionLink,
          'ignore' => true
      ));
      $this->welcome_pymk_limit->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    }


    $this->addElement('Text', 'welcome_search_people', array(
        'label' => 'Search for People',
        'description' => 'Enter the number of friends till which users should be shown the Search for People block in the Welcome Tab (This block shows to users a search field to search for their friends who might be members of the site. This enables them to easily and quickly add them as friends to grow their network on your site. Note that for this to be visible, you must place the "Welcome: Search for People" widget in this page from the Layout Editor. If you do not want this widget to be available in the Welcome Tab, then enter 0 below. This block will no more be visible to users in the Welcome Tab after their number of friends reaches the limit set by you.).',
        'value' => $settings->getSetting('welcome.search.people', 30),
        'maxlength' => '10',
        'validators' => array(
            array('Int', true),
        // array('GreaterThan', true, array(0)),
        ),
    ));


    $this->addElement('Radio', 'welcome_profile_photo', array(
        'label' => 'Profile Photo',
        'description' => 'Do you want the Profile Photo block to be available in the Welcome Tab for users who have not uploaded a profile photo (This block will enable users to easily and quickly upload a profile photo, thus increasing trust on your website. Note that for this to be visible, you must place the "Welcome: Profile Photo Uploading" widget in this page from the Layout Editor.)?',         'multiOptions' => array(
            1 => 'Yes',
            0 => 'No',
        ),
        'value' => $settings->getSetting('welcome.profile.photo', 1),
    ));


    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('suggestion')) {
      $this->addElement('Text', 'welcome_suggestion', array(
          'label' => 'Suggestions',
          'description' => 'Enter the number of days after signup till which users should be shown the Suggestions block in the Welcome Tab (This block shows to users suggestions of various content types of your site, thus enabling them to explore content and motivating them to generate their own content on your site. Note that for this to be visible, you must place the "Explore Suggestions" widget in this page from the Layout Editor. If you do not want this widget to be available in the Welcome Tab, then enter 0 below. This block will no more be visible to users in the Welcome Tab after their number of days after signup reaches the limit set by you. You can set the number of items to be visible in this from the Edit Settings for this widget.).',
          'value' => $settings->getSetting('welcome.suggestion', 50),
          'maxlength' => '10',
          'validators' => array(
              array('Int', true),
          // array('GreaterThan', true, array(0)),
          ),
      ));
    } else {
    $a = '<a href="http://www.socialengineaddons.com/socialengine-suggestions-recommendations-plugin"
target="blank">Suggestions / Recommendations / People you may know & Inviter</a>';
      $suggestionLink = sprintf('The Suggestions block in the Welcome Tab requires the "%s" plugin installed or enabled on your site. This block shows to users suggestions of various content types of your site, thus enabling them to explore content and motivating them to generate their own content on your site.', $a);

      $this->addElement('Dummy', 'welcome_suggestion', array(
          'label' => 'Suggestions',
          'description' => $suggestionLink,
          'ignore' => true
      ));
      $this->welcome_suggestion->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    }


    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitelike')) {
      $this->addElement('Text', 'welcomeTab_isLike', array(
          'label' => 'Most Liked Items (Popular Content)',
          'description' => 'Enter the number of days after signup till which users should be shown the Popular Content block in the Welcome Tab (This block shows to users the most Liked items of your site, thus enabling them to explore quality content on your site. Note that for this to be visible, you must place the "Welcome: Most Liked Items" widget in this page from the Layout Editor. If you do not want this widget to be available in the Welcome Tab, then enter 0 below. This block will no more be visible to users in the Welcome Tab after their number of days after signup reaches the limit set by you.).',
          'value' => $settings->getSetting('welcomeTab.isLike', 50),
          'maxlength' => '10',
          'validators' => array(
              array('Int', true),
          // array('GreaterThan', true, array(0)),
          ),
      ));

      $this->addElement('Text', 'welcome_like_limit', array(
          'label' => 'Most Liked Items Limit',
          'description' => 'How many likes do you want to display in the most liked items block in the Welcome Tab? (Note: For this to be visible, please place the "Most Liked Items" widget in this Welcome page from the Layout Editor.)',
          'value' => $settings->getSetting('welcome.like.limit', 20),
          'maxlength' => '10',
          'validators' => array(
              array('Int', true),
          // array('GreaterThan', true, array(0)),
          ),
      ));
    }else {
    $b = '<a href="http://www.socialengineaddons.com/socialengine-likes-plugin-and-widgets" target="blank">Likes Plugin and Widgets</a>';
      $likeLink = sprintf('The Popular Content block in the Welcome Tab requires the FREE "%s" plugin installed and enabled on your site. This block shows to users the most Liked items of your site, thus enabling them to explore quality content on your site.', $b);

      $this->addElement('Dummy', 'welcomeTab_isLike', array(
          'label' => 'Most Liked Items (Popular Content)',
          'description' => $likeLink,
          'ignore' => true
      ));
      $this->welcomeTab_isLike->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    }

    $this->addElement('Radio', 'welcomeTab_isOtherWid', array(
        'label' => 'Allow Other Widgets',
        'description' => 'Do you want other widgets to also be displayed in the Welcome Tab? (You can place the desired widgets in this Welcome Tab page from the Layout Editor. Please note that if you enable this setting, then you should be sure to place the desired extra widgets in Welcome Tab, than the ones mentioned above.)', 
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('welcomeTab.isOtherWid', 0),
    ));


    // Submit Button
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }

}

?>