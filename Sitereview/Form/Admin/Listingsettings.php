<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Listingsettings.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_Listingsettings extends Engine_Form {

  public function init() {

    //GET DECORATORS
    $this->loadDefaultDecorators();

    $this->setTitle('Listing Type Settings')
            ->setDescription('Below, you will be able to configure and customize your listing based on various parameters like Allowing Reviews on Listings, Writing Overviews for Listings, Price Information, and a lot more.')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
            ->setAttrib('name', 'sitereviews_create');

    $this->getDecorator('Description')->setOption('escape', false);

    $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', 1);

    $this->addElement('Text', 'title_singular', array(
        'label' => 'Singular Listing Title',
        'description' => 'Please enter Singular Title for listing. This text will come in places like feeds generated, widgets etc.',
        'allowEmpty' => false,
        'required' => true,
        'validators' => array(
            array('NotEmpty', true),
            // array('Alnum', true),
            array('StringLength', true, array(3, 32)),
            array('Regex', true, array('/^[a-zA-Z0-9-_\s]+$/')),
        ),
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
        //new Engine_Filter_StringLength(array('max' => '32')),
            )));

    $this->addElement('Text', 'title_plural', array(
        'label' => 'Plural Listing Title',
        'description' => 'Please enter Plural Title for listings. This text will come in places like Main Navigation Menu, Listing Main Navigation Menu, widgets etc.',
        'allowEmpty' => false,
        'required' => true,
        'validators' => array(
            array('NotEmpty', true),
            // array('Alnum', true),
            array('StringLength', true, array(3, 32)),
            array('Regex', true, array('/^[a-zA-Z0-9-_\s]+$/')),
        ),
        'filters' => array(
            'StripTags',
            new Engine_Filter_Censor(),
        //new Engine_Filter_StringLength(array('max' => '32')),
            )));

    $this->addElement('Text', 'slug_singular', array(
        'label' => 'Listings URL alternate text for "product"',
        'description' => 'Please enter the text below which you want to display in place of "product" in the URLs of this plugin.',
        'allowEmpty' => false,
        'required' => true,
        'validators' => array(
            array('NotEmpty', true),
            // array('Alnum', true),
            array('StringLength', true, array(3, 16)),
            array('Regex', true, array('/^[a-zA-Z0-9-_]+$/')),
        ),
    ));

    $this->addElement('Text', 'slug_plural', array(
        'label' => 'Listings URL alternate text for "products',
        'description' => 'Please enter the text below which you want to display in place of "products" in the URLs of this plugin.',
        'allowEmpty' => false,
        'required' => true,
        'validators' => array(
            array('NotEmpty', true),
            // array('Alnum', true),
            array('StringLength', true, array(3, 16)),
            array('Regex', true, array('/^[a-zA-Z0-9-_]+$/')),
        ),
    ));

    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('sitereviewpaidlisting')) {
      //VALUE FOR ENABLE/DISABLE PACKAGE
      $this->addElement('Radio', 'package', array(
          'label' => 'Packages',
          'description' => 'Do you want Packages to be activated for this listing type? Packages can vary based on the features available to the listings created under them. If enabled, users will have to select a package in the first step while creating a new listing. Listing owners will be able to change their package later. To manage listing packages, go to Manage Packages section. (Note: If packages are enabled, then feature settings for listings will depend on packages, and member levels based feature settings will be off. If packages are disabled, then feature settings for listings could be configured for member levels.)',
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'onclick' => 'showUiOption(this.value)',
          'value' => 0,
      ));

      $this->addElement('Radio', 'package_view', array(
          'label' => 'Package View',
          'description' => 'Select the view type of packages that will be shown in the first step of listing creation.',
          'multiOptions' => array(
              1 => 'Vertical',
              0 => 'Horizontal'
          ),
          'value' => 0,
      ));

      $this->addElement('Radio', 'package_description', array(
          'label' => 'Allow Package Description',
          'description' => 'Do you want to also display description on Packages page?',
          'multiOptions' => array(
              1 => 'Yes',
              0 => 'No'
          ),
          'value' => 1,
      ));
    }


    $this->addElement('Radio', 'claimlink', array(
        'label' => 'Claim a Listing',
        'description' => 'Do you want users to be able to file claims for listings? (Claims filed by users can be managed from the Manage Claims section.)',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'onclick' => 'showclaim(this.value)',
        'value' => 0,
    ));

    $this->addElement('Radio', 'claim_show_menu', array(
        'label' => 'Claim a Listing link',
        'description' => 'Select the position for the "Claim a Listing" link.',
        'multiOptions' => array(
            2 => 'Show this link on Listings Navigation Menu.',
            1 => 'Show this link on Footer Menu.',
            0 => 'Do not show this link.'
        ),
        'value' => 2,
    ));

    $this->addElement('Radio', 'claim_email', array(
        'label' => 'Notification for Listing Claim',
        'description' => 'Do you want to receive e-mail notification when a member claims a listing?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 1,
    ));

    $this->addElement('Radio', 'allow_apply', array(
        'label' => 'Allow Apply Now',
        'description' => "Do you want to allow Apply Now button for this listing type?",
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No',
        ),
        'onclick' => 'showApplication(this.value)',
        'value' => 0,
    ));

    $this->addElement('Radio', 'show_application', array(
        'label' => 'Manage Applications',
        'description' => 'Do you want to show "Manage Applications" option in dashboard panel? (If enabled listing owners will be able to monitor the applied applications from here.)',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 1,
    ));

    $this->addElement('Radio', 'show_editor', array(
        'label' => 'TinyMCE Editor',
        'description' => "Do you want to allow TinyMCE editor for description of listings?",
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No',
        ),
        'value' => 0,
    ));

    $this->addElement('Radio', 'show_tag', array(
        'label' => 'Allow Tags',
        'description' => 'Do you want to enable listing owners to add tags for their listings?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 1,
    ));
    
    $this->addElement('Radio', 'select_alternatives', array(
        'label' => 'Allow Listing Owners to Add "Best Alternatives"',
        'description' => 'Do you want to allow listing owners to add "Best Alternatives" of their listings? If select "No" only editors and site admin will be able add "Best Alternatives".',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No',
        ),
        'value' => 0,
    ));    
        
    $this->addElement('Radio', 'show_status', array(
        'label' => 'Allow Listing Owners to Choose Status',
        'description' => "Do you want to allow listing owners to choose “Published” / “Save As draft” status of their listings?",
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No',
        ),
        'value' => 1,
    ));

    $this->addElement('Radio', 'show_browse', array(
        'label' => 'Allow to Show Listings on Browse Page',
        'description' => "Do you want to allow listing owners to choose to show their listings Browse page and in various blocks?",
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No',
        ),
        'value' => 1,
    ));

    $this->addElement('Radio', 'reviews', array(
        'label' => 'Allow Reviews',
        'description' => 'Do you want to allow editors and users to write review on listings? (Note: From Member Level Settings, you can choose if visitors should be able to review listings. You can edit other settings for reviews on your site from \'Reviews & Ratings\' section.)',
        'multiOptions' => array(
            3 => 'Yes, allow Editors and Users',
            2 => 'Yes, allow Users only',
            1 => 'Yes, allow Editors only',
            0 => 'No',
        ),
        'value' => 3,
        'onclick' => 'hideOwnerReviews(this.value);'
    ));

    $this->addElement('Radio', 'allow_review', array(
        'label' => 'Allow Only User Ratings',
        'description' => "Do you want to allow users to only rate listings?",
        'multiOptions' => array(
            0 => 'Yes, allow Users to only rate listings.',
            1 => 'No, allow users to review and rate listings.',
        ),
        'value' => 1,
    ));

    $this->addElement('Radio', 'allow_owner_review', array(
        'label' => 'Allow Listing Owners to Review',
        'description' => 'Do you want to allow listing owners to review and rate listings posted by them?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 0,
    ));

    $this->addElement('Radio', 'wishlist', array(
        'label' => 'Enable "Add to Wishlist" Link',
        'description' => 'Do you want to enable "Add to Wishlist" link for listings? (If enabled, then users will be able to add listings to their Wishlists. This settings work as a “Add to Favourites” link if Site Admin allow users to add the listings as their favourites from Global Settings of this plugin.)',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 1,
    ));

    $this->addElement('Radio', 'compare', array(
        'label' => 'Enable Comparison',
        'description' => 'Do you want to enable the comparison of listings? (If enabled, then users will be able to compare the listings.)',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 1,
    ));

    $this->addElement('Radio', "photo_type", array(
        'label' => 'Listing Profile Photo',
        'description' => 'Which photo do you want to make as profile photo of the listing?',
        'multiOptions' => array(
            'listing' => 'Listing’s main photo (Photo uploaded while listing creation will be set as listings profile photo. Listing owners will be able to change the profile photo from their listing profile page.)',
            'user' => 'Listing owner’s photo (Listings owners will not be able to change the profile photo of their listings.)'
        ),
        'value' => 'listing'
    ));

    $this->addElement('Radio', 'expiry', array(
        'label' => 'Listing Duration',
        'description' => 'Do you want fixed duration listings on your website? (Fixed Duration listings will get expired after certain time and will not appear in home, browse pages and widgets.)',
        'multiOptions' => array(
            0 => 'No',
            1 => 'Yes, Listing owners will be able to choose if their listings should get expired along with expiry time.',
            2 => 'Yes, make all listings expire after a fixed duration. (You can choose the duration below.)'
        ),
        'onchange' => 'showExpiryDuration(this.value)',
        'value' => 0,
    ));

    $this->addElement('Duration', 'admin_expiry_duration', array(
        'label' => 'Duration',
        'description' => 'Select the duration after which Listings will expire. (This count will start from the listings approval dates. Users will see this duration while creating their listings.)',
        'value' => array('1', 'week'),
    ));

    $multiOptions = array(
        'day' => 'Day(s)',
        'week' => 'Week(s)',
        'month' => 'Month(s)',
        'year' => 'Year(s)');
    $this->getElement('admin_expiry_duration')
            ->setMultiOptions($multiOptions)
    ;

    $this->addElement('Radio', 'price', array(
        'label' => 'Allow Price',
        'description' => 'Do you want the Price field to be enabled for Listings?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 1,
    ));

    $this->addElement('Radio', 'where_to_buy', array(
        'label' => "Allow 'Where to Buy'",
        'description' => "Do you want the 'Where to Buy' field to be enabled for Listings? (Below, you can choose to enable / disable the Price field while adding Where to Buy option.)",
        'multiOptions' => array(
            2 => 'Yes, allow Where To Buy, with Price field',
            1 => 'Yes, allow Where To Buy, without Price field',
            0 => 'No'
        ),
        'value' => 2,
    ));

    $this->addElement('Radio', 'category_edit', array(
        'label' => 'Edit Listings Category',
        'description' => 'Do you want to allow listing owners to edit category of their listings?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 1,
    ));

    $this->addElement('Radio', 'edit_creationdate', array(
        'label' => 'Allow Publishing Date',
        'description' => 'Do you want to allow listing owners to add publishing (posted on) date of their listings?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 0,
    ));

    $listingTypeTable = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');
    $listingTypeTableName = $listingTypeTable->info('name');
    $select = $listingTypeTable->select()
            ->from($listingTypeTableName, array('title_plural', 'listingtype_id'));

    $listingTypeDatas = $listingTypeTable->fetchAll($select)->toArray();
    $listingTypes = array();
    foreach ($listingTypeDatas as $key) {
      $listingTypes[$key['listingtype_id']] = $key['title_plural'];
    }

    $this->addElement('Select', 'member_level', array(
        'label' => 'Inherit Member Level Settings',
        'description' => 'Select from below the listing type of which you want to inherit the Member Level Settings for this listing type. (Select \'Default Settings\', if you do not want to inherit Member Level Settings from any listing type.)',
        'multiOptions' => $listingTypes,
    ));

    $this->addElement('Radio', 'body_allow', array(
        'label' => 'Allow Description',
        'description' => 'Do you want to allow listing owners to write description for their listings?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 1,
        'onclick' => 'showDescription(this.value)'
    ));

    $this->addElement('Radio', 'body_required', array(
        'label' => 'Description Required',
        'description' => 'Do you want to make Description a mandatory field for listings?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 1,
    ));

    $this->addElement('Radio', 'overview', array(
        'label' => 'Allow Overview',
        'description' => 'Do you want to allow listing owners to write overview for their listings?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 1,
        'onclick' => 'showOverviewText(this.value)'
    ));

    $this->addElement('Radio', 'overview_creation', array(
        'label' => 'Overview while Listing Creation',
        'description' => 'Do you want to allow listing owners to write overview while creating listings?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 0,
    ));

    $this->addElement('Radio', 'location', array(
        'label' => 'Location Field',
        'description' => 'Do you want the Location field to be enabled for Listings?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 1,
    ));

    $this->addElement('MultiCheckbox', "contact_detail", array(
        'label' => 'Contact Detail Options',
        'description' => 'Choose the contact details options from below that you want to be enabled for the listings. (Users will be able to fill below chosen details for their listings from their Listing Dashboard. To disable contact details section from Listing dashboard, simply uncheck all the options.)',
        'multiOptions' => array(
            'phone' => 'Phone',
            'website' => 'Website',
            'email' => 'Email',
        ),
        'value' => array('phone', 'website', 'email')
    ));

    $this->addElement('Radio', "metakeyword", array(
        'label' => 'Meta Tags / Keywords',
        'description' => 'Do you want to enable listing owners to add Meta Tags / Keywords for their listings? (If enabled, then listing owners will be able to add them from "Meta Keyword" section of their Listing Dashboard.)',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 1,
    ));

    $this->addElement('Radio', 'sponsored', array(
        'label' => 'Sponsored Label',
        'description' => 'Do you want to show the "SPONSORED" label on the main pages of sponsored listings below the listing title?',
        'onclick' => 'showsponsored(this.value)',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 1,
    ));

    $this->addElement('Text', 'sponsored_color', array(
        'decorators' => array(array('ViewScript', array(
                    'viewScript' => '_formImagerainbowSponsred.tpl',
                    'class' => 'form element'
            )))
    ));

    $this->addElement('Radio', 'featured', array(
        'label' => 'Featured Label',
        'description' => 'Do you want to show the "FEATURED" label on the main pages of featured listings below the listing title?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'onclick' => 'showfeatured(this.value)',
        'value' => 1,
    ));

    $this->addElement('Text', 'featured_color', array(
        'decorators' => array(array('ViewScript', array(
                    'viewScript' => '_formImagerainbowFeatured.tpl',
                    'class' => 'form element'
            )))
    ));


    $this->addElement('Radio', 'profile_tab', array(
        'label' => 'Tabs Design Type',
        'description' => 'Select the design type of the tabs, for the widgets placed under the tab container on the listing profile page.',
        'multiOptions' => array(
            1 => 'Multiple Listing Types - New Tabs',
            0 => 'SocialEnigne - Default Tabs'
        ),
        'value' => 1,
    ));

    $this->addElement('Text', 'navigation_tabs', array(
        'label' => 'Tabs in Listings navigation bar',
        'allowEmpty' => false,
        'maxlength' => '2',
        'required' => true,
        'description' => 'How many tabs do you want to show on Listings main navigation bar by default? (Note: If number of tabs exceeds the limit entered by you then a "More" tab will appear, clicking on which will show the remaining hidden tabs. To choose the tab to be shown in this navigation menu, and their sequence, please visit: "Layout" > "Menu Editor")',
        'value' => 6,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        ),
    ));
    
    $this->addElement('Radio', 'redirection', array(
        'label' => 'Redirection of Listing Type link',
        'description' => 'Please select the redirection page for this listing, when user click on "Listing Type" link at various pages.',
        "multiOptions" => array(
            'home' => 'Listings Home Page',
            'index' => 'Listings Browse Page'
        ),
        'value' => 'home'
    ));        

    $this->addElement('Radio', 'subscription', array(
        'label' => 'Enable Subscriptions',
        'description' => 'Do you want to enable subscription for listings of this type? (If enabled, then members will be able to subscribe to the listings of this type posted by another member. Thus, whenever the other member posts a new listing of this type, then the first one will get a notification.)',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => 0
    ));

    if (empty($listingtype_id)) {
      $this->addElement('Checkbox', 'main_menu', array(
          'description' => 'Enable in Main Navigation Menu',
          'label' => 'Yes, enable this listing type for members in Main Navigation Menu. (Note: You can also enable / disable listing type from Layout >> Menu Editor section.)',
          'value' => 1,
      ));
    } else {
      $this->addElement('Checkbox', 'translation_file', array(
          'description' => 'Replace Language Files',
          'label' => "Replace language files for this listing type in all languages folders. Note: Changes will not reflect in your 'custom.csv' file.",
          'onclick' => 'showUpdateWarning()',
      ));
    }

    $subform = new Sitereview_Form_Admin_Settings_LanguagePhrases();
    $this->addSubForm($subform, 'languagephrases');
    $this->addElement('Checkbox', 'pinboard_layout', array(
        'description' => 'Pinboard Layout',
        'label' => "Enable Pinboard Layout for the Home Page of this listing type. (You can later change the layout of the Home Page from the Layout Editor.)"
    ));

    $this->addElement('Button', 'save', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
  }

}