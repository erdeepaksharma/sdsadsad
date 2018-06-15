<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: content.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
$view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
$type_video = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video');

if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proximity.search.kilometer', 0)) {
  $locationDescription = "Choose the kilometers within which listings will be displayed. (This setting will only work, if you have chosen 'Yes' in the above setting.)";
  $locationLableS = "Kilometer";
  $locationLable = "Kilometers";
} else {
  $locationDescription = "Choose the miles within which listings will be displayed. (This setting will only work, if you have chosen 'Yes' in the above setting.)";
  $locationLableS = "Mile";
  $locationLable = "Miles";
}

$detactLocationElement = array(
    'Select',
    'detactLocation',
    array(
        'label' => 'Do you want to display listings based on user’s current location?',
        'multiOptions' => array(
            1 => 'Yes',
            0 => 'No'
        ),
        'value' => '0'
    )
);

$defaultLocationDistanceElement = array(
    'Select',
    'defaultLocationDistance',
    array(
        'label' => $locationDescription,
        'multiOptions' => array(
            '0' => '',
            '1' => '1 ' . $locationLableS,
            '2' => '2 ' . $locationLable,
            '5' => '5 ' . $locationLable,
            '10' => '10 ' . $locationLable,
            '20' => '20 ' . $locationLable,
            '50' => '50 ' . $locationLable,
            '100' => '100 ' . $locationLable,
            '250' => '250 ' . $locationLable,
            '500' => '500 ' . $locationLable,
            '750' => '750 ' . $locationLable,
            '1000' => '1000 ' . $locationLable,
        ),
        'value' => '1000'
    )
);

$category_listings_multioptions = array(
    'view_count' => $view->translate('Views'),
    'like_count' => $view->translate('Likes'),
    'comment_count' => $view->translate('Comments'),
    'review_count' => $view->translate('Reviews'),
);

//CHECK IF FACEBOOK PLUGIN IS ENABLE
$fbmodule = Engine_Api::_()->getDbtable('modules', 'core')->getModule('facebookse');
$checkVersion = Engine_Api::_()->sitereview()->checkVersion($fbmodule, '4.2.7p1');
if (!empty($fbmodule) && !empty($fbmodule->enabled) && $checkVersion == 1) {
  $show_like_button = array(
      '1' => $view->translate('Yes, show SocialEngine Core Like button'),
      '2' => $view->translate('Yes, show Facebook Like button'),
      '0' => $view->translate('No'),
  );
  $default_value = 2;
} else {
  $show_like_button = array(
      '1' => $view->translate('Yes, show SocialEngine Core Like button'),
      '0' => $view->translate('No'),
  );
  $default_value = 1;
}

$popularity_options = array(
    'view_count' => $view->translate('Most Viewed'),
    'like_count' => $view->translate('Most Liked'),
    'comment_count' => $view->translate('Most Commented'),
    'review_count' => $view->translate('Most Reviewed'),
    'rating_avg' => $view->translate('Most Rated (Average Rating)'),
    'rating_editor' => $view->translate('Most Rated (Editor Rating)'),
    'rating_users' => $view->translate('Most Rated (User Ratings)'),
    'creation_date' => $view->translate('Most Recent'),
    'modified_date' => $view->translate('Recently Updated'),
);

$featuredSponsoredElement = array(
    'Select',
    'fea_spo',
    array(
        'label' => $view->translate('Show Listings'),
        'multiOptions' => array(
            '' => '',
            'newlabel' => $view->translate('New Only'),
            'featured' => $view->translate('Featured Only'),
            'sponsored' => $view->translate('Sponsored Only'),
            'fea_spo' => $view->translate('Either Featured or Sponsored'),
            'createdbyfriends' => $view->translate('Created By Friends'),
        ),
        'value' => '',
    )
);

$statisticsElement = array(
    'MultiCheckbox',
    'statistics',
    array(
        'label' => $view->translate('Choose the statistics that you want to be displayed for the Listings in this block.'),
        'multiOptions' => array("viewCount" => "Views", "likeCount" => "Likes", "commentCount" => "Comments", 'reviewCount' => 'Reviews'),
    //'value' =>array("viewCount","likeCount","commentCount","reviewCount"),
    ),
);

$statisticsWishlistElement = array(
    'MultiCheckbox',
    'statisticsWishlist',
    array(
        'label' => $view->translate('Choose the statistics that you want to be displayed for the Wishlist in this block.'),
        'multiOptions' => array("viewCount" => "Views", "likeCount" => "Likes", "followCount" => "Followers", "entryCount" => "Listings"),
    //'value' =>array("viewCount","likeCount","commentCount","reviewCount"),
    ),
);

$ratingTypeElement = array(
    'Select',
    'ratingType',
    array(
        'label' => $view->translate('Rating Type'),
        'multiOptions' => array('rating_avg' => $view->translate('Average Ratings'), 'rating_editor' => $view->translate('Only Editor Ratings'), 'rating_users' => $view->translate('Only User Ratings'), 'rating_both' => $view->translate('Both User and Editor Ratings')),
    )
);

$showViewMoreContent = array(
    'Select',
    'show_content',
    array(
        'label' => 'What do you want for view more content?',
        'description' => '',
        'multiOptions' => array(
            '1' => 'Pagination',
            '2' => 'Show View More Link at Bottom',
            '3' => 'Auto Load Listings on Scrolling Down'),
        'value' => 2,
    )
);

//GET LISTING TYPE TABLE
$listingTypeTable = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');

//GET LISTING TYPE COUNT
$listingTypeCount = $listingTypeTable->getListingTypeCount();

if ($listingTypeCount > 1) {

  $listingTypes1 = $listingTypes2 = $listingTypes = array();

  $listingTypes1 = $listingTypeTable->getListingTypesArray();

  $listingTypes2['-1'] = $view->translate('All Types');
  $listingTypes2 = $listingTypes2 + $listingTypes1;

  $listingTypeElement1 = array(
      'Select',
      'listingtype_id',
      array(
          'label' => $view->translate('Listing Type'),
          'multiOptions' => $listingTypes1,
      )
  );

  $listingTypeElement2 = array(
      'Select',
      'listingtype_id',
      array(
          'label' => $view->translate('Listing Type'),
          'multiOptions' => $listingTypes2,
      )
  );

  $listingTypeCategoryElement = array(
      'Select',
      'listingtype_id',
      array(
          'label' => $view->translate('Listing Type'),
          'multiOptions' => $listingTypes2,
          'onchange' => "addOptions(this.value, 'listingtype_id', 'category_id', 0);",
          'value' => 0,
      )
  );
} else {
  $listingTypeElement1 = array(
      'Hidden',
      'listingtype_id',
      array(
          'label' => $view->translate('Listing Type'),
          'value' => 1,
          'order' => 1041
      )
  );

  $listingTypeElement2 = array(
      'Hidden',
      'listingtype_id',
      array(
          'label' => $view->translate('Listing Type'),
          'value' => 1,
          'order' => 1042
      )
  );

  $listingTypeCategoryElement = array(
      'Hidden',
      'listingtype_id',
      array(
          'label' => $view->translate('Listing Type'),
          'value' => 1,
          'order' => 1043
      )
  );
}

$categoryElement = array(
    'Select',
    'category_id',
    array(
        'RegisterInArrayValidator' => false,
        'decorators' => array(array('ViewScript', array(
                    'viewScript' => 'application/modules/Sitereview/views/scripts/_category.tpl',
                    'class' => 'form element')))
        ));

$calendarElement = array(
    'Select',
    'date',
    array(
        'RegisterInArrayValidator' => false,
        'decorators' => array(array('ViewScript', array(
                    'viewScript' => 'application/modules/Sitereview/views/scripts/_calendar.tpl',
                    'class' => 'form element')))
        ));

$hiddenCatElement = array(
    'Text',
    'hidden_category_id',
    array(
        ));

$hiddenSubCatElement = array(
    'Text',
    'hidden_subcategory_id',
    array(
        ));

$hiddenSubSubCatElement = array(
    'Text',
    'hidden_subsubcategory_id',
    array(
        ));

