<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Settings.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_Compare_Settings extends Engine_Form {

  public function init() {

    $this->setTitle('Comparison Settings');
    $i=6780;
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $listingtype_id = $request->getParam('listingtype_id', 1);
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $listingtype_id);
    $listingTypesName = $listingtypeArray->title_plural;
    $this->addElement('Select', 'listtype_id', array(
        'label' => "Listing Type",
        'value' => $listingtype_id,
        'onchange' => 'changeCategory(this)',
    ));
    if (empty($listingtypeArray->compare)) {
      $this->addElement('Dummy', 'note', array(
          'description' => '<div class="tip"><span>' . Zend_Registry::get('Zend_Translate')->_("You have not allowed comparison of listings for this listing type. Please go to the 'Manage Listing Types' section of this plugin to allow the comparison by editing this listing type.") . '</span></div>',
          'decorators' => array(
              'ViewHelper', array(
                  'description', array('placement' => 'APPEND', 'escape' => false)
          ))
      ));
    } else {
      $categories = Engine_Api::_()->getDbtable('categories', 'sitereview')->getCategoriesList($listingtype_id, 0, array('category_id'));
      $categoriesCount = count($categories);

      if (empty($categoriesCount)) {

        $this->addElement('Dummy', 'note', array(
            'description' => '<div class="tip"><span>' . Zend_Registry::get('Zend_Translate')->_("You have not yet created any category for '$listingTypesName' yet. Please create some categories for this listing type from 'Categories' section, to configure its comparison settings.") . '</span></div>',
            'decorators' => array(
                'ViewHelper', array(
                    'description', array('placement' => 'APPEND', 'escape' => false)
            ))
        ));
      } else {

        $this->addElement('Select', 'category_id', array(
            'label' => "Category",
            'value' => 1,
            'onchange' => 'changeCategory(this)',
        ));
        $this->addElement('Select', 'subcategory_id', array(
            'label' => "Sub-category",
            'value' => 1,
            'onchange' => 'changeCategory(this)',
        ));
        $this->addElement('Select', 'subsubcategory_id', array(
            'label' => "3rd Level Category",
            'value' => 1,
            'onchange' => 'changeCategory(this)',
        ));
        if ($listingtypeArray->reviews == 1 || $listingtypeArray->reviews == 3) {
          $this->addElement('Checkbox', 'editor_rating', array(
              'label' => "Show Editor Rating.",
              'description' => "Editor Rating",
              'value' => 1,
              'onchange' => 'toggleEditorParm(this)',
          ));
        } else {
          $this->addElement('hidden', 'editor_rating', array(
              'order' => $i++,
              'value' => 0,
          ));
        }
        $this->addElement('MultiCheckbox', 'editor_rating_fields', array(
            'label' => "Editor Rating Parameters",
            'description' => 'Choose the rating parameters (rated by editors) from below that you want to display under the "Editor Ratings" section on listings comparison page.',
            'multiOptions' => array()
        ));
        if ($listingtypeArray->reviews == 2 || $listingtypeArray->reviews == 3) {
          $this->addElement('Checkbox', 'user_rating', array(
              'label' => "Show User Ratings.",
              'description' => "User Ratings",
              'value' => 1,
              'onchange' => 'toggleUserParm(this)',
          ));
        } else {
          $this->addElement('hidden', 'user_rating', array(
             'order' => $i++,
              'value' => 0,
          ));
        }
        $this->addElement('MultiCheckbox', 'user_rating_fields', array(
            'label' => "User Rating Parameters",
            'description' => 'Choose the rating parameters (rated by users) from below that you want to display under the "User Ratings" section on listings comparison page.',
            'multiOptions' => array()
        ));
        $this->addElement('Dummy', 'field_dummy_1', array(
            'label' => "Listing Information",
            'description' => 'Choose the options from below that you want to display under the "Information" section on listings comparison page.)'
        ));
        if (isset($listingtypeArray->show_tag) && !empty($listingtypeArray->show_tag)) {
          $this->addElement('Checkbox', 'tags', array(
              'label' => "Tags",
              'value' => 1,
          ));
        }
        if (!empty($listingtypeArray->price)) {
          $this->addElement('Checkbox', 'price', array(
              'label' => "Price",
              'value' => 1,
          ));
        } else {
          $this->addElement('hidden', 'price', array(
             'order' => $i++,
              'label' => "Price",
              'value' => 0,
          ));
        }
        if (!empty($listingtypeArray->location)) {
          $this->addElement('Checkbox', 'location', array(
              'label' => "Location",
              'value' => 1,
          ));
        } else {
          $this->addElement('hidden', 'location', array(
             'order' => $i++,
              'value' => 0,
          ));
        }
        $this->addElement('Dummy', 'field_dummy_3', array(
            'label' => "",
        ));

        $this->addElement('MultiCheckbox', 'custom_fields', array(
            'multiOptions' => array()
        ));

        $this->addElement('Dummy', 'field_dummy_2', array(
            'label' => "Listing Statistics",
            'description' => 'Choose the options from below that you want to be display under the "Statistics" section on listings comparison page.)'
        ));
        $this->addElement('Checkbox', 'views', array(
            'label' => "Total Views",
            'value' => 1,
        ));
        $this->addElement('Checkbox', 'comments', array(
            'label' => "Total Comments",
            'value' => 1,
        ));
        $this->addElement('Checkbox', 'likes', array(
            'label' => "Total Likes",
            'value' => 1,
        ));
        if ($listingtypeArray->reviews > 1) {
          $this->addElement('Checkbox', 'reviews', array(
              'label' => "Total Reviews",
              'value' => 1,
          ));
        } else {
          $this->addElement('hidden', 'reviews', array(
             'order' => $i++,
              'value' => 0,
          ));
        }
        $this->addElement('Checkbox', 'summary', array(
            'label' => "Show description of listings.",
            'description' => 'Listing Summary',
            'value' => 1,
        ));
        $this->addElement('Checkbox', 'enabled', array(
            'label' => "Yes, enable comparison of listings.",
            'description' => 'Enable Comparison',
            'value' => 1,
        ));

        $this->addElement('Button', 'save', array(
            'label' => 'Save',
            'type' => 'submit',
            'ignore' => true,
            'decorators' => array('ViewHelper')
        ));
      }
    }
  }

}