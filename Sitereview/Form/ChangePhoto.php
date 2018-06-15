<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Sitereview
 * @copyright  Copyright 2012-2013 BigStep Technologies Pvt. Ltd.
 * @license    http://www.socialengineaddons.com/license/
 * @version    $Id: ChangePhoto.php 6590 2013-04-01 00:00:00Z SocialEngineAddOns $
 * @author     SocialEngineAddOns
 */
class Sitereview_Form_ChangePhoto extends Engine_Form {

  public function init() {

    $listing_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listing_id', null);
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
    $listingtype_id = $sitereview->listingtype_id;

    $this->setTitle("Edit Profile Picture")
            ->setAttrib('enctype', 'multipart/form-data')
            ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
            ->setAttrib('name', 'EditPhoto');

    $this->addElement('Image', 'current', array(
        'label' => 'Current Photo',
        'ignore' => true,
        'decorators' => array(array('ViewScript', array(
                    'viewScript' => '_formEditImage.tpl',
                    'class' => 'form element',
                    'testing' => 'testing'
            )))
    ));
    Engine_Form::addDefaultDecorators($this->current);

    $this->addElement('File', 'Filedata', array(
        'label' => 'Choose New Photo',
        'destination' => APPLICATION_PATH . '/public/temporary/',
        'validators' => array(
            array('Extension', false, 'jpg,jpeg,png,gif'),
        ),
        'onchange' => 'javascript:uploadPhoto();'
    ));
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $this->addElement('Dummy', 'choose', array(
        'label' => 'Or',
        'description' => "<a href='" . $view->url(array('listing_id' => $listing_id, "change_url" => 1), "sitereview_albumspecific_listtype_$listingtype_id", true) . "'>" . Zend_Registry::get('Zend_Translate')->_('Choose From Existing Pictures') . "</a>",
    ));
    $this->getElement('choose')->getDecorator('Description')->setOptions(array('placement', 'APPEND', 'escape' => false));

    $this->addElement('Hidden', 'coordinates', array(
       'order' => 9865,
        'filters' => array(
            'HtmlEntities',
        )
    ));

    //GET LISTING TYPE ID
    $listingtype_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('listingtype_id', null);

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $url = $view->url(array('action' => 'remove-photo', 'listing_id' => $listing_id), "sitereview_specific_listtype_$listingtype_id", true);
    $sitereview = Engine_Api::_()->getItem('sitereview_listing', $listing_id);
    if ($sitereview->photo_id != 0) {

      $this->addElement('Button', 'remove', array(
          'label' => 'Remove Photo',
          'onclick' => "removePhotoListing('$url');",
          'decorators' => array(
              'ViewHelper',
          ),
      ));

      $url = $view->url(array('listing_id' => $listing_id, 'slug' => $sitereview->getSlug()), "sitereview_entry_view_listtype_$listingtype_id", true);

      $this->addElement('Cancel', 'cancel', array(
          'label' => 'cancel',
          'prependText' => ' or ',
          'link' => true,
          'onclick' => "removePhotoListing('$url');",
          'decorators' => array(
              'ViewHelper',
          ),
      ));

      $this->addDisplayGroup(array('remove', 'cancel'), 'buttons', array());
    }
  }

}