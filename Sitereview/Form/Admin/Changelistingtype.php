<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Changelistingtype.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Admin_Changelistingtype extends Engine_Form {

  public function init() {

    $this->setMethod('post');
    $this->setTitle("Change Listing Type")
            ->setDescription('Select a listing type for this listing from the field given below and then click on "Save Changes" to save it.');
    
    //GET LISTING ID 
    $listing_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listing_id', null);
    $listing = Engine_Api::_()->getItem('sitereview_listing', $listing_id);

    $listingTypes = Engine_Api::_()->getDbTable('listingtypes', 'sitereview')->getListingTypesArray($listing->listingtype_id, 1);
    
    $this->addElement('Select', 'listingtype_id', array(
        'label' => 'Listing Type',
        'multiOptions' => $listingTypes,
        'required' => true,
        'allowEmpty' => false,
        'value' => 0
    ));

    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'onclick' => 'return check_submit();',
        'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'cancel',
        'link' => true,
        'prependText' => ' or ',        
        'onclick' => 'javascript:parent.Smoothbox.close()',
        'decorators' => array(
            'ViewHelper',
        ),
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $this->getDisplayGroup('buttons');
  }

}