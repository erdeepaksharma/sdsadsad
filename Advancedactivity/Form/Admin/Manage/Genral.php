<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Advancedactivity
 * @copyright  Copyright 2011-2012 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Genral.php 6590 2012-26-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Advancedactivity_Form_Admin_Manage_Genral extends Engine_Form {

  public function init() {

    $this
            ->setTitle('General Lists Settings')
            ->setDescription('These are the general settings for Activity Feeds Lists on your community.');

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->addElement('Select', 'advancedactivity_defaultvisible', array(
        'label' => "Default Visible Items",
        'description' => 'Select the number of items that should be visible by default for lists based filtering on member home activity feeds. (You can choose the content types that are important for your website to be visible by default. The items beyond this count will appear in a "More" dropdown. To choose the sequence of items, visit the Content Lists tab.)',
        'multiOptions' => array(
            '0' => '0',
            '1' => "1",
            '2' => '2',
            "3" => "3",
            "4" => "4",
            "5" => "5",
            "6" => "6",
            "7" => "7",
            "8" => "8",
            "9" => "9"
        ),
        'value' => $settings->getSetting('advancedactivity.defaultvisible', 7),
        'disableTranslator' => 'true'
    ));

    $this->addElement('Radio', 'advancedactivity_save_filter', array(
       'label' => 'Save Last Feeds Filtering',
        'description' => 'Do you want to save the last feeds filtering choice selected by members on their Member Home Pages such that when they visit their Member Home Pages, the feeds will automatically be filtered based on last filtreing selection? [Note: Only Content Lists filtering choices will be saved.]',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('advancedactivity.save.filter', 0),
    ));
    
    // VALUE FOR ENABLE /DISABLE Proximity Search
    $this->addElement('Radio', 'advancedactivity_customlist_filtering', array(
        'label' => 'Custom Lists Filtering',
        'description' => 'Enable Custom Lists for filtering of activity feeds on member home page. (If enabled, users will be able to create their custom lists from various content types & friends, to filter activity feeds on them. This would allow users to easily view updates from entities that are important to them and which they are interested in. Users could create different lists containing different entities according to their choice and interests. To administer content types available for custom lists, visit the Custom Lists tab.)',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('advancedactivity.customlist.filtering', 1),
    ));

    // VALUE FOR ENABLE /DISABLE Proximity Search IN Kilometer
    if ($settings->getSetting('user.friends.lists')) {
      $this->addElement('Radio', 'advancedactivity_friendlist_filtering', array(
          'label' => 'Friend Lists Filtering',
          'description' => 'Enable users to filter activity feeds on member home page over Friend Lists. (Friend Lists are lists in which users organize their friends from the Friends section of their profiles. Users can use this to see updates of their close friends, colleagues, family, class-mates, etc.)',
          'multiOptions' => array(
              '1' => 'Yes',
              '0' => 'No'
          ),
          'value' => $settings->getSetting('advancedactivity.friendlist.filtering', 1),
      ));
    } else {
      $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
      $link = $view->url(array("module" => "user", "controller" => "settings", "action" => "friends"), "admin_default", true);
      $this->addElement('Dummy', 'advancedactivity_friendlist_filtering_dummy', array(
          'description' => sprintf("<div class='tip'> <span>You can also enable users to filter activity feeds based on their Friend Lists like family, close friends, co-workers, etc. However, you are not able to see the option for activating that because you have disabled Friend Lists from Friendship Settings. To enable it, %s.</span></div>", "<a href='" . $link . "'>go here</a>"),
      ));
      $this->getElement('advancedactivity_friendlist_filtering_dummy')->getDecorator('Description')->setOptions(array('placement', 'APPEND', 'escape' => false));
    }

    $content = $settings->getSetting('activity.content', 'everyone');
    $tip = null;
    if ($content == 'friends') {
      $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
      $link = $view->url(array("module" => "activity", "controller" => "settings"), "admin_default", true);
      $tip = sprintf("<div class='tip'> <span>Note: In Activity Feed Settings, you have chosen ‘My Friends’ for the ‘Feed Content’ field. Please %s, either to choose ‘All Members’ or ‘My Friends & Networks’ for this field.</span></div>", "<a href='" . $link . "'>click here</a>");
    }
    // VALUE FOR ENABLE /DISABLE Proximity Search
    $this->addElement('Radio', 'advancedactivity_networklist_filtering', array(
        'label' => 'Networks Based Filtering',
        'description' => 'Enable users to filter activity feeds on member home page over Networks. (Users can use this to see updates from selected Networks.)' . $tip,
        'multiOptions' => array(
            2 => 'Yes, enable users to filter feeds from all networks.',
            1 => 'Yes, enable users to filter feeds only based on networks joined by them.',
            0 => 'No'
        ),
        'value' => $settings->getSetting('advancedactivity.networklist.filtering', 0),
    ));
    $this->getElement('advancedactivity_networklist_filtering')->getDecorator('Description')->setOptions(array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    if (Engine_Api::_()->hasModuleBootstrap('advancedactivitypost')) {
      $this->addElement('Radio', 'aaf_category_filtering', array(
          'label' => 'Category Based Filtering',
          'description' => 'Enable users to filter activity feeds on member home page based on Categroy. (Users can use this to see updates from selected Category.)',
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'value' => $settings->getSetting('aaf.category.filtering', 1),
      ));

      $this->addElement('Radio', 'aaf_categorylist_filtering', array(
          'label' => 'Custom Categroy Lists Filtering',
          'description' => 'Enable Custom Categroy Lists for filtering of activity feeds on member home page. (If enabled, users will be able to create their custom lists from categroies, to filter activity feeds on them. )',
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'value' => $settings->getSetting('aaf.categorylist.filtering', 1),
      ));
    }
    // Element: submit
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }

}

?>