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

if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proximity.search.kilometer', 0)) {
  $locationDescription = "Choose the kilometers within which listings will be displayed. (This setting will only work, if you have chosen 'Yes' in the above setting.)";
  $locationLableS = "Kilometer";
  $locationLable = "Kilometers";
} else {
  $locationDescription = "Choose the miles within which listings will be displayed. (This setting will only work, if you have chosen 'Yes' in the above setting.)";
  $locationLableS = "Mile";
  $locationLable = "Miles";
}

$detactLocationElement =   array(
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

$type_video = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.show.video');

$category_listings_multioptions = array(
    'view_count' => $view->translate('Views'),
    'like_count' => $view->translate('Likes'),
    'comment_count' => $view->translate('Comments'),
    'review_count' => $view->translate('Reviews'),
);

// //CHECK IF FACEBOOK PLUGIN IS ENABLE
// $fbmodule = Engine_Api::_()->getDbtable('modules', 'core')->getModule('facebookse');
// 
// if (!empty($fbmodule) && !empty($fbmodule->enabled) && $fbmodule->version > '4.2.7p1') {
//   $show_like_button = array(
//       '1' => $view->translate('Yes, show SocialEngine Core Like button'),
//       '2' => $view->translate('Yes, show Facebook Like button'),
//       '0' => $view->translate('No'),
//   );
//   $default_value = 2;
// } else {
  $show_like_button = array(
      '1' => $view->translate('Yes, show SocialEngine Core Like button'),
      '0' => $view->translate('No'),
  );
  $default_value = 1;
// }

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
      )
  );

  $listingTypeElement2 = array(
      'Hidden',
      'listingtype_id',
      array(
          'label' => $view->translate('Listing Type'),
          'value' => 1,
      )
  );

  $listingTypeCategoryElement = array(
      'Hidden',
      'listingtype_id',
      array(
          'label' => $view->translate('Listing Type'),
          'value' => 1,
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
      )
  );

  $listingTypeElement2 = array(
      'Hidden',
      'listingtype_id',
      array(
          'label' => $view->translate('Listing Type'),
          'value' => 1,
      )
  );

  $listingTypeCategoryElement = array(
      'Hidden',
      'listingtype_id',
      array(
          'label' => $view->translate('Listing Type'),
          'value' => 1,
      )
  );
}

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
        ),
        'value' => '',
    )
);

