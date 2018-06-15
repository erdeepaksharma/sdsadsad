<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Contactinfo.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Contactinfo extends Engine_Form {

  public function init() {

    $sitereview = Engine_Api::_()->getItem('sitereview_listing', Zend_Controller_Front::getInstance()->getRequest()->getParam('listing_id', null));
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);
    $listing_singular_lc = strtolower($listingtypeArray->title_singular);
    $contact_detail_array = (array) $listingtypeArray->contact_detail;
    //INITIALIZATION
    $show_phone = $show_email = $show_website = 0;

    if (in_array("phone", $contact_detail_array)) {
      $show_phone = 1;
    }
    if (in_array("email", $contact_detail_array)) {
      $show_email = 1;
    }
    if (in_array("website", $contact_detail_array)) {
      $show_website = 1;
    }

    if ($show_phone || $show_email || $show_website) {
      $this->setTitle('Contact Details')
              ->setDescription("Contact information will be displayed on your $listing_singular_lc profile.")
              ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
              ->setAttrib('name', 'contactinfo');

      if ($show_phone) {
        $this->addElement('Text', 'phone', array(
            'label' => 'Phone:',
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
                new Engine_Filter_StringLength(array('max' => '63')),
                )));
      }

      if ($show_email) {
        $this->addElement('Text', 'email', array(
            'label' => 'Email:',
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
                new Engine_Filter_StringLength(array('max' => '127')),
                )));
      }

      if ($show_website) {
        $this->addElement('Text', 'website', array(
            'label' => 'Website:',
            'filters' => array(
                'StripTags',
                new Engine_Filter_Censor(),
                new Engine_Filter_StringLength(array('max' => '127')),
                )));
      }

      if ($show_phone || $show_email || $show_website)
        $this->addElement('Button', 'submit', array(
            'label' => 'Save Details',
            'type' => 'submit',
            'ignore' => true,
        ));
    }
    else {

      $this->addElement('Dummy', 'option', array(
          'description' => '<div class="tip"><span>Admin has not choose any option to show contact detail.</span></div>',
      ));
      $this->getElement('option')->getDecorator('Description')->setOptions(array('placement', 'PREPEND', 'escape' => false));
    }
  }

}