<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Add.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Wishlist_Add extends Engine_Form {

  public function init() {

    $this->setTitle('Add To Wishlist')
            ->setAttrib('id', 'form-upload-wishlist')
            ->setAttrib('enctype', 'multipart/form-data');

    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    $wishlistDatas = Engine_Api::_()->getDbtable('wishlists', 'sitereview')->getUserWishlists($viewer_id);
    $wishlistDatasCount = Count($wishlistDatas);
    $listing_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listing_id', null);
    $listing = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
    $listingtype_id = Engine_Api::_()->getDbtable('listings', 'sitereview')->getListingTypeId($listing_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $listing_plural_lc = strtolower($listingtypeArray->title_plural);
    $listing_singular_lc = strtolower($listingtypeArray->title_singular);
    if ($wishlistDatasCount >= 1) {
      $this->setDescription("Please select the wishlists in which you want to add this $listing_singular_lc.");
    }

    $wishlistIdsDatas = Engine_Api::_()->getDbtable('wishlistmaps', 'sitereview')->pageWishlists($listing_id, $viewer_id);

    if (!empty($wishlistIdsDatas)) {
      $wishlistIdsDatas = $wishlistIdsDatas->toArray();
      $wishlistIds = array();
      foreach ($wishlistIdsDatas as $wishlistIdsData) {
        $wishlistIds[] = $wishlistIdsData['wishlist_id'];
      }
    }

    foreach ($wishlistDatas as $wishlistData) {
      if (in_array($wishlistData->wishlist_id, $wishlistIds)) {
        $this->addElement('Checkbox', 'inWishlist_' . $wishlistData->wishlist_id, array(
            'label' => $wishlistData->title,
            'value' => 1,
        ));
      } else {
        $this->addElement('Checkbox', 'wishlist_' . $wishlistData->wishlist_id, array(
            'label' => $wishlistData->title,
            'value' => 0,
        ));
      }
    }

    if ($wishlistDatasCount >= 1) {
      $this->addElement('dummy', 'dummy_text', array('label' => "You can also add this $listing_singular_lc in a new wishlist below:"));
    } else {
      $this->addElement('dummy', 'dummy_text', array('label' => "You have not created any wishlist yet. Get Started by creating and adding $listing_plural_lc."));
    }

    if ($wishlistDatasCount) {
      $this->addElement('Text', 'title', array(
          'label' => 'Wishlist Name',
          'maxlength' => '63',
          'filters' => array(
            'StripTags',
              new Engine_Filter_Censor(),
              new Engine_Filter_StringLength(array('max' => '63')),
          )
      ));
    } else {
      $this->addElement('Text', 'title', array(
          'label' => 'Wishlist Name',
          'maxlength' => '63',
          'required' => true,
          'allowEmpty' => false,
          'filters' => array(
            'StripTags',
              new Engine_Filter_Censor(),
              new Engine_Filter_StringLength(array('max' => '63')),
          )
      ));
    }

    $this->addElement('Textarea', 'body', array(
        'label' => 'Description',
        'maxlength' => '512',
        'filters' => array(
          'StripTags',
            new Engine_Filter_Censor(),
            new Engine_Filter_StringLength(array('max' => '512')),
        )
    ));

    $availableLabels = array(
        'everyone' => 'Everyone',
        'registered' => 'All Registered Members',
        'owner_network' => 'Friends and Networks',
        'owner_member_member' => 'Friends of Friends',
        'owner_member' => 'Friends Only',
        'owner' => 'Just Me'
    );

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('sitereview_wishlist', $viewer, 'auth_view');
    $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));
    $orderPrivacyHiddenFields = 786590;
    if (count($viewOptions) == 1) {
      $this->addElement('hidden', 'auth_view', array('value' => key($viewOptions), 'order' => ++$orderPrivacyHiddenFields));
    }
    elseif (count($viewOptions) < 1) {
      $this->addElement('hidden', 'auth_view', array('value' => 'everyone', 'order' => ++$orderPrivacyHiddenFields));
    } else {
      $this->addElement('Select', 'auth_view', array(
          'label' => 'View Privacy',
          'description' => 'Who may see this wishlist?',
          'multiOptions' => $viewOptions,
          'value' => key($viewOptions),
      ));
      $this->auth_view->getDecorator('Description')->setOption('placement', 'append');
    }

    $this->addElement('Button', 'submit', array(
        'label' => 'Save',
        'ignore' => true,
        'decorators' => array('ViewHelper'),
        'type' => 'submit'
    ));

    $this->addElement('Cancel', 'cancel', array(
        'prependText' => ' or ',
        'label' => 'cancel',
        'link' => true,
        'onclick' => "javascript:parent.Smoothbox.close();",
        'decorators' => array(
            'ViewHelper'
        ),
    ));

    $this->addDisplayGroup(array(
        'submit',
        'cancel'
            ), 'buttons', array(
        'decorators' => array(
            'FormElements',
            'DivDivDivWrapper',
        ),
    ));
  }

}