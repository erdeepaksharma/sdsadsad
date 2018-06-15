<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Search.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Wishlist_Search extends Engine_Form {

  public function init() {

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $this->setAttribs(array(
                'id' => 'filter_form',
                'class' => 'global_form_box wishlist_search_form',
            ))
            ->setMethod('GET')
            ->setAction($view->url(array(), "sitereview_wishlist_general", true));

    $this->addElement('Text', 'search', array(
        'label' => "Search",
                   'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),
    ));
    
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    if($viewer_id) {
      $this->addElement('Select', 'search_wishlist', array(
          'label' => 'Wishlists',
          'multiOptions' => array(
              '' => '',
              'my_wishlists' => 'My Wishlists',
              'friends_wishlists' => 'My Friends Wishlists',
              'like_wishlists' => 'Wishlists I Liked',
              'follow_wishlists' => 'Wishlists Followed by me',
          ),
          'onchange' => 'showMemberNameSearch();',
      )); 
    }
    
    $this->addElement('Text', 'text', array(
        'label' => "Member’s Name / Email",
                   'filters' => array(
                     'StripTags',
                      new Engine_Filter_Censor(),
                    ),
    ));    

    $this->addElement('Select', 'orderby', array(
        'label' => 'Browse By',
        'multiOptions' => array(
						'wishlist_id' => 'Most Recent',
            'total_item' => 'Maximum Entries',
            'like_count' => 'Most Liked',
            'view_count' => 'Most Viewed',
            'follow_count' => 'Most Followed',
        ),
   //     'onchange' => 'this.form.submit();',
    ));

    $this->addElement('hidden', 'viewType', array(
      'order' => 6001,
        'value'=>'grid'
    ));

    $this->addElement('Button', 'done', array(
        'label' => 'Search',
        'type' => 'Submit',
    //    'onclick' => 'this.form.submit();',
    ));
  }

}
