<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Create.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_Editors_Create extends Engine_Form {

  public function init() {

    $this->setMethod('post');
    $this->setTitle("Add New Editor")
            ->setDescription('Below, you can use the auto-suggest box to add a member as editor who will be allowed to write editor reviews for listings on your site.')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    $this->addElement('Hidden', 'user_id', array( 'order' => 965,));
    $this->addElement('Text', 'title', array(
        'label' => 'User name',
        'description' => 'Start typing the name of the member.',
        'allowEmpty' => false,
        'required' => true,
    ));

    $this->addElement('Textarea', 'details', array(
        'label' => 'About Editor',
        'description' => "Enter description about the editor. (Note: This description will be displayed in 'Listing Profile: About Editor' and 'Editor / Member Profile: About Editor' widgets. Editors will also be able to write about themselves in the 'Editor / Member Profile: About Editor' widget.)",
    ));

    $this->addElement('Text', 'designation', array(
        'label' => 'Designation',
        'description' => "Enter the designation of the editor. (Note: This designation will be displayed in 'Editor / Member Profile: About Editor' widget.)",
        'maxlength' => 64,
    ));

    $multiOptions = array();
    $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypes();
    foreach ($listingTypes as $listingType) {
      $multiOptions["$listingType->listingtype_id"] = $listingType->title_plural;
    }

    if (Count($listingTypes) > 1) {
      $this->addElement('MultiCheckbox', 'listingtypes', array(
          'label' => 'Listing Types',
          'description' => 'Choose the listing types from below for which editors will be allowed to write editor reviews.',
          'multiOptions' => $multiOptions,
      ));
    }

    $this->addElement('Checkbox', 'email_notify', array(
        'description' => 'Email Notification',
        'label' => 'Send email notification when a new listing is created.',
        'value' => 1,
    ));

    $this->addElement('Button', 'submit', array(
        'label' => 'Add Member',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
  }

}