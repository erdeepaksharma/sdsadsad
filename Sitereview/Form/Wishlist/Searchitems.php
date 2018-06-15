<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Searchitems.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Wishlist_Searchitems extends Engine_Form {

  public function init() {

    $this->setAttribs(array(
                'id' => 'wishlist_items_filter_form',
                'class' => 'global_form_box',
            ))
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    parent::init();

    $this->addElement('Text', 'search', array(
        'label' => 'Search:',
                   'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),
    ));

    $params = array();
    $params['visible'] = $params['wishlist'] = 1;
    $ListingTypesArray = Engine_Api::_()->getDbtable('listingtypes', 'sitereview')->getListingTypesArray(0, 1, $params);

    if (Count($ListingTypesArray) >= 2) {
      $this->addElement('Select', 'listingtype_id', array(
          'label' => 'Listing Type:',
          'multiOptions' => $ListingTypesArray,
          'onchange' => "addOptions(this.value, 'listingtype_id', 'category_id', 0);",
      ));
    } else {
      $this->addElement('Hidden', 'listingtype_id', array(
        'order' => 6001,
          'value' => 1,
      ));
    }

    $this->addElement('Select', 'category_id', array(
        'RegisterInArrayValidator' => false,
        'decorators' => array(array('ViewScript', array(
                    'viewScript' => 'application/modules/Sitereview/views/scripts/wishlist/_browse_search_category.tpl',
                    'class' => 'form element')))
    ));

    $this->addElement('Select', 'orderby', array(
        'label' => 'Browse By:',
        'multiOptions' => array(
            'rating_avg' => 'Most Rated (Overall)',
            'rating_editor' => 'Most Rated (Editor)',
            'rating_users' => 'Most Rated (Users)',
            'review_count' => 'Most Reviewed',
            'date' => 'Recently Added',
            'view_count' => 'Most Viewed',
            'like_count' => 'Most Liked',
            'comment_count' => 'Most Commented',
        ),
        'value' => 'date'
    ));
    $this->addElement('hidden', 'viewType', array(
      'order' => 6002,
        'value' => 'pin'
    ));

    $this->addElement('Button', 'done', array(
        'label' => 'Search',
        'type' => 'Submit',
        'ignore' => true,
    ));
  }

}