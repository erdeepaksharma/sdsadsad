<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: Upload.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_Photo_SitemobileUpload extends Engine_Form {

  public function init() {

    $sitereview = Engine_Api::_()->getItem('sitereview_listing', Zend_Controller_Front::getInstance()->getRequest()->getParam('listing_id', null));
    Engine_Api::_()->sitereview()->setListingTypeInRegistry($sitereview->listingtype_id);
    $listingtypeArray = Zend_Registry::get('listingtypeArray' . $sitereview->listingtype_id);

    $listing_singular_lc = strtolower($listingtypeArray->title_singular);

    $this
            ->setTitle('Add New Photos')
            ->setDescription("Choose photos on your computer to add to this $listing_singular_lc. (2MB maximum).")
            ->setAttrib('id', 'form-upload')
            ->setAttrib('class', 'global_form sitereview_form_upload')
            ->setAttrib('name', 'albums_create')
            ->setAttrib('enctype', 'multipart/form-data')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    $this->addElement('FancyUpload', 'file');

    $this->addElement('Button', 'submit', array(
        'label' => 'Save Photos',
        'type' => 'submit',
    ));
  }

}