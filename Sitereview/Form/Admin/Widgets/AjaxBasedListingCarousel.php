<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Adsettings.php 2013-09-02 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_Widgets_AjaxBasedListingCarousel extends Core_Form_Admin_Widget_Standard {

  public function init() {
     $i=9876;
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;    
    
    //GET LISTING TYPE TABLE
    $listingTypeTable = Engine_Api::_()->getDbTable('listingtypes', 'sitereview');

    //GET LISTING TYPE COUNT
    $listingTypeCount = $listingTypeTable->getListingTypeCount();
    
    //IS BLOGS LISTING TYPE IS ENABLE
    $blogListingId = $listingTypeTable->select()->from($listingTypeTable->info('name'), 'listingtype_id')
            ->where('title_plural = ?', 'Blogs')
            ->where('visible = ?', '1')
            ->query()->fetchColumn();
    
    if( !empty($blogListingId) )
    {
      $url = $view->url(array(), "sitereview_general_listtype_".$blogListingId, true);
      $titleLinkValue = '<a href="'.$url.'">'.$view->translate("Read More »").'</a>';
    }
    else
      $titleLinkValue = '';

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
             'order' => $i++,
              'label' => $view->translate('Listing Type'),
              'value' => 1,
          )
      );

      $listingTypeElement2 = array(
          'Hidden',
          'listingtype_id',
          array(
             'order' => $i++,
              'label' => $view->translate('Listing Type'),
              'value' => 1,
          )
      );

      $listingTypeCategoryElement = array(
          'Hidden',
          'listingtype_id',
          array(
             'order' => $i++,
              'label' => $view->translate('Listing Type'),
              'value' => 1,
          )
      );
    }
    
    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('sitereview.proximity.search.kilometer', 0)) {
      $locationDescription = "Choose the kilometers within which listings will be displayed. (This setting will only work, if you have chosen 'Yes' in the above setting.)";
      $locationLableS = "Kilometer";
      $locationLable = "Kilometers";
    } else {
      $locationDescription = "Choose the miles within which listings will be displayed. (This setting will only work, if you have chosen 'Yes' in the above setting.)";
      $locationLableS = "Mile";
      $locationLable = "Miles";
    }
    
    $this->addElement('Text', 'titleLink', array(
            'label' => $view->translate('Enter Title Link'),
            'description' => 'If you do not want to show title link, then simply leave this field empty.',
            'value' => $titleLinkValue,
        )
    );
    
    if( $listingTypeCount > 1 )
    {
      $this->addElement('Select', 'listingtype_id', array(
              'label' => $view->translate('Listing Type'),
              'multiOptions' => $listingTypes2,
              'onchange' => "addOptions(this.value, 'listingtype_id', 'category_id', 0);",
              'value' => 0,
          )
      );
    }
    else
    {
       $this->addElement('Hidden', 'listingtype_id', array(
                'order' => $i++,
                'label' => $view->translate('Listing Type'),
                'value' => 1,
            )
        );
    }
    
    $this->addElement('Select', 'ratingType', array(
            'label' => $view->translate('Rating Type'),
            'multiOptions' => array('rating_avg' => $view->translate('Average Ratings'), 'rating_editor' => $view->translate('Only Editor Ratings'), 'rating_users' => $view->translate('Only User Ratings'), 'rating_both' => $view->translate('Both User and Editor Ratings')),
        )
    );
    
    $this->addElement('Select', 'fea_spo', array(
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
    
    $this->addElement('Select', 'category_id', array(
          'RegisterInArrayValidator' => false,
          'decorators' => array(array('ViewScript', array(
                      'viewScript' => 'application/modules/Sitereview/views/scripts/_category.tpl',
                      'class' => 'form element')))
          )
    );
    
    $this->addElement('Text', 'hidden_category_id', array(
        ));

    $this->addElement('Text', 'hidden_subcategory_id', array(
        ));

    $this->addElement('Text', 'hidden_subsubcategory_id', array(
        ));
    
    $this->addElement('Select', 'detactLocation', array(
            'label' => 'Do you want to display listings based on user’s current location?',
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No'
            ),
            'value' => '0'
        )
    );
    
    $this->addElement('Select', 'defaultLocationDistance',array(
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
            
    $this->addElement('Radio', 'viewType', array(
            'label' => $view->translate('Carousel Type'),
            'multiOptions' => array(
                '0' => $view->translate('Horizontal'),
                '1' => $view->translate('Vertical'),
            ),
            'value' => '0',
        )
    );
    
    $this->addElement('Text', 'blockHeight', array(
            'label' => $view->translate('Enter the height of each slideshow item.'),
            'value' => 240,
        )
    );
    
    $this->addElement('Text', 'blockWidth', array(
            'label' => $view->translate('Enter the width of each slideshow item.'),
            'value' => 150,
        )
    );
    
    $this->addElement('Text', 'itemCount', array(
            'label' => $view->translate('Enter number of listings in a Row / Column for Horizontal / Vertical Carousel Type respectively as selected by you from the above setting.'),
            'value' => 3,
        )
    );
    
    $this->addElement('Radio', 'showPagination', array(
            'label' => $view->translate('Do you want to show next / previous pagination?'),
            'multiOptions' => array( 1 => $view->translate('Yes'), 0 => $view->translate('No') ),
            'value' => '1',
        )
    );
    
    $this->addElement('Select', 'popularity', array(
            'label' => $view->translate('Popularity Criteria'),
            'multiOptions' => array(
                'view_count' => $view->translate('Most Viewed'),
                'like_count' => $view->translate('Most Liked'),
                'comment_count' => $view->translate('Most Commented'),
                'review_count' => $view->translate('Most Reviewed'),
                'rating_avg' => $view->translate('Most Rated (Average Rating)'),
                'rating_editor' => $view->translate('Most Rated (Editor Rating)'),
                'rating_users' => $view->translate('Most Rated (User Ratings)'),
                'creation_date' => $view->translate('Most Recent'),
                'modified_date' => $view->translate('Recently Updated'),
              ),
            'value' => 'creation_date',
        )
    );
    
    $this->addElement('MultiCheckbox', 'showOptions', array(
            'label' => $view->translate('Choose the action link or detail to be available for each listing.'),
            'multiOptions' => array("category" => "Category", "rating" => "Rating", "review" => "Review", "compare" => "Compare", "wishlist" => "Add to Wishlist"),
        )
    );
    
    $this->addElement('Radio', 'featuredIcon', array(
            'label' => $view->translate('Do you want to show the featured icon / label. (You can choose the marker from the \'Global Settings\' section in the Admin Panel.)'),
            //'description' => $view->translate('(If selected "No", only one review will be displayed from a reviewer.)'),
            'multiOptions' => array(
                1 => $view->translate('Yes'),
                0 => $view->translate('No')
            ),
            'value' => '1',
        )
    );
    
    $this->addElement('Radio', 'sponsoredIcon', array(
            'label' => $view->translate('Do you want to show the sponsored icon / label. (You can choose the marker from the \'Global Settings\' section in the Admin Panel.)'),
            //'description' => $view->translate('(If selected "No", only one review will be displayed from a reviewer.)'),
            'multiOptions' => array(
                1 => $view->translate('Yes'),
                0 => $view->translate('No')
            ),
            'value' => '1',
        )
    );
    
    $this->addElement('Radio', 'newIcon', array(
            'label' => $view->translate('Do you want to show the new icon / label. (You can choose the marker from the \'Global Settings\' section in the Admin Panel.)'),
            //'description' => $view->translate('(If selected "No", only one review will be displayed from a reviewer.)'),
            'multiOptions' => array(
                1 => $view->translate('Yes'),
                0 => $view->translate('No')
            ),
            'value' => '1',
        )
    );
    
    $this->addElement('Text', 'interval', array(
            'label' => $view->translate('Speed'),
            'description' => $view->translate('(transition interval between two slides in millisecs)'),
            'value' => 300,
        )
    );
    
    $this->addElement('Text', 'truncation', array(
        'label' => $view->translate('Title Truncation Limit'),
        'value' => 50,
        'validators' => array(
            array('Int', true),
            array('GreaterThan', true, array(0)),
        )
    )
  );
  }
}