<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Siteforum
 * @copyright  Copyright 2015-2016 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: content.php 6590 2015-12-23 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
  $isEnableRating = Engine_Api::_()->getApi('settings', 'core')->getSetting('siteforum.rating', 1);
  
  $popularity_options = array(
                              'creation_date' => 'Recently Created',
                              'modified_date' => 'Recently Updated',
                              'post_count' => 'Maximum Posts',
                              'view_count' => 'Most Viewed',
                              'like_count' => 'Most Liked',
                          );
  $statistics_options = array("viewCount" => "Views", "postCount" => "Number of posts", "likeCount" => "Total likes");
    if($isEnableRating) {
        $popularity_options = array_merge($popularity_options, array( 'rating' => 'Most Rated'));
        $statistics_options = array_merge($statistics_options, array( "ratings" => "Ratings" ));
    }

    return array(
    array(
        'title' => 'Profile Forum Topics',
        'description' => 'Displays a member\'s forum topics on their profile. You can configure various settings for this widget from the Edit settings section of this widget. This widget should be placed in the Tabbed Blocks area of the Member Profile page.',
        'category' => 'Advanced Forums',
        'type' => 'widget',
        'name' => 'siteforum.profile-siteforum-topics',
        'isPaginated' => true,
        'defaultParams' => array(
            'title' => 'Forum Topics',
            'titleCount' => true,
        ),
        'requirements' => array(
            'subject' => 'user',
        ),
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'popular_criteria',
                    array(
                        'label' => 'Popularity Criteria',
                        'description' => '',
                        'multiOptions' => $popularity_options,
                        'value' => 'creation_date',
                    ),
                ),
                array(
                    'MultiCheckbox',
                    'statistics',
                    array(
                        'label' => 'Choose the statistics that you want to be displayed for the Topic in this block.',
                        'multiOptions' => $statistics_options,
                    ),
                ),
                array(
                    'Text',
                    'itemCountPerPage',
                    array(
                        'label' => 'Number of topics to show per page.',
                        'allowEmpty' => false,
                        'value' => 10,
                    ),
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                ),
                array(
                    'Text',
                    'truncationDescription',
                    array(
                        'label' => 'Topic Description Truncation Limit.[Note: Enter 0 to hide the description.]',
                        'value' => 64,
                        'validators' => array(
                            array('Int', true),
                        ),
                    ),
                ),
            ),
        ),
    ),
    array(
        'title' => 'Profile Forum Posts',
        'description' => 'Displays a member\'s forum posts on their profile. You can configure various settings for this widget from the Edit settings section of this widget. This widget should be placed in the Tabbed Blocks area of the Member Profile page.',
        'category' => 'Advanced Forums',
        'type' => 'widget',
        'name' => 'siteforum.profile-siteforum-posts',
        'isPaginated' => true,
        'autoEdit' => true,
        'defaultParams' => array(
            'title' => 'Forum Posts',
            'titleCount' => true,
        ),
        'requirements' => array(
            'subject' => 'user',
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'popular_criteria',
                    array(
                        'label' => 'Popularity Criteria:',
                        'description' => '',
                        'multiOptions' => array(
                            'creation_date' => 'Recently Created',
                            'modified_date' => 'Recently Updated',
                            'thanks_count' => 'Most Thanked',
                            'like_count' => 'Most Liked',
                        ),
                        'value' => 'creation_date',
                    ),
                ),
                array(
                    'MultiCheckbox',
                    'statistics',
                    array(
                        'label' => 'Choose the statistics that you want to be displayed for the Post in this block.',
                        'multiOptions' => array("thankCount" => "Total Thanks", "likeCount" => "Total Likes"),
                    ),
                ),
                array(
                    'Text',
                    'itemCountPerPage',
                    array(
                        'label' => 'Number of posts to show per page.',
                        'allowEmpty' => false,
                        'value' => 10,
                    ),
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                ),
                array(
                    'Text',
                    'truncationDescription',
                    array(
                        'label' => 'Description Truncation Limit.[Note: Enter 0 to hide the description.]',
                        'value' => 64,
                        'validators' => array(
                            array('Int', true),
                        ),
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => 'Topic View',
        'description' => 'Displays Topic Content. You can configure various settings for this widget from the Edit settings section of this widget. This widget should be placed in the Tabbed Blocks area of the Advanced Forums - Topic View Page.',
        'category' => 'Advanced Forums',
        'type' => 'widget',
        'name' => 'siteforum.topic-view',
        'isPaginated' => true,
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'itemCountPerPage',
                    array(
                        'label' => 'Posts per topic page:',
                        'allowEmpty' => false,
                        'value' => 25,
                    ),
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                ),
                array(
                    'MultiCheckbox',
                    'shareOptions',
                    array(
                        'label' => 'Choose the social share links, you want to display in this block.',
                        'multiOptions' => array("facebook" => "Facebook", "twitter" => "Twitter", "linkedin" => "Linkedin", "google" => "Google+", "community" => "Community"),
                    ),
                ),
              array(
                    'Radio',
                    'topicsorder',
                    array(
                                    'label' => 'Select the order in which topics should be displayed on your website.',
                                    'multiOptions' => array(
                                                    1 => 'Newer to older',
                                                    0 => 'Older to newer'
                                    ),
                                    'value' => 1,
                    )
                ),
            )
        )
    ),
    array(
        'title' => "Forum View",
        'description' => 'Displays topics of forum being currently viewed. You can configure various settings for this widget from the Edit settings section of this widget. This widget should be placed on "Advanced Forums - Forum View Page" and "Advanced Forums - Search Forum\'s Topic Page".',
        'category' => 'Advanced Forums',
        'type' => 'widget',
        'name' => 'siteforum.forum-topics',
        'isPaginated' => true,
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Text',
                    'itemCountPerPage',
                    array(
                        'label' => 'Number of topics to show per page.',
                        'allowEmpty' => false,
                        'value' => 25,
                    ),
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                ),
                array(
                    'Select',
                    'popular_criteria',
                    array(
                        'label' => 'Popularity Criteria',
                        'description' => '',
                        'multiOptions' => $popularity_options,
                        'value' => 'creation_date',
                    ),
                ),
                array(
                    'MultiCheckbox',
                    'statistics',
                    array(
                        'label' => 'Choose the statistics that you want to be displayed for the Topic in this block.',
                        'multiOptions' => $statistics_options,
                    ),
                ),
                array(
                    'Radio',
                    'onlineIcon',
                    array(
                        'label' => 'Show online icon with user photo.',
                        'multiOptions' => array(
                            1 => 'Yes',
                            0 => 'No'
                        ),
                        'value' => 1,
                    )),
            ),
        ),
    ),
    array(
        'title' => 'Breadcrumb Navigation',
        'description' => 'Displays breadcrumb navigation, based on the page being currently viewed. This widget should be placed at "Advanced Forums - Topic View Page", "Advanced Forums - Forum View Page", "Advanced Forums - Forum Topic Create Page", "Advanced Forums - Forum View Page".',
        'category' => 'Advanced Forums',
        'type' => 'widget',
        'name' => 'siteforum.breadcrumb',
        'isPaginated' => true,
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Hidden',
                    'itemCountPerPage',
                    array('order' => 1001)
                ),
              array(
                    'Radio',
                    'showDashboardLink',
                    array(
                        'label' => 'Do you want to show “User Dashboard” link?',
                        'multiOptions' => array(
                            1 => 'Yes',
                            0 => 'No'
                        ),
                        'value' => 1,
                    )),
            )
        ),
    ),
    array(
        'title' => 'Popular Topic Tags',
        'description' => 'Displays popular tags. You can choose to display tags alphabetically from the Edit Settings of this widget. This widget should be placed at "Advanced Forums - Topic Tags Page".',
        'category' => 'Advanced Forums',
        'type' => 'widget',
        'name' => 'siteforum.tags-cloud',
        'isPaginated' => true,
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Hidden',
                    'itemCountPerPage',
                    array('order' => 1002)
                ),
                array(
                    'Radio',
                    'orderingType',
                    array(
                        'label' => 'Do you want to show popular forum topic tags in alphabetical order?',
                        'multiOptions' => array(
                            1 => 'Yes',
                            0 => 'No'
                        ),
                        'value' => '0'
                    )
                ),
                array(
                    'Text',
                    'totalTags',
                    array(
                        'label' => 'Number of tags to show. Enter 0 for displaying all tags.',
                        'value' => 25
                    )
                )
            )
        ),
    ),
    array(
        'title' => 'Forum Statistics',
        'description' => 'Displays forum statistics. You can configure various settings for this widget from the Edit settings section of this widget. If you configure this widget at "Advanced Forums - Forum View Page" than it will show the statistics of forum, being currently viewed.',
        'category' => 'Advanced Forums',
        'type' => 'widget',
        'name' => 'siteforum.forum-statistics',
        'isPaginated' => true,
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Hidden',
                    'itemCountPerPage',
                    array('order' => 1003)
                ),
                array(
                    'MultiCheckbox',
                    'statistics',
                    array(
                        'label' => 'Select statistics you want to show',
                        'multiOptions' => array("totalForums" => "Total Forums", "topicCount" => "Total Topics", "postCount" => "Total Posts", "activeUsers" => "Active Users", "totalUsers" => "Total Users"),
                    ),
                ),
            )
        ),
    ),
    array(
        'title' => 'Forum Categories, Subcategories and Forums',
        'description' => 'Displays forum categories, subcategories and forums. You can configure various settings for this widget from the Edit settings section of this widget. This widget should be placed at Advanced Forums - Forums Home Page.',
        'category' => 'Advanced Forums',
        'type' => 'widget',
        'name' => 'siteforum.forum-categories',
        'isPaginated' => true,
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Radio',
                    'show_expand',
                    array(
                        'label' => 'Do you want to show expand/collapse button with categories and subcategories?',
                        'multiOptions' => array(
                            1 => 'Yes',
                            0 => 'No'
                        ),
                        'value' => 1
                    )),
                array(
                    'Radio',
                    'show_empty_category',
                    array(
                        'label' => 'Do you want all the categories and subcategories to be shown to the users even if they have 0 forums in them?',
                        'multiOptions' => array(
                            1 => 'Yes',
                            0 => 'No'
                        ),
                        'value' => 0,
                    )),
                array(
                    'MultiCheckbox',
                    'show_icon',
                    array(
                        'label' => 'Show icons with:',
                        'multiOptions' => array("category" => "Categories", "subcategory" => "Subcategories", "forum" => "Forums"),
                    ),
                ),
                array(
                    'Text',
                    'truncationLastPost',
                    array(
                        'label' => 'Truncation limit of title of last post.',
                        'value' => 30,
                        'validators' => array(
                            array('Int', true),
                        ),
                    )
                ),
                array(
                    'Hidden',
                    'itemCountPerPage',
                    array('order' => 1004)
                ),
            ),
        )
    ),
    array(
        'title' => 'Quick Navigation',
        'description' => 'Allows you to quickly navigate to forums, its categories and sub-categories via dropdown.',
        'category' => 'Advanced Forums',
        'type' => 'widget',
        'name' => 'siteforum.quick-navigation',
        'isPaginated' => true,
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
              array(
                    'Radio',
                    'show_empty_category',
                    array(
                        'label' => 'Do you want all the categories and subcategories to be shown to the users even if they have 0 forums in them?',
                        'multiOptions' => array(
                            1 => 'Yes',
                            0 => 'No'
                        ),
                        'value' => 0,
                    )),
                array(
                    'Radio',
                    'hierarchy',
                    array(
                        'label' => 'Select the hierarchy that you want to display in this block.',
                        'multiOptions' => array(
                            1 => 'Only Categories',
                            2 => 'Categories and subcategories',
                            3 => 'Categories, subcategories and forums',
                        ),
                        'value' => 3
                    )),
                array(
                    'MultiCheckbox',
                    'show_navigation',
                    array(
                        'label' => 'Choose from below options which you want to enable in this widget:',
                        'multiOptions' => array("navigation" => "Quick Navigation", "dashboard" => "User Dashboard"),
                    ),
                ),
                array(
                    'Hidden',
                    'itemCountPerPage',
                    array('order' => 1005)
                )
            )
        )
    ),
    array(
        'title' => 'Most Popular Posts',
        'description' => 'Displays forums on the basis of the popularity criteria being chosen. You can configure various settings for this widget from the Edit settings section of this widget.',
        'category' => 'Advanced Forums',
        'type' => 'widget',
        'name' => 'siteforum.popular-posts',
        'isPaginated' => true,
        'autoEdit' => true,
        'defaultParams' => array(
            'title' => 'Recent Forum Posts',
        ),
        'requirements' => array(
            'no-subject',
        ),
        'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'popular_criteria',
                    array(
                        'label' => 'Popularity Criteria',
                        'description' => '',
                        'multiOptions' => array(
                            'creation_date' => 'Recently Created',
                            'modified_date' => 'Recently Updated',
                            'thanks_count' => 'Most Thanked',
                            'like_count' => 'Most Liked',
                        ),
                        'value' => 'creation_date',
                    ),
                ),
                array(
                    'MultiCheckbox',
                    'statistics',
                    array(
                        'label' => 'Choose the statistics that you want to be displayed for the posts in this block.',
                        'multiOptions' => array("thankCount" => "Total Thanks", "likeCount" => "Total Likes"),
                    ),
                ),
                array(
                    'Text',
                    'itemCountPerPage',
                    array(
                        'label' => 'Number of topics to show',
                        'allowEmpty' => false,
                        'value' => 5,
                    ),
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                ),
                array(
                    'Text',
                    'truncationDescription',
                    array(
                        'label' => 'Post’s description truncation limit. [Note: Enter 0 to hide the description.]',
                        'value' => 64,
                        'validators' => array(
                            array('Int', true),
                        ),
                    )
                ),
                array(
                    'Text',
                    'truncationLastPost',
                    array(
                        'label' => 'Topic Title Truncation Limit',
                        'value' => 25,
                        'validators' => array(
                            array('Int', true),
                        ),
                    )
                ),
            ),
        ),
    ),
    array(
        'title' => 'Most Popular Topics',
        'description' => 'Displays popular topics. You can configure various settings for this widget from the Edit settings section of this widget.',
        'category' => 'Advanced Forums',
        'type' => 'widget',
        'name' => 'siteforum.popular-topics',
        'isPaginated' => true,
        'autoEdit' => true,
        'defaultParams' => array(
            'title' => 'Recent Forum Topics',
        ),
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'popular_criteria',
                    array(
                        'label' => 'Popularity Criteria',
                        'description' => '',
                        'multiOptions' => $popularity_options,
                        'value' => 'creation_date',
                    ),
                ),
                array(
                    'MultiCheckbox',
                    'statistics',
                    array(
                        'label' => 'Choose the statistics that you want to be displayed for the topics in this block.',
                        'multiOptions' => $statistics_options,
                    ),
                ),
                array(
                    'Text',
                    'itemCountPerPage',
                    array(
                        'label' => 'Number of posts to show',
                        'allowEmpty' => false,
                        'value' => 5,
                    ),
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                ),
                array(
                    'Text',
                    'truncationTitle',
                    array(
                        'label' => 'Title Truncation Limit.',
                        'value' => 25,
                        'validators' => array(
                            array('Int', true),
                        ),
                    ),
                ),
            ),
        ),
        'requirements' => array(
            'no-subject',
        ),
    ),
    array(
        'title' => 'Most Popular Users',
        'description' => ' Displays popular users. You can configure various settings for this widget from the Edit settings section of this widget.',
        'category' => 'Advanced Forums',
        'type' => 'widget',
        'name' => 'siteforum.popular-users',
        'isPaginated' => true,
        'autoEdit' => true,
        'defaultParams' => array(
            'title' => 'Recent Forum Users',
        ),
        'requirements' => array(
            'no-subject',
        ),
        'autoEdit' => true,
        'adminForm' => array(
            'elements' => array(
                array(
                    'Select',
                    'popular_criteria',
                    array(
                        'label' => 'Popularity Criteria',
                        'description' => '',
                        'multiOptions' => array(
                            'topic_count' => 'Most topic posted',
                            'post_count' => 'Maximum Posts',
                            'thank_count' => 'Most Thanks',
                            'reputation_count' => 'Most reputations',
                        ),
                        'value' => 'topic_count',
                    ),
                ),
                array(
                    'Radio',
                    'show_online_user',
                    array(
                        'label' => 'Show online users only?',
                        'multiOptions' => array(
                            1 => 'Yes',
                            0 => 'No'
                        ),
                        'value' => 1,
                    )),
                array(
                    'Radio',
                    'onlineIcon',
                    array(
                        'label' => 'Show online icon with user photo.',
                        'multiOptions' => array(
                            1 => 'Yes',
                            0 => 'No'
                        ),
                        'value' => 1,
                    )),
                array(
                    'Text',
                    'itemCountPerPage',
                    array(
                        'label' => 'Number of users to show',
                        'allowEmpty' => false,
                        'value' => 3,
                    ),
                    'validators' => array(
                        array('Int', true),
                        array('GreaterThan', true, array(0)),
                    ),
                ),
            ),
        ),
    ),
    array(
        'title' => "Search Forum's Topic Form",
        'description' => 'Displays the form for searching forums\'s topic on the basis of various fields and filters. You can configure various settings for this widget from the Edit settings section of this widget.',
        'category' => 'Advanced Forums',
        'type' => 'widget',
        'name' => 'siteforum.browse-search',
        'autoEdit' => true,
        'requirements' => array(
            'no-subject',
        ),
        'adminForm' => array(
            'elements' => array(
              
              array(
                    'Radio',
                    'viewType',
                    array(
                        'label' => 'Show Search Form',
                        'multiOptions' => array(
                            'horizontal' => 'Horizontal',
                            'vertical' => 'Vertical',
                        ),
                        'value' => 'horizontal'
                    )
                ),
              
                array(
                    'Text',
                    'searchWidth',
                    array(
                        'label' => 'Width for search box (It will work only if you have selected \'Horizontal\' option in this setting.)',
                        'value' => 600,
                    )
                ),
               
                array(
                    'Text',
                    'forumWidth',
                    array(
                        'label' => 'Width for forum filtering (It will work only if you have selected \'Horizontal\' option in this setting.)',
                        'value' => 300,
                    )
                ),
            ),
        ),
    ),
);
