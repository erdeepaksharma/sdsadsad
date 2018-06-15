<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Global.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_Settings_Global extends Engine_Form {

  public function init() {

    $this->setTitle('Global Settings')
            ->setDescription('These settings affect all members in your community.')
            ->setName('review_global');

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->addElement('Text', 'sitereview_lsettings', array(
        'label' => 'Enter License key For Multiple Listing Types Plugin Core (Reviews & Ratings Plugin)',
        'description' => "Please enter your license key that was provided to you when you purchased this plugin. If you do not know your license key, please contact the Support Team of SocialEngineAddOns from the Support section of your Account Area.(Key Format: XXXXXX-XXXXXX-XXXXXX )",
        'value' => $settings->getSetting('sitereview.lsettings'),
    ));

    $isSitereviewlistingtypeEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype');
    if( !empty($isSitereviewlistingtypeEnabled) ) {
      $this->addElement('Text', 'sitereviewlistingtype_lsettings', array(
          'label' => 'Enter License key For Reviews & Ratings - Multiple Listing Types Extension',
          'description' => "Please enter your license key that was provided to you when you purchased this plugin. If you do not know your license key, please contact the Support Team of SocialEngineAddOns from the Support section of your Account Area.(Key Format: XXXXXX-XXXXXX-XXXXXX )",
          'value' => $settings->getSetting('sitereviewlistingtype.lsettings'),
      ));
    }
    
    $isSitereviewpaidlistingEnabled = Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting');
    if( !empty($isSitereviewpaidlistingEnabled) ) {
      $this->addElement('Text', 'sitereviewpaidlisting_lsettings', array(
          'label' => 'Enter License key For Multiple Listing Types - Paid Listings Extension',
          'description' => "Please enter your license key that was provided to you when you purchased this plugin. If you do not know your license key, please contact the Support Team of SocialEngineAddOns from the Support section of your Account Area.(Key Format: XXXXXX-XXXXXX-XXXXXX )",
          'value' => $settings->getSetting('sitereviewpaidlisting.lsettings'),
      ));
    }

    if (APPLICATION_ENV == 'production') {
      $this->addElement('Checkbox', 'environment_mode', array(
          'label' => 'Your community is currently in "Production Mode". We recommend that you momentarily switch your site to "Development Mode" so that the CSS of this plugin renders fine as soon as the plugin is installed. After completely installing this plugin and visiting few pages of your site, you may again change the System Mode back to "Production Mode" from the Admin Panel Home. (In Production Mode, caching prevents CSS of new plugins to be rendered immediately after installation.)',
          'description' => 'System Mode',
          'value' => 1,
      ));
    } else {
      $this->addElement('Hidden', 'environment_mode', array('order' => 990, 'value' => 0));
    }

    $this->addElement('Button', 'submit_lsetting', array(
        'label' => 'Activate Your Plugin Now',
        'type' => 'submit',
        'ignore' => true
    ));

    $this->addElement('Radio', 'sitereview_network', array(
        'label' => 'Browse by Networks',
        'description' => "Do you want to show listings according to viewer's network if he has selected any? (If set to no, all the listings will be shown.)",
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'onclick' => 'showDefaultNetwork(this.value)',
        'value' => $settings->getSetting('sitereview.network', 0),
    ));

    $this->addElement('Radio', 'sitereview_default_show', array(
        'label' => 'Set Only My Networks as Default in search',
        'description' => 'Do you want to set "Only My Networks" option as default for Show field in the search form widget? (This widget appears on the listings browse and home pages, and enables users to search and filter listings.)',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'onclick' => 'showDefaultNetworkType(this.value)',
        'value' => $settings->getSetting('sitereview.default.show', 0),
    ));

    $this->addElement('Radio', 'sitereview_networks_type', array(
        'label' => 'Network selection for Listings',
        'description' => "You have chosen that viewers should only see Listings of their network(s). How should a Listing's network(s) be decided?",
        'multiOptions' => array(
            0 => "Listing Owner's network(s) [If selected, only members belonging to listing owner's network(s) will see the Listings.]",
            1 => "Selected Networks [If selected, listing owner will be able to choose the networks of which members will be able to see their Listing.]"
        ),
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.networks.type', 0),
    ));

    $this->addElement('Radio', 'sitereview_privacybase', array(
        'label' => 'Display of All Listings in Browse pages & Widgets',
        'description' => "With respect to Listings Privacy, do you want all listings to be shown to users on the Browse pages (Browse Listings, Locations, Pinboard, etc.) and in widgets, or do you want only those listings to appear in these for which the user has view privacy. If you choose ‘Yes’, then whenever a user clicks on an listing for which he does not have view privacy, then he will be shown a “Private Page” message. If you choose ‘No’, then strict privacy checks will be applied on these Browse pages & widgets which might slightly affect their loading speeds (To minimize such delays, we are using caching based displays.).",
        'multiOptions' => array(
            0 => 'Yes, show all listings irrespective of their view privacy.',
            1 => 'No, only show those listings for which user has view privacy.'
        ),
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('siteevent.privacybase', 0),
    )); 

    $this->addElement('Radio', 'sitereview_proximity_search_kilometer', array(
        'label' => 'Proximity Search',
        'description' => 'Do you want proximity search to be enabled for listings? (Proximity search will enable users to search for listings within a certain distance from a location. Proximity search will work only if you enable the \'Location Field\' from \'Manage Listing Type\' section of this plugin.)',
        'multiOptions' => array(
            0 => 'Miles',
            1 => 'Kilometers'
        ),
        'value' => $settings->getSetting('sitereview.proximity.search.kilometer', 0),
    ));

    $this->addElement('Text', 'sitereview_map_city', array(
        'label' => 'Centre Location for Map at Listings Home and Browse Listings',
        'description' => 'Enter the location which you want to be shown at centre of the map which is shown on Listings Home and Browse Listings when Map View is chosen to view Listings.(To show the whole world on the map, enter the word "World" below.)',
        'required' => true,
        'value' => $settings->getSetting('sitereview.map.city', "World"),
    ));

    $this->addElement('Select', 'sitereview_map_zoom', array(
        'label' => "Default Zoom Level for Map at Listings Home and Browse Listings",
        'description' => 'Select the default zoom level for the map which is shown on Listings Home and Browse Listings when Map View is chosen to view Listings. (Note that as higher zoom level you will select, the more number of surrounding cities/locations you will be able to see.)',
        'multiOptions' => array(
            '1' => "1",
            "2" => "2",
            "4" => "4",
            "6" => "6",
            "8" => "8",
            "10" => "10",
            "12" => "12",
            "14" => "14",
            "16" => "16"
        ),
        'value' => $settings->getSetting('sitereview.map.zoom', 1),
        'disableTranslator' => 'true'
    ));

    $this->addElement('Radio', 'sitereview_map_sponsored', array(
        'label' => 'Sponsored Listings with a Bouncing Animation',
        'description' => 'Do you want the sponsored listings to be shown with a bouncing animation in the Map?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sitereview.map.sponsored', 1),
    ));
    
    $descriptionDuplicateTitle = 'Allow users to create the listing with the title, which may be same as any of existing listings.';
    if (Engine_Api::_()->hasModuleBootstrap('sitereviewlistingtype')) {
        $descriptionDuplicateTitle = 'Allow users to create the listing with the title, which may be same as any of existing listings. [Note: If you select "No", still user will be able to create listings with the same title in different listing types.]';
    }
    
    $this->addElement('Radio', 'sitereview_duplicatetitle', array(
        'label' => 'Listing Title Duplication',
        'description' => $descriptionDuplicateTitle,
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sitereview.sitereview_duplicatetitle', 1),
    ));    
    
    $this->addElement('Radio', 'sitereview_favourite', array(
        'label' => 'Add to Favourites',
        'description' => 'Allow users to add the listings as their favourites. (Note: If you enable this setting then wishlist functionality will automatically be disabled.)',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sitereview.favourite', 0),
    ));    
    
      $redirect_array = array(
          1 => 'Listing Profile page',
          0 => 'Listing Dashboard',
      );

      $this->addElement('Radio', 'sitereview_create_redirection', array(
              'label' => 'Redirection after Listing Creation',
              'description' => 'Where do you want to redirect Listing Owners after Listing creation?',
              'multiOptions' => $redirect_array,
              'value' => $settings->getSetting('sitereview.create.redirection', 0),
      ));
    
    $this->addElement('Radio', 'sitereview_fs_markers', array(
        'label' => 'Featured, Sponsored and New Markers',
        'description' => 'On Listings Home, Browse Listings and My Listings how do you want a Listing to be indicated as Featured, Sponsored and New ? (Note: Listing having "New" marker will be indicated by labels only.)',
        'multiOptions' => array(
            1 => 'Using Labels (See FAQ for customizing the labels)',
            0 => 'Using Icons',
        ),
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.fs.markers', 1),
    ));

    $this->addElement('Radio', 'sitereview_tinymceditor', array(
        'label' => 'Tinymce Editor',
        'description' => 'Allow TinyMCE editor for discussion message of Listings.',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => $settings->getSetting('sitereview.tinymceditor', 1),
    ));

    $this->addElement('Radio', 'sitereview_editorprofile', array(
        'label' => 'Editor Profile Link',
        'description' => 'Where do you want to redirect users, when they click on Editors’ photo, name and view profile links?',
        'multiOptions' => array(
            1 => 'On Editor Profile',
            0 => 'On Member Profile',
        ),
        'value' => $settings->getSetting('sitereview.editorprofile', 1),
    ));

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $localeObject = Zend_Registry::get('Locale');
    $currencyCode = Engine_Api::_()->getApi('settings', 'core')->getSetting('payment.currency', 'USD');
    $currencyName = Zend_Locale_Data::getContent($localeObject, 'nametocurrency', $currencyCode);
    $this->addElement('Dummy', 'sitereview_currency', array(
        'label' => 'Currency',
        'description' => "<b>" . $currencyName . "</b> <br class='clear' /> <a href='" . $view->url(array('module' => 'payment', 'controller' => 'settings'), 'admin_default', true) . "' target='_blank'>" . Zend_Registry::get('Zend_Translate')->_('edit currency') . "</a>",
    ));
    $this->getElement('sitereview_currency')->getDecorator('Description')->setOptions(array('placement', 'APPEND', 'escape' => false));

    //Custom code start
    $this->addElement('Textarea', 'sitereview_defaultlistingcreate_email', array(
                            'label' => 'Alerted by email',
                            'description' => 'Please enter comma-separated list, or one-email-per-line. Email is sent to the below enter emails when members create new Pages.',
                            'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.defaultlistingcreate.email', Engine_API::_()->seaocore()->getSuperAdminEmailAddress()),
                        ));
    // Custom code end

    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewlistingtype')) {
      $this->addElement('Radio', 'sitereview_showcategories_menu', array(
          'label' => 'Listing Types Navigation Bar',
          'description' => "Do you want to display Listing Types Navigation Bar above the 'Navigation Tabs' widget as configured by you from the Layout Editor? (If you select 'Yes', then all the listings types on your site will be displayed in this bar. Categories of listing types will be displayed on mouseover.)",
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'value' => $settings->getSetting('sitereview.showcategories.menu', 1),
      ));
    }

    $this->addElement('Hidden', 'is_remove_note', array('value' => 0, 'order' => 999));
    
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $field = 'sitereview_code_share';
    $this->addElement('Dummy', "$field", array(
        'label' => 'Social Share Widget Code',
        'description' => "<a class='smoothbox' href='". $view->url(array('module' => 'seaocore', 'controller' => 'settings', 'action' => 'social-share', 'field' => "$field"), 'admin_default', true) ."'>Click here</a> to add your social share code.",
        'ignore' => true,
    ));
    $this->$field->addDecorator('Description', array('placement' => 'PREPEND', 'class' => 'description', 'escape' => false));

    $this->addElement('Button', 'save', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }

}
