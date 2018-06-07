<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: content.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
$final_array = array(
  array(
    'title' => 'Advanced Activity Feeds',
    'description' => 'Displays the advanced activity feeds on your site. This widget facilitates you to enable any of the 3 available tabs: Welcome, Site Activity Feeds and Twitter Feeds at various widget locations. <br>If placed on Content / Member profile pages, it will display the feeds related to content / member, else overall site activity feeds will show.',
    'category' => 'Advanced Activities',
    'type' => 'widget',
    'name' => 'advancedactivity.home-feeds',
    'defaultParams' => array(
      'title' => 'What\'s New',
      'showFeeds' => 1,
      'advancedactivity_tabs' => array("aaffeed"),
      'statusBoxDesign' => 'activator_buttons'
    ),
    'autoEdit' => true,
    'adminForm' => 'Advancedactivity_Form_Admin_Widget_HomeFeeds',
  ),
  array(
    'title' => 'Welcome: Search for People',
    'description' => 'This is a widget for the Welcome Tab. This block shows to users a search field to search for their friends who might be members of the site. This enables them to easily and quickly add them as friends to grow their network on your site.',
    'category' => 'Advanced Activities',
    'type' => 'widget',
    'name' => 'advancedactivity.search-for-people',
    'defaultParams' => array(
      'title' => '',
    ),
  ),
  array(
    'title' => 'Greeting / Announcement',
    'description' => 'This will show the greetings, based on selected criteria.',
    'category' => 'Advanced Activities',
    'type' => 'widget',
    'autoEdit' => true,
    'name' => 'advancedactivity.greeting',
    'defaultParams' => array(
      'title' => '',
    ),
    'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'greetingType',
                    array(
                        'label' => 'Greeting Type',
                        'multiOptions' => array(
                            'all' => 'All',
                            'userbased' => 'User Based Auto Greeting',
                            'custom' => 'Custom greeting / announcement',
                            ),
                        'value' => 'all',
                    )
                )
            )
         )
  ),
    array(
    'title' => 'On This Day',
    'description' => 'This will show the best feed of the user that was posted on the same day 1 year ago.',
    'category' => 'Advanced Activities',
    'type' => 'widget',
    'name' => 'advancedactivity.on-this-day',
    'defaultParams' => array(
      'title' => '',
    ),
  ),
  array(
    'title' => ('Welcome: Profile Photo Uploading'),
    'description' => ('This is a widget for the Welcome Tab. This block will enable users to easily and quickly upload a profile photo, thus increasing trust on your website.'),
    'category' => ('Advanced Activities'),
    'type' => 'widget',
    'name' => 'advancedactivity.profile-photo',
    'defaultParams' => array(
      'title' => '',
    ),
  ),
  array(
    'title' => ('Welcome: Custom Blocks'),
    'description' => ("This is a widget for the Welcome Tab. You can use custom blocks to show welcome content to users which is different from the already available blocks. For example, you can introduce those features / aspects of your website that form your site's most important core features. To manage content of this widget, please go to the Custom Blocks tab in Welcome Settings of Advanced Activity Feeds plugin."),
    'category' => ('Advanced Activities'),
    'type' => 'widget',
    'name' => 'advancedactivity.custom-block',
    'defaultParams' => array(
      'title' => '',
    ),
  ),
  array(
    'title' => ('Welcome: Welcome Message'),
    'description' => ('This is a widget for the Welcome Tab. This block shows to users a welcome message with their name in it, thus increasing personalization on your website.'),
    'category' => ('Advanced Activities'),
    'type' => 'widget',
    'name' => 'advancedactivity.welcome-message',
    'defaultParams' => array(
      'title' => '',
    ),
  ),
//    array(
//        'title' => ('Activity Post Feed Button Widget'),
//        'description' => ('This is a widget for the show post feed button.'),
//        'category' => ('Advanced Activities'),
//        'type' => 'widget',
//        'name' => 'advancedactivity.post-feed-button',
//        'defaultParams' => array(
//            'title' => '',
//        ),
//        'autoEdit' => true,
//        'adminForm' => array(
//            'elements' => array(
//                array(
//                    "Textarea",
//                    "description",
//                    array(
//                        'label' => 'Description',
//                    )
//                ),
//            )
//        )
//    ),
);

return $final_array;
?>