$final_array = array(
    array(
        'title' => $view->translate('Listing Profile: Overview'),
        'description' => $view->translate('This widget forms the Overview tab on the Listing Profile page and displays the overview of the listing, which the owner has created using the editor in listing dashboard. This widget should be placed in the Tabbed Blocks area of the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.overview-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Overview'),
            'titleCount' => true,
            'loaded_by_ajax' => 1
        ),
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'loaded_by_ajax',
                    array(
                        'label' => $view->translate('Widget Content Loading'),
                        'description' => $view->translate('Do you want the content of this widget to be loaded via AJAX, after the loading of main webpage content? (Enabling this can improve webpage loading speed. Disabling this would load content of this widget along with the page content.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Radio',
                    'showAfterEditorReview',
                    array(
                        'label' => $view->translate('Do you want to display this block even when the Overview is shown in "Listing Profile: Editor Review / Overview / Description" widget?'),
                        'multiOptions' => array(
                            2 => $view->translate('Yes, always display this block.'),
                            1 => $view->translate('No, display this block when Overview is not displayed in that widget.'),
                        // 0 => 'Show Overview in Editor Review tab only till Editor Review has not been written.'
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Radio',
                    'showComments',
                    array(
                        'label' => $view->translate('Enable Comments'),
                        'description' => $view->translate('Do you want to enable comments in this widget? (If enabled, then users will be able to comment on the listing being viewed. Note: If you enable this, then you should not place the ‘Listing / Review Profile: Comments & Replies’ widget on Multiple Listing Types - Listing Profile page.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 0,
                    )
                ),
            )
        )
    ),
    array(
        'title' => $view->translate('Listing Profile: Description'),
        'description' => $view->translate('This widget forms the Description tab on the Listing Profile page and displays the description of the listing. This widget should be placed in the Tabbed Blocks area of the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.description-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Description'),
            'titleCount' => true,
            'loaded_by_ajax' => 1
        ),
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'showAlways',
                    array(
                        'label' => $view->translate('Do you want to display this block even when the Description is shown in "Listing Profile: Editor Review / Overview / Description" widget?'),
                        'multiOptions' => array(
                            2 => $view->translate('Yes, always display this block.'),
                            1 => $view->translate('No, display this block when Description is not displayed in that widget.'),
                        // 0 => 'Show Overview in Editor Review tab only till Editor Review has not been written.'
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Radio',
                    'loaded_by_ajax',
                    array(
                        'label' => $view->translate('Widget Content Loading'),
                        'description' => $view->translate('Do you want the content of this widget to be loaded via AJAX, after the loading of main webpage content? (Enabling this can improve webpage loading speed. Disabling this would load content of this widget along with the page content.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
//                array(
//                    'Radio',
//                    'showComments',
//                    array(
//                        'label' => $view->translate('Enable Comments'),
//                        'description' => $view->translate('Do you want to enable comments in this widget? (If enabled, then users will be able to comment on the listing being viewed. Note: If you enable this, then you should not place the ‘Listing / Review Profile: Comments & Replies’ widget on Multiple Listing Types - Listing Profile page.)'),
//                        'multiOptions' => array(
//                            1 => $view->translate('Yes'),
//                            0 => $view->translate('No')
//                        ),
//                        'value' => 0,
//                    )
//                ),
            )
        )
    ),
    array(
        'title' => $view->translate('Listing Profile: Listing Archives'),
        'description' => $view->translate('Displays the month-wise archives for the listings posted on your site by the listing owner which is being currently viewed. This widget should be placed on Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.archives-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Archives'),
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Navigation Tabs'),
        'description' => $view->translate('Displays the Navigation tabs for \'Multiple Listing Types Plugin\' having links of Products, Editors, Wishlists etc. This widget should be placed at the top of \'Multiple Listing Types - Editors Home\', \'Multiple Listing Types - Categories Home\', \'Multiple Listing Types - Listings Home\', \'Multiple Listing Types - Browse Products\',\'Multiple Listing Types - Most Rated Products\', \'Multiple Listing Types - Browse Products\' Locations\' and \'Multiple Listing Types - Browse Reviews\' page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.navigation-sitereview',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Categories Hierarchy for Listings (sidebar)'),
        'description' => $view->translate('Displays the Categories, Sub-categories and 3rd Level-categories of Listings in an expandable form. Displays categories of particular listing type, if you have "Multiple Listing Types - Listing Type Creation Extension" installed on your site. Clicking on them will redirect the viewer to Multiple Listing Types - Browse Listings page displaying the list of listings created in that category. Multiple settings are available to customize this widget. It is recommended to place this widget in \'Full Width\'.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.categories-sidebar-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Categories'),
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeElement1,
            ),
        ),
    ),
    array(
        'title' => $view->translate('Categories Home: Categories Hierarchy for Listings'),
        'description' => $view->translate('Displays the Categories, Sub-categories and 3rd Level-categories of Listings in an expandable form. Clicking on them will redirect the viewer to the list of listings created in that category. Multiple settings are available to customize this widget. This widget should be placed on the Categories Home Page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.categories-home',
        'defaultParams' => array(
            'title' => $view->translate('Categories'),
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'showAllCategories',
                    array(
                        'label' => $view->translate('Do you want all the categories, sub-categories and 3rd level categories to be shown to the users even if they have 0 listings in them?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Radio',
                    'show2ndlevelCategory',
                    array(
                        'label' => $view->translate('Do you want to show sub-categories in this widget?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Radio',
                    'show3rdlevelCategory',
                    array(
                        'label' => $view->translate('Do you want to show 3rd level category to the viewer? This settings will only work if you choose to show sub-categories from the setting above.'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Select',
                    'orderBy',
                    array(
                        'label' => $view->translate('Categories Ordering'),
                        'multiOptions' => array('category_name' => $view->translate('Alphabetical'), 'cat_order' => $view->translate('Ordering as in categories tab')),
                        'value' => 'category_name',
                    ),
                ),
                array(
                    'Radio',
                    'viewType',
                    array(
                        'label' => $view->translate('Show 3rd level categories of sub-categories in'),
                        'multiOptions' => array('expanded' => $view->translate('Expanded View'), 'collapsed' => $view->translate('Collapsed View')),
                        'value' => 'expanded',
                    )
                ),
                array(
                    'Radio',
                    'showCount',
                    array(
                        'label' => $view->translate('Show Listings count along with Categories,Sub-categories and 3rd level categories.
'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 0,
                    )
                ),
            )
        ),
    ),
    array(
        'title' => $view->translate('Categories Hierarchy for Listings'),
        'description' => $view->translate('Displays the Categories, Sub-categories and 3<sup>rd</sup> Level-categories of listings in an expandable form. Clicking on them will redirect the viewer to the list of listings created in that category. Multiple settings are available to customize this widget. It is recommended to place this widget in the middle column of the Multiple Listing Types - Listings Home page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.categories-middle-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Categories'),
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeElement1,
                array(
                    'Radio',
                    'showAllCategories',
                    array(
                        'label' => $view->translate('Do you want all the categories, sub-categories and 3rd level categories to be shown to the users even if they have 0 listings in them?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 0,
                    )
                ),
                array(
                    'Radio',
                    'show2ndlevelCategory',
                    array(
                        'label' => $view->translate('Do you want to show sub-categories in this widget?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Radio',
                    'show3rdlevelCategory',
                    array(
                        'label' => $view->translate('Do you want to show 3rd level category to the viewer? This settings will only work if you choose to show sub-categories from the setting above.'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Radio',
                    'showCount',
                    array(
                        'label' => $view->translate('Show Listings count along with Categories,Sub-categories and 3rd level categories.
'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 0,
                    )
                ),
            )
        ),
    ),
    array(
        'title' => $view->translate('Sponsored Categories'),
        'description' => $view->translate('Displays the Sponsored categories, sub-categories and 3<sup>rd</sup> level-categories. You can make categories as Sponsored from "Categories" section of Admin Panel.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.categories-sponsored',
        'defaultParams' => array(
            'title' => $view->translate('Sponsored Categories'),
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeElement2,
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of categories to show. Enter 0 for displaying all categories.)'),
                        'value' => 0,
                    )
                ),
                array(
                    'Radio',
                    'showIcon',
                    array(
                        'label' => $view->translate('Do you want to display the icons along with the categories in this block?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
            )
        ),
    ),
    array(
        'title' => $view->translate('Member Profile: Profile Listings'),
        'description' => $view->translate('Displays a member\'s listings on their profile. This widget should be placed in the Tabbed Blocks area of Member Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.profile-sitereview',
        'defaultParams' => array(
            'title' => 'Listings',
            'titleCount' => true,
            'statistics' => array("viewCount", "likeCount", "commentCount", "reviewCount")
        ),
        'adminForm' => array(
            'elements' => array(
                $ratingTypeElement,
                $listingTypeElement2,
                $statisticsElement,
                array(
                    'Text',
                    'truncation',
                    array(
                        'label' => $view->translate('Title Truncation Limit'),
                        'value' => 35,
                    )
                ),
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of Listings to show)'),
                        'value' => 10,
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Popular Listings Slideshow'),
        'description' => $view->translate('Displays listings based on the Popularity Criteria and other settings configured by you in an attractive slideshow with interactive controls. You can place this widget multiple times on a page with different popularity criterion chosen for each placement.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.slideshow-sitereview',
        'autoEdit' => 'true',
        'defaultParams' => array(
            'title' => $view->translate('Featured Listings'),
            'titleCount' => true,
            'statistics' => array("viewCount", "likeCount", "commentCount", "reviewCount")
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeCategoryElement,
                $categoryElement,
                $hiddenCatElement,
                $hiddenSubCatElement,
                $hiddenSubSubCatElement,
                $ratingTypeElement,
                $detactLocationElement,
                $defaultLocationDistanceElement,
                $featuredSponsoredElement,
                $statisticsElement,
                array(
                    'Radio',
                    'showExpiry',
                    array(
                        'label' => $view->translate('Show Expiry Date'),
                        'multiOptions' => array("1" => "Yes", "0" => "No"),
                        'value' => 0,
                    )
                ),
                array(
                    'Select',
                    'popularity',
                    array(
                        'label' => $view->translate('Popularity Criteria'),
                        'multiOptions' => array_merge($popularity_options, array('random' => 'Random', 'end_date' => 'Expiring Soon (having end date)')),
                        'value' => 'creation_date',
                    )
                ),
                array(
                    'Select',
                    'interval',
                    array(
                        'label' => $view->translate('Popularity Duration (This duration will be applicable to these Popularity Criteria:  Most Liked, Most Commented, Most Rated and Most Recent.)'),
                        'multiOptions' => array('week' => '1 Week', 'month' => '1 Month', 'overall' => 'Overall'),
                        'value' => 'overall',
                    )
                ),
                array(
                    'Radio',
                    'featuredIcon',
                    array(
                        'label' => $view->translate('Do you want to show the featured icon / label? (You can choose the marker from the \'Global Settings\' section in the Admin Panel.)'),
                        //'description' => $view->translate('(If selected "No", only one review will be displayed from a reviewer.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'Radio',
                    'sponsoredIcon',
                    array(
                        'label' => $view->translate('Do you want to show the sponsored icon / label? (You can choose the marker from the \'Global Settings\' section in the Admin Panel.)'),
                        //'description' => $view->translate('(If selected "No", only one review will be displayed from a reviewer.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'Radio',
                    'newIcon',
                    array(
                        'label' => $view->translate('Do you want to show the new icon / label. (You can choose the marker from the \'Global Settings\' section in the Admin Panel.)'),
                        //'description' => $view->translate('(If selected "No", only one review will be displayed from a reviewer.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'Text',
                    'truncation',
                    array(
                        'label' => $view->translate('Title Truncation Limit'),
                        'value' => 45,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
                array(
                    'Text',
                    'count',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of listings to show)'),
                        'value' => 10,
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: Listing Photos Slideshow'),
        'description' => $view->translate('Displays a Video and Photos selected by the listing owners from their Listing dashboard in an attractive slideshow. (If you place this widget, then users will be able to select photos and a video to be displayed in this slideshow from Photos and Videos section respectively of their Listing Dashboard. Note: If you place this widget, then you should disable the listing photos slideshow setting available in the \'Listing Profile: Editor Review / Overview / Description\' widget.) It should be placed on Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.slideshow-list-photo',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'slideshow_height',
                    array(
                        'label' => $view->translate('Enter the height of the slideshow (in pixels).'),
                        'value' => 400,
                    ),
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                ),
                array(
                    'Text',
                    'slideshow_width',
                    array(
                        'label' => $view->translate('Enter the width of the slideshow (in pixels).'),
                        'value' => 600,
                    ),
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                ),
                array(
                    'Radio',
                    'showCaption',
                    array(
                        'label' => $view->translate('Do you want to show image description in this Slideshow?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Radio',
                    'showButtonSlide',
                    array(
                        'label' => "Do you want to show thumbnails for photos and video navigation in this Slideshow? (If you select No, then small circles will be shown at Slideshow bottom for slides navigation.)",
                        'multiOptions' => array(
                            1 => 'Yes',
                            0 => 'No'
                        ),
                        'value' => 0,
                    )
                ),
                array(
                    'Radio',
                    'mouseEnterEvent',
                    array(
                        'label' => "By which action do you want slides navigation to occur from thumbnails / small circles?",
                        'multiOptions' => array(
                            1 => 'Mouse-over',
                            0 => 'On-click'
                        ),
                        'value' => 0,
                    )
                ),
                array(
                    'Radio',
                    'thumbPosition',
                    array(
                        'label' => "Where do you want to show image thumbnails?",
                        'multiOptions' => array(
                            'bottom' => 'In the bottom of Slideshow',
                            'left' => 'In the left of Slideshow',
                            'right' => 'In the right of Slideshow',
                        ),
                        'value' => 'bottom',
                    )
                ),
                array(
                    'Radio',
                    'autoPlay',
                    array(
                        'label' => "Do you want the Slideshow to automatically start playing when Listing Profile page is opened?",
                        'multiOptions' => array(
                            1 => 'Yes',
                            0 => 'No'
                        ),
                        'value' => 0,
                    )
                ),
                array(
                    'Text',
                    'slidesLimit',
                    array(
                        'label' => $view->translate('How many slides you want to show in slideshow?'),
                        'value' => 20,
                    ),
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                ),
                array(
                    'Text',
                    'captionTruncation',
                    array(
                        'label' => $view->translate('Truncation limit for slideshow description'),
                        'value' => 200,
                    ),
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                ),
            )
        )
    ),
    array(
        'title' => $view->translate('Listing / Review Profile: Where to Buy'),
        'description' => $view->translate('Displays the Where to Buy options for the listing being viewed. You can place this widget on Multiple Listing Types - Review Profile page, to display Where to Buy options for the listing for which the current review is written. This widget should be placed on the Multiple Listing Types - Review Profile or Multiple Listing Types - Listing Profile pages in right / left column or in the Tabbed Blocks area of Multiple Listing Types - Listings Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.price-info-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Where to Buy'),
            'titleCount' => true,
        ),
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'layout_column',
                    array(
                        'label' => $view->translate('Where do you want to show this widget?'),
                        'multiOptions' => array(
                            '1' => $view->translate('Right / Left column'),
                            '0' => $view->translate('Middle / Right Extended / Left Extended column'),
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'text',
                    'limit',
                    array(
                        'label' => $view->translate('Count (number of Where to Buy options to show. Note: This setting will only work if you choose "Right / Left column" in the above setting.)'),
                        'value' => 4,
                    )
                ),
                array(
                    'Radio',
                    'loaded_by_ajax',
                    array(
                        'label' => $view->translate('Widget Content Loading'),
                        'description' => $view->translate('Do you want the content of this widget to be loaded via AJAX, after the loading of main webpage content? (Enabling this can improve webpage loading speed. Disabling this would load content of this widget along with the page content.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 0,
                    )
                )
            ),
        ),
    ),
    array(
        'title' => $view->translate('Review / Editor Profile: Social Share Buttons'),
        'description' => $view->translate("Contains Social Sharing buttons and enables users to easily share Reviews / Editors' profiles on their favorite Social Networks. It is recommended to place this widget on the Multiple Listing Types - Review Profile page or Multiple Listing Types - Editor Profile page. You can customize the code for social sharing buttons from Global Settings of this plugin by adding your own code generated from: <a href='http://www.addthis.com' target='_blank'>http://www.addthis.com</a>"),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.socialshare-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Social Share'),
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing / Wishlist Profile: Share and Report Options'),
        'description' => $view->translate("Displays the various action link options to users viewing a listing / wishlist (Report, Print, Share, etc). It also contains Social Sharing buttons to enable users to easily share listings / wishlists on their favourite Social Network. You can customize the code for social sharing buttons from Global Settings of this plugin by adding your own code generated from: <a href='http://www.addthis.com' target='_blank'>http://www.addthis.com</a>. You can manage the Action Links available in this widget from the Edit settings of this widget. This widget should be placed on the Multiple Listing Types - Listing Profile page or the Multiple Listing Types - Wishlist Profile page."),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.share',
        'autoEdit' => true,
        'defaultParams' => array(
            'title' => $view->translate('Share and Report'),
            'titleCount' => true,
            'options' => array("siteShare", "friend", "report", "print", "socialShare"),
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'options',
                    array(
                        'label' => $view->translate('Select the options that you want to display in this block.'),
                        'multiOptions' => array("siteShare" => "Site Share", "friend" => "Tell a Friend", "report" => "Report", 'print' => 'Print', 'socialShare' => 'Social Share'),
                    //'value' => array("siteShare","friend","report","print","socialShare"),
                    ),
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: Listing Title'),
        'description' => $view->translate('Displays the Title of the listing. This widget should be placed on the Multiple Listing Types - Listing Profile page, in the middle column at the top.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.title-sitereview',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: Specifications'),
        'description' => $view->translate('Displays the Questions added from the "Profile Fields" section in the Admin Panel. This widget should be placed in the Tabbed Blocks area of Multiple Listing Types - Listings Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.specification-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Specs'),
            'titleCount' => true,
            'loaded_by_ajax' => 1
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'loaded_by_ajax',
                    array(
                        'label' => $view->translate('Widget Content Loading'),
                        'description' => $view->translate('Do you want the content of this widget to be loaded via AJAX, after the loading of main webpage content? (Enabling this can improve webpage loading speed. Disabling this would load content of this widget along with the page content.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                )
            )
        )
    ),
    array(
        'title' => $view->translate('Listing / Review Profile: Quick Specifications'),
        'description' => $view->translate('Displays the Questions enabled to be shown in this widget from the \'Profile Fields\' section in the Admin Panel. This widget should be placed in the right / left column on the Multiple Listing Types - Review Profile page or Multiple Listing Types - Listings Profile.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.quick-specification-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Quick Specifications'),
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'show_specificationlink',
                    array(
                        'label' => $view->translate('Show \'Full Specification\' link. (Note: This link will only be displayed, if you have placed \'Listing Profile: Specification\' widget in the Tabbed Blocks area of the Multiple Listing Types - Listing Profile page as users will be redirected to this tab on clicking the link.'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    ),
                ),
                array(
                    'Text',
                    'show_specificationtext',
                    array(
                        'label' => $view->translate('Please enter the text below which you want to display in place of "Full Specifications" link in this widget.'),
                        'value' => 'Full Specifications',
                    ),
                ),
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Number of specifications to show'),
                        'value' => 5,
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: Listing Information'),
        'description' => $view->translate('Displays the owner, category, tags, views, and other information about a listing. This widget should be placed on Multiple Listing Types - Listing Profile page in the left column.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.information-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Information'),
            'titleCount' => true,
            'showContent' => array("ownerPhoto", "ownerName", "modifiedDate", "viewCount", "likeCount", "commentCount", "tags", "location", "compare", "price", "addtowishlist")
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'showContent',
                    array(
                        'label' => $view->translate('Select the information options that you want to be available in this block.'),
                        'multiOptions' => array("ownerPhoto" => "Listing Owner's Photo", "ownerName" => "Owner's Name", "modifiedDate" => "Modified Date", "viewCount" => "Views", "likeCount" => "Likes", "commentCount" => "Comments", "tags" => "Tags", "location" => "Location", "price" => "Price", "compare" => "Compare", "addtowishlist" => "Add to Wishlist"),
                    ),
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: Listing Cover Photo'),
        'description' => $view->translate('Displays the main cover photo of a listing. This widget must be placed on the Multiple Listing Types - Listing Profile page at the top of left column.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.mainphoto-sitereview',
        'defaultParams' => array(
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'ownerName',
                    array(
                        'label' => $view->translate('Do you want to display listing owner’s name in this widget?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 0,
                    )
                ),
            )
        )
    ),
    array(
        'title' => $view->translate("Listing Profile: Listing Owner's Photo"),
        'description' => $view->translate("Displays the Listing owner's photo with owner's name. This widget should be placed in the right column of Listing Profile Page."),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.listing-owner-photo',
        'requirements' => array(
            'subject' => 'sitereview_listing',
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: Map'),
        'description' => $view->translate('This widget forms the Map tab on the Listing Profile page. It displays the map showing the listing position as well as the location details of the listing.It should be placed in the Tabbed Blocks area of the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.location-sitereview',
        'defaultParams' => array(
            'title' => 'Map',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                $ratingTypeElement,
            )
        )
    ),
    array(
        'title' => 'Listing Profile: Left / Right Column Map',
        'description' => 'This widget displays the map showing location of the Listing being currently viewed. It should be placed in the left / right column of the Multiple Listing Types - Listing Profile page.',
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.location-sidebar-sitereview',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'height',
                    array(
                        'label' => 'Enter the height of the map (in pixels).',
                        'value' => 200,
                    )
                ),
            ),
        ),
    ),    
    array(
        'title' => $view->translate('Listing Profile: Listing Options'),
        'description' => $view->translate('Displays the various action link options to users viewing a Listing. This widget should be placed on the Multiple Listing Types - Listing Profile page in the left column, below the listing profile photo.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.options-sitereview',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: Listing Photos'),
        'description' => $view->translate('This widget forms the Photos tab on the Listing Profile page and displays the photos of the listing. This widget should be placed in the Tabbed Blocks area of the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.photos-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Photos'),
            'titleCount' => true,
            'loaded_by_ajax' => 1
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'loaded_by_ajax',
                    array(
                        'label' => $view->translate('Widget Content Loading'),
                        'description' => $view->translate('Do you want the content of this widget to be loaded via AJAX, after the loading of main webpage content? (Enabling this can improve webpage loading speed. Disabling this would load content of this widget along with the page content.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(Number of photos to show)'),
                        'value' => 20,
                    )
                ),
            )
        )
    ),
    array(
        'title' => $view->translate('Listing Profile: Listing Videos'),
        'description' => $view->translate('This widget forms the Videos tab on the Listing Profile page and displays the videos of the listing. This widget should be placed in the Tabbed Blocks area of the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.video-sitereview',
        'defaultParams' => array(
            'title' => 'Videos',
            'titleCount' => true,
            'loaded_by_ajax' => 1
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'loaded_by_ajax',
                    array(
                        'label' => $view->translate('Widget Content Loading'),
                        'description' => $view->translate('Do you want the content of this widget to be loaded via AJAX, after the loading of main webpage content? (Enabling this can improve webpage loading speed. Disabling this would load content of this widget along with the page content.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Text',
                    'count',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of videos to show)'),
                        'value' => 10,
                    )
                ),
                array(
                    'Text',
                    'truncation',
                    array(
                        'label' => $view->translate('Title Truncation Limit'),
                        'value' => 35,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
            )
        )
    ),
    array(
        'title' => $view->translate('Post a New Listing'),
        'description' => $view->translate('Displays the link to Post a New Listing.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.newlisting-sitereview',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeElement1,
            ),
        ),
    ),
    array(
        'title' => $view->translate('Browse Top Rated Listings Link'),
        'description' => $view->translate('Displays the link to view Browse Top Rated Listings page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.mostrated-browse-sitereview',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeElement1,
            ),
        ),
    ),
    array(
        'title' => $view->translate('Popular Locations'),
        'description' => $view->translate('Displays the popular locations of listings with frequency. (Note: If you have installed our Multiple Listing Types - Listing Type Creation Extension then you can choose to display listings corresponding to the Listing Type selected from Edit section of this widget.)'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.popularlocation-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Popular Locations'),
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeElement1,
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of locations to show)'),
                        'value' => 10,
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Popular Listing Tags'),
        'description' => $view->translate('Displays popular tags with frequency. This widget should be placed on the \'Multiple Listing Types - Listing Profile\' / \'Multiple Listing Types - Browse Listings\' / \'Multiple Listing Types - Listings Home\' pages.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.tagcloud-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Popular Tags (%s)'),
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'title',
                    array(
                        'label' => $view->translate('Title'),
                        'description' => $view->translate("Enter below the format in which you want to display the title of the widget. (Note: To display count of tags on listings browse and home pages, enter title as: Title (%s). To display listing owner’s name on listing profile page, enter title as: %s's Tags.)"),
                        'value' => 'Popular Tags (%s)',
                    )
                ),
                $listingTypeElement1,
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of tags to show)'),
                        'value' => 25,
                    )
                ),
                array(
                    'Radio',
                    'loaded_by_ajax',
                    array(
                        'label' => 'Widget Content Loading',
                        'description' => 'Do you want the content of this widget to be loaded via AJAX, after the loading of main webpage content? (Enabling this can improve webpage loading speed. Disabling this would load content of this widget along with the page content.)',
                        'multiOptions' => array(
                            1 => 'Yes',
                            0 => 'No'
                        ),
                        'value' => 1,
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: Owner Listings'),
        'description' => $view->translate('Displays a list of other listings owned by the listing owner. This widget should be placed on Reviews: Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.userlisting-sitereview',
        'defaultParams' => array(
            'title' => "%s's Listings",
            'titleCount' => true,
            'statistics' => array("likeCount", "reviewCount")
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'title',
                    array(
                        'label' => $view->translate('Title'),
                        'description' => $view->translate("Enter below the format in which you want to display the title of the widget. (Note: To display listing owner’s name on listing profile page, enter title as: %s's Listings.)"),
                        'value' => "%s's Listings",
                    )
                ),
                $ratingTypeElement,
                $statisticsElement,
                array(
                    'Radio',
                    'viewType',
                    array(
                        'label' => $view->translate('Choose the View Type for listings.'),
                        'multiOptions' => array(
                            'listview' => $view->translate('List View'),
                            'gridview' => $view->translate('Grid View'),
                        ),
                        'value' => 'listview',
                    )
                ),
                array(
                    'Text',
                    'columnWidth',
                    array(
                        'label' => $view->translate('Column Width For Grid View.'),
                        'value' => '180',
                    )
                ),
                array(
                    'Text',
                    'columnHeight',
                    array(
                        'label' => $view->translate('Column Height For Grid View.'),
                        'value' => '328',
                    )
                ),
                array(
                    'Text',
                    'count',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of Listings to show)'),
                        'value' => 3,
                    )
                ),
                array(
                    'Text',
                    'truncation',
                    array(
                        'label' => $view->translate('Title Truncation Limit'),
                        'value' => 24,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: About Listing'),
        'description' => $view->translate('Displays the About Listing information for listings as entered by listing owners. This widget should be placed on the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.write-sitereview',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Browse Listings: Breadcrumb'),
        'description' => $view->translate('Displays breadcrumb based on the categories searched from the search form widget. This widget should be placed on Multiple Listing Types - Browse Listings page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.browse-breadcrumb-sitereview',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Browse Listings'),
        'description' => $view->translate('Displays a list of all the listings on your site. This widget should be placed on Multiple Listing Types - Browse Listings page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.browse-listings-sitereview',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
            'layouts_views' => array("1", "2", "3"),
            'layouts_order' => 1,
            'statistics' => array("viewCount", "likeCount", "commentCount", "reviewCount"),
            'columnWidth' => '180',
            'truncationGrid' => 90
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeElement1,
                $ratingTypeElement,
                $detactLocationElement,
                $defaultLocationDistanceElement,
                array(
                    'MultiCheckbox',
                    'layouts_views',
                    array(
                        'label' => $view->translate('Choose the view types that you want to be available for listings.'),
                        'multiOptions' => array("1" => $view->translate("List View"), "2" => $view->translate("Grid View"), "3" => $view->translate("Map View")),
                    //'value' => array("0" => "1", "1" => "2", "2" => "3"),
                    ),
                ),
                array(
                    'Radio',
                    'layouts_order',
                    array(
                        'label' => $view->translate('Select a default view type for Listings.'),
                        'multiOptions' => array("1" => $view->translate("List View"), "2" => $view->translate("Grid View"), "3" => $view->translate("Map View")),
                        'value' => 1,
                    )
                ),
                array(
                    'Text',
                    'columnWidth',
                    array(
                        'label' => $view->translate('Column Width For Grid View.'),
                        'value' => '180',
                    )
                ),
                array(
                    'Text',
                    'columnHeight',
                    array(
                        'label' => $view->translate('Column Height For Grid View.'),
                        'value' => '328',
                    )
                ),
                $statisticsElement,
                array(
                    'Radio',
                    'showExpiry',
                    array(
                        'label' => $view->translate('Show Expiry Date'),
                        'multiOptions' => array("1" => "Yes", "0" => "No"),
                        'value' => 0,
                    )
                ),
                array(
                    'Radio',
                    'viewType',
                    array(
                        'label' => $view->translate("Do you want to show 'Where to Buy' options associated with the listings in this block? (Note: If you select 'Yes' below, then you should place this widget in the Right Extended / Left Extended Column.)"),
                        'multiOptions' => array(
                            '1' => $view->translate('Yes'),
                            '0' => $view->translate('No'),
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'Radio',
                    'bottomLine',
                    array(
                        'label' => $view->translate('Choose from below what you want to display with listing title.'),
                        'multiOptions' => array(
                            '1' => $view->translate("Editor Review's Bottom line (If there is no Editor Review, then Listing's description will be displayed.)"),
                            '0' => $view->translate("Listing's description"),
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'Radio',
                    'postedby',
                    array(
                        'label' => $view->translate("Show posted by option. (Selecting 'Yes' here will display the member's name who has created the listing.)"),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'Radio',
                    'orderby',
                    array(
                        'label' => $view->translate('Default ordering in Browse Listings. (Note: Selecting multiple ordering will make your page load slower.)'),
                        'multiOptions' => array(
                            'creation_date' => $view->translate('All listings in descending order of creation.'),
                            'view_count' => $view->translate('All listings in descending order of views.'),
                            'title' => $view->translate('All listings in alphabetical order.'),
                            'sponsored' => $view->translate('Sponsored listings followed by others in descending order of creation.'),
                            'featured' => $view->translate('Featured listings followed by others in descending order of creation.'),
                            'fespfe' => $view->translate('Sponsored & Featured listings followed by Sponsored listings followed by Featured listings followed by others in descending order of creation.'),
                            'spfesp' => $view->translate('Featured & Sponsored listings followed by Featured listings followed by Sponsored listings followed by others in descending order of creation.'),
                            'newlabel' => $view->translate('Listings marked as New followed by others in descending order of creation.'),
                        ),
                        'value' => 'creation_date',
                    )
                ),
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of Listings to show)'),
                        'value' => 10,
                    )
                ),
                array(
                    'Text',
                    'truncation',
                    array(
                        'label' => $view->translate('Title Truncation Limit'),
                        'value' => 25,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
                array(
                    'Text',
                    'truncationGrid',
                    array(
                        'label' => $view->translate('Title Truncation Limit in Grid View'),
                        'value' => 90,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
                $showViewMoreContent,
            ),
        ),
    ),
    array(
        'title' => $view->translate('Browse Top Rated Listings'),
        'description' => $view->translate('Displays a list of all the top rated listings on your site. This widget should be placed on Multiple Listing Types - Browse Top Rated Listings page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.rated-listings-sitereview',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
            'layouts_views' => array("1", "2", "3"),
            'layouts_order' => 1,
            'statistics' => array("viewCount", "likeCount", "commentCount", "reviewCount"),
            'columnWidth' => '180',
            'truncationGrid' => 90
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeCategoryElement,
                $categoryElement,
                $hiddenCatElement,
                $hiddenSubCatElement,
                $hiddenSubSubCatElement,
                $ratingTypeElement,
                $detactLocationElement,
                $defaultLocationDistanceElement,
                array(
                    'MultiCheckbox',
                    'layouts_views',
                    array(
                        'label' => $view->translate('Choose the view types that you want to be available for listings.'),
                        'multiOptions' => array("1" => $view->translate("List View"), "2" => $view->translate("Grid View"), "3" => $view->translate("Map View")),
                    //'value' => array("0" => "1", "1" => "2", "2" => "3"),
                    ),
                ),
                array(
                    'Radio',
                    'layouts_order',
                    array(
                        'label' => $view->translate('Select a default view type for Listings.'),
                        'multiOptions' => array("1" => $view->translate("List View"), "2" => $view->translate("Grid View"), "3" => $view->translate("Map View")),
                        'value' => 1,
                    )
                ),
                array(
                    'Text',
                    'columnWidth',
                    array(
                        'label' => $view->translate('Column Width For Grid View.'),
                        'value' => '180',
                    )
                ),
                array(
                    'Text',
                    'columnHeight',
                    array(
                        'label' => $view->translate('Column Height For Grid View.'),
                        'value' => '328',
                    )
                ),
                $statisticsElement,
                array(
                    'Radio',
                    'showExpiry',
                    array(
                        'label' => $view->translate('Show Expiry Date'),
                        'multiOptions' => array("1" => "Yes", "0" => "No"),
                        'value' => 0,
                    )
                ),
                array(
                    'Radio',
                    'viewType',
                    array(
                        'label' => $view->translate("Do you want to show 'Where to Buy' options associated with the listings in this block? (Note: If you select 'Yes' below, then you should place this widget in the Right Extended / Left Extended Column.)"),
                        'multiOptions' => array(
                            '1' => $view->translate('Yes'),
                            '0' => $view->translate('No'),
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'Radio',
                    'bottomLine',
                    array(
                        'label' => $view->translate('Choose from below what you want to display with listing title.'),
                        'multiOptions' => array(
                            '1' => $view->translate("Editor Review's Bottom line (If there is no Editor Review, then Listing's description will be displayed.)"),
                            '0' => $view->translate("Listing's description"),
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'Radio',
                    'postedby',
                    array(
                        'label' => $view->translate("Show posted by option. (Selecting 'Yes' here will display the member's name who has created the listing.)"),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of Listings to show)'),
                        'value' => 10,
                    )
                ),
                array(
                    'Text',
                    'truncation',
                    array(
                        'label' => $view->translate('Title Truncation Limit'),
                        'value' => 25,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
                array(
                    'Text',
                    'truncationGrid',
                    array(
                        'label' => $view->translate('Title Truncation Limit in Grid View'),
                        'value' => 90,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: Best Alternatives'),
        'description' => $view->translate('Displays listings similar to the listing being viewed as Best Alternative listings. The similar listings are shown based on the listings selected by the editors as similar listings from the listing profile page or bottom-level category of the listing being viewed. This widget should be placed on Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.similar-items-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Best Alternatives'),
            'titleCount' => true,
            'statistics' => array("likeCount", "commentCount", "reviewCount")
        ),
        'adminForm' => array(
            'elements' => array(
                $ratingTypeElement,
                array(
                    'Radio',
                    'showSameCategoryListings',
                    array(
                        'label' => $view->translate('Show same category listings if editor/owner of listing do not selected any best alternative listing.'),
                        'multiOptions' => array(
                            '1' => $view->translate('Yes'),
                            '0' => $view->translate('No'),
                        ),
                        'value' => '1',
                    )
                ),                
                array(
                    'Radio',
                    'viewType',
                    array(
                        'label' => $view->translate('Display Type'),
                        'multiOptions' => array(
                            '1' => $view->translate('Horizontal'),
                            '0' => $view->translate('Vertical'),
                            'gridview' => $view->translate('Grid View'),
                        ),
                        'value' => '0',
                    )
                ),
                array(
                    'Text',
                    'columnWidth',
                    array(
                        'label' => $view->translate('Column Width For Grid View.'),
                        'value' => '180',
                    )
                ),
                array(
                    'Text',
                    'columnHeight',
                    array(
                        'label' => $view->translate('Column Height For Grid View.'),
                        'value' => '328',
                    )
                ),
                $statisticsElement,
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of Listings to show)'),
                        'value' => 3,
                    )
                ),
                array(
                    'Text',
                    'truncation',
                    array(
                        'label' => $view->translate('Title Truncation Limit'),
                        'value' => 24,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Popular / Recent / Random Listings'),
        'description' => $view->translate('Displays Listings based on the Popularity Criteria and other settings that you choose for this widget. You can place this widget multiple times on a page with different popularity criterion chosen for each placement.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.listings-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Reviews'),
            'titleCount' => true,
            'statistics' => array("likeCount", "reviewCount"),
            'viewType' => 'listview',
            'columnWidth' => '180'
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeCategoryElement,
                $ratingTypeElement,
                $featuredSponsoredElement,
                $detactLocationElement,
                $defaultLocationDistanceElement,
                array(
                    'Radio',
                    'viewType',
                    array(
                        'label' => $view->translate('Choose the View Type for listings.'),
                        'multiOptions' => array(
                            'listview' => $view->translate('List View'),
                            'gridview' => $view->translate('Grid View'),
                        ),
                        'value' => 'listview',
                    )
                ),
                array(
                    'Text',
                    'columnWidth',
                    array(
                        'label' => $view->translate('Column Width For Grid View.'),
                        'value' => '180',
                    )
                ),
                array(
                    'Text',
                    'columnHeight',
                    array(
                        'label' => $view->translate('Column Height For Grid View.'),
                        'value' => '328',
                    )
                ),
                array(
                    'Select',
                    'popularity',
                    array(
                        'label' => $view->translate('Popularity Criteria'),
                        'multiOptions' => array_merge($popularity_options, array('random' => $view->translate('Random'), 'end_date' => 'Expiring Soon (having end date)')),
                        'value' => 'view_count',
                    )
                ),
                array(
                    'Select',
                    'interval',
                    array(
                        'label' => $view->translate('Popularity Duration (This duration will be applicable to these Popularity Criteria:  Most Liked, Most Commented, Most Rated and Most Recent.)'),
                        'multiOptions' => array('week' => '1 Week', 'month' => '1 Month', 'overall' => 'Overall'),
                        'value' => 'overall',
                    )
                ),
                $categoryElement,
                $hiddenCatElement,
                $hiddenSubCatElement,
                $hiddenSubSubCatElement,
                $statisticsElement,
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of Listings to show)'),
                        'value' => 3,
                    )
                ),
                array(
                    'Text',
                    'truncation',
                    array(
                        'label' => $view->translate('Title Truncation Limit'),
                        'value' => 16,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Most Discussed Listings'),
        'description' => $view->translate('Displays the listings having the most number of discussions. Multiple settings available in the Edit Settings of this widget.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.most-discussed-listings',
        'defaultParams' => array(
            'title' => $view->translate('Reviews'),
            'titleCount' => true,
            'viewType' => 'listview',
            'columnWidth' => '180'
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeCategoryElement,
                $ratingTypeElement,
                $featuredSponsoredElement,
                $detactLocationElement,
                $defaultLocationDistanceElement,
                array(
                    'Radio',
                    'viewType',
                    array(
                        'label' => $view->translate('Choose the View Type for listings.'),
                        'multiOptions' => array(
                            'listview' => $view->translate('List View'),
                            'gridview' => $view->translate('Grid View'),
                        ),
                        'value' => 'listview',
                    )
                ),
                array(
                    'Text',
                    'columnWidth',
                    array(
                        'label' => $view->translate('Column Width For Grid View.'),
                        'value' => '180',
                    )
                ),
                array(
                    'Text',
                    'columnHeight',
                    array(
                        'label' => $view->translate('Column Height For Grid View.'),
                        'value' => '328',
                    )
                ),
                $categoryElement,
                $hiddenCatElement,
                $hiddenSubCatElement,
                $hiddenSubSubCatElement,
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of Listings to show)'),
                        'value' => 3,
                    )
                ),
                array(
                    'Text',
                    'truncation',
                    array(
                        'label' => $view->translate('Title Truncation Limit'),
                        'value' => 16,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Search Listings Form'),
        'description' => $view->translate('Displays the form for searching Listings on the basis of various fields and filters. Settings for this form can be configured from the Search Form Settings section.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.search-sitereview',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeElement1,
                array(
                    'Radio',
                    'viewType',
                    array(
                        'label' => $view->translate('Show Search Form'),
                        'multiOptions' => array(
                            'horizontal' => $view->translate('Horizontal'),
                            'vertical' => $view->translate('Vertical'),
                        ),
                        'value' => 'vertical'
                    )
                ),
                array(
                    'Radio',
                    'locationDetection',
                    array(
                        'label' => "Allow browser to detect user's current location.",
                        'multiOptions' => array(
                            1 => 'Yes',
                            0 => 'No'
                        ),
                        'value' => 0,
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('AJAX based Listings Carousel'),
        'description' => $view->translate('This widget contains an attractive AJAX based carousel, showcasing the listings on the site. You can choose to show sponsored / featured / new listings in this widget from the settings of this widget. You can place this widget multiple times on a page with different criterion chosen for each placement.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.sponsored-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Listings Carousel'),
            'titleCount' => true,
            'showOptions' => array("category", "rating", "review", "compare", "wishlist"),
        ),
        'adminForm' => 'Sitereview_Form_Admin_Widgets_AjaxBasedListingCarousel',
    ),
    array(
        'title' => $view->translate('Listing of the Day'),
        'description' => $view->translate('Displays a listing as listing of the day. You can choose the listing to be shown in this widget from the settings of this widget. Other settings are also available.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.item-sitereview',
        'adminForm' => 'Sitereview_Form_Admin_Settings_Dayitem',
        'defaultParams' => array(
            'title' => $view->translate('Listing of the Day'),
        ),
    ),
    array(
        'title' => $view->translate('Review of the Day'),
        'description' => $view->translate('Displays a review as review of the day. You can choose the review to be shown in this widget from the settings of this widget. Other settings are also available.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.review-of-the-day',
        'adminForm' => 'Sitereview_Form_Admin_Settings_Reviewdayitem',
        'defaultParams' => array(
            'title' => $view->translate('Review of the Day'),
        ),
    ),
    array(
        'title' => $view->translate('Recently Viewed by Users'),
        'description' => $view->translate('Displays listings that have been recently viewed by Users of your site. Multiple settings are available for this widget in its Edit section.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.recently-viewed-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Recently Viewed By Friends'),
            'titleCount' => true,
            'statistics' => array("likeCount", "reviewCount"),
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeElement2,
                $ratingTypeElement,
                $featuredSponsoredElement,
                array(
                    'Radio',
                    'show',
                    array(
                        'label' => $view->translate('Show recently viewed listings of:'),
                        'multiOptions' => array(
                            '2' => $view->translate('Viewed by any user.'),
                            '1' => $view->translate('Currently logged-in member’s friends.'),
                            '0' => $view->translate('Self (Currently logged-in member).'),
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'Radio',
                    'viewType',
                    array(
                        'label' => $view->translate('Choose the View Type for listings.'),
                        'multiOptions' => array(
                            'listview' => $view->translate('List View'),
                            'gridview' => $view->translate('Grid View'),
                        ),
                        'value' => 'listview',
                    )
                ),
                array(
                    'Text',
                    'columnWidth',
                    array(
                        'label' => $view->translate('Column Width For Grid View.'),
                        'value' => '180',
                    )
                ),
                array(
                    'Text',
                    'columnHeight',
                    array(
                        'label' => $view->translate('Column Height For Grid View.'),
                        'value' => '328',
                    )
                ),
//                 $listingTypeElement1,
//                 $ratingTypeElement,
                $statisticsElement,
//                 $featuredSponsoredElement,
                array(
                    'Text',
                    'truncation',
                    array(
                        'label' => $view->translate('Title Truncation Limit'),
                        'value' => 16,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    ),
                ),
                array(
                    'Text',
                    'count',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of Listings to show)'),
                        'value' => 3,
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Message for Zero Listings'),
        'description' => $view->translate('This widget should be placed in the top of the middle column of Multiple Listing Types - Listings Home page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.zerolisting-sitereview',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Close Listing Message'),
        'description' => $view->translate('If a Listing is closed, then show its message.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.closelisting-sitereview',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: Like Button for Listings'),
        'description' => $view->translate('This is the Like Button to be placed on Multiple Listing Types - Listing Profile Page. The best place to put this widget is right above the Tabbed Block of the Review: Listing Profile Page. If you have the Likes Plugins and Widgets from SocialEngineAddOns installed on your site, then you may replace this button widget of that plugin.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'seaocore.like-button',
        'defaultParams' => array(
            'title' => '',
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: "Write a Review" Button for Listings'),
        'description' => $view->translate('This is the "Write a Review" Button to be placed on Multiple Listing Types - Listing Profile page. When clicked, users will be redirected to write review for the listing being viewed. The best place to put this widget is right above the Tabbed Block of the Review: Listing Profile Page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.review-button',
        'defaultParams' => array(
            'title' => '',
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: "Apply Now" Button for Listings'),
        'description' => $view->translate('This is the "Apply Now" Button to be placed on Multiple Listing Types - Listing Profile page. When clicked, users will get a pop-up to apply for the listing being currently viewed. If you are having site related to jobs, Properties, etc you can use this widget for sending resumes, message to the owner of the listing, etc.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.applynow-button',
        'defaultParams' => array(
            'title' => '',
            'show_option' => array("1", "2", "3", "4", "5"),
        ),
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'show_option',
                    array(
                        'label' => $view->translate('Choose the fields that you want to be show on the form.'),
                        'multiOptions' => array("1" => $view->translate("Your Name"), "2" => $view->translate("Your Email Address"), "3" => $view->translate("Contact Number"), "4" => $view->translate("Browse File (Resume)"), "5" => $view->translate("Message")),
                    ),
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: Listing Discussions'),
        'description' => $view->translate('This widget forms the Discussions tab on the Multiple Listing Types - Listing Profile page and displays the discussions of the listing. This widget should be placed in the Tabbed Blocks area of the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.discussion-sitereview',
        'defaultParams' => array(
            'title' => 'Discussions',
            'titleCount' => true,
            'loaded_by_ajax' => 1
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'loaded_by_ajax',
                    array(
                        'label' => $view->translate('Widget Content Loading'),
                        'description' => $view->translate('Do you want the content of this widget to be loaded via AJAX, after the loading of main webpage content? (Enabling this can improve webpage loading speed. Disabling this would load content of this widget along with the page content.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                )
            )
        )
    ),
    array(
        'title' => $view->translate('Listing Profile: Listing Likes'),
        'description' => $view->translate('Displays that which all users have liked a listing. This widget should be placed on the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'seaocore.people-like',
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of users to show)'),
                        'value' => 3,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Ajax based main Listings Home widget'),
        'description' => $view->translate("Contains multiple Ajax based tabs showing Recently Posted, Popular, Most Reviewed, Featured and Sponsored listings in a block in separate ajax based tabs respectively. You can configure various settings for this widget from the Edit settings."),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.recently-popular-random-sitereview',
        'defaultParams' => array(
            'title' => "",
            'titleCount' => "",
            'statistics' => array("viewCount", "likeCount", "commentCount", "reviewCount"),
            'layouts_views' => array("listZZZview", "gridZZZview", "mapZZZview"),
            'ajaxTabs' => array("recent", "mostZZZreviewed", "mostZZZpopular", "featured", "sponsored", "expiringZZZsoon"),
            'showContent' => array("price", "location", "endDate"),
            'recent_order' => 1,
            'reviews_order' => 2,
            'popular_order' => 3,
            'featured_order' => 4,
            'sponosred_order' => 5,
            'columnWidth' => '180'
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeCategoryElement,
                $ratingTypeElement,
                $categoryElement,
                $hiddenCatElement,
                $hiddenSubCatElement,
                $hiddenSubSubCatElement,
                $statisticsElement,
                $detactLocationElement,
                $defaultLocationDistanceElement,
                array(
                    'MultiCheckbox',
                    'layouts_views',
                    array(
                        'label' => $view->translate('Choose the view types that you want to be available for listings on the home and browse pages of listings.'),
                        'multiOptions' => array("listZZZview" => "List View", "gridZZZview" => "Grid View", "mapZZZview" => "Map View")
                    ),
                ),
                array(
                    'MultiCheckbox',
                    'showContent',
                    array(
                        'label' => $view->translate('Select the information options that you want to be available in this block.'),
                        'multiOptions' => array("price" => "Price", "location" => "Location", "endDate" => "End / Expiry Date"),
                    ),
                ),
                array(
                    'Radio',
                    'defaultOrder',
                    array(
                        'label' => $view->translate('Select a default view type for Listings'),
                        'multiOptions' => array("listZZZview" => $view->translate("List View"), "gridZZZview" => $view->translate("Grid View"), "mapZZZview" => $view->translate("Map View")),
                        'value' => "listZZZview",
                    )
                ),
                array(
                    'Radio',
                    'listViewType',
                    array(
                        'label' => $view->translate("Do you want to show 'Where to Buy' options associated with the listings in this block? (Note: If you select 'Yes' below, then you should place this widget in the Right Extended / Left Extended Column.)"),
                        'multiOptions' => array(
                            'tabular' => $view->translate('Yes'),
                            'list' => $view->translate('No'),
                        ),
                        'value' => 'list',
                    )
                ),
                array(
                    'Text',
                    'columnWidth',
                    array(
                        'label' => $view->translate('Column Width For Grid View.'),
                        'value' => '180',
                    )
                ),
                array(
                    'Text',
                    'columnHeight',
                    array(
                        'label' => $view->translate('Column Height For Grid View.'),
                        'value' => '328',
                    )
                ),
                array(
                    'MultiCheckbox',
                    'ajaxTabs',
                    array(
                        'label' => $view->translate('Select the tabs that you want to be available in this block.'),
                        'multiOptions' => array("recent" => "Recent", "mostZZZreviewed" => "Most Reviewed", "mostZZZpopular" => "Most Popular", "featured" => "Featured", "sponsored" => "Sponsored", "expiringZZZsoon" => "Expiring Soon (having end date)")
                    )
                ),
                array(
                    'Text',
                    'recent_order',
                    array(
                        'label' => $view->translate('Recent Tab (order)'),
                        'value' => 1
                    ),
                ),
                array(
                    'Text',
                    'reviews_order',
                    array(
                        'label' => $view->translate('Most Reviewed Tab (order)'),
                        'value' => 2
                    ),
                ),
                array(
                    'Text',
                    'popular_order',
                    array(
                        'label' => $view->translate('Most Popular Tab (order)'),
                        'value' => 3
                    ),
                ),
                array(
                    'Text',
                    'featured_order',
                    array(
                        'label' => $view->translate('Featured Tab (order)'),
                        'value' => 4
                    ),
                ),
                array(
                    'Text',
                    'sponosred_order',
                    array(
                        'label' => $view->translate('Sponosred Tab (order)'),
                        'value' => 5
                    ),
                ),
                array(
                    'Text',
                    'expiring_order',
                    array(
                        'label' => $view->translate('Expiring Soon Tab (order)'),
                        'value' => 6
                    ),
                ),
                array(
                    'Radio',
                    'postedby',
                    array(
                        'label' => $view->translate('Show posted by option. (Selecting "Yes" here will display the member\'s name who has created the listing.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'Text',
                    'limit',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of Listings to show)'),
                        'value' => 12,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        )
                    ),
                ),
                array(
                    'Text',
                    'truncationList',
                    array(
                        'label' => $view->translate('Title Truncation Limit in List View'),
                        'value' => 600,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
                array(
                    'Text',
                    'truncationGrid',
                    array(
                        'label' => $view->translate('Title Truncation Limit in Grid View'),
                        'value' => 90,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
            )
        ),
    ),
    array(
        'title' => $view->translate('Categorically Popular Listings'),
        'description' => $view->translate('This attractive widget categorically displays the most popular listings on your site. It displays 5 Listings for each category. From the edit popup of this widget, you can choose the number of categories to show, criteria for popularity and the duration for consideration of popularity.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.category-listings-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Popular Listings'),
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeElement1,
                $detactLocationElement,
                $defaultLocationDistanceElement,
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('No. of categories to show. Enter 0 to show all categories.'),
                        'value' => 0,
                    )
                ),
                array(
                    'Text',
                    'listingCount',
                    array(
                        'label' => $view->translate('No. of listings to be shown in each category.'),
                        'value' => 5,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
                array(
                    'Select',
                    'popularity',
                    array(
                        'label' => $view->translate('Popularity Criteria'),
                        'multiOptions' => $category_listings_multioptions,
                        'value' => 'view_count',
                    )
                ),
                array(
                    'Select',
                    'interval',
                    array(
                        'label' => $view->translate('Popularity Duration (This duration will be applicable to all Popularity Criteria except Views.)'),
                        'multiOptions' => array('week' => '1 Week', 'month' => '1 Month', 'overall' => 'Overall'),
                        'value' => 'overall',
                    )
                ),
                array(
                    'Text',
                    'truncation',
                    array(
                        'label' => $view->translate('Title Truncation Limit'),
                        'value' => 25,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('AJAX Search for Listings'),
        'description' => $view->translate("This widget searches over Listing Titles via AJAX. The search interface is similar to Facebook search."),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.searchbox-sitereview',
        'defaultParams' => array(
            'title' => $view->translate("Search"),
            'titleCount' => "",
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeElement2,
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: Related Listings'),
        'description' => $view->translate('Displays a list of all listings related to the listing being viewed. The related listings are shown based on the tags and top-level category of the listing being viewed. You can choose the related listing criteria from the Edit Settings. This widget should be placed on the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.related-listings-view-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Related Listings'),
            'titleCount' => true,
            'statistics' => array("likeCount", "reviewCount")
        ),
        'adminForm' => array(
            'elements' => array(
                $ratingTypeElement,
                array(
                    'Radio',
                    'related',
                    array(
                        'label' => $view->translate('Choose which all Listings should be displayed here as Listings related to the current Listing.'),
                        'multiOptions' => array(
                            'tags' => $view->translate("Listings having same tag. (Note: 'Tags Field' should be enabled from Global Settings.)"),
                            'categories' => $view->translate('Listings associated with same \'Categories\'.')
                        ),
                        'value' => 'categories',
                    )
                ),
                array(
                    'Radio',
                    'viewType',
                    array(
                        'label' => $view->translate('Choose the View Type for listings.')
                        ,
                        'multiOptions' => array(
                            'listview' => $view->translate('List View'),
                            'gridview' => $view->translate('Grid View'),
                        ),
                        'value' => 'listview',
                    )
                ),
                array(
                    'Text',
                    'columnWidth',
                    array(
                        'label' => $view->translate('Column Width For Grid View.'),
                        'value' => '180',
                    )
                ),
                array(
                    'Text',
                    'columnHeight',
                    array(
                        'label' => $view->translate('Column Height For Grid View.'),
                        'value' => '328',
                    )
                ),
                $statisticsElement,
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of Listings to show)'),
                        'value' => 3,
                    )
                ),
                array(
                    'Text',
                    'truncation',
                    array(
                        'label' => $view->translate('Title Truncation Limit'),
                        'value' => 24,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: Editor Review / Overview / Description'),
        'description' => $view->translate("This widget forms a tab on the Multiple Listing Types - Listing Profile page which displays Editor Review / Overview / Description of the listing. If Editor Review is written, then the Editor Review will be shown in this block, otherwise Overview of the listing will display. If Overview is also not written, then the description of the listing will be shown. Multiple settings are available to customize this widget. This widget should be placed in Tabbed Blocks area of the Multiple Listing Types - Listing Profile page."),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.editor-reviews-sitereview',
        'autoEdit' => true,
        'defaultParams' => array(
            'titleEditor' => $view->translate("Review"),
            'titleOverview' => $view->translate("Overview"),
            'titleDescription' => $view->translate("Description"),
            'titleCount' => "",
            'loaded_by_ajax' => 1
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'titleEditor',
                    array(
                        'label' => $view->translate('Title for Editor Review'),
                        'value' => $view->translate("Review"),
                    )
                ),
                array(
                    'Text',
                    'titleOverview',
                    array(
                        'label' => $view->translate('Title for Overview'),
                        'value' => $view->translate("Overview"),
                    )
                ),
                array(
                    'Text',
                    'titleDescription',
                    array(
                        'label' => $view->translate('Title for Description'),
                        'value' => $view->translate("Description"),
                    )
                ),
                array(
                    'Hidden',
                    'title',
                    array('order' => 1044)
                ),
                array(
                    'Radio',
                    'loaded_by_ajax',
                    array(
                        'label' => $view->translate('Widget Content Loading'),
                        'description' => $view->translate('Do you want the content of this widget to be loaded via AJAX, after the loading of main webpage content? (Enabling this can improve webpage loading speed. Disabling this would load content of this widget along with the page content.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Radio',
                    'show_slideshow',
                    array(
                        'label' => $view->translate('Show Slideshow'),
                        'description' => $view->translate('Do you want to display listing photos slideshow in this block? (If you select \'Yes\', then users will be able to select photos and a video to be displayed in this slideshow from Photos and Videos section respectively of their Listing Dashboard. Note: If you enable this, then you should not place the \'Listing Profile: Listing Photos Slideshow\' widget on Multiple Listing Types - Listing Profile page.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Text',
                    'slideshow_height',
                    array(
                        'label' => $view->translate('Enter the height of the slideshow (in pixels).'),
                        'value' => 400,
                    ),
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                ),
                array(
                    'Text',
                    'slideshow_width',
                    array(
                        'label' => $view->translate('Enter the width of the slideshow (in pixels).'),
                        'value' => 600,
                    ),
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                ),
                array(
                    'Radio',
                    'showCaption',
                    array(
                        'label' => $view->translate('Do you want to show image description in this Slideshow?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Radio',
                    'showButtonSlide',
                    array(
                        'label' => "Do you want to show thumbnails for photos and video navigation in this Slideshow? (If you select No, then small circles will be shown at Slideshow bottom for slides navigation.)",
                        'multiOptions' => array(
                            1 => 'Yes',
                            0 => 'No'
                        ),
                        'value' => 0,
                    )
                ),
                array(
                    'Radio',
                    'mouseEnterEvent',
                    array(
                        'label' => "By which action do you want slides navigation to occur from thumbnails / small circles?",
                        'multiOptions' => array(
                            1 => 'Mouse-over',
                            0 => 'On-click'
                        ),
                        'value' => 0,
                    )
                ),
                array(
                    'Radio',
                    'thumbPosition',
                    array(
                        'label' => "Where do you want to show image thumbnails?",
                        'multiOptions' => array(
                            'bottom' => 'In the bottom of Slideshow',
                            'left' => 'In the left of Slideshow',
                            'right' => 'In the right of Slideshow',
                        ),
                        'value' => 'bottom',
                    )
                ),
                array(
                    'Radio',
                    'autoPlay',
                    array(
                        'label' => "Do you want the Slideshow to automatically start playing when Listing Profile page is opened?",
                        'multiOptions' => array(
                            1 => 'Yes',
                            0 => 'No'
                        ),
                        'value' => 0,
                    )
                ),
                array(
                    'Text',
                    'slidesLimit',
                    array(
                        'label' => $view->translate('How many slides you want to show in slideshow?'),
                        'value' => 20,
                    ),
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                ),
                array(
                    'Text',
                    'captionTruncation',
                    array(
                        'label' => $view->translate('Truncation limit for slideshow description'),
                        'value' => 200,
                    ),
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                ),
                array(
                    'Radio',
                    'showComments',
                    array(
                        'label' => $view->translate('Enable Comments'),
                        'description' => $view->translate('Do you want to enable comments in this widget? (If enabled, then users will be able to comment on the listing being viewed. Note: If you enable this, then you should not place the ‘Listing / Review Profile: Comments & Replies’ widget on Multiple Listing Types - Listing Profile page.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
            )
        )
    ),
    array(
        'title' => $view->translate('Editor / Member Profile: Profile Reviews'),
        'description' => $view->translate('Displays a list of all the reviews written by the editors / members of your site whose profile is being viewed. From Edit settings of this widget, you can choose to show Editor reviews or User Reviews in this widget. This widget should be placed in the Tabbed Blocks area of Multiple Listing Types - Editor Profile page or Member Profile page. '),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.editor-profile-reviews-sitereview',
        'autoEdit' => true,
        'defaultParams' => array(
            'title' => $view->translate("Reviews"),
            'titleCount' => "",
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'type',
                    array(
                        'label' => $view->translate('Review Type'),
                        'description' => $view->translate('Choose the type of reviews that you want to display in this widget.'),
                        'multiOptions' => array(
                            'user' => 'User Reviews',
                            'editor' => 'Editor Reviews'
                        ),
                        'value' => 'user',
                    ),
                ),
                array(
                    'Radio',
                    'onlyListingtypeEditorReviews',
                    array(
                        'label' => $view->translate('Do you want to show user reviews from all listing types?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No, show user reviews only from those listing types for which the member is editor of.')
                        ),
                        'value' => 1,
                    ),
                ),
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of reviews to show)'),
                        'value' => 10,
                    )
                ),
//                array(
//                    'Radio',
//                    'loaded_by_ajax',
//                    array(
//                        'label' => 'Widget Content Loading',
//                        'description' => 'Do you want the content of this widget to be loaded via AJAX, after the loading of main webpage content? (Enabling this can improve webpage loading speed. Disabling this would load content of this widget along with the page content.)',
//                        'multiOptions' => array(
//                            1 => 'Yes',
//                            0 => 'No'
//                        ),
//                        'value' => 1,
//                    )
//                ),                
            ),
        ),
    ),
    array(
        'title' => $view->translate('Editor / Member Profile: Comments & Replies'),
        'description' => $view->translate("Displays a list of all the comments and replies by the members on Listings  and Reviews on your site. This widget should be placed in the Tabbed Blocks area of Multiple Listing Types - Editor Profile page or Member Profile page."),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.editor-replies-sitereview',
        'defaultParams' => array(
            'title' => $view->translate("Replies"),
            'titleCount' => "",
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of comments & replies to show)'),
                        'value' => 5,
                    )
                ),
                array(
                    'Radio',
                    'onlyListingtypeEditor',
                    array(
                        'label' => $view->translate('Do you want to show comments and replies from all listing types?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No, show comments and replies only from those listing types for which the member is editor of.')
                        ),
                        'value' => 1,
                    ),
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Editor Profile: Editor’s Member Profile Photo'),
        'description' => $view->translate('Displays Editors’ member profile photo on their editor profile. This widget should be placed on Multiple Listing Types - Editor Profile page in the right / left column.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.editor-photo-sitereview',
        'defaultParams' => array(
            'title' => "",
            'titleCount' => "",
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Featured Editor'),
        'description' => $view->translate('Displays the Featured Editor on your site. Edit settings of this widget contains option to select Featured Editor.'),
        'category' => 'Multiple Listing Types',
        'autoEdit' => true,
        'type' => 'widget',
        'name' => 'sitereview.editor-featured-sitereview',
        'adminForm' => 'Sitereview_Form_Admin_Editors_Featured',
        'defaultParams' => array(
            'title' => $view->translate('Featured Editor'),
        ),
    ),
    array(
        'title' => $view->translate('Editors Home: Editors'),
        'description' => $view->translate("Displays a list of all the editors on site. This widget should be placed on 'Multiple Listing Types - Editors Home' page."),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.editors-home',
        'defaultParams' => array(
            'title' => "",
            'titleCount' => "",
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'superEditor',
                    array(
                        'label' => $view->translate('Show Super Editor.'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    ),
                ),
            ),
        )
    ),
    array(
        'title' => $view->translate('Editors Statistics'),
        'description' => $view->translate('Displays statistics of all the Editors on your site added by you from the \'Manage Editors\' section in the Admin Panel. (Note: Number of editors in each listing type will also be displayed in this widget, if you have \'Multiple Listing Types - Listing Type Creation Extension\' installed on your site.)'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.editors-home-statistics-sitereview',
        'defaultParams' => array(
            'title' => "",
            'titleCount' => "",
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        )
    ),
    array(
        'title' => $view->translate('Listing Profile: About Editor'),
        'description' => $view->translate('Displays the description (written by you from the \'Manage Editor\' section in the Admin Panel and Editors) about the Editor who has written \'Editor Review\' for the listing. This widget should be placed on the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.about-editor-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('About Me'),
            'titleCount' => "",
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Editor / Member Profile: About Editor'),
        'description' => $view->translate('Displays the description written by you (from the \'Manage Editors\' section in the Admin Panel) and Editors (using this widget) about the Editor whose Editor Profile is being viewed. This widget should be placed on the Multiple Listing Types - Editor Profile page or Member Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.editor-profile-info',
        'defaultParams' => array(
            'title' => $view->translate("About Me"),
            'titleCount' => "",
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'show_badge',
                    array(
                        'label' => $view->translate('Displays the  badge assigned by you from \'Manage Editors\' section.'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    ),
                ),
                array(
                    'Radio',
                    'show_designation',
                    array(
                        'label' => $view->translate('Displays the designation assigned by you from \'Manage Editors\' section.'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    ),
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Editor / Member Profile: Editor’s Name and Designation'),
        'description' => $view->translate('Displays the name and designation of the Editor whose profile is being viewed. This widget should be placed on the Multiple Listing Types - Editor Profile page or Member Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.editor-profile-title',
        'defaultParams' => array(
            'title' => "",
            'titleCount' => "",
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'show_designation',
                    array(
                        'label' => $view->translate('Do you want to display Editor’s designation in this block? (You can assign the designation from the ‘Manage Editors’ section of this plugin.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    ),
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: User Reviews'),
        'description' => $view->translate('This widget forms the User Reviews tab on the Multiple Listing Types - Listing Profile page and displays all the reviews written by the users of your site for the Listing being viewed. This widget should be placed in the Tabbed Blocks area of the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.user-sitereview',
        'defaultParams' => array(
            'title' => $view->translate("User Reviews"),
            'titleCount' => "true",
            'loaded_by_ajax' => 1
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'itemProsConsCount',
                    array(
                        'label' => $view->translate('Number of reviews’ Pros and Cons to be displayed in the search results using \'Only Pros\' and \'Only Cons\' in the \'Show\' review search bar.'),
                        'value' => 3,
                    )
                ),
                array(
                    'Text',
                    'itemReviewsCount',
                    array(
                        'label' => $view->translate('Number of user reviews to show'),
                        'value' => 3,
                    )
                ),
                array(
                    'Radio',
                    'loaded_by_ajax',
                    array(
                        'label' => $view->translate('Widget Content Loading'),
                        'description' => $view->translate('Do you want the content of this widget to be loaded via AJAX, after the loading of main webpage content? (Enabling this can improve webpage loading speed. Disabling this would load content of this widget along with the page content.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Review Profile: Review View'),
        'description' => $view->translate('Displays the main Review. You can configure various setting from Edit Settings of this widget. This widget should be placed on Multiple Listing Types - Review Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.profile-review-sitereview',
        'defaultParams' => array(
            'title' => 'Reviews',
            'titleCount' => true,
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: Breadcrumb'),
        'description' => $view->translate('Displays breadcrumb of the listing based on the categories. This widget should be placed on the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.list-profile-breadcrumb',
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: Listing Information & Options'),
        'description' => $view->translate('Displays listing profile photo with listing information and various action links that can be performed on the Listings from their Profile page (edit, delete, tell a friend, share, etc.). You can manage the Action Links available in this widget from the Menu Editor section by choosing Multiple Listing Types - Listing Profile Page Options Menu. You can choose various information options from the Edit settings of this widget. This widget should be placed on the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.list-information-profile',
        'defaultParams' => array(
            'title' => '',
            'showContent' => array("postedDate", "postedBy", "viewCount", "likeCount", "commentCount", "photo", "photosCarousel", "tags", "location", "description", "title", "compare", "wishlist", "reviewCreate", "endDate")
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'showContent',
                    array(
                        'label' => $view->translate('Select the information options that you want to be available in this block.'),
                        'multiOptions' => array("title" => "Title", "postedDate" => "Posted Date", "postedBy" => "Posted By", "viewCount" => "Views", "likeCount" => "Likes", "commentCount" => "Comments", "photo" => "Photo", "photosCarousel" => 'Photos Carousel (Note: Carousel will only be displayed, if the listing has atleast 2 photos and Photo option of this setting is enabled.)', "tags" => "Tags", "location" => "Location", "description" => "Description", "compare" => "Compare", "wishlist" => "Add to Wishlist", "reviewCreate" => "Write a review", "endDate" => "End Date"),
                    ),
                ),
                array(
                    'Radio',
                    'like_button',
                    array(
                        'label' => $view->translate('Do you want to enable Like button in this block?'),
                        'multiOptions' => $show_like_button,
                        'value' => $default_value,
                    ),
                ),
                array(
                    'Radio',
                    'actionLinks',
                    array(
                        'label' => $view->translate('Do you want action links like print, tell a friend, edit details, etc. to the available for the listings in this block?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Text',
                    'truncationDescription',
                    array(
                        'label' => $view->translate("Enter the trucation limit for the Listing Description. (If you want to show the full description, then enter '0'.)"),
                        'value' => 300,
                    )
                ),
            )
        )
    ),
    array(
        'title' => $view->translate('Listing Profile: Listing Rating'),
        'description' => $view->translate('This widget displays the overall rating given to the listing by editors, member of your site and other users along with the rating parameters as configured by you from the Reviews & Ratings section in the Admin Panel. You can choose who should be able to give review from the Admin Panel. Multiple settings are available to customize this widget. This widget should be placed in the left column on the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.overall-ratings',
        'defaultParams' => array(
            'title' => 'Reviews',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'show_rating',
                    array(
                        'label' => $view->translate('Select from below type of ratings to be displayed in this widget'),
                        'multiOptions' => array(
                            'avg' => $view->translate('Combined Editor and User Rating'),
                            'both' => $view->translate('Editor and User Ratings separately'),
                            'editor' => $view->translate('Only Editor Ratings'),
                        ),
                        'value' => 'avg',
                    ),
                ),
                array(
                    'Radio',
                    'ratingParameter',
                    array(
                        'label' => $view->translate('Do you want to show Rating Parameters in this widget?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
            ),
        )
    ),
    array(
        'title' => $view->translate('Listing Profile: Listing User Ratings'),
        'description' => $view->translate('This widget displays the overall ratings given by members of your site on the listing being currently viewed. This widget should be placed in the right / left column on the Multiple Listing Types - Listing Profile page. (This widget will only display when you have chosen \'Yes, allow Users to only rate listings.\' value for the field \'Allow Only User Ratings\' for the associated listing type.)'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.user-ratings',
        'defaultParams' => array(
            'title' => 'User Ratings',
            'titleCount' => true,
        ),
        'adminForm' => array(
        )
    ),
    array(
        'title' => $view->translate('Top Reviewers'),
        'description' => $view->translate('This widget shows the top reviewers for the listings on your site based on the number of reviews posted by them. Multiple settings are available for this widget.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.top-reviewers-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Top Reviewers'),
        ),
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                $listingTypeElement2,
                array(
                    'Select',
                    'type',
                    array(
                        'label' => $view->translate('Review Type'),
                        'description' => $view->translate('Choose the review type for which maximum reviewers should be shown in this widget.'),
                        'multiOptions' => array(
                            'overall' => $view->translate('Overall'),
                            'user' => $view->translate('User Reviews'),
                            'editor' => $view->translate('Editor Reviews')
                        ),
                        'value' => 'user'
                    )
                ),
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of reviewers to show)'),
                        'value' => 3,
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Top Posters'),
        'description' => $view->translate('This widget shows the top posters on your site based on the number of listings posted by them. Multiple settings are available for this widget.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.top-posters',
        'defaultParams' => array(
            'title' => $view->translate('Top Listing Posters'),
        ),
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                $listingTypeElement2,
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of posters to show)'),
                        'value' => 3,
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Editor Profile: Similar Editor'),
        'description' => $view->translate('Displays Editors similar to the Editors whose profile is being viewed. You can choose to display similar editors of a particular listing type, if you have "Multiple Listing Types - Listing Type Creation Extension" installed on your site. Multiple settings are available to customize this widget. This widget should be placed on Multiple Listing Types - Editor Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.editors-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Site Editors'),
        ),
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'viewType',
                    array(
                        'label' => $view->translate('Display Type'),
                        'multiOptions' => array(
                            '1' => $view->translate('Horizontal'),
                            '0' => $view->translate('Vertical'),
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of editors to show)'),
                        'value' => 4,
                    )
                ),
                $listingTypeElement2,
                array(
                    'Radio',
                    'superEditor',
                    array(
                        'label' => $view->translate('Show Super Editor.'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    ),
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Popular / Recent / Random Reviews'),
        'description' => $view->translate('Displays Reviews based on the Popularity Criteria and other settings that you choose for this widget. You can place this widget multiple times on a page with different popularity criterion chosen for each placement.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.popular-reviews-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Popular Reviews'),
            'statistics' => array("viewCount"),
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeElement2,
                array(
                    'Select',
                    'type',
                    array(
                        'label' => $view->translate('Review Type'),
                        'multiOptions' => array(
                            'overall' => $view->translate('All Reviews'),
                            'user' => $view->translate('User Reviews'),
                            'editor' => $view->translate('Editor Reviews'),
                        ),
                        'value' => 'user'
                    )
                ),
                array(
                    'Radio',
                    'status',
                    array(
                        'label' => $view->translate('Do you want to show only featured reviews.'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '0',
                    )
                ),
                array(
                    'Select',
                    'popularity',
                    array(
                        'label' => $view->translate('Popularity Criteria.(The popularity criterion: Most Helpful, Most Liked, Most Commented and Most Replied will not be applicable, if you have chosen Editor Reviews from the \'Review Type\' setting above.)'),
                        'multiOptions' => array(
                            'view_count' => $view->translate('Most Viewed'),
                            'like_count' => $view->translate('Most Liked'),
                            'comment_count' => $view->translate('Most Commented'),
                            'helpful_count' => $view->translate('Most Helpful'),
                            'reply_count' => $view->translate('Most Replied'),
                            'review_id' => $view->translate('Most Recent'),
                            'modified_date' => $view->translate('Recently Updated'),
                            'RAND()' => $view->translate('Random'),
                        ),
                        'value' => 'view_count',
                    )
                ),
                array(
                    'Select',
                    'interval',
                    array(
                        'label' => $view->translate('Popularity Duration (This duration will be applicable to these Popularity Criteria: Most Liked, Most Commented, Most Recent and Recently Updated)'),
                        'multiOptions' => array('week' => '1 Week', 'month' => '1 Month', 'overall' => 'Overall'),
                        'value' => 'overall',
                    )
                ),
                array(
                    'Radio',
                    'groupby',
                    array(
                        'label' => $view->translate('Show multiple reviews from the same editor / user.'),
                        'description' => $view->translate('(If selected "No", only one review will be displayed from a reviewer.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'MultiCheckbox',
                    'statistics',
                    array(
                        'label' => $view->translate('Choose the statistics to be displayed for the reviews in this widget. (Note: This settings will not work if you choose to show Editor Reviews from the "Review Type" setting above.)'),
                        'multiOptions' => array("viewCount" => $view->translate("Views"), "likeCount" => $view->translate("Likes"), "commentCount" => $view->translate("Comments"), 'replyCount' => $view->translate('Replies'), 'helpfulCount' => $view->translate('Helpful')),
                    //'value' => array("viewCount","likeCount"),
                    ),
                ),
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of reviews to show)'),
                        'value' => 3,
                    )
                ),
                array(
                    'Text',
                    'truncation',
                    array(
                        'label' => $view->translate('Title Truncation limit'),
                        'value' => 16,
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Browse Reviews: Search Reviews Form'),
        'description' => $view->translate('Displays the form for searching reviews. It is recommended to place this widget on Multiple Listing Types - Browse Reviews page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.review-browse-search',
        'defaultParams' => array(
            'title' => '',
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Browse Reviews: User Reviews Statistics'),
        'description' => $view->translate('Displays statistics for all the reviews written by the users of your site. This widget should be placed in the left column of the Multiple Listing Types - Browse Review page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.reviews-statistics',
        'defaultParams' => array(
            'title' => 'Reviews Statistics',
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Editor / Member Profile: Editor’s Reviews Statistics'),
        'description' => $view->translate('Displays statistics for all the editor reviews written by the Editor whose Editor Profile is being viewed. This widget should be placed on the Multiple Listing Types - Editor Profile page or Member Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.editor-profile-statistics',
        'defaultParams' => array(
            'title' => 'Editor Statistics',
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Review Profile: Breadcrumb'),
        'description' => $view->translate('Displays breadcrumb of the review based on the categories and the listing to which it belongs. This widget should be placed on the Multiple Listing Types - Review Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.profile-review-breadcrumb-sitereview',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Reviews & Ratings: Browse Wishlists'),
        'description' => $view->translate('Displays a list of wishlists created by adding listings on your site. This widget should be placed on "Multiple Listing Types - Browse Wishlists" page. (Note: This widget will not render if admin enabled the "Add to Favourites" setting from the "Global Settings" section in the Admin Panel.)'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.wishlist-browse',
        'defaultParams' => array(
            'title' => '',
            'viewTypes' => array("list", "grid"),
            'statisticsWishlist' => array("viewCount", "likeCount", "followCount", "entryCount"),
            'viewTypeDefault' => 'list',
            'listThumbsCount' => 4,
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'viewTypes',
                    array(
                        'label' => $view->translate('Choose the view types.'),
                        'multiOptions' => array("list" => $view->translate("List View"), "grid" => $view->translate("Pinboard View")),
                    ),
                ),
                array(
                    'Radio',
                    'viewTypeDefault',
                    array(
                        'label' => $view->translate('Choose the default view type'),
                        'multiOptions' => array("list" => $view->translate("List View"), "grid" => $view->translate("Pinboard View")),
                        'value' => 'list',
                    )
                ),
                array(
                    'MultiCheckbox',
                    'followLike',
                    array(
                        'label' => $view->translate('Choose the action link to be available for each Wishlist pinboard item.'),
                        'multiOptions' => array(
                            'follow' => $view->translate('Follow / Unfollow'),
                            'like' => $view->translate('Like / Unlike'),
                        ),
                    )
                ),
                $statisticsWishlistElement,
                array(
                    'Text',
                    'listThumbsCount',
                    array(
                        'label' => $view->translate('Enter the number of listing thumbnails to be shown along with the cover photo of a wishlist. (This setting will only work, if you have chosen Pinboard View from the above setting.)'),
                        'value' => 4,
                    )
                ),
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Number of wishlists to show per page'),
                        'value' => 20,
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Search Wishlists Form'),
        'description' => $view->translate('Displays the form for searching wishlists. It is recommended to place this widget on Multiple Listing Types - Browse Wishlists page. (Note: This widget will not render if admin enabled the "Add to Favourites" setting from the "Global Settings" section in the Admin Panel.)'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.wishlist-browse-search',
        'defaultParams' => array(
            'title' => '',
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'viewType',
                    array(
                        'label' => $view->translate('Form Type'),
                        'multiOptions' => array(
                            'horizontal' => $view->translate('Horizontal'),
                            'vertical' => $view->translate('Vertical'),
                        ),
                        'value' => 'horizontal'
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Popular / Recent / Random Wishlists'),
        'description' => $view->translate('Displays Wishlists based on the Popularity Criteria and other settings that you choose for this widget. You can place this widget multiple times on a page with different popularity criterion chosen for each placement. (Note: This widget will not render if admin enabled the "Add to Favourites" setting from the "Global Settings" section in the Admin Panel.)'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.wishlist-listings',
        'autoEdit' => true,
        'defaultParams' => array(
            'title' => $view->translate('Popular Wishlists'),
            'statisticsWishlist' => array("followCount", "entryCount"),
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'type',
                    array(
                        'label' => $view->translate('Show Wishlists of:'),
                        'multiOptions' => array(
                            'friends' => $view->translate('Currently logged-in member’s friends.'),
                            'viewer' => $view->translate('Currently logged-in member.'),
                            'none' => $view->translate('Everyone')
                        ),
                        'value' => 'none'
                    )
                ),
                array(
                    'Select',
                    'orderby',
                    array(
                        'label' => $view->translate('Popularity Criteria'),
                        'multiOptions' => array(
                            'total_item' => $view->translate('Having maximum number of Listings'),
                            'creation_date' => $view->translate('Most Recent'),
                            'view_count' => $view->translate('Most Viewed'),
                            'like_count' => $view->translate('Most Liked'),
                            'follow_count' => $view->translate('Most Followed'),
                            'RAND()' => $view->translate('Random')
                        ),
                        'value' => 'creation_date',
                    )
                ),
                $statisticsWishlistElement,
                array(
                    'Text',
                    'limit',
                    array(
                        'label' => $view->translate('Number of wishlists to show'),
                        'value' => 3,
                    )
                ),
                array(
                    'Text',
                    'truncation',
                    array(
                        'label' => $view->translate('Title Truncation limit'),
                        'value' => 16,
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Create a New Wishlist'),
        'description' => $view->translate('Displays the link to Create a New Wishlist. (Note: This widget will not render if admin enabled the "Add to Favourites" setting from the "Global Settings" section in the Admin Panel.)'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.wishlist-creation-link',
        'defaultParams' => array(
            'title' => '',
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Add to Wishlist'),
        'description' => $view->translate('Displays the link to Add to Wishlist. (Note: This widget will display "My Favourites" link if admin enabled the "Add to Favourite" setting from the "Global Settings" section in the Admin Panel.)'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.wishlist-add-link',
        'defaultParams' => array(
            'title' => '',
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Wishlist Profile: Added Listings'),
        'description' => $view->translate('Displays a list of all the listings added in the wishlist being viewed. This widget should be placed on the Multiple Listing Types - Wishlist Profile page. (Note: This widget will list all the entries added by you as favourites if admin enabled the "Add to Favourites" setting from the "Global Settings" section in the Admin Panel.)'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.wishlist-profile-items',
        'autoEdit' => true,
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
            'shareOptions' => array("siteShare", "friend", "report", "print", "socialShare"),
            'viewTypes' => array("list", "pin"),
            'viewTypeDefault' => 'pin',
            'statistics' => array("likeCount", "reviewCount"),
            'statisticsWishlist' => array("viewCount", "likeCount", "followCount", "entryCount"),
            'show_buttons' => array("wishlist", "comment", "like", "share", "facebook", "pinit")
        ),
        'adminForm' => array(
            'elements' => array(
                $ratingTypeElement,
                array(
                    'Radio',
                    'postedby',
                    array(
                        'label' => $view->translate('Show posted by option. (Selecting "Yes" here will display the member\'s name who has created the wishlist.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'MultiCheckbox',
                    'shareOptions',
                    array(
                        'label' => $view->translate('Select the options that you want to display in this block.'),
                        'multiOptions' => array("siteShare" => "Site Share", "friend" => "Tell a Friend", "report" => "Report", 'print' => 'Print', 'socialShare' => 'Social Share'),
                    //'value' => array("siteShare","friend","report","print","socialShare"),
                    ),
                ),
                array(
                    'MultiCheckbox',
                    'viewTypes',
                    array(
                        'label' => $view->translate('Choose the view types.'),
                        'multiOptions' => array("list" => $view->translate("List View"), "pin" => $view->translate("Pinboard View")),
                    ),
                ),
                array(
                    'Radio',
                    'viewTypeDefault',
                    array(
                        'label' => $view->translate('Choose the default view type'),
                        'multiOptions' => array("list" => $view->translate("List View"), "pin" => $view->translate("Pinboard View")),
                        'value' => 'pin',
                    )
                ),
                array(
                    'Text',
                    'itemWidth',
                    array(
                        'label' => $view->translate('One listing Width'),
                        'description' => $view->translate('Enter the width for each pinboard item.'),
                        'value' => 237,
                    )
                ),
                array(
                    'Radio',
                    'withoutStretch',
                    array(
                        'label' => $view->translate('Do you want to display the images without stretching them to the width of each wishlist block? (This setting will only work, if you have chosen Pinboard View from the above setting.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '0',
                    )
                ),
                $statisticsElement,
                $statisticsWishlistElement,
                array(
                    'Radio',
                    'postedbyInList',
                    array(
                        'label' => $view->translate('Show posted by option. (Selecting "Yes" here will display the member\'s name who has created the listing in pinboard view.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'MultiCheckbox',
                    'followLike',
                    array(
                        'label' => $view->translate('Choose the action link to be available for wishlists displayed in this block.'),
                        'multiOptions' => array(
                            'follow' => $view->translate('Follow / Unfollow'),
                            'like' => $view->translate('Like / Unlike'),
                        ),
                    )
                ),
                array(
                    'MultiCheckbox',
                    'show_buttons',
                    array(
                        'label' => $view->translate('Choose the action links that you want to be available for each Listing pinboard item.'),
                        'multiOptions' => array("wishlist" => "Wishlist", "compare" => "Compare", "comment" => "Comment", "like" => "Like / Unlike", 'share' => 'Share', 'facebook' => 'Facebook', 'twitter' => 'Twitter', 'pinit' => 'Pin it', 'tellAFriend' => 'Tell a Friend', 'print' => 'Print')
                    ),
                ),
                array(
                    'Text',
                    'truncationDescription',
                    array(
                        'label' => $view->translate("Enter the trucation limit for the Listing Description. (If you want to hide the description, then enter '0'.)"),
                        'value' => 100,
                    )
                ),
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of Listings to show)'),
                        'value' => 10,
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Browse Listings\' Locations'),
        'description' => $view->translate("Displays a list of all the listings having location entered corresponding to them on the site. This widget should be placed on Multiple Listing Types - Browse Listings' Location page."),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.browselocation-sitereview',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Search Listings Location Form'),
        'description' => $view->translate('Displays the form for searching Listings corresponding to location on the basis of various filters. This widget should be placed in the Multiple Listing Types - Browse Listing’s Location page'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.location-search',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'street',
                    array(
                        'label' => $view->translate('Show street option.'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Radio',
                    'city',
                    array(
                        'label' => $view->translate('Show city option.'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Radio',
                    'state',
                    array(
                        'label' => $view->translate('Show state option.'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Radio',
                    'country',
                    array(
                        'label' => $view->translate('Show country option.'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Profile: Listing Photos Carousel'),
        'description' => $view->translate('Displays photo thumbnails in an attractive carousel, clicking on which opens the photo in lightbox. This widget should be placed on the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.photos-carousel',
        'defaultParams' => array(
            'title' => '',
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of photos to show)'),
                        'value' => 2,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    ),
                ),
            ),
        ),
        'requirements' => array(
            'subject' => 'sitereview',
        ),
    ),
    array(
        'title' => $view->translate('Listing / Review Profile: Comments & Replies'),
        'description' => $view->translate('Enable users to comment and reply on the listing / review being viewed. Displays all the comments and replies on the listings / reviews. This widget should be placed on Multiple Listing Types - Listing Profile page or Multiple Listing Types - Review Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'seaocore.seaocores-nestedcomments',
        'defaultParams' => array(
            'title' => $view->translate('Comments')
        ),
        'requirements' => array(
            'subject',
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Types / Category Navigation Bar'),
        'description' => $view->translate('Displays listing types / categories in this block. You can configure various settings for this widget from the Edit settings.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.listtypes-categories',
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                $listingTypeElement2,
                array(
                    'Radio',
                    'viewDisplayHR',
                    array(
                        'label' => $view->translate('Select the placement position of the navigation bar'),
                        'multiOptions' => array(
                            1 => $view->translate('Horizontal (If you have chosen a listing type in the above setting, then categories of that listing type will be displayed otherwise all the listing types will be displayed with their categories on mouseover on listing type names.)'),
                            0 => $view->translate('Vertical (Category hierarchy of the listing type chosen in the above setting will be displayed.)')
                        ),
                        'value' => 1,
                    )
                ),
                array(
                    'Hidden',
                    'nomobile',
                    array(
                        'label' => '',
                        'order' => 1045
                    )
                ),
        ))
    ),
    array(
        'title' => $view->translate('Categories Banner'),
        'description' => $view->translate('Displays banners for categories, sub-categories and 3rd level categories on Multiple Listing Types - Browse Listings page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => false,
        'name' => 'sitereview.categories-banner-sitereview',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => $view->translate('Review Profile: Owner Reviews'),
        'description' => $view->translate('Displays the other reviews posted by the owner of the review which is being viewed. This widget should be placed on Multiple Listing Types - Review Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.ownerreviews-sitereview',
        'autoEdit' => true,
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
            'statistics' => array("likeCount", "replyCount", "commentCount")
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'statistics',
                    array(
                        'label' => $view->translate('Choose the statistics to be displayed for the reviews in this widget.'),
                        'multiOptions' => array("viewCount" => "Views", "likeCount" => "Likes", "commentCount" => "Comments", 'replyCount' => 'Replies', 'helpfulCount' => 'Helpful'),
                    //'value' => array("likeCount","replyCount","commentCount"),
                    ),
                ),
                array(
                    'Text',
                    'count',
                    array(
                        'label' => $view->translate('Number of reviews to show'),
                        'value' => 3,
                    )
                ),
                array(
                    'Text',
                    'truncation',
                    array(
                        'label' => $view->translate('Title Truncation Limit'),
                        'value' => 24,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Browse Listings: Pinboard View'),
        'description' => $view->translate('Displays listings in Pinboard View on the Listings Browse page. Multiple settings are available to customize this widget.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.pinboard-browse',
        'defaultParams' => array(
            'title' => $view->translate('Recent'),
            'statistics' => array("likeCount", "reviewCount"),
            'show_buttons' => array("wishlist", "compare", "comment", "like", 'share', 'facebook', 'twitter', 'pinit')
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeCategoryElement,
                $ratingTypeElement,
                $detactLocationElement,
                $defaultLocationDistanceElement,
                $statisticsElement,
                array(
                    'Radio',
                    'postedby',
                    array(
                        'label' => $view->translate('Show posted by option. (Selecting "Yes" here will display the member\'s name who has created the listing.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'Radio',
                    'userComment',
                    array(
                        'label' => $view->translate('Do you want to show user comments and enable user to post comment or not?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    ),
                ),
                array(
                    'Select',
                    'showPrice',
                    array(
                        'label' => $view->translate('Show price option. (Selecting "Yes" here will display the price of the listing.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '0',
                    )
                ),
                array(
                    'Select',
                    'showLocation',
                    array(
                        'label' => $view->translate('Show location option. (Selecting "Yes" here will display the location of the listing.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '0',
                    )
                ),
                array(
                    'Select',
                    'autoload',
                    array(
                        'label' => $view->translate('Do you want to enable auto-loading of old pinboard items when users scroll down to the bottom of this page?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '1'
                    )
                ),
                array(
                    'Select',
                    'defaultLoadingImage',
                    array(
                        'label' => $view->translate('Do you want to show a Loading image when this widget renders on a page?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1
                    )
                ),
                array(
                    'Text',
                    'itemWidth',
                    array(
                        'label' => $view->translate('One Item Width'),
                        'description' => $view->translate('Enter the width for each pinboard item.'),
                        'value' => 237,
                    )
                ),
                array(
                    'Radio',
                    'withoutStretch',
                    array(
                        'label' => $view->translate('Do you want to display the images without stretching them to the width of each pinboard item?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '0',
                    )
                ),
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of Listings to show)'),
                        'value' => 12,
                    )
                ),
                array(
                    'Text',
                    'noOfTimes',
                    array(
                        'label' => $view->translate('Auto-Loading Count'),
                        'description' => $view->translate('Enter the number of times that auto-loading of old pinboard items should occur on scrolling down. (Select 0 if you do not want such a restriction and want auto-loading to occur always. Because of auto-loading on-scroll, users are not able to click on links in footer; this setting has been created to avoid this.)'),
                        'value' => 0,
                    )
                ),
                array(
                    'MultiCheckbox',
                    'show_buttons',
                    array(
                        'label' => $view->translate('Choose the action links that you want to be available for the Listings displayed in this block. (This setting will only work, if you have chosen Pinboard View from the above setting.)'),
                        'multiOptions' => array("wishlist" => "Wishlist", "compare" => "Compare", "comment" => "Comment", "like" => "Like / Unlike", 'share' => 'Share', 'facebook' => 'Facebook', 'twitter' => 'Twitter', 'pinit' => 'Pin it', 'tellAFriend' => 'Tell a Friend', 'print' => 'Print')
                    ),
                ),
                array(
                    'Text',
                    'truncationDescription',
                    array(
                        'label' => $view->translate("Enter the trucation limit for the Listing Description. (If you want to hide the description, then enter '0'.)"),
                        'value' => 100,
                    )
                ),
            ),
        ),
    ),    
    array(
        'title' => $view->translate('Listings Home: Pinboard View'),
        'description' => $view->translate('Displays listings in Pinboard View on the Listings Home page. Multiple settings are available to customize this widget.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.pinboard-listings-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Recent'),
            'statistics' => array("likeCount", "reviewCount"),
            'show_buttons' => array("wishlist", "compare", "comment", "like", 'share', 'facebook', 'twitter', 'pinit')
        ),
        'adminForm' => array(
            'elements' => array(
                $listingTypeCategoryElement,
                $ratingTypeElement,
                $featuredSponsoredElement,
                $detactLocationElement,
                $defaultLocationDistanceElement,
                array(
                    'Select',
                    'popularity',
                    array(
                        'label' => $view->translate('Popularity Criteria'),
                        'multiOptions' => $popularity_options,
                        'value' => 'creation_date',
                    )
                ),
                array(
                    'Select',
                    'interval',
                    array(
                        'label' => $view->translate('Popularity Duration (This duration will be applicable to these Popularity Criteria:  Most Liked, Most Commented, Most Rated and Most Recent.)'),
                        'multiOptions' => array('week' => '1 Week', 'month' => '1 Month', 'overall' => 'Overall'),
                        'value' => 'overall',
                    )
                ),
                $categoryElement,
                $hiddenCatElement,
                $hiddenSubCatElement,
                $hiddenSubSubCatElement,
                $statisticsElement,
                array(
                    'Radio',
                    'postedby',
                    array(
                        'label' => $view->translate('Show posted by option. (Selecting "Yes" here will display the member\'s name who has created the listing.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'Radio',
                    'userComment',
                    array(
                        'label' => $view->translate('Do you want to show user comments and enable user to post comment or not?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1,
                    ),
                ),
                array(
                    'Select',
                    'price',
                    array(
                        'label' => $view->translate('Show price option. (Selecting "Yes" here will display the price of the listing.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '0',
                    )
                ),
                array(
                    'Select',
                    'location',
                    array(
                        'label' => $view->translate('Show location option. (Selecting "Yes" here will display the location of the listing.)'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '0',
                    )
                ),
                array(
                    'Select',
                    'autoload',
                    array(
                        'label' => $view->translate('Do you want to enable auto-loading of old pinboard items when users scroll down to the bottom of this page?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '1'
                    )
                ),
                array(
                    'Select',
                    'defaultLoadingImage',
                    array(
                        'label' => $view->translate('Do you want to show a Loading image when this widget renders on a page?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => 1
                    )
                ),
                array(
                    'Text',
                    'itemWidth',
                    array(
                        'label' => $view->translate('One Item Width'),
                        'description' => $view->translate('Enter the width for each pinboard item.'),
                        'value' => 237,
                    )
                ),
                array(
                    'Radio',
                    'withoutStretch',
                    array(
                        'label' => $view->translate('Do you want to display the images without stretching them to the width of each pinboard item?'),
                        'multiOptions' => array(
                            1 => $view->translate('Yes'),
                            0 => $view->translate('No')
                        ),
                        'value' => '0',
                    )
                ),
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of Listings to show)'),
                        'value' => 12,
                    )
                ),
                array(
                    'Text',
                    'noOfTimes',
                    array(
                        'label' => $view->translate('Auto-Loading Count'),
                        'description' => $view->translate('Enter the number of times that auto-loading of old pinboard items should occur on scrolling down. (Select 0 if you do not want such a restriction and want auto-loading to occur always. Because of auto-loading on-scroll, users are not able to click on links in footer; this setting has been created to avoid this.)'),
                        'value' => 0,
                    )
                ),
                array(
                    'MultiCheckbox',
                    'show_buttons',
                    array(
                        'label' => $view->translate('Choose the action links that you want to be available for the Listings displayed in this block. (This setting will only work, if you have chosen Pinboard View from the above setting.)'),
                        'multiOptions' => array("wishlist" => "Wishlist", "compare" => "Compare", "comment" => "Comment", "like" => "Like / Unlike", 'share' => 'Share', 'facebook' => 'Facebook', 'twitter' => 'Twitter', 'pinit' => 'Pin it', 'tellAFriend' => 'Tell a Friend', 'print' => 'Print')
                    //'value' =>array("viewCount","likeCount","commentCount","reviewCount"),
                    ),
                ),
                array(
                    'Text',
                    'truncationDescription',
                    array(
                        'label' => $view->translate("Enter the trucation limit for the Listing Description. (If you want to hide the description, then enter '0'.)"),
                        'value' => 100,
                    )
                ),
            ),
        ),
    ),
);

if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('communityad')) {
  $ads_Array = array(
      array(
          'title' => $view->translate('Review Ads Widget'),
          'description' => $view->translate('Displays community ads and links to view various pages of Advertisements / Community Ads plugin.'),
          'category' => 'Multiple Listing Types',
          'type' => 'widget',
          'name' => 'sitereview.review-ads',
          'defaultParams' => array(
              'title' => '',
              'titleCount' => true,
          ),
          'adminForm' => array(
              'elements' => array(
              ),
          ),
          ));
}

$video_widgets = array(
    array(
        'title' => $view->translate('Video View Page: People Also Liked'),
        'description' => $view->translate('Displays a list of other Listing Videos that the people who liked this Listing Video also liked. You can choose the number of entries to be shown. This widget should be placed on Multiple Listing Types - Video View Page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.show-also-liked',
        'defaultParams' => array(
            'title' => $view->translate('People Also Liked'),
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of videos to show)'),
                        'value' => 3,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    ),
                ),
            ),
        ),
        'requirements' => array(
            'subject' => 'sitereview',
        ),
    ),
    array(
        'title' => $view->translate('Video View Page: Other Videos From Listing'),
        'description' => $view->translate('Displays a list of other Listing Videos corresponding to the Listing of which the video is being viewed. You can choose the number of entries to be shown. This widget should be placed on Multiple Listing Types - Video View Page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.show-same-poster',
        //'isPaginated' => true,
        'defaultParams' => array(
            'title' => $view->translate('Other Videos From Listing'),
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of videos to show)'),
                        'value' => 3,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    ),
                ),
            ),
        ),
        'requirements' => array(
            'subject' => 'sitereview',
        ),
    ),
    array(
        'title' => $view->translate('Video View Page: Similar Videos'),
        'description' => $view->translate('Displays Listing Videos similar to the Listing Video being viewed based on tags. You can choose the number of entries to be shown. This widget should be placed on Multiple Listing Types - Video View Page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.show-same-tags',
        'defaultParams' => array(
            'title' => $view->translate('Similar Videos'),
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'itemCount',
                    array(
                        'label' => $view->translate('Count'),
                        'description' => $view->translate('(number of videos to show)'),
                        'value' => 3,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    ),
                ),
            ),
        ),
    ),
    array(
        'title' => $view->translate('Listing Video View'),
        'description' => $view->translate("This widget should be placed on the Multiple Listing Types - Video View page."),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.video-content',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => 'Create a Claim',
        'description' => 'This widget allow members to create a claim for listing type.  This widget should be placed on Multiple Listing Types - Claim a Listing page.',
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.create-claim',
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    ),
    array(
        'title' => 'My Claimed Listings',
        'description' => 'Display a list of all the listings claimed by member.  This widget should be placed on Multiple Listing Types - My Claimed Listing page.',
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.claimed-listings',
        'adminForm' => array(
            'elements' => array(
            ),
        ),
    )
);

if ($listingTypeCount > 1) {

  $sitereviewlistingtype_widgets = array(array(
          'title' => $view->translate('Listing Types & Categories Hierarchy (sidebar)'),
          'description' => $view->translate('Displays the Listing Type along with their categories and sub-categories in an expandable form. Clicking on them will redirect the viewer to Multiple Listing Types - Browse Listings page displaying the list of Listings created in that listing type / category (Note: This widget will only be displayed if you have atleast 2 listing types on your site.). It is recommended to place this widget in the right / left column.'),
          'category' => 'Multiple Listing Types',
          'type' => 'widget',
          'name' => 'sitereview.categories-home-sidebar',
          'defaultParams' => array(
              'title' => $view->translate('Explore Listing Types'),
              'titleCount' => true,
          ),
          'adminForm' => array(
              'elements' => array(
              ),
          ),
          ));

  $final_array = array_merge($final_array, $sitereviewlistingtype_widgets);
}

if (empty($type_video)) {
  $final_array = array_merge($final_array, $video_widgets);
}

if (!empty($ads_Array)) {
  $final_array = array_merge($final_array, $ads_Array);
}

return $final_array;