$categoryElement = array(
    'Select',
    'category_id',
    array(
        'RegisterInArrayValidator' => false,
        'decorators' => array(array('ViewScript', array(
                    'viewScript' => 'application/modules/Sitereview/views/scripts/_category.tpl',
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

return array(
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
                    'category_icon_view',
                    array(
                        'label' => $view->translate('Do you want icon view for categories?'),
                        'multiOptions' => array("1" => "yes", "0" => "No"),
                        'value' => 0
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
        'title' => $view->translate('Browse Wishlists'),
        'description' => $view->translate('Displays a list of all the wishlists on your site. This widget should be placed on the Multiple Listing Types - Browse Wishlists page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'sitereview.wishlist-browse',
        'defaultParams' => array(
            'title' => '',
            'statisticsWishlist' => array("viewCount", "likeCount", "followCount", "entryCount"),
        ),
        'adminForm' => array(
            'elements' => array(
                $statisticsWishlistElement,
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
        'title' => $view->translate('Wishlist Profile: Added Listings'),
        'description' => $view->translate('Displays a list of all the listings added in the wishlist being viewed. This widget should be placed on the Multiple Listing Types - Wishlist Profile page.'),
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
                $statisticsWishlistElement,
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
        'title' => $view->translate('Navigation Tabs'),
        'description' => $view->translate('Displays the Navigation tabs for \'Multiple Listing Types Plugin\' having links of Products, Editors, Wishlists etc. This widget should be placed at the top of \'Multiple Listing Types - Editors Home\', \'Multiple Listing Types - Categories Home\', \'Multiple Listing Types - Listings Home\', \'Multiple Listing Types - Browse Products\', \'Multiple Listing Types - Browse Products\' Locations\' and \'Multiple Listing Types - Browse Reviews\' page.'),
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
        'title' => $view->translate('Browse Reviews: Search Reviews Form'),
        'description' => $view->translate('Displays the form for searching reviews. It is recommended to place this widget on Multiple Listing Types - Browse Reviews page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.review-browse-search',
        'defaultParams' => array(
            'title' => '',
        ),
       'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'search',
                    array(
                        'label' => $view->translate('Select the display type for Search.'),
                        'multiOptions' => array(
                            1 => $view->translate('Only Search Text field'),
                            3 => $view->translate('Expanded Advanced Search'),
                            2 => $view->translate('Search Text field with expandable Advanced Search options'),
                        ),
                        'value' => 2,
                    )
                )
            )
        )
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
                array(
                    'MultiCheckbox',
                    'layouts_views',
                    array(
                        'label' => $view->translate('Choose the view types that you want to be available for listings on the home and browse pages of listings.'),
                        'multiOptions' => array("listview" => "List View", "gridview" => "Grid View")
                    ),
                ),
                array(
                    'Radio',
                    'viewType',
                    array(
                        'label' => $view->translate('Select a default view type for Listings.'),
                        'multiOptions' => array(
                            'listview' => $view->translate('List View'),
                            'gridview' => $view->translate('Grid View'),
                        ),
                        'value' => 'gridview',
                    )
                ),
                array(
                    'MultiCheckbox',
                    'showContent',
                    array(
                        'label' => $view->translate('Select the information options that you want to be available in this block.'),
                        'multiOptions' => array("price" => "Price", "location" => "Location", "endDate" => "End / Expiry Date", "postedDate" => "Posted Date"),
                    ),
                ),
                
                array(
                    'Radio',
                    'bottomLine',
                    array(
                        'label' => $view->translate('Choose from below what you want to display with listing title for List View.'),
                        'multiOptions' => array(
                            '1' => $view->translate("Editor Review's Bottom line (If there is no Editor Review, then Listing's description will be displayed.)"),
                            '0' => $view->translate("Listing's description"),
                            '2' => $view->translate("Not Any")
                        ),
                        'value' => '1',
                    )
                ),
                
                array(
                    'Radio',
                    'bottomLineGrid',
                    array(
                        'label' => $view->translate('Choose from below what you want to display with listing title for Grid View.'),
                        'multiOptions' => array(
                            '1' => $view->translate("Editor Review's Bottom line (If there is no Editor Review, then Listing's description will be displayed.)"),
                            '0' => $view->translate("Listing's description"),
                            '2' => $view->translate("Not Any")
                        ),
                        'value' => '2',
                    )
                ),
                array(
                    'Text',
                    'columnHeight',
                    array(
                        'label' => $view->translate('Column Height For Grid View.'),
                        'value' => '228',
                    )
                ),
                array(
                    'Select',
                    'popularity',
                    array(
                        'label' => $view->translate('Popularity Criteria'),
                        'multiOptions' => array_merge($popularity_options, array('end_date' => 'Expiring Soon (having end date)')),
                        'value' => 'view_count',
                    )
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
                    'truncationList',
                    array(
                        'label' => $view->translate('Title Truncation Limit in List View'),
                        'value' => 100,
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
                        'value' => 100,
                        'validators' => array(
                            array('Int', true),
                            array('GreaterThan', true, array(0)),
                        ),
                    )
                ),
                $detactLocationElement,
                $defaultLocationDistanceElement,
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
                        'multiOptions' => array("1" => $view->translate("List View"), "2" => $view->translate("Grid View")),
                    //'value' => array("0" => "1", "1" => "2", "2" => "3"),
                    ),
                ),
                array(
                    'Radio',
                    'layouts_order',
                    array(
                        'label' => $view->translate('Select a default view type for Listings.'),
                        'multiOptions' => array("1" => $view->translate("List View"), "2" => $view->translate("Grid View")),
                        'value' => 2,
                    )
                ),
//                array(
//                    'Text',
//                    'columnWidth',
//                    array(
//                        'label' => $view->translate('Column Width For Grid View.'),
//                        'value' => '180',
//                    )
//                ),
                array(
                    'Text',
                    'columnHeight',
                    array(
                        'label' => $view->translate('Column Height For Grid View.'),
                        'value' => '228',
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
//                array(
//                    'Radio',
//                    'viewType',
//                    array(
//                        'label' => $view->translate("Do you want to show 'Where to Buy' options associated with the listings in this block? (Note: If you select 'Yes' below, then you should place this widget in the Right Extended / Left Extended Column.)"),
//                        'multiOptions' => array(
//                            '1' => $view->translate('Yes'),
//                            '0' => $view->translate('No'),
//                        ),
//                        'value' => '1',
//                    )
//                ),
                array(
                    'Radio',
                    'bottomLine',
                    array(
                        'label' => $view->translate('Choose from below what you want to display with listing title for List View.'),
                        'multiOptions' => array(
                            '1' => $view->translate("Editor Review's Bottom line (If there is no Editor Review, then Listing's description will be displayed.)"),
                            '0' => $view->translate("Listing's description"),
                            '2' => $view->translate("Not Any")
                        ),
                        'value' => '1',
                    )
                ),
                array(
                    'MultiCheckbox',
                    'showContent',
                    array(
                        'label' => $view->translate('Select the information options that you want to be available in this block.'),
                        'multiOptions' => array("price" => "Price", "location" => "Location", "endDate" => "End / Expiry Date", "postedDate" => "Posted Date"),
                    ),
                ),
                array(
                    'Radio',
                    'bottomLineGrid',
                    array(
                        'label' => $view->translate('Choose from below what you want to display with listing title for Grid View.'),
                        'multiOptions' => array(
                            '1' => $view->translate("Editor Review's Bottom line (If there is no Editor Review, then Listing's description will be displayed.)"),
                            '0' => $view->translate("Listing's description"),
                            '2' => $view->translate("Not Any")
                        ),
                        'value' => '2',
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
            ),
        ),
    ),

    array(
        'title' => $view->translate('Listing Profile: Breadcrumb'),
        'description' => $view->translate('Displays breadcrumb of the listing based on the categories. This widget should be placed on the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.list-profile-breadcrumb'
    ),
    array(
        'title' => $view->translate('Listing Profile: Listing Information & Options'),
        'description' => $view->translate('Displays listing profile photo with listing information and various action links that can be performed on the Listings from their Profile page (edit, delete, tell a friend, share, etc.). You can manage the Action Links available in this widget from the Menu Editor section by choosing Multiple Listing Types - Listing Profile Page Options Menu. You can choose various information options from the Edit settings of this widget. This widget should be placed on the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
       // 'autoEdit' => true,
        'name' => 'sitereview.list-information-profile',
        'defaultParams' => array(
            'title' => '',
            'showContent' => array("photo", "title", "postedBy", "postedDate", "viewCount", "commentCount", "tags", "endDate", "location", "phone", "email", "website", "price", "description", "likeButton", "newlabel", "sponsored", "featured", "reviewCreate", "wishlist")
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'showContent',
                    array(
                        'label' => $view->translate('Select the information options that you want to be available in this block.'),
                        'multiOptions' => array("photo" => "Photo", "title" => "Title", "postedBy" => "Posted By", "postedDate" => "Posted Date", "viewCount" => "Views", "likeCount" => "Likes", "commentCount" => "Comments", "tags" => "Tags", "endDate" => "End Date", "location" => "Location", "phone" => "Phone", "email" => "Email", "website" => "Website", "price" => "Price", "description" => "About / Description", "newlabel" => "New Label", "sponsored" => "Sponsored", "featured" => "Featured", "reviewCreate" => "Write a review","wishlist" => "Add to Wishlist"),
                    ),
                ),
								$ratingTypeElement,
                array(
                    'Radio',
                    'like_button',
                    array(
                        'label' => $view->translate('Do you want to enable Like button in this block?'),
                        'multiOptions' => $show_like_button,
                        'value' => $default_value,
                    ),
                ),
            )
        )
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
            'titleCount' => ""
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
                    array()
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
        'title' => $view->translate('Listing Profile: Specifications'),
        'description' => $view->translate('Displays the Questions added from the "Profile Fields" section in the Admin Panel. This widget should be placed in the Tabbed Blocks area of Multiple Listing Types - Listings Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.specification-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Specs'),
            'titleCount' => true
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
    ),
    array(
        'title' => $view->translate('Listing Profile: Listing Photos'),
        'description' => $view->translate('This widget forms the Photos tab on the Listing Profile page and displays the photos of the listing. This widget should be placed in the Tabbed Blocks area of the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.photos-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Photos'),
            'titleCount' => true
        ),
        'adminForm' => array(
					'elements' => array(
							array(
									'Text',
									'itemCount',
									array(
											'label' => $view->translate('Count'),
											'description' => $view->translate('(Number of photos to show)'),
											'value' => 20,
									)
							),
						),
				),
    ),
    array(
        'title' => $view->translate('Listing Profile: Listing Videos'),
        'description' => $view->translate('This widget forms the Videos tab on the Listing Profile page and displays the videos of the listing. This widget should be placed in the Tabbed Blocks area of the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.video-sitereview',
        'defaultParams' => array(
            'title' => 'Videos',
            'titleCount' => true
        ),
        'adminForm' => array(
            'elements' => array(
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
        'title' => $view->translate('Listing Profile: Overview'),
        'description' => $view->translate('This widget forms the Overview tab on the Listing Profile page and displays the overview of the listing, which the owner has created using the editor in listing dashboard. This widget should be placed in the Tabbed Blocks area of the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.overview-sitereview',
        'defaultParams' => array(
            'title' => $view->translate('Overview'),
            'titleCount' => true
        ),
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'showAfterEditorReview',
                    array(
                        'label' => $view->translate('Do you want to display this block even when the Overview is shown in "Listing Profile: Editor Review / Overview / Description" widget?'),
                        'multiOptions' => array(
                            2 => $view->translate('Yes, always display this block.'),
                            1 => $view->translate('No, display this block when Overview is not displayed in that widget.'),
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
        'title' => 'Listing Profile: Description',
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
//                array(
//                    'Radio',
//                    'loaded_by_ajax',
//                    array(
//                        'label' => $view->translate('Widget Content Loading'),
//                        'description' => $view->translate('Do you want the content of this widget to be loaded via AJAX, after the loading of main webpage content? (Enabling this can improve webpage loading speed. Disabling this would load content of this widget along with the page content.)'),
//                        'multiOptions' => array(
//                            1 => $view->translate('Yes'),
//                            0 => $view->translate('No')
//                        ),
//                        'value' => 1,
//                    )
//                ),
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
                    'columnHeight',
                    array(
                        'label' => $view->translate('Column Height For Grid View.'),
                        'value' => '228',
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
                    'viewType',
                    array(
                        'label' => $view->translate('Display Type'),
                        'multiOptions' => array(
														'listview' => $view->translate('List View'),
                            'gridview' => $view->translate('Grid View'),
                        ),
                        'value' => '0',
                    )
                ),
                array(
                    'Text',
                    'columnHeight',
                    array(
                        'label' => $view->translate('Column Height For Grid View.'),
                        'value' => '228',
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
        'title' => $view->translate('Listing Profile: "Write a Review" Button for Listings'),
        'description' => $view->translate('This is the "Write a Review" Button to be placed on Multiple Listing Types - Listing Profile page. When clicked, users will be redirected to write review for the listing being viewed. The best place to put this widget is right above the Tabbed Block of the Review: Listing Profile Page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.review-button',
        'defaultParams' => array(
            'title' => '',
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
            'titleCount' => true
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
                    'columnHeight',
                    array(
                        'label' => $view->translate('Column Height For Grid View.'),
                        'value' => '228',
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
        'title' => $view->translate('Listing Profile: User Reviews'),
        'description' => $view->translate('This widget forms the User Reviews tab on the Multiple Listing Types - Listing Profile page and displays all the reviews written by the users of your site for the Listing being viewed. This widget should be placed in the Tabbed Blocks area of the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.sitemobile-user-sitereview',
        'defaultParams' => array(
            'title' => $view->translate("User Reviews"),
            'titleCount' => "true"
        ),
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
			'title' => $view->translate('Listing / Review Profile: Where to Buy'),
			'description' => $view->translate('Displays the Where to Buy options for the listing being viewed. You can place this widget on Multiple Listing Types - Review Profile page, to display Where to Buy options for the listing for which the current review is written. This widget should be placed on the Multiple Listing Types - Review Profile or Multiple Listing Types - Listing Profile pages in right / left column or in the Tabbed Blocks area of Multiple Listing Types - Listings Profile page.'),
			'category' => 'Multiple Listing Types',
			'type' => 'widget',
			'name' => 'sitereview.price-info-sitereview',
			'defaultParams' => array(
					'title' => $view->translate('Where to Buy'),
					'titleCount' => true,
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
    ),
    array(
        'title' => 'Reviews Discussion Topic View',
        'description' => "This widget should be placed on the Reviews Discussion Topic View Page.",
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.discussion-content',
        'defaultParams' => array(
            'title' => '',
            'titleCount' => true,
        ),
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
        'title' => $view->translate('Editor Profile: Editor’s Member Profile Photo, Name, Description and Designation'),
        'description' => $view->translate('Displays Editors’ member profile photo, name, about, details and designation on their editor profile. This widget should be placed on Multiple Listing Types - Editor Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.editor-photo-sitereview',
        'defaultParams' => array(
            'title' => '',
            'showContent' => array("photo", "title", "about", "details", "designation", "forEditor", "emailMe")
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'showContent',
                    array(
                        'label' => $view->translate('Select the information options that you want to be available in this block.'),
                        'multiOptions' => array("photo" => "Photo", "title" => "Title", "about" => "About", "details" => "Description", "designation" => "Designation", "forEditor" => "For Editor", "emailMe" => "Email Me"),
                    ),
                ),
            )
        )
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
                            '1' => $view->translate('Currently logged-in member’s friends.'),
                            '0' => $view->translate('Currently logged-in member.'),
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
                    'columnHeight',
                    array(
                        'label' => $view->translate('Column Height For Grid View.'),
                        'value' => '228',
                    )
                ),
                $statisticsElement,
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
//    array(
//        'title' => $view->translate('Editor / Member Profile: Profile Reviews'),
//        'description' => $view->translate('Displays a list of all the reviews written by the editors / members of your site whose profile is being viewed. From Edit settings of this widget, you can choose to show Editor reviews or User Reviews in this widget. This widget should be placed in the Tabbed Blocks area of Multiple Listing Types - Editor Profile page or Member Profile page. '),
//        'category' => 'Multiple Listing Types',
//        'type' => 'widget',
//        'name' => 'sitereview.editor-profile-reviews-sitereview',
//        'autoEdit' => true,
//        'defaultParams' => array(
//            'title' => $view->translate("Reviews"),
//            'titleCount' => "",
//        ),
//        'adminForm' => array(
//            'elements' => array(
//                array(
//                    'Select',
//                    'type',
//                    array(
//                        'label' => $view->translate('Review Type'),
//                        'description' => $view->translate('Choose the type of reviews that you want to display in this widget.'),
//                        'multiOptions' => array(
//                            'user' => 'User Reviews',
//                            'editor' => 'Editor Reviews'
//                        ),
//                        'value' => 'user',
//                    ),
//                ),
//                array(
//                    'Radio',
//                    'onlyListingtypeEditorReviews',
//                    array(
//                        'label' => $view->translate('Do you want to show user reviews from all listing types?'),
//                        'multiOptions' => array(
//                            1 => $view->translate('Yes'),
//                            0 => $view->translate('No, show user reviews only from those listing types for which the member is editor of.')
//                        ),
//                        'value' => 1,
//                    ),
//                ),
//                array(
//                    'Text',
//                    'itemCount',
//                    array(
//                        'label' => $view->translate('Count'),
//                        'description' => $view->translate('(number of reviews to show)'),
//                        'value' => 10,
//                    )
//                ),
//            ),
//        ),
//    ),
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
        'title' => $view->translate('Content Profile: Content Likes'),
        'description' => $view->translate('Displays a list of all the users have Liked the content on which this widget is placed. This widget should be placed on any content’s profile / view page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'seaocore.sitemobile-people-like',
				'defaultParams' => array(
            'title' => "Member Likes",
            'titleCount' => "true",
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
        'title' => $view->translate('Listing Profile: Listing User Ratings'),
        'description' => $view->translate('This widget displays the overall rating given to the listing by member of your site and other users .This widget should be placed in the left column on the Multiple Listing Types - Listing Profile page.'),
        'category' => 'Multiple Listing Types',
        'type' => 'widget',
        'name' => 'sitereview.user-ratings',
        'defaultParams' => array(
            'title' => 'Ratings',
            'titleCount' => true,
        ),
        'adminForm' => array(
        )
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
						$listingTypeElement1,
						$ratingTypeElement,
//						$detactLocationElement,
//						$defaultLocationDistanceElement,                
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
            ),
        ),
    ),
);

?>